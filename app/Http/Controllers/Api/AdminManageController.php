<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminManageController extends Controller
{
 public function getUsers()
{
    $users = User::where('role', 'user')->get()->map(function ($user) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'profile_picture' => $user->profile_picture,
            'role' => $user->role,
        ];
    });

    return response()->json(['users' => $users], 200);
}
public function getDoctors()
{
    $doctors = User::where('role', 'doctor')->get()->map(function ($user) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'profile_picture' => $user->profile_picture,
            'role' => $user->role,
            'specialization' => $user->specialization,
            'license_number' => $user->license_number,
        ];
    });

    return response()->json(['doctors' => $doctors], 200);
}



public function destroy($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->delete();

    return response()->json(['message' => 'User deleted successfully'], 200);
}
}
