<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $name;
    public $status;

    public $player;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->status = $data['status'];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('lobby');
    }

    // Optionale Methode um die Daten zu formatieren
    public function broadcastWith(): array
    {
        return [
            'id' => $this->player->id,
            'name' => $this->player->name,
            'status' => $this->player->status,
            'type' => $this->player->type
        ];
    }
}