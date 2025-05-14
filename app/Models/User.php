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
        'profile_picture',
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

    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture 
            ? asset('storage/' . $this->profile_picture) 
            : asset('default-avatar.png');
    }

    public function getScheduleAttribute($value)
    {
        return json_decode($value); 
    }

    public function favouriteDoctors()
    {
        return $this->belongsToMany(User::class, 'favourites', 'user_id', 'doctor_id')
            ->where('role', 'doctor')
            ->withTimestamps();
    }

    public function favouredByUsers()
    {
        return $this->belongsToMany(User::class, 'favourites', 'doctor_id', 'user_id')
            ->withTimestamps();
    }

    // ✅ العلاقة مع جدول المواعيد
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }

        public function doctorRatings()
    {
        return $this->hasMany(Rating::class, 'doctor_id');
    }

 public function getAverageRatingAttribute()
{
    $average = $this->doctorRatings()->whereNotNull('rate')->avg('rate');

    return round(max($average ?? 0, 3), 1); // أقل شيء 3
}
// في نموذج User (App\Models\User)

public function ratings()
{
    return $this->hasMany(Rating::class, 'doctor_id');  // افترضنا أن الـ ratings تحتوي على عمود doctor_id للإشارة للطبيب.
}

public function getTotalRatingsCountAttribute()
{
    return $this->doctorRatings()->whereNotNull('rate')->count();  // حساب عدد التقييمات
}




}
