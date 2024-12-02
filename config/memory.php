<?php

return [
    'cleanup' => [
        'finished_games_days' => env('MEMORY_CLEANUP_FINISHED_GAMES_DAYS', 1),
        'guest_players_days' => env('MEMORY_CLEANUP_GUEST_PLAYERS_DAYS', 1),
    ]
];