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
        'stopped_at' => 'datetime'
    ];

    // Status-Konstanten
    const STATUS_WAITING = 'waiting';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_FINISHED = 'finished';

    protected static function booted()
    {
        static::saving(function ($game) {
            if ($game->status === self::STATUS_IN_PROGRESS && !$game->player_turn) {
                throw new \Exception('Ein laufendes Spiel muss einen aktiven Spieler haben');
            }
        });
    }

    // Relationships
    public function cards()
    {
        return $this->hasMany(MemoryCard::class, 'game_id')
                    ->inRandomOrder();
    }

    public function players()
    {
        return $this->belongsToMany(MemoryPlayer::class, 'memory_game_player', 'game_id', 'player_id')
                    ->using(MemoryGamePlayer::class)
                    ->withPivot(['player_score'])
                    ->withTimestamps();
    }

    // Status Helper
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

    // Spieler Management
    public function getActivePlayer()
    {
        return $this->players()->where('player_id', $this->player_turn)->first();
    }

    public function canPlayerMove(MemoryPlayer $player): bool
    {
        return $this->isInProgress() && $this->player_turn === $player->player_id;
    }

    public function checkGameCompletion(): bool
    {
        $allMatched = $this->cards()->whereNull('matched_by')->count() === 0;
        
        if ($allMatched) {
            $this->update([
                'status' => self::STATUS_FINISHED,
                'stopped_at' => now()
            ]);
        }
        
        return $allMatched;
    }

    public function start()
    {
        if (!$this->isWaiting()) {
            throw new \Exception('Spiel kann nicht gestartet werden');
        }
    
        $firstPlayer = $this->players()->inRandomOrder()->first();
        if (!$firstPlayer) {
            throw new \Exception('Keine Spieler verfügbar');
        }
        
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'player_turn' => $firstPlayer->player_id
        ]);
        
        return $this;
    }

    public function nextTurn()
    {
        if (!$this->isInProgress()) {
            throw new \Exception('Spielzug nur bei laufendem Spiel möglich');
        }
        
        $nextPlayer = $this->players()
            ->where('player_id', '!=', $this->player_turn)
            ->first();
            
        if (!$nextPlayer) {
            throw new \Exception('Kein nächster Spieler verfügbar');
        }
        
        $this->update(['player_turn' => $nextPlayer->player_id]);
        return $this;
    }
}