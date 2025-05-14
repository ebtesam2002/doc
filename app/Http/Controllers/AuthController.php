<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Verified;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Sign Up
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/i|unique:users',
            'phone' => 'required|string|regex:/^\d{10,}$/|unique:users',
            'location' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'location' => $request->location,
            'birth_date' => $request->birth_date,
            'password' => Hash::make($request->password),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'User registered successfully. Please verify your email.'], 201);
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Please verify your email.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login successful', 'token' => $token], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // Change Password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()->symbols()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    // Forgot Password (Request Reset Link)
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $code = rand(1000, 9999); // 4-digit code

        // Delete old codes
        DB::table('password_resets_codes')->where('email', $request->email)->delete();

        // Insert new code
        DB::table('password_resets_codes')->insert([
            'email' => $request->email,
            'code' => $code,
            'created_at' => now(),
            'verified' => false
        ]);

        // Send the code via email
        Mail::raw("Your password reset code is: $code", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Password Reset Code');
        });

        return response()->json(['message' => 'Verification code sent to your email.']);
    }

    // Verify code
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

        // Update verified status
        DB::table('password_resets_codes')
            ->where('code', $request->code)
            ->update(['verified' => true]);

        return response()->json([
            'message' => 'Code verified successfully.',
            'email' => $record->email
        ]);
    }

    // Reset password (no email required)
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
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Cleanup
        DB::table('password_resets_codes')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successful.']);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            return response()->json(['message' => 'Invalid verification link'], 403);
        }
    
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified'], 400);
        }
    
        $user->markEmailAsVerified();
        event(new Verified($user));
    
        return response()->json(['message' => 'Email verified successfully']);
    }
    

    // Resend Verification Email
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent']);
    }
}
