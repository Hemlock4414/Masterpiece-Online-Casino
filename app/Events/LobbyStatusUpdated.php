<?php

namespace App\Events;

use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LobbyStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lobby;

    public function __construct(Lobby $lobby)
    {
        $this->lobby = $lobby;
    }

    public function broadcastOn()
    {
        return new Channel('lobby'); // Normaler Channel statt Presence
    }

    public function broadcastWith(): array
    {
        return [
            'lobby_id' => $this->lobby->lobby_id,
            'challenger_id' => $this->lobby->challenger_id,
            'challenger_name' => $this->lobby->challenger_name,
            'challenger_registered' => !is_null($this->lobby->challenger_user_id),
            'challenged_id' => $this->lobby->challenged_id,
            'challenged_name' => $this->lobby->challenged_name,
            'challenged_registered' => !is_null($this->lobby->challenged_user_id),
            'status' => $this->lobby->status,
            'game_type' => $this->lobby->game_type,
            'expires_at' => $this->lobby->expires_at
        ];
    }
}