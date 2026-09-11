<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\MassSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Extract first name for personalized greeting
        $firstName = $user->first_name;
        if (empty($firstName)) {
            $parts = preg_split('/\s+/', trim($user->name ?: ''));
            $firstName = $parts[0] ?? 'Parishioner';
        }

        // Time-based greeting in Philippine Time (GMT+8)
        $hour = now()->setTimezone('Asia/Manila')->hour;
        if ($hour >= 5 && $hour < 12) {
            $greeting = "Good morning, {$firstName}!";
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = "Good afternoon, {$firstName}!";
        } else {
            $greeting = "Good evening, {$firstName}!";
        }

        // Active request counts for the authenticated user
        $activeMassIntentionsCount = $user->massIntentions()
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        $activeSacramentsCount = $user->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $totalActiveRequests = $activeMassIntentionsCount + $activeSacramentsCount;

        // Upcoming weekly Mass schedules count
        $upcomingMassCount = MassSchedule::where('is_active', true)->count();

        // Recent user activities
        $recentIntentions = $user->massIntentions()
            ->with('massSchedule')
            ->latest('requested_date')
            ->latest('id')
            ->take(3)
            ->get();

        $recentSacraments = $user->appointments()
            ->with('timeSlot.massSchedule')
            ->latest()
            ->take(3)
            ->get();

        return view('parishioner.dashboard', compact(
            'user',
            'greeting',
            'activeMassIntentionsCount',
            'activeSacramentsCount',
            'totalActiveRequests',
            'upcomingMassCount',
            'recentIntentions',
            'recentSacraments'
        ));
    }
}

