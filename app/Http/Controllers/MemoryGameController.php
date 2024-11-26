<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use Illuminate\Http\Request;

class MemoryGameController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    // Neues Spiel erstellen
    public function create(Request $request)
    {
        // Neues Spiel in der Datenbank erstellen
        $game = MemoryGame::create([
            'status' => 'waiting', // Status: Warten auf Start
        ]);

        // Anzahl Paare vom Benutzer festlegen (Standard: 8 Paare = 16 Karten)
        $pairs = $request->input('pairs', 8);

        // Karten generieren
        for ($i = 1; $i <= $pairs; $i++) {
            // Zwei Karten für jedes Paar erstellen
            MemoryCard::factory()->create([
                'game_id' => $game->id,
                'group_id' => $i,
            ]);

            MemoryCard::factory()->create([
                'game_id' => $game->id,
                'group_id' => $i,
            ]);
        }

        return response()->json($game->load('cards')); // Rückgabe des Spiels mit den Karten
    }

    /**
     * Display the specified resource.
     */
    // Ein vorhandenes Spiel mit seinen Karten abrufen:
    public function show(MemoryGame $game)
    {
        return response()->json($game->load('cards'));
    }

    /**
     * Update the specified resource in storage.
     */
    // Ein Spiel als "beendet" markieren:
    public function stop(MemoryGame $game)
    {
        $game->update([
            'status' => 'finished',
            'stopped_at' => now(),
        ]);
    
        return response()->json(['message' => 'Game finished successfully']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MemoryGame $memoryGame)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MemoryGame $memoryGame)
    {
        //
    }
}
