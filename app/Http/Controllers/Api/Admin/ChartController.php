<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

class ChartController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $userStats = User::where('role', 'user')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map(function($group) {
                return count($group);
            });

        $daysOfMonth = [];
        $date = $startOfMonth->copy();
        while ($date <= $endOfMonth) {
            $day = $date->format('Y-m-d');
            $daysOfMonth[$day] = $userStats->get($day, 0);
            $date->addDay();
        }

    
        $oneWeekAgo = Carbon::now()->subDays(6)->startOfDay();

        $pendingDoctors = User::where('role', 'doctor')
            ->whereNull('is_approved')
            ->where('created_at', '>=', $oneWeekAgo)
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map(function($group) {
                return count($group);
            });

       
        $daysOfWeek = [];
        $date = $oneWeekAgo->copy();
        while ($date <= Carbon::now()) {
            $day = $date->format('Y-m-d');
            $daysOfWeek[$day] = $pendingDoctors->get($day, 0);
            $date->addDay();
        }

        return response()->json([
            'user_chart' => [
                'labels' => array_keys($daysOfMonth),
                'data' => array_values($daysOfMonth),
            ],
            'pending_doctors_chart' => [
                'labels' => array_keys($daysOfWeek),
                'data' => array_values($daysOfWeek),
            ]
        ]);
    }
}

