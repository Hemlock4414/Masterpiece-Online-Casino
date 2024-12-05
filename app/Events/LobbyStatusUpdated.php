<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LobbyStatusUpdated implements ShouldBroadcast
{
    public $lobby;

    public function __construct(Lobby $lobby)
    {
        $this->lobby = $lobby;
    }

    public function broadcastOn()
    {
        return new Channel('lobby');
    }
    public function broadcastWith(): array
    {
        return [
            'lobby_id' => $this->lobby->lobby_id,
            'challenger_id' => $this->lobby->challenger_id,
            'challenger_name' => $this->lobby->challenger_name,
            'challenged_id' => $this->lobby->challenged_id,
            'challenged_name' => $this->lobby->challenged_name,
            'status' => $this->lobby->status,
        ];
    }
}
