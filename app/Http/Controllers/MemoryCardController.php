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
            $factory = new MemoryCardFactory();
    
            // Debug: Theme prüfen
            Log::info('Current game theme:', ['theme' => $game->theme ?? 'emojis']);
    
            $mappedCards = $cards->map(function($card) use ($game, $factory) {
                $content = $factory->getCardContent($game->theme ?? 'emojis', $card->group_id);
                
                // Debug: Karteninhalt prüfen
                Log::info('Card content:', [
                    'group_id' => $card->group_id,
                    'content' => $content
                ]);
    
                return [
                    'card_id' => $card->card_id,
                    'game_id' => $card->game_id,
                    'matched_by' => $card->matched_by,
                    'group_id' => $card->group_id,
                    'content' => $content['content'],
                    'name' => $content['name']
                ];
            });
    
            return response()->json($mappedCards);
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen der Karten:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Abrufen der Karten'], 500);
        }
    }

    public function updateMatched($gameId, Request $request)
    {
        try {
            $validated = $request->validate([
                'card_ids' => 'required|array',
                'card_ids.*' => 'required|integer',
                'player_id' => 'required|integer'
            ]);

            $game = MemoryGame::findOrFail($gameId);

            MemoryCard::where('game_id', $gameId)
                ->whereIn('card_id', $validated['card_ids'])
                ->update([
                    'matched_by' => $validated['player_id']
                ]);

            return response()->json([
                'message' => 'Karten erfolgreich gematcht',
                'updated_cards' => $validated['card_ids']
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Matchen der Karten:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Fehler beim Aktualisieren der gematchten Karten'
            ], 500);
        }
    }
}