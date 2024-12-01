<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryCard extends Model
{
    use HasFactory;

    protected $table = 'memory_cards';
    protected $primaryKey = 'card_id';

    protected $fillable = [
        'game_id',
        'matched_by',
        'is_matched',
        'card_image',
        'group_id',
    ];

    protected $casts = [
        'is_matched' => 'boolean',
    ];

    // Relationships
    public function game()
    {
        return $this->belongsTo(MemoryGame::class, 'game_id');
    }

    public function matchedBy()
    {
        return $this->belongsTo(MemoryPlayer::class, 'matched_by');
    }

    // Helper Methoden
    public function flip()
    {
        if ($this->is_matched) {
            throw new \Exception('Bereits gematchte Karte kann nicht geflippt werden');
        }
        
        $this->update(['is_flipped' => !$this->is_flipped]);
        return $this;
    }

    public function match(MemoryPlayer $player)
    {
        $this->update([
            'is_matched' => true,
            'matched_by' => $player->player_id
        ]);
        return $this;
    }

    public function reset()
    {
        $this->update([
            'is_flipped' => false,
            'is_matched' => false,
            'matched_by' => null
        ]);
        return $this;
    }

    public function canBeFlippedBy(MemoryPlayer $player): bool
    {
        return !$this->is_matched && 
            $this->game->isInProgress() && 
            $this->game->player_turn === $player->player_id;
    }

    public function hasMatchingCardFlipped(): bool
    {
        return $this->game->cards()
            ->where('group_id', $this->group_id)
            ->where('card_id', '!=', $this->card_id)
            ->where('is_flipped', true)
            ->where('is_matched', false)
            ->exists();
    }
}