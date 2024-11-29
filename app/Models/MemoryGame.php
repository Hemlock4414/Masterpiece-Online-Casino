<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryGame extends Model
{
    use HasFactory;

    protected $table = 'memory_games';
    protected $primaryKey = 'game_id';

    protected $fillable = [
        'status',
        'player_turn',
        'stopped_at',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'player_turn' => 'integer',
        'stopped_at' => 'datetime',
    ];

    // Status-Konstanten
    const STATUS_WAITING = 'waiting';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_FINISHED = 'finished';

    // Rest der Beziehungen
    public function cards()
    {
        return $this->hasMany(MemoryCard::class, 'game_id');
    }

    public function players()
    {
        return $this->belongsToMany(MemoryPlayer::class, 'memory_game_player', 'game_id', 'player_id')
                    ->using(MemoryGamePlayer::class)
                    ->withPivot(['player_score'])
                    ->withTimestamps();
    }

    // Helper Methoden
    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function getActivePlayer()
    {
        return $this->players()->where('player_id', $this->player_turn)->first();
    }

    public function getFlippedCards()
    {
        return $this->cards()
            ->where('is_flipped', true)
            ->where('is_matched', false)
            ->get();
    }

    public function checkGameCompletion()
    {
        $allMatched = $this->cards()->where('is_matched', false)->count() === 0;
        if ($allMatched) {
            $this->update([
                'status' => self::STATUS_FINISHED,
                'stopped_at' => now()
            ]);
        }
        return $allMatched;
    }

    // Neue Methode für Status-Änderung
    public function start()
    {
        if ($this->status !== self::STATUS_WAITING) {
            throw new \Exception('Spiel kann nicht gestartet werden');
        }
        
        $this->status = self::STATUS_IN_PROGRESS;
        $this->save();
        
        return $this;
    }
}