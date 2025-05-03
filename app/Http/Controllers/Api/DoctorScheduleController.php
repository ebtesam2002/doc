<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorSchedule;
use Carbon\Carbon;

class DoctorScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'الحساب الحالي ليس دكتوراً'], 403);
        }

        $schedules = DoctorSchedule::where('doctor_id', $user->id)->get();

        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'الحساب الحالي ليس دكتوراً'], 403);
        }

        $data = $request->validate([
            'date' => 'required|date',
            'slots' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'booking_price' => 'required|numeric|min:0',
            'address' => 'required|string',
        ]);

        $data['day'] = Carbon::parse($data['date'])->locale('ar')->isoFormat('dddd');
        $data['doctor_id'] = $user->id;

        $schedule = DoctorSchedule::create($data);

        return response()->json($schedule, 201);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'الحساب الحالي ليس دكتوراً'], 403);
        }

        $schedule = DoctorSchedule::where('doctor_id', $user->id)->findOrFail($id);

        $data = $request->validate([
            'date' => 'nullable|date',
            'slots' => 'nullable|integer|min:1',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'booking_price' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
        ]);

        if (isset($data['date'])) {
            $data['day'] = Carbon::parse($data['date'])->locale('ar')->isoFormat('dddd');
        }

        $schedule->update($data);

        return response()->json($schedule);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'الحساب الحالي ليس دكتوراً'], 403);
        }

        $schedule = DoctorSchedule::where('doctor_id', $user->id)->findOrFail($id);

        $schedule->delete();

        return response()->json(['message' => 'تم حذف الجدول بنجاح']);
    }
}
