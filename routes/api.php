<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostCommentController;

// TODO remove this on public release, only for testing!
Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!'], 200);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user', [UserController::class, 'show']);
});

Route::get('/users-info', [UserController::class, 'index']);

Route::get('/posts', [PostController::class, 'index']);

Route::post('/posts', [PostController::class, 'store']);

Route::get('/posts/my-posts', [PostController::class, 'getMyPost']);

Route::get('/posts/by-user/{user_id}', [PostController::class, 'getByUserId']);

Route::get('/posts/{id}', [PostController::class, 'show']);

Route::put('/posts/{id}', [PostController::class, 'update']);

Route::delete('/posts/{id}', [PostController::class, 'destroy']);

Route::get('/postcomments', [PostCommentController::class, 'index']);

Route::post('/postcomments', [PostCommentController::class, 'store']);

Route::get('/postcomments/{id}', [PostCommentController::class, 'show']);



