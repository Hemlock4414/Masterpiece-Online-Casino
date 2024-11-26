<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use Illuminate\Http\Request;

class MemoryCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Karten abrufen
    public function index(MemoryGame $game)
    {
        $cards = $game->cards; // Holt alle Karten, die zu einem Spiel gehören
        return response()->json($cards);
    }

    // Karte aufdecken
    public function flip(Request $request, MemoryGame $game)
    {
        $cardId = $request->input('card_id'); // ID der Karte, die aufgedeckt werden soll
        $card = $game->cards()->where('card_id', $cardId)->first();
    
        if (!$card) {
            return response()->json(['error' => 'Card not found'], 404);
        }
    
        if ($card->is_flipped) {
            return response()->json(['error' => 'Card already flipped'], 400);
        }
    
        // Karte als "aufgedeckt" markieren
        $card->update(['is_flipped' => true]);
    
        return response()->json(['message' => 'Card flipped successfully', 'card' => $card]);
    }
    
    

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Neues Spiel erstellen
        $game = MemoryGame::create([
            'status' => 'waiting', // Status des Spiels
        ]);

        // Anzahl der Paare vom Benutzer festlegen (Standard: 8)
        $pairs = $request->input('pairs', 8);

        // Karten mit der Factory erstellen
        for ($i = 1; $i <= $pairs; $i++) {
            // Zwei Karten pro Paar generieren
            MemoryCard::factory()->create([
                'game_id' => $game->id, // Spiel-ID
                'group_id' => $i,       // Paar-ID
            ]);

            MemoryCard::factory()->create([
                'game_id' => $game->id,
                'group_id' => $i,
            ]);
        }

        // Spiel und Karten zurückgeben
        return response()->json($game->load('cards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Card $card)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Card $card)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Card $card)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Card $card)
    {
        //
    }
}
