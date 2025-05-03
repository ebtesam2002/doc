<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DoctorProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if ($user->role !== 'doctor') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'location' => $user->location,
                'specialization' => $user->specialization,
                'license_number' => $user->license_number,
                'profile_picture' => $user->profile_picture
                    ? asset('storage/' . $user->profile_picture)
                    : null,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($user->role !== 'doctor') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

       
        $user->fill($request->only(['username', 'location', 'specialization']));

        
        if ($request->has('specialization')) {
            $user->specialization = $request->specialization;
        }

        
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::delete('public/' . $user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        
        \Log::info('Updated specialization:', ['specialization' => $user->specialization]);

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'location' => $user->location,
                'specialization' => $user->specialization,
                'license_number' => $user->license_number,
                'profile_picture' => $user->profile_picture
                    ? asset('storage/' . $user->profile_picture)
                    : null,
            ],
        ]);
    }
}
