<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StatisticsController extends Controller
{
    public function index()
    {
        $userCount = User::where('role', 'user')->count();

        $approvedDoctorsCount = User::where('role', 'doctor')
                                    ->where('is_approved', 1)
                                    ->count();

        $rejectedDoctorsCount = User::where('role', 'doctor')
                                    ->where('is_approved', 0)
                                    ->count();

        $pendingDoctorsCount = User::where('role', 'doctor')
                                    ->whereNull('is_approved')
                                    ->count();

        return response()->json([
            'user_count' => $userCount,
            'approved_doctors_count' => $approvedDoctorsCount,
            'rejected_doctors_count' => $rejectedDoctorsCount,
            'pending_doctors_count' => $pendingDoctorsCount,
        ]);
    }
}
