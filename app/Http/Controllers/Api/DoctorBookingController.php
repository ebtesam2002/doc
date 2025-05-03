<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class DoctorBookingController extends Controller
{
    public function myPatients(Request $request)
    {
        $doctor = $request->user();

        // التحقق من أن المستخدم دكتور
        if (!$doctor || $doctor->role !== 'doctor') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك الوصول إلى هذه البيانات.'
            ], 403);
        }

        // استعلام الحجوزات المرتبطة بهذا الدكتور والتي يكون المريض فيها مستخدم عادي
        $bookings = Booking::with(['user', 'doctorSchedule'])
            ->where('doctor_id', $doctor->id)
            ->whereHas('user', function ($query) {
                $query->where('role', 'user');
            })
            ->latest()
            ->get()
            ->map(function ($booking) {
                return [
                    'username' => optional($booking->user)->username,
                    'profile_picture' => $booking->user && $booking->user->profile_picture
                        ? asset('storage/' . $booking->user->profile_picture)
                        : null,
                    'date' => $booking->date,
                    'time' => $booking->time,
                    'day' => optional($booking->doctorSchedule)->day,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $bookings,
        ]);
    }
}
