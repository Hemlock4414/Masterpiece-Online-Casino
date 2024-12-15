<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MemoryPlayer;

class AuthenticateGuest
{
    public function handle(Request $request, Closure $next)
    {
        // Debug-Log für Session-Status
        \Log::info('Session Status:', [
            'has_guest_id' => session()->has('memoryGuestId'),
            'guest_id' => session('memoryGuestId'),
            'session_id' => session()->getId()
        ]);

        // Wenn der Benutzer bereits eingeloggt ist, überspringe die Gast-Authentifizierung
        if (auth()->check()) {
            return $next($request);
        }

        // Prüfe ob bereits eine Gast-ID in der Session existiert
        if (!session()->has('memoryGuestId')) {
            // Erstelle eine neue Gast-ID
            $guestId = 'guest_' . Str::random(10);
            session(['memoryGuestId' => $guestId]);
            session()->save(); // Explizit speichern

            \Log::info('Neue Gast-ID erstellt:', ['guestId' => $guestId]);

            // Erstelle einen Gast-Spieler in der Datenbank
            try {
                MemoryPlayer::createOrGetGuest($guestId);
            } catch (\Exception $e) {
                \Log::error('Fehler beim Erstellen des Gast-Spielers:', [
                    'error' => $e->getMessage(),
                    'guestId' => $guestId
                ]);
            }
        }

        // Setze den Gast-Status für Presence Channel
        if ($request->is('broadcasting/auth*')) {
            $player = MemoryPlayer::where('guest_id', session('memoryGuestId'))->first();
            
            if ($player) {
                $request->setUserResolver(function () use ($player) {
                    return (object)[
                        'id' => null,
                        'memory_player' => $player
                    ];
                });
            }
        }

        return $next($request); // Dies fehlte vorher
    }
}