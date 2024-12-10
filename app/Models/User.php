<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const REGISTRATION_BONUS = 1000;
    protected const STORAGE_PATH = 'storage/';
    protected const PROFILE_PICS_FOLDER = 'profile_pics/';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'firstname',
        'lastname',
        'birth_date',
        'nationality',
        'balance',
        'email',
        'password',
        'profile_pic',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $appends = ['profile_pic_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
            'balance' => 'integer',
        ];
    }

    /**
     * Calculate initial balance including registration bonus
     *
     * @param int $earnedBalance
     * @return int
     */
    public static function calculateInitialBalance($earnedBalance = 0): int
    {
        return self::REGISTRATION_BONUS + $earnedBalance;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the user's profile picture URL.
     *
     * @return string|null
     */
    public function getProfilePicUrlAttribute()
    {
        if ($this->profile_pic) {
            return asset('storage/' . $this->profile_pic);
        }
        return asset('storage/defaults/default-avatar.png');
    }

    /**
     * Get the memory game records for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function memoryPlayers(): HasMany
    {
        return $this->hasMany(MemoryPlayer::class, 'user_id');
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Automatisches Löschen des Profilbilds wenn User gelöscht wird
        static::deleting(function ($user) {
            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }
        });
    }
}