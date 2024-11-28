<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemoryCardController extends Controller
{
    public function index(MemoryGame $game)
    {
        try {
            $cards = $game->cards()->inRandomOrder()->get();
            return response()->json($cards);
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen der Karten:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Abrufen der Karten'], 500);
        }
    }

    public function flip(Request $request, MemoryGame $game)
    {
        try {
            // Validiere Request
            $validated = $request->validate([
                'card_id' => 'required|integer'
            ]);

            // Prüfe Spielstatus
            if ($game->status === 'finished') {
                return response()->json(['error' => 'Spiel ist bereits beendet'], 400);
            }

            // Finde die Karte
            $card = $game->cards()->where('card_id', $validated['card_id'])->first();
            
            if (!$card) {
                return response()->json(['error' => 'Karte nicht gefunden'], 404);
            }

            if ($card->is_flipped) {
                return response()->json(['error' => 'Karte ist bereits aufgedeckt'], 400);
            }

            // Zähle bereits aufgedeckte Karten
            $flippedCards = $game->cards()
                ->where('is_flipped', true)
                ->where('is_matched', false)
                ->get();

            if ($flippedCards->count() >= 2) {
                return response()->json(['error' => 'Es sind bereits zwei Karten aufgedeckt'], 400);
            }

            // Karte aufdecken
            $card->update(['is_flipped' => true]);

            // Wenn es die zweite aufgedeckte Karte ist, prüfe auf Paar
            if ($flippedCards->count() === 1) {
                $firstCard = $flippedCards->first();
                
                if ($firstCard->group_id === $card->group_id) {
                    // Paar gefunden
                    $firstCard->update(['is_matched' => true]);
                    $card->update(['is_matched' => true]);
                }
            }

            return response()->json([
                'card' => $card->fresh(),
                'is_pair' => $card->is_matched
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Aufdecken der Karte:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Aufdecken der Karte'], 500);
        }
    }
}