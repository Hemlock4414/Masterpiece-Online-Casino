<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CountryController extends Controller
{
    private $hauptLaender = [
        "Schweiz",
        "Deutschland",
        "Österreich",
        "Liechtenstein"
    ];

    public function getCountries()
    {
        try {
            // Wenn keine countries.json existiert, geben wir nur die Hauptländer zurück
            return response()->json([
                'hauptLaender' => $this->hauptLaender,
                'uebrigeLaender' => []
            ]);
        } catch (\Exception $e) {
            \Log::error('Fehler in CountryController: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}