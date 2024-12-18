<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryCard;
use App\Models\MemoryPlayer;
use Database\Factories\MemoryCardFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MemoryGameController extends Controller
{
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'cards_count' => 'required|integer|in:12,16,20',
                'guest_id' => 'nullable|integer',
                'theme' => 'required|string'
            ]);
    
            $game = new MemoryGame([
                'status' => 'waiting',
                'theme' => $validated['theme']
            ]);
            $game->save();
    
            // Spieler erstellen/finden...
            if (auth()->check()) {
                $player = MemoryPlayer::firstOrCreate(
                    ['user_id' => auth()->id()],
                    ['name' => auth()->user()->username]
                );
            } else {
                if ($request->guest_id) {
                    $player = MemoryPlayer::where('player_id', $request->guest_id)
                                        ->where('name', 'LIKE', 'Gast%')
                                        ->first();
                }
                
                if (!isset($player) || !$player) {
                    $player = new MemoryPlayer([
                        'name' => 'Gast ' . rand(1000, 9999)
                    ]);
                    $player->save();
                }
            }
    
            $game->players()->attach($player->player_id, [
                'player_score' => 0
            ]);
    
            // Karten erstellen
            $factory = new MemoryCardFactory();
            $cards = $factory->generateCardsForTheme(
                $validated['theme'],
                $validated['cards_count'] / 2
            );
            
            // Karten speichern und mit Inhalten anreichern
            $createdCards = collect();
            foreach ($cards as $cardData) {
                $cardData['game_id'] = $game->game_id;
                $card = MemoryCard::create($cardData);
                
                // Inhalt hinzufügen
                $content = $factory->getCardContent($validated['theme'], $card->group_id);                
                $card = array_merge($card->toArray(), [
                    'content' => $content['content'],
                    'name' => $content['name']
                ]);

                $createdCards->push($card);
            }
    
            DB::commit();

            $cardsData = $createdCards->map(function($card) {
                Log::info('Card before sending:', [
                    'id' => $card['card_id'],
                    'groupId' => $card['group_id'],
                    'content' => $card['content'] ?? 'no content',
                    'name' => $card['name'] ?? 'no name'
                ]);
                return $card;
            })->shuffle();
    
            $response = [
                'game' => [
                    'game_id' => $game->game_id,
                    'status' => $game->status,
                    'cards' => $cardsData,
                    'players' => $game->players
                ],
                'message' => 'Spiel erfolgreich erstellt'
            ];
            
            // Log::info('Final response:', $response);
            
            return response()->json($response, 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fehler beim Erstellen des Spiels:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Erstellen des Spiels'], 500);
        }
    }

    public function getCustomThemes()
    {
        try {
            $factory = new MemoryCardFactory();
            $themes = $factory->getCustomThemes();
            
            return response()->json($themes);
        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen der benutzerdefinierten Themen:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Abrufen der Themen'], 500);
        }
    }

    public function start($gameId, Request $request)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
    
            if ($game->status !== 'waiting') {
                return response()->json([
                    'error' => 'Spiel kann nicht gestartet werden',
                    'reason' => "Status ist '{$game->status}', sollte 'waiting' sein"
                ], 400);
            }
    
            // Hole den ersten verfügbaren Spieler
            $firstPlayer = $game->players()->first();
            
            if (!$firstPlayer) {
                return response()->json([
                    'error' => 'Keine Spieler verfügbar',
                ], 400);
            }
    
            // Aktualisiere das Spiel mit dem ersten Spieler als player_turn
            $game->update([
                'status' => 'in_progress',
                'player_turn' => $firstPlayer->player_id
            ]);
    
            // Factory für Karteninhalte
            $factory = new MemoryCardFactory();
            
            // Karten mit Inhalten anreichern
            $cards = $game->cards->map(function($card) use ($game, $factory) {
                $content = $factory->getCardContent($game->theme, $card->group_id);
                
                return array_merge($card->toArray(), [
                    'content' => $content['content'],
                    'name' => $content['name']
                ]);
            });
    
            // Lade die aktualisierten Beziehungen und setze die angereicherten Karten
            $game->load('players');
            
            return response()->json([
                'message' => 'Spiel erfolgreich gestartet',
                'game' => array_merge($game->toArray(), ['cards' => $cards]),
                'active_player' => $firstPlayer
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting game:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'game_id' => $gameId
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            $game->load(['cards', 'players']);
            
            return response()->json([
                'game_id' => $game->game_id,
                'status' => $game->status,
                'cards' => $game->cards,
                'players' => $game->players,
            ]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Laden des Spiels:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Laden des Spiels'], 500);
        }
    }

    public function stop($gameId, Request $request)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:finished,aborted'
            ]);
    
            $game = MemoryGame::findOrFail($gameId);
            $game->update([
                'status' => $validated['status'],
                'stopped_at' => now()
            ]);
    
            return response()->json([
                'message' => $validated['status'] === 'aborted' ? 'Spiel abgebrochen' : 'Spiel beendet',
                'status' => $validated['status']
            ]);
        } catch (\Exception $e) {
            Log::error('Fehler beim Beenden des Spiels:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fehler beim Beenden des Spiels'], 500);
        }
    }

    public function updatePlayerScore(Request $request, MemoryGame $game, $playerId)
    {
        try {
            $validated = $request->validate([
                'player_score' => 'required|integer|min:0'
            ]);

            $game->players()->updateExistingPivot($playerId, [
                'score' => $validated['player_score']
            ]);

            return response()->json([
                'message' => 'Punktzahl erfolgreich aktualisiert'
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren der Punktzahl:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Fehler beim Aktualisieren der Punktzahl',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Spielerwechsel
    public function nextTurn($gameId)
    {
        try {
            $game = MemoryGame::findOrFail($gameId);
            
            if (!$game->isInProgress()) {
                return response()->json([
                    'error' => 'Spielerwechsel nur bei aktivem Spiel möglich'
                ], 400);
            }

            $game->nextTurn();
            
            return response()->json([
                'message' => 'Spielerwechsel erfolgreich',
                'active_player' => $game->getActivePlayer()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Fehler beim Spielerwechsel:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}