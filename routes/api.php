<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemoryCardController;
use App\Http\Controllers\MemoryGameController;
use App\Http\Controllers\MemoryPlayerController;
use Illuminate\Support\Facades\File;

// TODO remove this on public release, only for testing!
Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!'], 200);
});

// Öffentliche Route für alle Posts (ohne Authentifizierung)

//User

Route::post('/login', [UserController::class, 'login']);

Route::post('/register', [UserController::class, 'register']);

Route::get('/users-info', [UserController::class, 'index']);

Route::post('/check-username', [UserController::class, 'checkUsername']);
Route::post('/check-email', [UserController::class, 'checkEmail']);

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

Route::prefix('memory-games')->group(function () {
    // Custom-Design-Karten holen
    Route::get('/custom-themes', [MemoryGameController::class, 'getCustomThemes']);
    // Spiel CRUD
    //Neues Spiel erstellen
    Route::post('/create', [MemoryGameController::class, 'create']);
    // Spiel anzeigen
    Route::get('/{gameId}', [MemoryGameController::class, 'show']);
    // Spiel starten
    Route::post('/{gameId}/start', [MemoryGameController::class, 'start']);
    // Spiel beenden
    Route::post('/{gameId}/stop', [MemoryGameController::class, 'stop']);

    // Karten-Management
    // alle Karten eines Memory-Spiels abrufen
    Route::get('/{gameId}/cards', [MemoryCardController::class, 'index']);
    // Matched Cards aktualisieren
    Route::post('/{gameId}/cards/match', [MemoryCardController::class, 'updateMatched']);

    // Spieler-Management
    // Spieler abrufen
    Route::get('/{gameId}/players', [MemoryPlayerController::class, 'index']);
    // Spieler hinzufügen
    Route::post('/{gameId}/players', [MemoryPlayerController::class, 'store']);
    // Punktestand aktualisieren
    Route::put('/{gameId}/players/{player}', [MemoryPlayerController::class, 'update']);
    // Spielerwechsel
    Route::post('/{gameId}/next-turn', [MemoryGameController::class, 'nextTurn']);
});