<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouriteController extends Controller
{
    public function index()
    {
        $favourites = Auth::user()->favouriteDoctors()->get();

        return response()->json($favourites->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->username,
                'profile_picture' => $doctor->profile_picture,
                'specialization' => $doctor->specialization ?? null, 
            ];
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $doctor = User::find($value);
                    if (!$doctor || $doctor->role !== 'doctor') {
                        $fail('Selected user is not a doctor.');
                    }
                },
            ],
        ]);

        Auth::user()->favouriteDoctors()->syncWithoutDetaching($request->doctor_id);

        return response()->json(['message' => 'Doctor added to favourites']);
    }

    public function destroy($doctor_id)
    {
        Auth::user()->favouriteDoctors()->detach($doctor_id);

        return response()->json(['message' => 'Doctor removed from favourites']);
    }
}

