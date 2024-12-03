<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
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
        return response()->json($lobby);
    }

    public function updateStatus(Request $request, $lobbyId)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,declined,in_game'
        ]);

        $lobby = Lobby::findOrFail($lobbyId);
        $lobby->update(['status' => $validated['status']]);

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
    
}
