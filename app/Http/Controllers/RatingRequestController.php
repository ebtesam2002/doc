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

}
