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
        $request->validate(['email' => 'required|email']);

        
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Password reset link sent to your email.']);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();

                
                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successful.'])
            : response()->json(['message' => 'Invalid token or email.'], 400);
    }

    // Verify Email
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
