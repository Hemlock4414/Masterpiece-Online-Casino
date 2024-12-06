<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use App\Models\MemoryPlayer;
use App\Events\PlayerStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MemoryGameController extends Controller
{
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'pairs' => 'integer|min:2|max:32',
                'guest_id' => 'nullable|integer'
            ]);
    
            $game = new MemoryGame([
                'status' => 'waiting',
            ]);
            $game->save();
    
            // Spieler erstellen/finden
            if (auth()->check()) {
                $player = MemoryPlayer::firstOrCreate(
                    ['user_id' => auth()->id()],
                    ['name' => auth()->user()->username]
                );
            } else {
                $player = MemoryPlayer::createOrGetGuest($request->guest_id);
            }

            $game->players()->attach($player->player_id, [
                'player_score' => 0
            ]);

            // Karten erstellen
            $pairs = $validated['pairs'] ?? 8;
            $cards = [];
            for ($i = 1; $i <= $pairs; $i++) {
                for ($j = 0; $j < 2; $j++) {
                    $cards[] = [
                        'game_id' => $game->game_id,
                        'group_id' => $i,
                        'card_image' => 'default.jpg',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
            shuffle($cards);
            MemoryCard::insert($cards);

            // Spieler-Status broadcasten
            broadcast(new PlayerStatusChanged([
                'id' => $player->player_id,
                'name' => $player->name,
                'status' => 'waiting',
                'isRegistered' => auth()->check()
            ]));

            DB::commit();

            return response()->json([
                'game_id' => $game->game_id,
                'status' => $game->status,
                'cards' => $game->cards,
                'players' => $game->players,
                'message' => 'Spiel erfolgreich erstellt'
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fehler beim Erstellen des Spiels:', ['error' => $e->getMessage()]);
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
    
            $firstPlayer = $game->players()->first();
            if (!$firstPlayer) {
                return response()->json(['error' => 'Keine Spieler verfügbar'], 400);
            }

            $game->update([
                'status' => 'in_progress',
                'player_turn' => $firstPlayer->player_id
            ]);
    
            // Status aller Spieler aktualisieren
            foreach ($game->players as $player) {
                broadcast(new PlayerStatusChanged([
                    'id' => $player->player_id,
                    'name' => $player->name,
                    'status' => 'in_game',
                    'isRegistered' => !is_null($player->user_id)
                ]));
            }
    
            $game->load(['cards', 'players']);
            return response()->json([
                'message' => 'Spiel erfolgreich gestartet',
                'game' => $game,
                'active_player' => $firstPlayer
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting game:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stop($gameId, Request $request)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:finished,aborted'
            ]);
    
            $game = MemoryGame::findOrFail($gameId);
            $game->update([
                'status' => $validated['status'],
                'stopped_at' => now()
            ]);
    
            // Spieler-Status auf 'available' setzen
            foreach ($game->players as $player) {
                broadcast(new PlayerStatusChanged([
                    'id' => $player->player_id,
                    'name' => $player->name,
                    'status' => 'available',
                    'isRegistered' => !is_null($player->user_id)
                ]));
            }
    
            return response()->json([
                'message' => $validated['status'] === 'aborted' ? 'Spiel abgebrochen' : 'Spiel beendet',
                'status' => $validated['status']
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

    // Spielerwechsel
    public function nextTurn($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            
            if (!$game->isInProgress()) {
                return response()->json([
                    'error' => 'Spielerwechsel nur bei aktivem Spiel möglich'
                ], 400);
            }

            $game->nextTurn();
            
            return response()->json([
                'message' => 'Spielerwechsel erfolgreich',
                'active_player' => $game->getActivePlayer()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Fehler beim Spielerwechsel:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}