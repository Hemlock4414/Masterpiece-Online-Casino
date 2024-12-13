<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Broadcasting\PresenceChannel;

class Lobby extends Model
{
    use HasFactory;
    use BroadcastsEvents;
    
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

    public function broadcastOn(string $event): array
    {
        return [new PresenceChannel('presence-game.lobby')];
    }

    public function broadcastWith(string $event): array
    {
        return [
            'lobby_id' => $this->lobby_id,
            'challenger_id' => $this->challenger_id,
            'challenger_name' => $this->challenger_name,
            'challenger_status' => $this->challenger_status,
            'challenged_id' => $this->challenged_id,
            'challenged_name' => $this->challenged_name,
            'challenged_status' => $this->challenged_status,
            'status' => $this->status,
            'game_type' => $this->game_type
        ];
    }

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