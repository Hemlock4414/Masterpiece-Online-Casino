<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerStatusChanged implements ShouldBroadcast
{
    use SerializesModels;

    public $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function broadcastOn()
    {
        return new Channel('lobby');
    }
}