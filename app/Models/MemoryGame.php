<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryGame extends Model
{
    use HasFactory;

    // Tabelle, die dieses Modell repräsentiert
    protected $table = 'memory_games';

    // Primärschlüssel
    protected $primaryKey = 'game_id';

    // Felder, die für Massenbearbeitung erlaubt sind
    protected $fillable = [
        'status',
        'player_turn', // Für Mehrspieler: aktueller Spieler am Zug
        'stopped_at',  // Zeitpunkt des Spielendes
    ];

    // Beziehung: Ein Spiel hat viele Karten
    public function cards()
    {
        return $this->hasMany(MemoryCard::class, 'game_id');
    }

    // Beziehung: Ein Spiel hat Spieler (Mehrspieler-Unterstützung)
    public function players()
    {
        return $this->belongsToMany(MemoryPlayer::class, 'memory_game_player', 'game_id', 'player_id')
                    ->using(MemoryGamePlayer::class)
                    ->withPivot('player_score');
    }
}
