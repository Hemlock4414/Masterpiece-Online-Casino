<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lobby extends Model
{
    use HasFactory;
    
    protected $table = 'lobbies';
    protected $primaryKey = 'lobby_id';

    protected $fillable = [
        'challenger_id',
        'challenger_type',
        'challenger_name',
        'challenger_user_id',
        'challenged_id',
        'challenged_type',
        'challenged_name',
        'challenged_user_id',
        'status',
        'game_type'
    ];

    // Morphe Beziehungen für verschiedene Spielertypen
    public function challenger()
    {
        return $this->morphTo(__FUNCTION__, 'challenger_type', 'challenger_id');
    }

    public function challenged()
    {
        return $this->morphTo(__FUNCTION__, 'challenged_type', 'challenged_id');
    }

    // User Beziehungen
    public function challengerUser()
    {
        return $this->belongsTo(User::class, 'challenger_user_id');
    }

    public function challengedUser()
    {
        return $this->belongsTo(User::class, 'challenged_user_id');
    }
}