<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Post;
use App\Models\Photo;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. JSON 파일 읽어오기
        $jsonPath = database_path('data/data.example.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("JSON 파일을 찾을 수 없습니다: {$jsonPath}");
            return;
        }

        $jsonData = File::get($jsonPath);
        $posts = json_decode($jsonData, true);

        // 2. 데이터 순회하며 Insert
        foreach ($posts as $postData) {

            // Post 데이터 생성
            $post = Post::create([
                'slug'         => $postData['id'],
                'category'     => $postData['category'],
                'title'        => $postData['title'], // 배열은 Model의 $casts 덕분에 자동 JSON 저장
                'taken_at'     => $postData['takenAt'],
                'location'     => $postData['location'],
                'country'      => $postData['country'],
                'lat'          => $postData['coordinates']['lat'] ?? null,
                'lng'          => $postData['coordinates']['lng'] ?? null,
                'camera_brand' => $postData['camera'] ?? null,
            ]);

            // Photo 데이터 생성 (1:N)
            foreach ($postData['images'] as $imageData) {

                // --- 데이터 변환 (정규화) ---

                // 1) 종횡비 파싱 ("aspect-[3/2]" -> 1.5, "aspect-square" -> 1.0)
                $aspectRatio = null;
                $aspectStr = $imageData['aspectRatio'] ?? '';
                if ($aspectStr === 'aspect-square') {
                    $aspectRatio = 1.0;
                } elseif (preg_match('/aspect-\[(\d+)\/(\d+)\]/', $aspectStr, $matches)) {
                    if ($matches[2] > 0) {
                        $aspectRatio = round($matches[1] / $matches[2], 3);
                    }
                }

                // 2) EXIF 데이터 파싱
                $exif = $imageData['exif'] ?? [];

                // 초점거리 ("40mm" -> 40)
                $focalLength = null;
                if (!empty($exif['focalLength']) && preg_match('/(\d+(\.\d+)?)/', $exif['focalLength'], $matches)) {
                    $focalLength = (float) $matches[1];
                }

                // 조리개 ("f/4" -> 4.0)
                $aperture = null;
                if (!empty($exif['aperture']) && preg_match('/f\/(\d+(\.\d+)?)/', $exif['aperture'], $matches)) {
                    $aperture = (float) $matches[1];
                }

                // 셔터스피드 ("1/640s" -> num: 1, den: 640)
                $shutterNum = null;
                $shutterDen = null;
                if (!empty($exif['shutterSpeed']) && preg_match('/(\d+)\/(\d+)s/', $exif['shutterSpeed'], $matches)) {
                    $shutterNum = (int) $matches[1];
                    $shutterDen = (int) $matches[2];
                }

                // ISO ("3200" -> 3200)
                $iso = null;
                if (!empty($exif['iso']) && preg_match('/(\d+)/', $exif['iso'], $matches)) {
                    $iso = (int) $matches[1];
                }

                // 사진 데이터 Insert
                Photo::create([
                    'post_id'                  => $post->id,
                    'title'                    => $imageData['title'],
                    'url'                      => $imageData['url'],
                    'aspect_ratio'             => $aspectRatio,
                    'description'              => $imageData['description'] ?? null,
                    'exif_body'                => $exif['body'] ?? null,
                    'exif_lens'                => $exif['lens'] ?? null,
                    'exif_focal_length'        => $focalLength,
                    'exif_aperture'            => $aperture,
                    'exif_shutter_speed_num'   => $shutterNum,
                    'exif_shutter_speed_den'   => $shutterDen,
                    'exif_iso'                 => $iso,
                ]);
            }
        }

        $this->command->info('JSON 데이터 파싱 및 시딩이 완료되었습니다!');
    }
}
