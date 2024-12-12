<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MemoryPlayer;

class AuthenticateGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Wenn der Benutzer bereits eingeloggt ist, überspringe die Gast-Authentifizierung
        if (auth()->check()) {
            return $next($request);
        }

        // Prüfe ob bereits eine Gast-ID in der Session existiert
        if (!session()->has('memoryGuestId')) {
            // Erstelle eine neue Gast-ID
            $guestId = 'guest_' . Str::random(10);
            session(['memoryGuestId' => $guestId]);

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
            $request->setUserResolver(function () {
                return null; // Erlaubt Gast-Zugriff auf Presence Channels
            });
        }

        return $next($request);
    }
}