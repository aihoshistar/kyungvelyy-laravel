<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Photo;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // 💡 캐시 파사드 추가

class PostController extends Controller
{
    /**
     * 메인 갤러리 페이지 렌더링
     */
    public function index($locale)
    {
        // 💡 [핵심] DB 조회 결과를 'portfolio_posts'라는 이름으로 영구 캐싱합니다.
        // 관리자가 사진을 올리거나 수정할 때만 이 캐시가 지워지므로, 
        // 평소 방문자들은 DB를 거치지 않고 즉시(0.001초) 데이터를 받게 됩니다!
        $posts = Cache::rememberForever('portfolio_posts', function () {
            return Post::with(['photos' => function($query) {
                // 포스트 내부의 여러 사진들은 order(순서) 또는 id에 맞춰 정방향 정렬
                $query->orderBy('id', 'asc');
            }])
            ->orderBy('taken_at', 'desc') // 최신 게시물이 위로 오게 (내림차순)
            ->get();
        });

        return view('welcome', compact('posts'));
    }

    /**
     * 특정 사진의 반응 토글 (POST)
     */
    public function toggleReaction(Request $request, Photo $photo)
    {
        $request->validate([
            'type' => 'required|string',
            'fingerprint' => 'required|string',
        ]);

        $type = $request->input('type');
        $fingerprint = $request->input('fingerprint');

        $reaction = $photo->reactions()->where('type', $type)->where('fingerprint', $fingerprint)->first();

        if ($reaction) {
            $reaction->delete();
            $status = 'removed';
        } else {
            $photo->reactions()->create([
                'type' => $type,
                'fingerprint' => $fingerprint
            ]);
            $status = 'added';
        }

        return response()->json([
            'status' => $status,
            'counts' => $photo->reaction_counts // Photo 모델의 Accessor 활용
        ]);
    }

    /**
     * 특정 사진의 최신 반응 카운트 조회 (GET)
     */
    public function getReactions(Photo $photo)
    {
        return response()->json([
            'counts' => $photo->reaction_counts
        ]);
    }
}