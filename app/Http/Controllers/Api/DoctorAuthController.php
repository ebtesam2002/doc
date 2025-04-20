<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use App\Events\DoctorRegistered;

class DoctorAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
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
}
