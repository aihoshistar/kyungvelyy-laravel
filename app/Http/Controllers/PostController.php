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
    public function index()
    {
        // Post를 불러올 때 관계된 사진(photos)도 함께 로드합니다.
        $posts = \App\Models\Post::with('photos')->orderBy('taken_at', 'desc')->get();

        // 데이터를 프론트엔드의 JS 변수로 온전히 넘기기 위해 JSON으로 캐스팅합니다.
        return view('welcome', [
            'posts' => $posts
        ]);
    }

    public function toggleReaction(Request $request, Photo $photo) {
        $type = $request->input('type');
        $fingerprint = $request->input('fingerprint');

        $reaction = $photo->reactions()->where('type', $type)->where('fingerprint', $fingerprint)->first();

        if ($reaction) {
            $reaction->delete();
            $status = 'removed';
        } else {
            $photo->reactions()->create(['type' => $type, 'fingerprint' => $fingerprint]);
            $status = 'added';
        }

        return response()->json([
            'status' => $status,
            'counts' => $photo->reaction_counts // 최신 카운트 반환
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
