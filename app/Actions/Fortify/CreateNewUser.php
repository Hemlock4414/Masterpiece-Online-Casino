<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Carbon\Carbon;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'playername' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class),
            ],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'birth_date' => ['required', 'string'],
            'nationality' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'profile_pic' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ])->validate();

        try {
            // Versuche das Datum zu konvertieren
            $birthDate = null;
            
            // Prüfe ob das Datum bereits im Y-m-d Format ist
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['birth_date'])) {
                $birthDate = $input['birth_date'];
            } 
            // Wenn nicht, versuche es aus dem deutschen Format zu konvertieren
            else {
                $birthDate = Carbon::createFromFormat('d.m.Y', $input['birth_date'])->format('Y-m-d');
            }

            if (!$birthDate) {
                throw new \Exception('Ungültiges Datumsformat');
            }

        return User::create([
            'playername' => $input['playername'],
            'firstname' => $input['firstname'],
            'lastname' => $input['lastname'],
            'email' => $input['email'],
            'birth_date' => $birth_date,
            'nationality' => $input['nationality'],
            'balance' => 0, // Standardwert für neue Benutzer
            'password' => Hash::make($input['password']),
            'profile_pic' => $input['profile_pic'] ?? null,
        ]);

        } catch (\Exception $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'birth_date' => ['Fehler bei der Datumsverarbeitung: ' . $e->getMessage()]
            ]);
        }
    }
}