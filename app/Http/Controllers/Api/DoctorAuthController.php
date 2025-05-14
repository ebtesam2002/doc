<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Events\DoctorRegistered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password as PasswordRule;

class DoctorAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string', // username is no longer unique
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'location' => 'required|string',
            'specialization' => 'required|string',
            'license_number' => 'required|digits_between:5,7|unique:users,license_number',
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'
            ],
        ], [
            'password.regex' => 'The password must contain at least one letter and one number.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verificationCode = Str::random(6);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'location' => $request->location,
            'specialization' => $request->specialization,
            'license_number' => $request->license_number,
            'password' => Hash::make($request->password),
            'role' => 'doctor',
            'verification_code' => $verificationCode,
        ]);

        event(new DoctorRegistered($user));

        return response()->json([
            'message' => 'Doctor registered successfully. Please check your email for verification code.',
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('role', 'doctor')
                    ->first();

        if (!$user || $user->verification_code !== $request->code) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        $user->update([
            'is_verified' => true,
            'verification_code' => null,
        ]);

        return response()->json(['message' => 'Doctor verified successfully']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('role', 'doctor')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_verified) {
            return response()->json(['message' => 'Please verify your account first'], 403);
        }

        if ($user->is_approved === 0) {
            return response()->json(['message' => 'Your registration has been rejected by the admin.'], 403);
        }

        if ($user->is_approved === null) {
            return response()->json(['message' => 'Your account is pending admin approval'], 403);
        }

        $token = $user->createToken('doctor-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'doctor' => $user,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'
            ],
        ], [
            'new_password.regex' => 'The new password must contain at least one letter and one number.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Old password is incorrect'], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }
public function getDoctorsBySpecialization($specialization)
{
    $doctors = User::where('role', 'doctor')
                    ->where('specialization', $specialization)
                    ->with('ratings') // تحميل التقييمات
                    ->select('id', 'username', 'profile_picture', 'specialization')
                    ->get();

    return response()->json([
        'status' => true,
        'doctors' => $doctors->map(function ($doctor) {
            return [
                'name' => $doctor->username,
                'image' => asset('storage/' . $doctor->profile_picture),
                'specialization' => $doctor->specialization,
                'average_rating' => $doctor->average_rating, // استخدام الـ accessor لحساب المتوسط
            ];
        })
    ]);
}
public function getDoctorProfile($id)
{
    $doctor = User::with(['schedules', 'ratings']) // اجلب التقييمات مع الجدول الخاص بالمواعيد
        ->where('id', $id)
        ->where('role', 'doctor')
        ->first();

    if (!$doctor) {
        return response()->json(['message' => 'Doctor not found'], 404);
    }

    // استدعاء دالة Accessor مباشرة لحساب متوسط التقييم
    $averageRating = $doctor->average_rating;  // هنا استدعي الـ accessor الذي يحدد متوسط التقييم

    return response()->json([
        'status' => true,
        'doctor' => [
            'name' => $doctor->username,
            'specialization' => $doctor->specialization,
            'image' => asset('storage/' . $doctor->profile_picture),
            'average_rating' => $averageRating,  // إضافة متوسط التقييم
        ],
        'schedules' => $doctor->schedules->map(function ($schedule) {
            return [
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'booking_price' => $schedule->booking_price,
                'address' => $schedule->address,
            ];
        }),
    ]);
}

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $code = rand(1000, 9999);

        DB::table('password_resets_codes')->where('email', $request->email)->delete();

        DB::table('password_resets_codes')->insert([
            'email' => $request->email,
            'code' => $code,
            'created_at' => now(),
            'verified' => false
        ]);

        Mail::raw("Your password reset code is: $code", function ($message) use ($request) {
            $message->to($request->email)->subject('Password Reset Code');
        });

        return response()->json(['message' => 'Verification code sent to your email.']);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:4',
        ]);

        $record = DB::table('password_resets_codes')
                    ->where('code', $request->code)
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid code'], 400);
        }

        DB::table('password_resets_codes')
            ->where('code', $request->code)
            ->update(['verified' => true]);

        return response()->json([
            'message' => 'Code verified successfully.',
            'email' => $record->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()->symbols()],
        ]);

        $record = DB::table('password_resets_codes')
                    ->where('email', $request->email)
                    ->where('verified', true)
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'You must verify the code first.'], 403);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets_codes')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successful.']);
    }
}
