<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryPlayer;
use App\Events\PlayerStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemoryPlayerController extends Controller
{
    public function index($gameId)
    {
        try {
            $game = MemoryGame::with(['players' => function($query) {
                $query->select('memory_players.*', 'users.name as user_name')
                      ->leftJoin('users', 'users.id', '=', 'memory_players.user_id')
                      ->where('memory_players.last_seen_at', '>', now()->subMinutes(5));
            }])->findOrFail($gameId);
            
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
            
            // Prüfungen für Multiplayer
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
    
            // Spieler erstellen/abrufen
            $player = auth()->check() 
                ? MemoryPlayer::firstOrCreate(
                    ['user_id' => auth()->id()],
                    [
                        'name' => auth()->user()->name,
                        'status' => 'available',
                        'last_seen_at' => now()
                    ]
                )
                : MemoryPlayer::createOrGetGuest($request->guest_id);
    
            if (!$player->canJoinGame($game)) {
                return response()->json([
                    'error' => 'Spieler kann diesem Spiel nicht beitreten'
                ], 400);
            }
    
            // Spieler zum Spiel hinzufügen
            $game->players()->attach($player->player_id, ['player_score' => 0]);

            // Status über Presence Channel aktualisieren
            $player->updateStatus('waiting');
            
            broadcast(new PlayerStatusChanged($player->getPresenceData()));
    
            return response()->json([
                'message' => 'Spieler erfolgreich hinzugefügt',
                'player' => $player,
                'game' => $game->fresh(['players'])
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

            // Status aktualisieren wenn Spiel beendet
            if ($game->isFinished()) {
                $player = MemoryPlayer::find($playerId);
                $player->updateStatus('available');
                broadcast(new PlayerStatusChanged($player->getPresenceData()));
            }

            return response()->json([
                'message' => 'Punktzahl aktualisiert',
                'game' => $game->fresh(['players'])
            ]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren der Punktzahl:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Aktualisieren der Punktzahl'], 500);
        }
    }

    public function updateStatus($playerId, Request $request)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:waiting,in_game,available,offline'
            ]);

            $player = MemoryPlayer::findOrFail($playerId);
            $player->updateStatus($validated['status']);
            
            broadcast(new PlayerStatusChanged($player->getPresenceData()));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren des Status:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Status konnte nicht aktualisiert werden'], 500);
        }
    }

    // Neue Methode für Presence Channel Heartbeat
    public function heartbeat($playerId)
    {
        try {
            $player = MemoryPlayer::findOrFail($playerId);
            $player->last_seen_at = now();
            $player->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Heartbeat:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Heartbeat fehlgeschlagen'], 500);
        }
    }
}