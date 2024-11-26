<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MemoryGamePlayer extends Pivot
{
    protected $table = 'memory_game_player'; // Pivot-Tabelle
    protected $primaryKey = 'game_player_id';

    protected $fillable = [
        'game_id',
        'player_id',
        'player_score',
    ];
}

