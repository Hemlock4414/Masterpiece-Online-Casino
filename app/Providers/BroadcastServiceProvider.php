<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Diese Route ermöglicht die Authentifizierung für private und presence channels
        Broadcast::routes(['middleware' => ['web']]);

        require base_path('routes/channels.php');
    }
}