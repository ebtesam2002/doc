<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\DoctorApprovedNotification;
use App\Notifications\DoctorRejectedNotification;


class DoctorController extends Controller
{
    
    public function pendingDoctors()
    {
        $pendingDoctors = User::where('role', 'doctor')
            ->where('is_verified', true)
            ->whereNull('is_approved')
            ->get(['id', 'username', 'email', 'phone', 'specialization', 'license_number']);

        return response()->json($pendingDoctors);
    }

    
    public function approveDoctor($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);
        $doctor->is_approved = 1;
        $doctor->save();

        $doctor->notify(new DoctorApprovedNotification());

        return response()->json(['message' => 'Doctor approved and email sent']);
    }


        
        public function rejectDoctor($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);
        $doctor->is_approved = 0;
        $doctor->save();

        $doctor->notify(new DoctorRejectedNotification());

        return response()->json(['message' => 'Doctor rejected and email sent']);
    }

}
