<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lobby extends Model
{
    protected $table = 'lobbies';
    protected $primaryKey = 'lobby_id';

    protected $fillable = [
        'challenger_id',
        'challenged_id',
        'status',
        'game_type'
    ];

    public function challenger()
    {
        return $this->belongsTo(MemoryPlayer::class, 'challenger_id', 'player_id');
    }

    public function challenged()
    {
        return $this->belongsTo(MemoryPlayer::class, 'challenged_id', 'player_id');
    }

    // Helper Methoden
    public function isActive()
    {
        return $this->status === 'pending' || $this->status === 'accepted';
    }

    public function canJoin(MemoryPlayer $player)
    {
        return $this->challenged_id === $player->player_id && $this->status === 'pending';
    }
}