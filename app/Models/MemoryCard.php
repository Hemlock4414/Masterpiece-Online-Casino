<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryCard extends Model
{
    use HasFactory;

    // Tabelle, die dieses Modell repräsentiert
    protected $table = 'memory_cards';

    // Primärschlüssel
    protected $primaryKey = 'card_id';

    // Felder, die für Massenbearbeitung erlaubt sind
    protected $fillable = [
        'game_id',
        'matched_by',
        'is_matched',
        'is_flipped',
        'card_image',
        'group_id',
    ];

    // Beziehung: Eine Karte gehört zu einem Spiel
    public function game()
    {
        return $this->belongsTo(MemoryGame::class, 'game_id');
    }

    // Beziehung: Eine Karte wurde von einem Spieler gematcht
    public function matchedBy()
    {
        return $this->belongsTo(MemoryPlayer::class, 'matched_by');
    }
}

