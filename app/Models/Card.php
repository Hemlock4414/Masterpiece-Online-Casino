<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    use HasFactory;

    // Tabelle, die dieses Modell repräsentiert
    protected $table = 'cards';

    // Primärschlüssel
    protected $primaryKey = 'card_id';

    // Felder, die für Massenbearbeitung erlaubt sind
    protected $fillable = [
        'game_id',
        'is_matched',
        'is_flipped',
        'card_image',
        'group_id',
    ];

    // Beziehung: Eine Karte gehört zu einem Spiel
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
