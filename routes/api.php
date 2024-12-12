<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LobbyController;
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
    Route::delete('/user/delete', [UserController::class, 'deleteAccount']);

});

// Memory Game

Route::prefix('memory-games')->group(function () {
    //Neues Spiel erstellen
    Route::post('/create', [MemoryGameController::class, 'create']);

    // Spiel starten
    Route::post('/{gameId}/start', [MemoryGameController::class, 'start']);

    // Spiel beenden
    Route::post('/{gameId}/stop', [MemoryGameController::class, 'stop']);

    // Punktestand aktualisieren
    Route::put('/{gameId}/players/{player}', [MemoryPlayerController::class, 'update']);

    // Spieler abrufen
    Route::get('/{gameId}/players', [MemoryPlayerController::class, 'index']);

    // Spieler hinzufügen
    Route::post('/{gameId}/players', [MemoryPlayerController::class, 'store']);

    // Spielerwechsel
    Route::post('/{gameId}/next-turn', [MemoryGameController::class, 'nextTurn']);

    // Matched Cards aktualisieren
    Route::post('/{gameId}/cards/match', [MemoryCardController::class, 'updateMatched']);

    // alle Karten eines Memory-Spiels abrufen
    Route::get('/{gameId}/cards', [MemoryCardController::class, 'index']);

    // Spiel anzeigen
    Route::get('/{gameId}', [MemoryGameController::class, 'show']);
});

// Spieler-Lobby mit Presence Channel Support
Route::prefix('lobby')->group(function () {
    // Status & Presence
    Route::post('/player-status', [LobbyController::class, 'updatePlayerStatus']);
    Route::post('/player-heartbeat/{playerId}', [MemoryPlayerController::class, 'heartbeat']);
    
    // Lobby Management
    Route::post('/challenge/{playerId}', [LobbyController::class, 'challengePlayer']);
    Route::post('/status/{lobbyId}', [LobbyController::class, 'updateLobbyStatus']);
    
    // Presence Channel Authentication wird automatisch durch Laravel gehandelt
    Route::get('/presence/auth', function () {
        return auth()->check() ? auth()->user() : ['guest_id' => session('memoryGuestId')];
    })->middleware('auth.broadcast');
});