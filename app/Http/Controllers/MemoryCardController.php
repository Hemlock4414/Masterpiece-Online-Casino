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
            Log::info('Card flip attempt:', [
                'game_id' => $gameId,
                'request_data' => $request->all()
            ]);

            $game = MemoryGame::findOrFail($gameId);
            
            $validated = $request->validate([
                'card_id' => 'required|integer',
                'player_id' => 'required|integer'
            ]);

            $card = $game->cards()
                ->where('card_id', $validated['card_id'])
                ->first();

            if (!$card) {
                return response()->json([
                    'error' => 'Karte nicht gefunden'
                ], 404);
            }

            if ($card->is_matched) {
                return response()->json([
                    'error' => 'Karte wurde bereits gematcht'
                ], 400);
            }

            return response()->json([
                'message' => 'Karte erfolgreich geflippt',
                'card' => $card,
                'game_status' => $game->status
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Flippen der Karte:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resetUnmatchedCards($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            return response()->json(['message' => 'Aktion erfolgreich']);
        } catch (\Exception $e) {
            Log::error('Fehler beim Zurücksetzen der Karten:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Zurücksetzen der Karten'], 500);
        }
    }
}