<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryPlayer;
use Illuminate\Http\Request;

class MemoryPlayerController extends Controller
{
    // Spieler eines Spiels abrufen
    public function index(MemoryGame $game)
    {
        return response()->json($game->players); // Gibt alle Spieler des Spiels zurück
    }

    // Spieler zum Spiel hinzufügen
    public function store(Request $request, MemoryGame $game)
    {
        $playerId = $request->input('player_id');

        // Spieler hinzufügen, falls nicht bereits hinzugefügt
        if (!$game->players()->where('player_id', $playerId)->exists()) {
            $game->players()->attach($playerId, ['player_score' => 0]);
            return response()->json(['message' => 'Player added successfully']);
        }

        return response()->json(['message' => 'Player already exists'], 400);
    }

    // Punktestand eines Spielers aktualisieren
    public function update(Request $request, MemoryGame $game, MemoryPlayer $player)
    {
        $newScore = $request->input('player_score');

        $game->players()->updateExistingPivot($player->id, ['player_score' => $newScore]);

        return response()->json(['message' => 'Player score updated successfully']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MemoryPlayer $memoryPlayer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MemoryPlayer $memoryPlayer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MemoryPlayer $memoryPlayer)
    {
        //
    }
}
