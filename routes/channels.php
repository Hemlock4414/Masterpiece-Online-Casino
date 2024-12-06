<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\MemoryPlayer;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('lobby', function () {
    return true; // Offener Channel
});
