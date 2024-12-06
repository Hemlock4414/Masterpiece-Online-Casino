<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\MemoryPlayer;
use App\Events\PlayerStatusChanged;
use App\Events\LobbyStatusUpdated;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LobbyController extends Controller
{
    public function getPlayers()
    {
        try {
            $player = auth()->check() 
                ? MemoryPlayer::where('user_id', auth()->id())->first()
                : MemoryPlayer::createOrGetGuest(session('memoryGuestId'));
            
            if (!$player) {
                return response()->json(['error' => 'Spieler nicht gefunden'], 404);
            }
    
            return response()->json([
                'id' => $player->player_id,
                'name' => $player->name,
                'status' => 'available',
                'isRegistered' => auth()->check()
            ]);
        } catch (\Exception $e) {
            \Log::error('Fehler bei getPlayers:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Laden der Spieler'], 500);
        }
    }

    public function challengePlayer(Request $request, $playerId)
    {
        try {
            $challenger = auth()->check() 
                ? MemoryPlayer::where('user_id', auth()->id())->first()
                : MemoryPlayer::createOrGetGuest(session('memoryGuestId'));
                
            $challenged = MemoryPlayer::find($playerId);
    
            if (!$challenger || !$challenged) {
                return response()->json(['error' => 'Spieler nicht gefunden'], 404);
            }

            // Prüfe ob bereits eine aktive Herausforderung existiert
            $existingChallenge = Lobby::where('challenged_id', $playerId)
                ->where('status', 'pending')
                ->where('created_at', '>', Carbon::now()->subMinutes(1))
                ->first();

            if ($existingChallenge) {
                return response()->json([
                    'error' => 'Spieler hat bereits eine aktive Herausforderung'
                ], 400);
            }

            // Erstelle neue Herausforderung
            $lobby = Lobby::create([
                'challenger_id' => $challenger->player_id,
                'challenger_type' => 'memory_player',
                'challenger_name' => $challenger->name,
                'challenged_id' => $challenged->player_id,
                'challenged_type' => 'memory_player',
                'challenged_name' => $challenged->name,
                'status' => 'pending',
                'game_type' => 'memory',
                'expires_at' => Carbon::now()->addMinute()
            ]);

            broadcast(new LobbyStatusUpdated($lobby));

            return response()->json($lobby);
        } catch (\Exception $e) {
            \Log::error('Fehler bei challengePlayer:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Herausfordern'], 500);
        }
    }

    public function updateLobbyStatus(Request $request, $lobbyId)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:accepted,declined,in_game'
            ]);

            $lobby = Lobby::findOrFail($lobbyId);
            $lobby->status = $validated['status'];
            $lobby->save();

            broadcast(new LobbyStatusUpdated($lobby));
            
            if ($validated['status'] === 'accepted') {
                // Hier könnte später die Spiel-Initialisierung erfolgen
            }

            return response()->json($lobby);
        } catch (\Exception $e) {
            \Log::error('Fehler bei updateLobbyStatus:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Status konnte nicht aktualisiert werden'], 500);
        }
    }

    public function checkExpiredChallenges()
    {
        try {
            $expiredLobbies = Lobby::where('status', 'pending')
                ->where('expires_at', '<', Carbon::now())
                ->get();

            foreach ($expiredLobbies as $lobby) {
                $lobby->update(['status' => 'declined']);
                broadcast(new LobbyStatusUpdated($lobby));
            }

            return response()->json(['message' => 'Abgelaufene Herausforderungen bearbeitet']);
        } catch (\Exception $e) {
            \Log::error('Fehler bei checkExpiredChallenges:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Prüfen abgelaufener Herausforderungen'], 500);
        }
    }

    public function updatePlayerStatus(Request $request)
    {
        try {
            $player = auth()->check() 
                ? MemoryPlayer::where('user_id', auth()->id())->first()
                : MemoryPlayer::createOrGetGuest(session('memoryGuestId'));
    
            if (!$player) {
                return response()->json(['error' => 'Spieler nicht gefunden'], 404);
            }
    
            // Status aktualisieren
            $player->touch(); // Aktualisiert updated_at für Online-Status
    
            broadcast(new PlayerStatusChanged([
                'id' => $player->player_id,
                'name' => $player->name,
                'status' => $request->status,
                'isRegistered' => auth()->check()
            ]));
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Status Update Error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getOnlinePlayers()
    {
        try {
            // Aktive Spieler der letzten 5 Minuten
            $players = MemoryPlayer::where('updated_at', '>', now()->subMinutes(5))
                ->get()
                ->map(function($player) {
                    return [
                        'id' => $player->player_id,
                        'name' => $player->name,
                        'status' => 'available',
                        'isRegistered' => !is_null($player->user_id)
                    ];
                });
    
            return response()->json($players);
        } catch (\Exception $e) {
            \Log::error('Fehler beim Laden der Online-Spieler:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Laden der Spieler'], 500);
        }
    }
}