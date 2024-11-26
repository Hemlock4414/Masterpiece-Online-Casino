<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryPlayer extends Model
{
    use HasFactory;

    protected $table = 'memory_players'; // Tabelle für Memory-Spieler
    protected $primaryKey = 'player_id';

    protected $fillable = [
        'user_id', // Falls ein Spieler mit einem Benutzer verknüpft ist
    ];

    public function games()
    {
        return $this->belongsToMany(MemoryGame::class, 'memory_game_player', 'player_id', 'game_id')
                    ->using(MemoryGamePlayer::class)
                    ->withPivot('player_score');
    }

    public function user()
    {
    return $this->belongsTo(User::class, 'user_id');
    }
    
}
