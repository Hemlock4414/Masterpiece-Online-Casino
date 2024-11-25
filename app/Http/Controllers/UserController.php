<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Hole alle Benutzer aus der Datenbank
        $users = User::all();

        // Gib die Benutzer zurück
        return $users;
    }

    // Speichere das Profilbild, falls es vorhanden ist
    if ($request->hasFile('profile_pic')) {
        $path = $request->file('profile_pic')->store('profile_pics', 'public');
        $user->profile_pic = $path; // Speichere den Pfad in der Datenbank
        $user->save();
    }

    // Generiere die öffentliche URL des Profilbildes
    $profilePicUrl = $user->profile_pic ? asset('storage/' . $user->profile_pic) : null;

    // Rückgabe der Benutzerdaten, einschließlich der Profilbild-URL
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_pic_url' => $profilePicUrl,
        ],
    ], 201);

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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json($user, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
