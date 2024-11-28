<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemoryPlayerController extends Controller
{
    public function index(MemoryGame $game)
    {
        try {
            return response()->json([
                'players' => $game->players()->with('user')->get()
            ]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen der Spieler:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Abrufen der Spieler'], 500);
        }
    }

    public function store(Request $request, MemoryGame $game)
    {
        try {
            $validated = $request->validate([
                'player_id' => 'required|integer|exists:users,id'
            ]);

            if ($game->status === 'finished') {
                return response()->json(['error' => 'Spiel ist bereits beendet'], 400);
            }

            if ($game->players()->where('player_id', $validated['player_id'])->exists()) {
                return response()->json(['error' => 'Spieler ist bereits im Spiel'], 400);
            }

            $game->players()->attach($validated['player_id'], ['score' => 0]);

            return response()->json([
                'message' => 'Spieler erfolgreich hinzugefügt',
                'player' => $game->players()->where('player_id', $validated['player_id'])->first()
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Hinzufügen des Spielers:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Hinzufügen des Spielers'], 500);
        }
    }

    public function update(Request $request, MemoryGame $game, $playerId)
    {
        try {
            $validated = $request->validate([
                'score' => 'required|integer|min:0'
            ]);

            if ($game->status === 'finished') {
                return response()->json(['error' => 'Spiel ist bereits beendet'], 400);
            }

            if (!$game->players()->where('player_id', $playerId)->exists()) {
                return response()->json(['error' => 'Spieler nicht im Spiel'], 404);
            }

            $game->players()->updateExistingPivot($playerId, [
                'score' => $validated['score']
            ]);

            return response()->json([
                'message' => 'Punktzahl erfolgreich aktualisiert',
                'player' => $game->players()->where('player_id', $playerId)->first()
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren der Punktzahl:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Aktualisieren der Punktzahl'], 500);
        }
    }
}