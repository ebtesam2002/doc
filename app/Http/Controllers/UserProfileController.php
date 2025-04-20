<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    
    public function update(Request $request)
    {
        $user = Auth::user();

        
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        
        if ($request->filled('username')) {
            $user->username = $request->username;
        }

        $user->location = $request->location;
        $user->birth_date = $request->birth_date;

        
        if ($request->hasFile('profile_picture')) {
           
            if ($user->profile_picture) {
                Storage::delete('public/profile_pictures/' . $user->profile_picture);
            }

            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile_pictures', $filename);

           
            $user->profile_picture = $filename;
        }

        
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    
    public function getProfile()
    {
        
        $user = Auth::user();

   
        return response()->json([
            'username' => $user->username,
            'email' => $user->email,      
            'phone' => $user->phone,      
            'location' => $user->location,
            'birth_date' => $user->birth_date,
        ]);
    }
}
