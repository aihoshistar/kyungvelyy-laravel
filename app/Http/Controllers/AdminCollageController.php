<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Photo;
use Illuminate\Http\Request;

class AdminCollageController extends Controller
{
    /**
     * 콜라주 어드민 페이지 렌더링
     */
    public function index()
    {
        return view('admin.collage.index');
    }

    /**
     * Post 비동기 검색 (드롭다운용)
     */
    public function searchPosts(Request $request)
    {
        $query = $request->get('q');
        
        $posts = Post::when($query, function ($q) use ($query) {
                // 다국어 JSON 필드 검색 (필요에 따라 title->ko 등으로 수정)
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get(['id', 'title', 'taken_at']);

        return response()->json($posts);
    }

    /**
     * 특정 Post에 속한 사진 비동기 로드
     */
    public function getPhotos($id)
    {
        $photos = Photo::where('post_id', $id)->orderBy('id', 'asc')->get();
        return response()->json($photos);
    }
}