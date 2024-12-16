<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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
    
    Route::post('/user/update/password', [UserController::class, 'updatePassword']);
    Route::post('/user/update/email', [UserController::class, 'updateEmail']);
    Route::post('/user/update/pic', [UserController::class, 'updateProfilePic']);
    Route::delete('/user/delete/pic', [UserController::class, 'deleteProfilePic']);
    Route::delete('/user/delete', [UserController::class, 'deleteAccount']);
});

// Memory Game

//Neues Spiel erstellen
Route::post('/memory-games/create', [MemoryGameController::class, 'create']);

// Spiel starten
Route::post('/memory-games/{gameId}/start', [MemoryGameController::class, 'start']);

// Spiel beenden
Route::post('/memory-games/{gameId}/stop', [MemoryGameController::class, 'stop']);

// Punktestand aktualisieren
Route::put('/memory-games/{gameId}/players/{player}', [MemoryPlayerController::class, 'update']); 

// Spieler abrufen
Route::get('/memory-games/{gameId}/players', [MemoryPlayerController::class, 'index']); 

// Spieler hinzufügen
Route::post('/memory-games/{gameId}/players', [MemoryPlayerController::class, 'store']); 

// Spielerwechsel
Route::post('/memory-games/{gameId}/next-turn', [MemoryGameController::class, 'nextTurn']);

// Matched Cards aktualisieren
Route::post('/memory-games/{gameId}/cards/match', [MemoryCardController::class, 'updateMatched']);

// alle Karten eines Memory-Spiels abrufen
Route::get('/memory-games/{gameId}/cards', [MemoryCardController::class, 'index']);

// Spiel anzeigen
Route::get('/memory-games/{gameId}', [MemoryGameController::class, 'show']);

