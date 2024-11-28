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
        'is_flipped',
        'card_image',
        'group_id',
    ];

    protected $casts = [
        'is_matched' => 'boolean',
        'is_flipped' => 'boolean',
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
}