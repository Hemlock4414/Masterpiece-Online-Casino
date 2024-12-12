<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Memory-Cleanup täglich um Mitternacht
        $schedule->command('memory:cleanup')
                ->dailyAt('00:00')
                ->appendOutputTo(storage_path('logs/memory-cleanup.log'));

        $schedule->command('lobby:check-expired')
                ->everyMinute()
                ->appendOutputTo(storage_path('logs/lobby-expired.log'));
                
        // Schedule für Presence Channel Cleanup
        $schedule->command('presence:cleanup')
                ->hourly()
                ->appendOutputTo(storage_path('logs/presence-cleanup.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}