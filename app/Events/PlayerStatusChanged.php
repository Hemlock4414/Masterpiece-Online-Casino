<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PlayerStatusChanged implements ShouldBroadcast
{
    use SerializesModels;

    public $player;

    public function __construct($player)
    {
        \Log::info('PlayerStatusChanged Event erstellt', ['player' => $player]);
        $this->player = $player;
    }

    public function broadcastOn()
    {
        return new Channel('lobby');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->player['id'],
            'name' => $this->player['name'],
            'status' => $this->player['status'],
            'isRegistered' => $this->player['isRegistered'] ?? false
        ];
    }
}