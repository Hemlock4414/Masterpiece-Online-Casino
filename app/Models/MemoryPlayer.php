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
        'guest_id',
        'status',
        'last_seen_at'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    // Bestehende Relationships bleiben unverändert
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
            $player = self::where('guest_id', $guestId)->first();
            if ($player) {
                $player->touch(); // Aktualisiert last_seen_at
                return $player;
            }
        }
    
        // Erstelle neuen Gast-Spieler
        $player = new self([
            'name' => 'Gast ' . rand(1000, 9999),
            'guest_id' => $guestId ?? 'guest_' . uniqid(),
            'status' => 'available',
            'last_seen_at' => now()
        ]);
        $player->save();
        
        session(['memoryGuestId' => $player->guest_id]);
        
        return $player;
    }

    // Neue Methoden für Presence Channel
    public function isOnline()
    {
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 5;
    }

    public function getPresenceData()
    {
        return [
            'id' => $this->player_id,
            'name' => $this->name,
            'status' => $this->status ?? 'available',
            'isRegistered' => !is_null($this->user_id),
            'last_seen' => $this->last_seen_at?->timestamp
        ];
    }

    public function updateStatus($status)
    {
        $this->status = $status;
        $this->last_seen_at = now();
        $this->save();
        return $this;
    }

    // Bestehende Methoden bleiben unverändert...
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