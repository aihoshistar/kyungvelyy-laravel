<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdminController extends Controller
{
    public function uploadView(Request $request)
    {
        $password = env('ADMIN_PASSWORD', 'admin1234');
        if ($request->has('key')) {
            if ($request->get('key') === $password) {
                session(['is_admin' => true]);
                return redirect('/admin/upload');
            }
        }

        if (!session('is_admin')) {
            abort(403, 'Unauthorized access.');
        }

        return view('admin.upload');
    }

    public function processUpload(Request $request)
    {
        if (!session('is_admin')) return response()->json(['error' => 'Unauthorized'], 403);

        $request->validate([
            'photo'         => 'required|image|mimes:jpeg,png,jpg|max:51200',
            'location_hint' => 'nullable|string',
            // 프론트엔드에서 post_id를 보냈을 때 해당 id가 posts 테이블에 있는지 검증
            'post_id'       => 'nullable|exists:posts,id' 
        ]);

        try {
            $file = $request->file('photo');
            $originalName = $file->getClientOriginalName();
            $locationHint = $request->input('location_hint', '서울특별시 (Seoul, Korea)');

            // 1. EXIF 데이터 추출
            $exif = @exif_read_data($file->getRealPath());
            $make = $exif['Make'] ?? 'Unknown';
            $model = $exif['Model'] ?? 'Unknown';
            $lens = $exif['UndefinedTag:0xA434'] ?? ($exif['LensModel'] ?? 'Unknown Lens');
            
            $focalLength = $this->evalFraction($exif['FocalLength'] ?? null);
            $aperture = $this->evalFraction($exif['FNumber'] ?? null);
            $iso = $exif['ISOSpeedRatings'] ?? null;
            
            $shutterNum = null; $shutterDen = null;
            if (isset($exif['ExposureTime'])) {
                $parts = explode('/', $exif['ExposureTime']);
                if (count($parts) == 2 && $parts[0] > 0) {
                    $shutterNum = 1;
                    $shutterDen = round($parts[1] / $parts[0]);
                }
            }

            $takenDate = isset($exif['DateTimeOriginal']) ? date('Y-m-d', strtotime($exif['DateTimeOriginal'])) : date('Y-m-d');
            $year = date('Y', strtotime($takenDate));
            $month = date('m', strtotime($takenDate));
            $day = date('d', strtotime($takenDate));

            $lat = $this->getGps($exif['GPSLatitude'] ?? null, $exif['GPSLatitudeRef'] ?? null);
            $lng = $this->getGps($exif['GPSLongitude'] ?? null, $exif['GPSLongitudeRef'] ?? null);

            $cameraBrand = stripos($make, 'nikon') !== false ? 'Nikon' : (stripos($make, 'sony') !== false ? 'Sony' : 'Other');

            // 2. 이미지 최적화
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            
            $width = $image->width();
            $height = $image->height();
            $aspectRatio = $height > 0 ? round($width / $height, 3) : 1.5;

            $image->scaleDown(width: 2560);
            $optimizedData = $image->toJpeg(90)->toString();
            $base64Image = base64_encode($optimizedData);

            // 3. Gemini 2.5 Flash를 이용한 AI 분석
            $geminiKey = env('GEMINI_API_KEY');
            
            if (!$geminiKey) {
                throw new \Exception("GEMINI_API_KEY가 .env 파일에 설정되지 않았습니다.");
            }

            $prompt = "You are a professional photographer's assistant. [User Input Location]: {$locationHint}. Analyze the provided image and generate metadata. The response MUST be a valid JSON object matching exactly this structure: {\"title\": {\"ko\": \"\", \"en\": \"\", \"ja\": \"\"}, \"location\": {\"ko\": \"\", \"en\": \"\", \"ja\": \"\"}, \"description\": {\"ko\": \"\", \"en\": \"\", \"ja\": \"\"}, \"category\": \"Landscape|Cityscape|Portrait\", \"country\": \"English Country Name\"}";

            $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$geminiKey}", [
                'contents' => [
                    ['parts' => [
                        ['text' => $prompt],
                        ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $base64Image]]
                    ]]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error.message', '알 수 없는 API 에러 발생');
                throw new \Exception("Gemini API 오류: " . $errorMsg);
            }

            $jsonText = $response->json('candidates.0.content.parts.0.text', '{}');
            $aiData = json_decode(trim($jsonText), true);

            if (!$aiData || !isset($aiData['title'])) {
                Log::error("Gemini Parse Error. Raw response: " . $jsonText);
                throw new \Exception("Gemini 응답을 JSON으로 변환할 수 없습니다.");
            }

            // 4. 슬러그(ID) 및 S3 키 생성
            $keyword = Str::slug($aiData['title']['en'] ?? 'photo');
            $slug = "{$takenDate}_{$keyword}_" . Str::random(4);
            $s3FileName = "{$slug}.jpg";
            $s3Key = "photo/{$year}/{$month}/{$day}/{$s3FileName}";

            // 5. S3 업로드 검증
            $uploadSuccess = Storage::disk('s3')->put($s3Key, $optimizedData);
            
            if (!$uploadSuccess) {
                throw new \Exception("S3 업로드에 실패했습니다. IAM 권한을 확인하세요.");
            }

            $customBaseUrl = env('AWS_URL', 'https://photo.kkyungvelyy.com');
            $finalUrl = rtrim($customBaseUrl, '/') . '/' . ltrim($s3Key, '/');

            // 6. 다국어 데이터를 유니코드 깨짐 없이 명시적으로 JSON 문자열 변환
            $safeTitle = json_encode($aiData['title'], JSON_UNESCAPED_UNICODE);
            $safeLocation = json_encode($aiData['location'] ?? ['ko'=>'','en'=>'','ja'=>''], JSON_UNESCAPED_UNICODE);
            $safeDesc = json_encode($aiData['description'] ?? ['ko'=>'','en'=>'','ja'=>''], JSON_UNESCAPED_UNICODE);

            // 7. DB 저장 (1:N 매핑의 핵심 로직)
            $postId = $request->input('post_id');
            $post = null;

            if ($postId) {
                // 프론트엔드에서 앨범 ID를 전달했다면 해당 앨범을 찾습니다.
                $post = Post::find($postId);
            }

            if (!$post) {
                // 앨범 ID가 없거나 유효하지 않다면(즉 첫 번째 사진이라면) 새로 앨범을 생성합니다.
                $post = Post::create([
                    'slug'         => $slug,
                    'category'     => $aiData['category'] ?? 'Landscape',
                    'title'        => json_decode($safeTitle, true), 
                    'taken_at'     => $takenDate,
                    'location'     => json_decode($safeLocation, true),
                    'country'      => $aiData['country'] ?? 'Unknown',
                    'lat'          => $lat,
                    'lng'          => $lng,
                    'camera_brand' => $cameraBrand,
                ]);
            }

            // 사진은 기존 앨범이든 새 앨범이든 $post->id 를 부모로 삼아 무조건 생성합니다. (1:N 완성)
            Photo::create([
                'post_id'                  => $post->id,
                'title'                    => json_decode($safeTitle, true),
                'url'                      => $finalUrl,
                'aspect_ratio'             => $aspectRatio,
                'description'              => json_decode($safeDesc, true),
                'exif_body'                => $model,
                'exif_lens'                => $lens,
                'exif_focal_length'        => $focalLength,
                'exif_aperture'            => $aperture,
                'exif_shutter_speed_num'   => $shutterNum,
                'exif_shutter_speed_den'   => $shutterDen,
                'exif_iso'                 => $iso,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully uploaded & analyzed: {$originalName}",
                'post_id' => $post->id, // 프론트엔드에 그룹화된 앨범 ID를 돌려줍니다.
                'data' => $aiData
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function evalFraction($fraction) {
        if (!$fraction) return null;
        $parts = explode('/', $fraction);
        if (count($parts) == 2 && $parts[1] != 0) return $parts[0] / $parts[1];
        return is_numeric($fraction) ? $fraction : null;
    }

    private function getGps($exifCoord, $hemi) {
        if (!$exifCoord || !$hemi) return null;
        $degrees = count($exifCoord) > 0 ? $this->evalFraction($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->evalFraction($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->evalFraction($exifCoord[2]) : 0;
        $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;
        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }
}