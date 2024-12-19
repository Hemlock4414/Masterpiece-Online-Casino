<?php

namespace App\Http\Controllers;

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
        $filePath = storage_path('app/countries.json');
        
        // Prüfen ob die Datei existiert
        if (!file_exists($filePath)) {
            \Log::error('countries.json nicht gefunden in: ' . $filePath);
            return response()->json([
                'hauptLaender' => $this->hauptLaender,
                'uebrigeLaender' => []
            ]);
        }

        try {
            $alleLaender = json_decode(file_get_contents($filePath), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON Parsing Error: ' . json_last_error_msg());
            }

            // Filtere die Hauptländer aus der Liste der übrigen Länder
            $uebrigeLaender = array_values(array_filter($alleLaender, function($land) {
                return !in_array($land, $this->hauptLaender);
            }));

            return response()->json([
                'hauptLaender' => $this->hauptLaender,
                'uebrigeLaender' => $uebrigeLaender
            ]);
        } catch (\Exception $e) {
            \Log::error('Fehler beim Lesen der countries.json: ' . $e->getMessage());
            return response()->json([
                'hauptLaender' => $this->hauptLaender,
                'uebrigeLaender' => []
            ]);
        }
    }
}