<?php

namespace App\Broadcasting;

use App\Models\MemoryPlayer;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GameLobbyChannel
{
    public function join($user = null)
    {
        try {
            $player = $user 
                ? MemoryPlayer::where('user_id', $user->id)->first()
                : MemoryPlayer::firstWhere('guest_id', session('memoryGuestId'))->first();

            if (!$player) {
                // Debug-Logging
                \Log::info('Player nicht gefunden', [
                    'user' => $user,
                    'guestId' => session('memoryGuestId')
                ]);
                return false;
            }

            // Debug-Logging
            \Log::info('Player gefunden', [
                'player' => $player,
                'response' => [
                    'id' => $player->player_id,
                    'name' => $player->name,
                    'status' => 'available',
                    'isRegistered' => !is_null($player->user_id)
                ]
            ]);

            return [
                'id' => $player->player_id,
                'name' => $player->name,
                'status' => 'available',
                'isRegistered' => !is_null($player->user_id)
            ];
        } catch (\Exception $e) {
            \Log::error('GameLobbyChannel Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}