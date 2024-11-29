<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use App\Models\MemoryPlayer;
use App\Models\User; 
use App\Models\MemoryGamePlayer;
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
                'theme' => 'required|string'
            ]);
    
            // Neues Spiel erstellen
            $game = new MemoryGame([
                'status' => 'waiting',
            ]);
            $game->save();
    
            Log::info('Spiel erstellt:', ['game_id' => $game->game_id]);
    
            // Spieler erstellen/finden basierend auf Auth-Status
            if (auth()->check()) {
                $player = MemoryPlayer::firstOrCreate(
                    ['user_id' => auth()->id()],
                    ['name' => auth()->user()->name]
                );
            } else {
                $player = new MemoryPlayer([
                    'name' => 'Gast ' . rand(1000, 9999)
                ]);
                $player->save();
            }
    
            Log::info('Spieler erstellt:', ['player_id' => $player->player_id]);
    
            // Verknüpfe Spieler mit Spiel
            $game->players()->attach($player->player_id, [
                'player_score' => 0
            ]);
    
            // Karten erstellen
            $pairs = $validated['pairs'] ?? 8;
            
            for ($i = 1; $i <= $pairs; $i++) {
                for ($j = 0; $j < 2; $j++) {
                    MemoryCard::create([
                        'game_id' => $game->game_id,
                        'group_id' => $i,
                        'is_flipped' => false,
                        'is_matched' => false,
                        'card_image' => 'default.jpg'  // Standardwert für card_image
                    ]);
                }
            }
    
            DB::commit();
    
            // Lade das Spiel mit gemischten Karten
            $game->load(['cards' => function($query) {
                $query->inRandomOrder();
            }, 'players']);
    
            return response()->json([
                'game_id' => $game->game_id,
                'status' => $game->status,
                'cards' => $game->cards,
                'players' => $game->players,
                'message' => 'Spiel erfolgreich erstellt'
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fehler beim Erstellen des Spiels:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Fehler beim Erstellen des Spiels'], 500);
        }
    }

    public function start($gameId, Request $request)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);

            if ($game->status !== 'waiting') {
                return response()->json([
                    'error' => 'Spiel kann nicht gestartet werden',
                    'reason' => "Status ist '{$game->status}', sollte 'waiting' sein"
                ], 400);
            }

            $game->status = 'in_progress';
            $game->save();

            $game->load('cards');

            return response()->json([
                'message' => 'Spiel erfolgreich gestartet',
                'game' => $game
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting game:', ['error' => $e->getMessage(), 'game_id' => $gameId]);
            return response()->json(['error' => 'Fehler beim Starten des Spiels'], 500);
        }
    }

    public function show($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            $game->load(['cards', 'players']);
            
            return response()->json([
                'game_id' => $game->game_id,
                'status' => $game->status,
                'cards' => $game->cards,
                'players' => $game->players,
            ]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Laden des Spiels:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Laden des Spiels'], 500);
        }
    }

    public function stop($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            $game->update([
                'status' => 'finished',
                'stopped_at' => now()
            ]);

            return response()->json([
                'message' => 'Spiel beendet',
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