<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LobbyController;

class CheckExpiredChallenges extends Command
{
    protected $signature = 'lobby:check-expired';
    protected $description = 'Prüft und entfernt abgelaufene Herausforderungen';

    public function handle()
    {
        $controller = new LobbyController();
        $controller->checkExpiredChallenges();
        $this->info('Abgelaufene Herausforderungen wurden geprüft.');
    }
}