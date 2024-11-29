<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemoryCardController extends Controller
{
    public function index($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            $cards = $game->cards()->inRandomOrder()->get();
            return response()->json($cards);
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen der Karten:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Abrufen der Karten'], 500);
        }
    }

    public function flip($gameId, Request $request)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            
            $validated = $request->validate([
                'card_id' => 'required|integer'
            ]);

            $card = $game->cards()
                ->where('card_id', $validated['card_id'])
                ->first();

            if (!$card) {
                return response()->json([
                    'error' => 'Karte nicht gefunden'
                ], 404);
            }

            if ($card->is_flipped) {
                return response()->json([
                    'error' => 'Karte ist bereits aufgedeckt',
                    'card' => $card
                ], 400);
            }

            if ($card->is_matched) {
                return response()->json([
                    'error' => 'Karte wurde bereits gematcht',
                    'card' => $card
                ], 400);
            }

            $card->update(['is_flipped' => true]);
            $card = $card->fresh();

            return response()->json([
                'message' => 'Karte erfolgreich aufgedeckt',
                'card' => $card
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Aufdecken der Karte:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Aufdecken der Karte'], 500);
        }
    }

    public function resetUnmatchedCards($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            $game->cards()->where('is_matched', false)->update(['is_flipped' => false]);
            return response()->json(['message' => 'Karten zurückgesetzt']);
        } catch (\Exception $e) {
            Log::error('Fehler beim Zurücksetzen der Karten:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Zurücksetzen der Karten'], 500);
        }
    }
}