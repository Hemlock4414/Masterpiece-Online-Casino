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
        'group_id'
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
    public function match(MemoryPlayer $player)
    {
        $this->update([
            'matched_by' => $player->player_id
        ]);
        return $this;
    }

    public function reset()
    {
        $this->update([
            'matched_by' => null
        ]);
        return $this;
    }
}