<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Hole alle Posts aus der Datenbank
        $posts = Post::all();

        // Gib die Posts zurück
        return $posts;

        // alternativer Einzeiler
        // return Post::all();
    }

    public function getMyPost(Request $request) {
        $posts = Post::where('user_id', '=', $request->user()->id)->get();
        return response()->json(['posts' => $posts], 200);  
    }
    
    public function getMyUserId(Request $request, $user_name) {
        $user = User::where('name', '=', $user_name)->first();

        $posts = $$user->posts();
        return response()->json(['posts' => $posts], 200);  
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Post::create($request->all());
        
            $validated = $request->validate([
                'title' => 'required|string',
                'content' => 'required|string',
            ]);

            Post::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'user_id' => $request->user()->id
            ]);

        return response()->json(['message' => 'Post created successfully'], 201);    
    }

    /** 
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $post = Post::find($id);

        return response()->json($post, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $post = Post::findOrFail($id); // Fehlermeldung inklusive

        if($request->user()->id !== $post->user->id) {
            return response()->json(['message' => 'You are not the owner!'], 402);
        }

        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content']
        ]);

        $post->save();      // aktualisiert final, keine Änderung mehr möglich

        return response()->json(['message' => 'Post updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        if($request->user()->id !== $post->user->id) {
            return response()->json(['message' => 'You are not the owner!'], 402);
        }
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully'], 204);
    }
}
