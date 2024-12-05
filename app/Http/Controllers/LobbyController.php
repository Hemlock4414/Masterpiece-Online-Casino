<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Events\PlayerStatusChanged;
use App\Events\LobbyStatusUpdated;
use Illuminate\Http\Request;
class LobbyController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'challenged_id' => 'required',
            'challenged_type' => 'required|string',
            'challenged_name' => 'required|string',
            'game_type' => 'required|string'
        ]);

        $lobby = new Lobby([
            'challenger_id' => $request->player_id,
            'challenger_type' => 'memory_player',
            'challenger_name' => $request->player_name,
            'challenger_user_id' => auth()->id(),
            'challenged_id' => $validated['challenged_id'],
            'challenged_type' => $validated['challenged_type'],
            'challenged_name' => $validated['challenged_name'],
            'game_type' => $validated['game_type']
        ]);

        $lobby->save();
        broadcast(new LobbyStatusUpdated($lobby));
        return response()->json($lobby);
    }

    public function updateStatus(Request $request, $lobbyId)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,declined,in_game'
        ]);
    
        $lobby = Lobby::findOrFail($lobbyId);
        $lobby->update(['status' => $validated['status']]);
        
        broadcast(new LobbyStatusUpdated($lobby));
        return response()->json($lobby);
    }

    public function getActiveLobby($playerId, $playerType)
    {
        return Lobby::where(function($query) use ($playerId, $playerType) {
            $query->where([
                'challenger_id' => $playerId,
                'challenger_type' => $playerType
            ])->orWhere([
                'challenged_id' => $playerId,
                'challenged_type' => $playerType
            ]);
        })->where('status', '!=', 'declined')
          ->first();
    }

    // Methode für aktive Spieler-Liste
    public function getPlayers()
    {
        try {
            // Abfrage: Herausforderer und Herausgeforderte in der Lobby
            $challengers = Lobby::select('challenger_id as player_id', 'challenger_name as name', 'challenger_type as type', 'status')
                ->where('status', '!=', 'declined')
                ->get();
    
            $challenged = Lobby::select('challenged_id as player_id', 'challenged_name as name', 'challenged_type as type', 'status')
                ->where('status', '!=', 'declined')
                ->get();
    
            // Beide Listen zusammenfügen
            $players = $challengers->merge($challenged)->unique('player_id');
    
            // Debugging: Log-Ausgabe
            \Log::info('Spielerabfrage erfolgreich', ['players' => $players]);
    
            return response()->json($players);
        } catch (\Exception $e) {
            // Fehler loggen und HTTP 500 zurückgeben
            \Log::error('Fehler bei getPlayers: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load players'], 500);
        }
    }
    

    // für Online-Status-Updates
    public function updatePlayerStatus(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,in_game,offline'
        ]);

        $lobby = Lobby::where(function($query) use ($request) {
            $query->where('challenger_id', $request->player_id)
                  ->orWhere('challenged_id', $request->player_id);
        })->first();

        if ($lobby) {
            if ($lobby->challenger_id == $request->player_id) {
                $lobby->challenger_status = $validated['status'];
            } else {
                $lobby->challenged_status = $validated['status'];
            }
            $lobby->save();

            broadcast(new PlayerStatusChanged($lobby));
        }

        return response()->json(['message' => 'Status updated']);
    }

    // Methode mit Verfügbarkeitsprüfung
    public function challengePlayer(Request $request, $playerId)
    {
        try {
            // Debugging: Anfrage loggen
            \Log::info('Herausforderung gestartet', $request->all());
    
            // Prüfe, ob Spieler bereits in einer aktiven Lobby ist
            $existingLobby = $this->getActiveLobby($playerId, $request->player_type);
            if ($existingLobby) {
                return response()->json(['message' => 'Player already in a game'], 400);
            }
    
            // Erstelle eine neue Lobby
            $validated = $request->validate([
                'challenged_id' => 'required',
                'challenged_type' => 'required|string',
                'challenged_name' => 'required|string',
                'game_type' => 'required|string',
            ]);
    
            $lobby = Lobby::create([
                'challenger_id' => $request->player_id,
                'challenger_type' => 'memory_player',
                'challenger_name' => $request->player_name,
                'challenged_id' => $validated['challenged_id'],
                'challenged_type' => $validated['challenged_type'],
                'challenged_name' => $validated['challenged_name'],
                'game_type' => $validated['game_type'],
                'status' => 'pending',
            ]);
    
            broadcast(new PlayerStatusChanged($lobby));
    
            return response()->json(['message' => 'Challenge sent', 'lobby' => $lobby]);
        } catch (\Exception $e) {
            // Fehler loggen
            \Log::error('Fehler bei challengePlayer: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create challenge'], 500);
        }
    }
    
    
}
