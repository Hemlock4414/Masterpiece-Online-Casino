<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MemoryCardController;
use App\Http\Controllers\MemoryGameController;
use App\Http\Controllers\MemoryPlayerController;

// TODO remove this on public release, only for testing!
Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!'], 200);
});

// Öffentliche Route für alle Posts (ohne Authentifizierung)

//User

Route::post('/login', [UserController::class, 'login']);

Route::post('/register', [UserController::class, 'register']);

Route::get('/users-info', [UserController::class, 'index']);

//Contact
// Route::post('/contact', [ContactController::class, 'send']);


// Geschützte Routen

Route::group(['middleware' => ['auth:sanctum']], function () {

    //User
    
    Route::get('/user', [UserController::class, 'show']);    
    
    Route::post('/user/update/name', [UserController::class, 'updateUsername']);
    Route::post('/user/update/password', [UserController::class, 'updatePassword']);
    Route::post('/user/update/email', [UserController::class, 'updateEmail']);
    Route::post('/user/update/pic', [UserController::class, 'updateProfilePic']);
    Route::delete('/user/delete', [UserController::class, 'deleteAccount']);
    Route::get('/user', [UserController::class, 'getUser']);

});

// Memory

    //Neues Spiel starten
    Route::post('/memory-games/create', [MemoryGameController::class, 'create']);

    // Spiel anzeigen
    Route::get('/memory-games/{game}', [MemoryGameController::class, 'show']);

    // Spiel beenden
    Route::post('/memory-games/{game}/stop', [MemoryGameController::class, 'stop']);

    // Spieler abrufen
    Route::get('/memory-games/{game}/players', [MemoryPlayerController::class, 'index']); 

    // Spieler hinzufügen
    Route::post('/memory-games/{game}/players', [MemoryPlayerController::class, 'store']); 

    // Punktestand aktualisieren
    Route::put('/memory-games/{game}/players/{player}', [MemoryPlayerController::class, 'update']); 

    // alle Karten eines Memory-Spiels abrufen
    Route::get('/memory-games/{game}/cards', [MemoryCardController::class, 'index']);

    // den Status einer Karte aktualisieren
    Route::post('/memory-games/{game}/cards/flip', [MemoryCardController::class, 'flip']);
    



/* 

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

 */

