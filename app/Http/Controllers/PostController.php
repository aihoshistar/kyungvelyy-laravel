<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Photo;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($locale)
    {
        // [수정] 최신 포스트(created_at 기준 내림차순)를 먼저 가져오도록 정렬 추가
        $posts = Post::with(['photos' => function($query) {
            // 포스트 내부의 여러 사진들은 order(순서)에 맞춰 정방향 정렬
            $query->orderBy('id', 'asc');
        }])
        ->orderBy('taken_at', 'desc') // 최신 게시물이 위로 오게 (내림차순)
        ->get();

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


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
