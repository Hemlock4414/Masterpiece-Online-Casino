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
            if ($user && isset($user->memory_player)) {
                $player = $user->memory_player;
            } else {
                $player = $user 
                    ? MemoryPlayer::firstWhere('user_id', $user->id)
                    : MemoryPlayer::firstWhere('guest_id', session('memoryGuestId'));
            }
    
            if (!$player) {
                \Log::error('Auth failed', [
                    'user' => $user,
                    'session' => session()->all()
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
            \Log::error('GameLobbyChannel Error:', ['error' => $e->getMessage()]);
            return false;
        }
    }
}