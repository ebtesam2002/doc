<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function book(Request $request)
    {
        $user = $request->user();

        // التحقق من أن المستخدم هو مريض وليس دكتور
        if ($user->role !== 'user') {
            return response()->json([
                'status' => false,
                'message' => 'فقط المرضى يمكنهم حجز المواعيد.'
            ], 403);
        }

        $data = $request->validate([
            'schedule_id' => 'required|exists:doctor_schedules,id',
            'time' => 'required|date_format:H:i',
        ]);

        try {
            $booking = DB::transaction(function () use ($data, $user) {
                $schedule = DoctorSchedule::where('id', $data['schedule_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $existing = Booking::where('schedule_id', $schedule->id)
                    ->where('date', $schedule->date)
                    ->where('time', $data['time'])
                    ->where('status', 'booked')
                    ->count();

                if ($existing >= $schedule->slots) {
                    throw new \Exception('عذرًا، الموعد محجوز بالكامل.');
                }

                $booking = Booking::create([
                    'user_id' => $user->id, // المريض
                    'doctor_id' => $schedule->doctor_id,
                    'schedule_id' => $schedule->id,
                    'date' => $schedule->date,
                    'time' => $data['time'],
                    'status' => 'booked',
                ]);

                if ($existing + 1 >= $schedule->slots) {
                    $schedule->update(['is_full' => true]);
                }

                return $booking;
            });

            $booking->load('doctorSchedule.doctor');

            $doctorName = optional($booking->doctorSchedule->doctor)->username ?? 'غير معروف';
            $address = optional($booking->doctorSchedule)->address ?? 'غير متوفر';

            return response()->json([
                'message' => 'تم الحجز بنجاح',
                'booking' => [
                    'id' => $booking->id,
                    'doctor_name' => $doctorName,
                    'date' => $booking->date,
                    'time' => $booking->time,
                    'status' => $booking->status,
                    'address' => $address,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function myTransactions(Request $request)
    {
        $user = $request->user();

        $bookings = Booking::with('doctor')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($booking) {
                $doctor = optional($booking->doctor);
                $address = optional($booking->doctorSchedule)->address ?? 'غير متوفر';

                return [
                    'doctor_name' => $doctor->username ?? 'غير معروف',
                    'doctor_image' => $doctor->profile_picture ? asset('storage/' . $doctor->profile_picture) : null,
                    'specialization' => $doctor->specialization ?? 'غير محدد',
                    'address' => $address,
                    'date' => $booking->date,
                    'day' => Carbon::parse($booking->date)->translatedFormat('l'),
                    'time' => $booking->time,
                    'status' => $booking->status,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $bookings
        ]);
    }

    public function cancelBooking(Request $request, $bookingId)
    {
        $user = $request->user();

        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'الحجز غير موجود أو لا يخصك.'
            ], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'status' => false,
                'message' => 'تم إلغاء هذا الحجز مسبقاً.'
            ]);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'status' => true,
            'message' => 'تم إلغاء الحجز بنجاح.'
        ]);
    }
}
