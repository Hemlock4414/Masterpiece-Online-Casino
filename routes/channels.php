// routes/channels.php
<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\MemoryPlayer;

Broadcast::channel('presence-game.lobby', function ($user = null) {
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
        \Log::error('Presence Channel Auth Error:', ['error' => $e->getMessage()]);
        return false;
    }
});

// Private Spiel-Channel für spezifische Spiele
Broadcast::channel('game.{gameId}', function ($user, $gameId) {
    try {
        $player = $user 
            ? MemoryPlayer::where('user_id', $user->id)->first()
            : MemoryPlayer::createOrGetGuest(session('memoryGuestId'));

        if (!$player) {
            return false;
        }

        // Prüfe ob der Spieler am Spiel teilnimmt
        $game = \App\Models\MemoryGame::find($gameId);
        return $game && $game->players()->where('player_id', $player->player_id)->exists();
    } catch (\Exception $e) {
        \Log::error('Game Channel Auth Error:', ['error' => $e->getMessage()]);
        return false;
    }
});