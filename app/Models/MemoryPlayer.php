<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryPlayer extends Model
{
    use HasFactory;

    protected $table = 'memory_players';
    protected $primaryKey = 'player_id';

    protected $fillable = [
        'user_id',
        'name',
    ];

    // Relationships
    public function games()
    {
        return $this->belongsToMany(MemoryGame::class, 'memory_game_player', 'player_id', 'game_id')
                    ->using(MemoryGamePlayer::class)
                    ->withPivot(['player_score'])
                    ->withTimestamps();
    }

    public static function createOrGetGuest($guestId = null)
    {
        if ($guestId) {
            $player = self::where('player_id', $guestId)
                         ->where('name', 'LIKE', 'Gast%')
                         ->first();
            if ($player) {
                return $player;
            }
        }

        $player = new self([
            'name' => 'Gast ' . rand(1000, 9999)
        ]);
        $player->save();
        
        session(['memoryGuestId' => $player->player_id]);
        
        return $player;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function matchedCards()
    {
        return $this->hasMany(MemoryCard::class, 'matched_by');
    }

    // Helper Methoden
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