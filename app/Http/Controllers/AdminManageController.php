<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // 💡 Cache 파사드 추가

class AdminManageController extends Controller
{
    // 1. 관리자 메타데이터 수정 페이지 렌더링
    public function index()
    {
        if (!session('is_admin')) {
            return redirect('/admin/upload'); // 관리자 인증이 안되어있으면 업로드(로그인) 페이지로
        }
        return view('admin.manage');
    }

    // 2. 전체 앨범 및 사진 데이터 불러오기 (API)
    public function getPosts()
    {
        if (!session('is_admin')) return response()->json(['error' => 'Unauthorized'], 403);
        
        // 최신순으로 앨범과 속한 사진들을 모두 가져옵니다.
        $posts = Post::with('photos')->orderBy('created_at', 'desc')->get();
        return response()->json($posts);
    }

    // 3. 앨범(Post) 메타데이터 수정 (API)
    public function updatePost(Request $request, $id)
    {
        if (!session('is_admin')) return response()->json(['error' => 'Unauthorized'], 403);

        try {
            $post = Post::findOrFail($id);

            // 한글/일본어 유니코드 깨짐 방지를 위해 JSON 문자열로 직접 변환 후 디코딩하여 할당
            $safeTitle = json_encode($request->input('title'), JSON_UNESCAPED_UNICODE);
            $safeLocation = json_encode($request->input('location'), JSON_UNESCAPED_UNICODE);

            $post->update([
                'category' => $request->input('category', $post->category),
                'country' => $request->input('country', $post->country),
                'taken_at' => $request->input('taken_at', $post->taken_at),
                'lat' => $request->input('lat', $post->lat),
                'lng' => $request->input('lng', $post->lng),
                'title' => json_decode($safeTitle, true),
                'location' => json_decode($safeLocation, true),
            ]);

            // 💡 앨범이 수정되면 캐시 삭제
            Cache::forget('portfolio_posts');

            return response()->json(['success' => true, 'message' => 'Album updated successfully']);
        } catch (\Exception $e) {
            Log::error("Post Update Error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // 4. 개별 사진(Photo) 메타데이터 및 EXIF 수정 (API)
    public function updatePhoto(Request $request, $id)
    {
        if (!session('is_admin')) return response()->json(['error' => 'Unauthorized'], 403);

        try {
            $photo = Photo::findOrFail($id);

            $safeTitle = json_encode($request->input('title'), JSON_UNESCAPED_UNICODE);
            $safeDesc = json_encode($request->input('description'), JSON_UNESCAPED_UNICODE);

            $photo->update([
                'title' => json_decode($safeTitle, true),
                'description' => json_decode($safeDesc, true),
                'exif_body' => $request->input('exif_body', $photo->exif_body),
                'exif_lens' => $request->input('exif_lens', $photo->exif_lens),
                'exif_focal_length' => $request->input('exif_focal_length', $photo->exif_focal_length),
                'exif_aperture' => $request->input('exif_aperture', $photo->exif_aperture),
                'exif_shutter_speed_num' => $request->input('exif_shutter_speed_num', $photo->exif_shutter_speed_num),
                'exif_shutter_speed_den' => $request->input('exif_shutter_speed_den', $photo->exif_shutter_speed_den),
                'exif_iso' => $request->input('exif_iso', $photo->exif_iso),
            ]);

            return response()->json(['success' => true, 'message' => 'Photo updated successfully']);
        } catch (\Exception $e) {
            Log::error("Photo Update Error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyPost($id)
    {
        if (!session('is_admin')) return response()->json(['error' => 'Unauthorized'], 403);

        try {
            $post = Post::findOrFail($id);
            
            // 앨범에 속한 사진 데이터들도 DB에서 삭제 (S3 파일은 별도 배치나 이벤트로 처리하는 것이 안전함)
            Photo::where('post_id', $id)->delete();
            $post->delete();

            // 💡 앨범이 삭제되어도 캐시 삭제
            Cache::forget('portfolio_posts');

            return response()->json(['success' => true, 'message' => 'Album deleted successfully']);
        } catch (\Exception $e) {
            Log::error("Post Delete Error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}