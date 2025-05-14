<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\Booking;

class PrescriptionController extends Controller
{
    // كتابة وصفة طبية
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'medicines' => 'required|string',
        ]);

        $doctor = $request->user();

        // التحقق من صلاحية الدكتور
        $booking = Booking::where('id', $request->booking_id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'الحجز غير صالح.'], 403);
        }

        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'medicines' => $request->medicines,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الوصفة الطبية بنجاح.',
            'data' => $prescription
        ]);
    }

    // عدد كل الروشتات اللي كتبها الدكتور
    public function count(Request $request)
    {
        $doctor = $request->user();

        $count = Prescription::where('doctor_id', $doctor->id)->count();

        return response()->json([
            'status' => true,
            'total_prescriptions' => $count
        ]);
    }


    public function myPrescriptions(Request $request)
{
    $user = $request->user();

    $prescriptions = Prescription::with(['booking.doctorSchedule', 'doctor'])
        ->where('user_id', $user->id)
        ->latest()
        ->get()
        ->map(function ($prescription) {
            return [
                'doctor_name' => optional($prescription->doctor)->username,
                'clinic_address' => optional($prescription->booking->doctorSchedule)->address,
                'day' => optional($prescription->booking->doctorSchedule)->day,
                'date' => optional($prescription->booking)->date,
                'medicines' => $prescription->medicines,
            ];
        });

    return response()->json([
        'status' => true,
        'data' => $prescriptions
    ]);
}

}

