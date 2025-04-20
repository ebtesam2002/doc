<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'phone',
        'location',
        'birth_date',
        'password',
        'role',
        'specialization',
        'license_number',
        'verification_code',
        'is_verified',
        'profile_picture', // added profile_picture
        'rate',             // added rate
        'schedule',         // added schedule
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Accessor for profile picture URL
    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture 
            ? asset('storage/' . $this->profile_picture) 
            : asset('default-avatar.png');
    }

    // Optionally, add accessor for schedule if it's stored as JSON
    public function getScheduleAttribute($value)
    {
        return json_decode($value); // Assuming schedule is stored as JSON in the database
    }
}
