<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PlayerStatusChanged implements ShouldBroadcast
{
    use SerializesModels;

    public $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('presence-game.lobby')];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->player['id'],
            'name' => $this->player['name'],
            'status' => $this->player['status'],
            'isRegistered' => $this->player['isRegistered'] ?? false,
            'timestamp' => now()->timestamp
        ];
    }
}