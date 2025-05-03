<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'date',
        'day',
        'slots',
        'start_time',
        'end_time',
        'booking_price',
        'address',
    ];

    public function doctor()
{
    return $this->belongsTo(User::class, 'doctor_id');
}

}
