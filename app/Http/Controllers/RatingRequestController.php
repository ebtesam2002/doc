<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Support\Carbon;

class RatingRequestController extends Controller
{
public function sendRequests()
{
    $now = Carbon::now();

    $bookings = Booking::whereDate('date', '<=', $now->toDateString())
        ->whereTime('time', '<=', $now->toTimeString())
        ->whereDoesntHave('rating')
        ->get();

    if ($bookings->isEmpty()) {
        return response()->json(['message' => 'لا توجد حجوزات مؤهلة لإرسال طلبات التقييم.']);
    }

    foreach ($bookings as $booking) {
        Rating::create([
            'user_id' => $booking->user_id,
            'doctor_id' => $booking->doctor_id,
            'booking_id' => $booking->id,
            'is_requested' => true,
        ]);
    }

    return response()->json(['message' => 'Rating requests sent.']);
}






public function submitRating(Request $request)
{
    $request->validate([
        'rating_id' => 'required|exists:ratings,id',
        'rate' => 'required|integer|min:1|max:5',
    ]);

    $rating = Rating::where('id', $request->rating_id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    if ($rating->rate !== null) {
        return response()->json(['message' => 'Already rated.'], 400);
    }

    $rating->update([
        'rate' => $request->rate,
    ]);

    return response()->json(['message' => 'Rating submitted successfully.']);
}


public function pendingRatings()
{
    $ratings = Rating::where('user_id', auth()->id())
        ->whereNull('rate')
        ->where('is_requested', true)
        ->with(['doctor:id,username']) // eager loading doctor info
        ->get();

    $data = $ratings->map(function ($rating) {
        return [
            'rating_id' => $rating->id,
            'doctor_name' => $rating->doctor->username,
            'stars' => [1, 2, 3, 4, 5], // client can use to display rating stars
        ];
    });

    return response()->json($data);
}


}
