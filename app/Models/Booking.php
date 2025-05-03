<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DoctorSchedule;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 
        'doctor_id',
        'schedule_id',
        'date',
        'time',
        'status',
    ];

    
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id')->where('role', 'doctor');
    }
    
    
    public function doctorSchedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id');
    }
    

    public function user()
    {
        return $this->belongsTo(User::class); 
    }
}
