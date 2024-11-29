<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MemoryGameController extends Controller
{
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Validierung der Eingabe
            $validated = $request->validate([
                'pairs' => 'integer|min:2|max:32',
            ]);

            // Neues Spiel erstellen
            $game = new MemoryGame([
                'status' => 'waiting',
            ]);
            $game->save();

            Log::info('Neues Spiel erstellt:', ['game_id' => $game->game_id]);

            // Anzahl Paare (Standard: 8)
            $pairs = $validated['pairs'] ?? 8;

            // Karten erstellen
            for ($i = 1; $i <= $pairs; $i++) {
                // Zwei Karten pro Paar
                for ($j = 0; $j < 2; $j++) {
                    MemoryCard::factory()->create([
                        'game_id' => $game->game_id,
                        'group_id' => $i,
                        'is_flipped' => false,
                    ]);
                }
            }

            DB::commit();

            // Lade das Spiel mit Karten und gebe es zurück
            $game->load(['cards' => function($query) {
                $query->inRandomOrder(); // Mische die Karten
            }]);

            // Nur ein return statement am Ende
            return response()->json([
                'game_id' => $game->game_id,
                'status' => $game->status,
                'cards' => $game->cards,
                'message' => 'Spiel erfolgreich erstellt'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fehler beim Erstellen des Spiels:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Erstellen des Spiels'], 500);
        }
    }

    public function start(Request $request, $gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);

            if (!$game->isWaiting()) {
                return response()->json([
                    'error' => 'Spiel kann nicht gestartet werden',
                    'reason' => "Status ist '{$game->status}', sollte 'waiting' sein"
                ], 400);
            }

            $game->start();
            $game->load('cards', 'players');

            return response()->json([
                'message' => 'Spiel erfolgreich gestartet',
                'game' => $game
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting game:', [
                'error' => $e->getMessage(),
                'game_id' => $gameId
            ]);
            
            return response()->json([
                'error' => 'Fehler beim Starten des Spiels',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(MemoryGame $game)
    {
        try {
            // Lade das Spiel mit Karten und Spielern
            $game->load(['cards', 'players']);

            // Prüfe ob alle Karten gematcht sind
            $unmatchedCards = $game->cards()->where('is_matched', false)->count();
            if ($unmatchedCards === 0 && $game->status === 'in_progress') {
                $game->update([
                    'status' => 'finished',
                    'stopped_at' => now()
                ]);
            }
            return response()->json([
                'game_id' => $game->game_id,
                'status' => $game->status,
                'cards' => $game->cards,
                'players' => $game->players,
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Laden des Spiels:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Fehler beim Laden des Spiels',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stop(MemoryGame $game)
    {
        try {
            if ($game->status === 'finished') {
                return response()->json(['message' => 'Spiel wurde bereits beendet', 'status' => 'finished']);
            }

            $game->update([
                'status' => 'finished',
                'stopped_at' => now(),
            ]);

            return response()->json([
                'message' => 'Spiel erfolgreich beendet',
                'status' => 'finished'
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Beenden des Spiels:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Beenden des Spiels'], 500);
        }
    }

    public function updatePlayerScore(Request $request, MemoryGame $game, $playerId)
    {
        try {
            $validated = $request->validate([
                'player_score' => 'required|integer|min:0'
            ]);

            $game->players()->updateExistingPivot($playerId, [
                'score' => $validated['player_score']
            ]);

            return response()->json([
                'message' => 'Punktzahl erfolgreich aktualisiert'
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren der Punktzahl:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Fehler beim Aktualisieren der Punktzahl',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}