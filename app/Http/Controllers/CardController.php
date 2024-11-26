<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Karten abrufen
    public function index(Game $game)
    {
        $cards = $game->cards; // Holt alle Karten, die zu einem Spiel gehören
        return response()->json($cards);
    }

    // Karte aufdecken
    public function flip(Card $card, Request $request)
    {
        $playerId = $request->input('player_id'); // ID des Spielers, der die Karte aufdeckt

        // Überprüfen, ob die Karte bereits aufgedeckt wurde
        if ($card->is_flipped) {
            return response()->json(['error' => 'Card already flipped'], 400);
        }

        // Karte aktualisieren
        $card->update([
            'is_flipped' => true,
            'last_flipped_by' => $playerId,
        ]);

        return response()->json($card);
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
