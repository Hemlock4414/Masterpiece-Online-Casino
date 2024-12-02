<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MemoryPlayer;
use App\Models\MemoryGame;
use Carbon\Carbon;

class CleanupMemoryGames extends Command
{
    protected $signature = 'memory:cleanup {--dry-run : Zeigt nur an, was gelöscht würde}';
    protected $description = 'Bereinigt alte Memory-Spiele und nicht mehr benötigte Gast-Spieler';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("Starte Bereinigung...");
        $this->info($dryRun ? "DRY RUN - Es werden keine Daten gelöscht" : "LIVE RUN");

        // Erst alle Spiele anzeigen
        $allGames = MemoryGame::where('status', 'finished')
                    ->orWhere('status', 'aborted')
                    ->get();
        
        $this->info("Gesamt gefundene Spiele: " . $allGames->count());
        foreach ($allGames as $game) {
            $this->info("Spiel ID: {$game->game_id}, Status: {$game->status}, Letzte Aktualisierung: {$game->updated_at}");
        }

        // Dann die alten Spiele
        $oldGames = MemoryGame::where(function($query) {
            $query->where('status', 'finished')
                  ->orWhere('status', 'aborted');
        })
        ->where('updated_at', '<', Carbon::now()->subDay())
        ->get();

        $this->info("\nGefundene alte Spiele: " . $oldGames->count());

        // Alle Gast-Spieler anzeigen
        $allGuests = MemoryPlayer::whereNull('user_id')->get();
        $this->info("\nGesamt gefundene Gast-Spieler: " . $allGuests->count());
        foreach ($allGuests as $guest) {
            $this->info("Gast ID: {$guest->player_id}, Name: {$guest->name}, Letzte Aktualisierung: {$guest->updated_at}");
        }

        // Dann die zu löschenden Gast-Spieler
        $guestPlayers = MemoryPlayer::whereNull('user_id')
            ->whereDoesntHave('games', function($query) {
                $query->where('status', 'in_progress')
                      ->orWhere('status', 'waiting');
            })
            ->where('updated_at', '<', Carbon::now()->subDay())
            ->get();

        $this->info("\nZu löschende Gast-Spieler: " . $guestPlayers->count());

        $this->info("\nBereinigung abgeschlossen!");
    }
}