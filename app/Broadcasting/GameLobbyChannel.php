<?php

namespace App\Broadcasting;

use App\Models\MemoryPlayer;
use Illuminate\Support\Facades\Log;

class GameLobbyChannel
{
    public function join($user = null)
    {
        try {
            $player = $user 
                ? MemoryPlayer::where('user_id', $user->id)->first()
                : MemoryPlayer::createOrGetGuest(session('memoryGuestId'));

            if (!$player) {
                return false;
            }

            return [
                'id' => $player->player_id,
                'name' => $player->name,
                'status' => 'available',
                'isRegistered' => !is_null($player->user_id),
                'last_seen' => now()->timestamp
            ];
        } catch (\Exception $e) {
            Log::error('Presence Channel Join Error:', ['error' => $e->getMessage()]);
            return false;
        }
    }
}