<?php

namespace App\Http\Controllers;

use App\Models\PostComment;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $postcomments = PostComment::all();

        // Gib die Posts zurück
        return $postcomments;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        PostComment::create([
            'title' => $validated['title'],
            'content' => $validated['content']
        ]);

        return response()->json(['message' => 'Comment created successfully'], 201); 
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $postcomment = PostComment::find($id);

        return response()->json($postcomment, 200);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PostComment $postComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PostComment $postComment)
    {
        //
    }
}
