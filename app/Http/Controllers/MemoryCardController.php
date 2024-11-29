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
            if ($game->status === 'finished') {
                return response()->json(['error' => 'Spiel ist bereits beendet'], 400);
            }

            $cardId = $request->input('card_id');
            $card = $game->cards()->where('card_id', $cardId)->first();
        
            if (!$card) {
                return response()->json(['error' => 'Karte nicht gefunden'], 404);
            }
        
            if ($card->is_flipped) {
                return response()->json(['error' => 'Karte ist bereits aufgedeckt'], 400);
            }
        
            // Karte aufdecken
            $card->update(['is_flipped' => true]);
        
            // Prüfe ob alle Karten gematcht sind
            $unmatchedCards = $game->cards()->where('is_matched', false)->count();
            if ($unmatchedCards === 0) {
                $game->update([
                    'status' => 'finished',
                    'stopped_at' => now()
                ]);
            }
        
            return response()->json([
                'message' => 'Karte erfolgreich aufgedeckt',
                'card' => $card,
                'game_status' => $game->status
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Aufdecken der Karte:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Aufdecken der Karte'], 500);
        }
    }
}