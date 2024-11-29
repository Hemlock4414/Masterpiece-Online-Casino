<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemoryPlayerController extends Controller
{
    public function index($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            return response()->json($game->players);
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen der Spieler:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Abrufen der Spieler'], 500);
        }
    }


    public function store($gameId, Request $request)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            
            // Prüfe ob noch ein Platz frei ist
            if ($game->players()->count() >= 2) {
                return response()->json([
                    'error' => 'Spiel ist bereits voll'
                ], 400);
            }
    
            if (!$game->isWaiting()) {
                return response()->json([
                    'error' => 'Spiel wurde bereits gestartet'
                ], 400);
            }
    
            $player = new MemoryPlayer([
                'name' => auth()->check() ? auth()->user()->name : 'Gast ' . rand(1000, 9999),
                'user_id' => auth()->id()
            ]);
            $player->save();
    
            if (!$player->canJoinGame($game)) {
                return response()->json([
                    'error' => 'Spieler kann diesem Spiel nicht beitreten'
                ], 400);
            }
    
            $game->players()->attach($player->player_id, ['player_score' => 0]);
    
            return response()->json([
                'message' => 'Spieler erfolgreich hinzugefügt',
                'player' => $player
            ]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Hinzufügen des Spielers:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update($gameId, $playerId, Request $request)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            
            $validated = $request->validate([
                'player_score' => 'required|integer|min:0'
            ]);

            $game->players()->updateExistingPivot($playerId, [
                'player_score' => $validated['player_score']
            ]);

            return response()->json(['message' => 'Punktzahl aktualisiert']);
        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren der Punktzahl:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Aktualisieren der Punktzahl'], 500);
        }
    }
}