<?php

namespace App\Broadcasting;

use App\Models\MemoryPlayer;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GameLobbyChannel
{
    public function join($user = null): array|bool
    {
        $player = $user 
            ? MemoryPlayer::where('user_id', $user->id)->first()
            : MemoryPlayer::where('guest_id', session('memoryGuestId'))->first();

        if (!$player) return false;

        return [
            'id' => $player->player_id,
            'name' => $player->name,
            'status' => 'available', // Default Status bei Join
            'isRegistered' => !is_null($player->user_id)
        ];
    }
}
