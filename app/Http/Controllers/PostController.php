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
    // 💡 [수정] 라우터에서 넘어오는 $slug 와 $photoIndex 파라미터를 받을 수 있도록 추가합니다. (기본값 null)
    /**
     * 메인 갤러리 페이지 렌더링
     */
    public function index($locale, $slug = null, $photoIndex = null)
    {
        // 캐시에서 데이터 가져오기
        $posts = Cache::rememberForever('portfolio_posts', function () {
            return Post::with(['photos' => function($query) {
                $query->orderBy('id', 'asc');
            }])->orderBy('taken_at', 'desc')->get();
        });

        // 💡 [SEO 핵심] 딥링크(Slug) 접속 시 해당 포스트 정보를 별도로 추출
        $seoPost = null;
        if ($slug) {
            $seoPost = $posts->first(function ($p) use ($slug) {
                return $p->slug === $slug || (string)$p->id === $slug;
            });
        }

        // seoPost가 있으면 해당 정보를, 없으면 기본 정보를 뷰로 전달
        return view('welcome', compact('posts', 'seoPost', 'photoIndex'));
    }

    /**
     * 💡 사이트맵(sitemap.xml) 동적 생성
     */
    public function sitemap()
    {
        $posts = Cache::rememberForever('portfolio_posts', function () {
            return Post::with(['photos' => function($query) {
                $query->orderBy('id', 'asc');
            }])->orderBy('taken_at', 'desc')->get();
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach(['ko', 'en', 'ja'] as $locale) {
            $xml .= '<url><loc>' . htmlspecialchars(url("/{$locale}")) . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';
        }

        foreach($posts as $post) {
            $slug = $post->slug ?? $post->id;
            $updatedAt = $post->updated_at ? $post->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString();
            
            foreach($post->photos as $index => $photo) {
                foreach(['ko', 'en', 'ja'] as $locale) {
                    $xml .= '<url>';
                    $xml .= '<loc>' . htmlspecialchars(url("/{$locale}/archive/{$slug}/" . ($index + 1))) . '</loc>';
                    $xml .= '<lastmod>' . $updatedAt . '</lastmod>';
                    $xml .= '<changefreq>weekly</changefreq>';
                    $xml .= '<priority>0.8</priority>';
                    $xml .= '</url>';
                }
            }
        }
        $xml .= '</urlset>';

        if (ob_get_length()) ob_end_clean();

        return response($xml)->header('Content-Type', 'text/xml; charset=utf-8');
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