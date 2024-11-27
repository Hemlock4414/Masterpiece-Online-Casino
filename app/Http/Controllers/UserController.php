<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        // Hole alle Benutzer aus der Datenbank
        $users = User::all();

        // Gib die Benutzer zurück
        return $users;
    }

    public function getUser()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    }

    public function register(Request $request)
    {
        Log::info('Registrierungsversuch:', [
            'birth_date' => $request->birth_date,
            'all_data' => $request->all()
        ]);

        // Validierung der Eingabedaten
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'birth_date' => ['required', 'string'],
            'nationality' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            // Hier fügen wir deutsche Fehlermeldungen hinzu
            'username.required' => 'Bitte geben Sie einen Spielernamen ein',
            'username.unique' => 'Dieser Spielername ist bereits vergeben',
            'firstname.required' => 'Bitte geben Sie Ihren Vornamen ein',
            'lastname.required' => 'Bitte geben Sie Ihren Nachnamen ein',
            'email.required' => 'Bitte geben Sie eine E-Mail-Adresse ein',
            'email.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein',
            'email.unique' => 'Diese E-Mail-Adresse ist bereits registriert',
            'birth_date.required' => 'Bitte geben Sie Ihr Geburtsdatum ein',
            'nationality.required' => 'Bitte geben Sie Ihre Nationalität an',
            'password.required' => 'Bitte geben Sie ein Passwort ein',
            'password.min' => 'Das Passwort muss mindestens 8 Zeichen lang sein',
            'password.confirmed' => 'Die Passwörter stimmen nicht überein'
        ]);

        if ($validator->fails()) {
            Log::warning('Validierungsfehler:', ['errors' => $validator->errors()->toArray()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $birthDate = $request->birth_date;
            
            // Wenn das Datum im deutschen Format ist (DD.MM.YYYY)
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $birthDate)) {
                Log::info('Konvertiere deutsches Datum', ['original' => $birthDate]);
                
                // Zerlege das Datum in seine Bestandteile
                list($day, $month, $year) = explode('.', $birthDate);
                
                // Erstelle das Datum im SQL-Format
                $birthDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                
                Log::info('Datum konvertiert', ['converted' => $birthDate]);
                
                // Validiere das konvertierte Datum
                if (!Carbon::createFromFormat('Y-m-d', $birthDate)->isValid()) {
                    throw new \Exception('Ungültiges Datum nach Konvertierung');
                }
            } 
            // Wenn das Datum bereits im ISO-Format ist (YYYY-MM-DD)
            elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
                Log::info('Datum bereits im ISO-Format', ['date' => $birthDate]);
                
                // Validiere das ISO-Datum
                if (!Carbon::createFromFormat('Y-m-d', $birthDate)->isValid()) {
                    throw new \Exception('Ungültiges ISO-Datum');
                }
            } 
            else {
                throw new \Exception('Datum ist in keinem erkannten Format');
            }

            Log::info('Finales Datum für Datenbank:', ['birth_date' => $birthDate]);

            // Speichere den Benutzer
            $user = User::create([
                'username' => $request->username,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'birth_date' => $birthDate,
                'nationality' => $request->nationality,
                'balance' => 0,
                'password' => Hash::make($request->password),
            ]);

            Log::info('User erfolgreich erstellt', ['user_id' => $user->id]);

            // Speichere das Profilbild, falls vorhanden
            if ($request->hasFile('profile_pic')) {
                $path = $request->file('profile_pic')->store('profile_pics', 'public');
                $user->profile_pic = $path;
                $user->save();
                Log::info('Profilbild gespeichert', ['path' => $path]);
            }

            // Nach erfolgreicher Registrierung direkt einloggen
            Auth::login($user);
            // Session regenerieren für Sicherheit
            $request->session()->regenerate();

            // Generiere die öffentliche URL des Profilbildes
            $profilePicUrl = $user->profile_pic ? asset('storage/' . $user->profile_pic) : null;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'birth_date' => Carbon::parse($user->birth_date)->format('d.m.Y'),
                    'nationality' => $user->nationality,
                    'balance' => $user->balance,
                    'profile_pic_url' => $profilePicUrl,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registrierungsfehler:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'input_date' => $request->birth_date
            ]);
            
            return response()->json([
                'errors' => [
                    'birth_date' => ['Fehler bei der Datumsverarbeitung: ' . $e->getMessage()]
                ]
            ], 422);
        }
    }

    public function login(Request $request)
    {
    $credentials = $request->validate([
        'login' => 'required|string',  // Ein Feld für beide (username oder email)
        'password' => 'required|string'
    ]);

    // Prüfe, ob die Eingabe eine E-Mail-Adresse ist
    $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Versuche den Login mit dem entsprechenden Feld
    $loginData = [
        $loginType => $credentials['login'],
        'password' => $credentials['password']
    ];

    if (Auth::attempt($loginData, $request->boolean('remember'))) {
        $request->session()->regenerate();
        
        return response()->json([
            'user' => Auth::user()
        ], 200);
    }

    return response()->json([
        'message' => 'Die Anmeldedaten sind ungültig.'
    ], 422);
    }

    public function show(Request $request)
    {
        try {
            Log::info('User-Abfrage gestartet', ['user_id' => $request->user()->id]);
            
            $user = $request->user();

            if (!$user) {
                Log::warning('Kein authentifizierter User gefunden');
                return response()->json(['error' => 'Nicht authentifiziert'], 401);
            }

            // Generiere die öffentliche URL des Profilbildes
            $profilePicUrl = $user->profile_pic ? asset('storage/' . $user->profile_pic) : null;

            // Formatiere das Datum ins deutsche Format
            $birthDate = $user->birth_date ? Carbon::parse($user->birth_date)->format('d.m.Y') : null;

            Log::info('User-Daten erfolgreich abgerufen', ['user_id' => $user->id]);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'birth_date' => $birthDate,
                    'nationality' => $user->nationality,
                    'balance' => $user->balance,
                    'profile_pic_url' => $profilePicUrl,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Abrufen der User-Daten:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Fehler beim Abrufen der User-Daten'
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Überprüfe das aktuelle Passwort
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Das aktuelle Passwort ist falsch'], 401);
        }

        // Aktualisiere das Passwort
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Passwort erfolgreich aktualisiert'], 200);
    }

    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Überprüfe das aktuelle Passwort
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Das Passwort ist falsch'], 401);
        }

        // Aktualisiere die E-Mail-Adresse
        $user->email = $request->new_email;
        $user->save();

        return response()->json(['message' => 'E-Mail-Adresse erfolgreich aktualisiert'], 200);
    }

    public function updateProfilePic(Request $request)
    {
        $request->validate([
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $user = Auth::user();
    
        // Altes Profilbild löschen, falls vorhanden
        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }
    
        // Neues Bild speichern
        $path = $request->file('profile_pic')->store('profile_pics', 'public');
    
        // Pfad in der Datenbank speichern
        $user->profile_pic = $path;
        $user->save();
    
        // Profilbild-URL zurückgeben
        return response()->json([
            'message' => 'Profilbild erfolgreich aktualisiert',
            'profile_pic_url' => $user->profile_pic_url,
        ]);
    }

    public function deleteAccount()
    {
        // Hole den eingeloggten Benutzer
        $user = auth()->user();

        // Lösche den Benutzer
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully'], 200);
    }

}
