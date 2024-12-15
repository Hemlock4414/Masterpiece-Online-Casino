<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Broadcasting\PresenceChannel;

class MemoryPlayer extends Model
{
    use HasFactory;
    use BroadcastsEvents;

    protected $table = 'memory_players';
    protected $primaryKey = 'player_id';

    protected $fillable = [
        'user_id', 
        'guest_id',
        'name',
    ];

    public function games()
    {
        return $this->belongsToMany(MemoryGame::class, 'memory_game_player', 'player_id', 'game_id')
                    ->using(MemoryGamePlayer::class)
                    ->withPivot(['player_score'])
                    ->withTimestamps();
    }

    public static function createOrGetGuest($guestId)
    {
        if ($guestId) {
            $player = self::where('guest_id', $guestId)->first();
            if ($player) {
                return $player;
            }
        }
    
        // Session ID für Konsistenz verwenden
        $sessionId = session()->getId();
        $guestId = 'guest_' . $sessionId;
    
        $player = new self([
            'name' => 'Gast ' . substr($guestId, -4),
            'guest_id' => $guestId
        ]);
        $player->save();
        
        session(['memoryGuestId' => $guestId]);
        session()->save();  // Explizit speichern
        
        return $player;
    }

    public function broadcastOn(string $event): array
    {
        return [new PresenceChannel('presence-game.lobby')];
    }

    public function broadcastWith(string $event): array
    {
        return [
            'id' => $this->player_id,
            'name' => $this->name,
            'isRegistered' => !is_null($this->user_id),
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function matchedCards()
    {
        return $this->hasMany(MemoryCard::class, 'matched_by');
    }

    public function incrementScore(MemoryGame $game, $points = 1)
    {
        $currentScore = $this->games()->where('game_id', $game->game_id)->first()->pivot->player_score;
        $this->games()->updateExistingPivot($game->game_id, [
            'player_score' => $currentScore + $points
        ]);
        return $this;
    }

    public function getCurrentScore(MemoryGame $game)
    {
        return $this->games()->where('game_id', $game->game_id)->first()->pivot->player_score ?? 0;
    }

    public function canJoinGame(MemoryGame $game): bool
    {
        $currentPlayers = $game->players()->count();
        return $currentPlayers < 2 && $game->isWaiting();
    }

    public function isActiveIn(MemoryGame $game): bool
    {
        return $game->player_turn === $this->player_id;
    }

    public function hasMatchedPair(MemoryGame $game): bool
    {
        return $this->matchedCards()
            ->where('game_id', $game->game_id)
            ->where('is_matched', true)
            ->exists();
    }
}