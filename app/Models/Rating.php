<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Booking;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_id',
        'booking_id',
        'rate',
        'is_requested',
    ];

    // العلاقة بالمستخدم اللي عمل التقييم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة بالدكتور
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // العلاقة بالحجز
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    
}
