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
        $users = User::all();
        return $users;
    }

    public function getUser()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['message' => 'Benutzer nicht authentifiziert'], 401);
        }
    }

    public function register(Request $request)
    {
        Log::info('Registrierungsversuch:', [
            'birth_date' => $request->birth_date,
            'all_data' => $request->all()
        ]);

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
            
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $birthDate)) {
                Log::info('Konvertiere deutsches Datum', ['original' => $birthDate]);
                list($day, $month, $year) = explode('.', $birthDate);
                $birthDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                Log::info('Datum konvertiert', ['converted' => $birthDate]);
                
                if (!Carbon::createFromFormat('Y-m-d', $birthDate)->isValid()) {
                    throw new \Exception('Ungültiges Datum nach Konvertierung');
                }
            } 
            elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
                Log::info('Datum bereits im ISO-Format', ['date' => $birthDate]);
                
                if (!Carbon::createFromFormat('Y-m-d', $birthDate)->isValid()) {
                    throw new \Exception('Ungültiges ISO-Datum');
                }
            } 
            else {
                throw new \Exception('Datum ist in keinem erkannten Format');
            }

            Log::info('Finales Datum für Datenbank:', ['birth_date' => $birthDate]);

            // Berechne das Gesamtguthaben
            $earnedBalance = $request->earned_balance ?? 0;
            $totalBalance = User::calculateInitialBalance($earnedBalance);

            $user = User::create([
                'username' => $request->username,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'birth_date' => $birthDate,
                'nationality' => $request->nationality,
                'balance' => $totalBalance,
                'password' => Hash::make($request->password),
            ]);

            Log::info('User erfolgreich erstellt', [
                'user_id' => $user->id,
                'initial_balance' => $totalBalance,
                'registration_bonus' => User::REGISTRATION_BONUS,
                'earned_balance' => $earnedBalance
            ]);

            if ($request->hasFile('profile_pic')) {
                $path = $request->file('profile_pic')->store('profile_pics', 'public');
                $user->profile_pic = $path;
                $user->save();
                Log::info('Profilbild gespeichert', ['path' => $path]);
            }

            Auth::login($user);
            $request->session()->regenerate();

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
                    'registration_bonus' => User::REGISTRATION_BONUS,
                    'earned_balance' => $earnedBalance,
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
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

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

            $profilePicUrl = $user->profile_pic ? asset('storage/' . $user->profile_pic) : null;
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
                    'created_at' => $user->created_at,
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

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Das aktuelle Passwort ist falsch'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Passwort erfolgreich aktualisiert'], 200);
    }

    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'email' => 'required|email|unique:users,email',
        ], [
            'email.unique' => 'Diese E-Mail-Adresse wird bereits verwendet.',
            'email.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
            'email.required' => 'E-Mail-Adresse ist erforderlich.',
            'current_password.required' => 'Bitte geben Sie Ihr aktuelles Passwort ein.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Das Passwort ist falsch'], 401);
        }

        $user->email = $request->email;
        $user->save();

        return response()->json(['message' => 'E-Mail-Adresse erfolgreich aktualisiert'], 200);
    }

    public function updateProfilePic(Request $request)
    {
        try {
            $request->validate([
                'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        
            $user = Auth::user();

            // Debug-Logging
            \Log::info('Profilbild Update gestartet', [
                'user_id' => $user->id,
                'hat_altes_bild' => !empty($user->profile_pic)
            ]);
        
            if ($user->profile_pic && Storage::disk('public')->exists($user->profile_pic)) {
                Storage::disk('public')->delete($user->profile_pic);
                \Log::info('Altes Profilbild gelöscht');
            }
        
            $path = $request->file('profile_pic')->store('profile_pics', 'public');
            \Log::info('Neues Profilbild gespeichert', ['pfad' => $path]);
        
            $user->profile_pic = $path;
            $user->save();
        
            return response()->json([
                'message' => 'Profilbild erfolgreich aktualisiert',
                'profile_pic_url' => asset('storage/' . $path),
            ]);
        
        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren des Profilbildes:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Fehler beim Hochladen des Profilbildes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteProfilePic()
    {
        try {
            $user = auth()->user();
            
            // Wenn ein Profilbild existiert, lösche es
            if ($user->profile_pic && Storage::disk('public')->exists($user->profile_pic)) {
                Storage::disk('public')->delete($user->profile_pic);
            }
            
            // Setze den profile_pic Wert auf null
            $user->profile_pic = null;
            $user->save();
            
            return response()->json([
                'message' => 'Profilbild wurde erfolgreich gelöscht',
                'profile_pic_url' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Fehler beim Löschen des Profilbildes:', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'error' => 'Fehler beim Löschen des Profilbildes'
            ], 500);
        }
    }

    public function deleteAccount()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Benutzer nicht gefunden oder bereits gelöscht'
                ], 200);
            }
            
            if ($user->profile_pic && Storage::disk('public')->exists($user->profile_pic)) {
                Storage::disk('public')->delete($user->profile_pic);
            }
            
            // Explizites Logout vor dem Löschen
            \Auth::guard('web')->logout();
            
            $user->delete();
            
            return response()->json([
                'message' => 'Konto wurde erfolgreich gelöscht'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Fehler beim Löschen des Kontos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Konto wurde möglicherweise bereits gelöscht',
                'error_details' => $e->getMessage()
            ], 200);
        }
    }
}