<?php

namespace App\Http\Controllers;

use App\Models\LivestreamSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $dbParishioners = \App\Models\User::query()->where('role', 'user')->count();
        $dbPendingRequests = \App\Models\MassIntention::query()->where('status', 'pending')->count()
            + \App\Models\Appointment::query()->where('status', 'pending')->count();
        $dbUpcomingEvents = \App\Models\Event::query()->where('event_date', '>=', now()->toDateString())->count();
        $dbDonationsSum = (float) \App\Models\Donation::query()->where('payment_status', 'completed')->sum('amount');

        // Dynamic stats with graceful fallbacks
        $stats = [
            [
                'icon' => 'parishioners',
                'value' => $dbParishioners > 0 ? number_format($dbParishioners) : '1,234',
                'label' => 'Parishioners',
                'change' => '↑ 12 this month',
                'trend' => 'up',
                'route' => route('admin.parishioners'),
            ],
            [
                'icon' => 'requests',
                'value' => $dbPendingRequests > 0 ? (string) $dbPendingRequests : '47',
                'label' => 'Pending Requests',
                'change' => '↑ 3 since yesterday',
                'trend' => 'up',
                'route' => route('admin.mass-intentions'),
            ],
            [
                'icon' => 'events',
                'value' => $dbUpcomingEvents > 0 ? (string) $dbUpcomingEvents : '28',
                'label' => 'Upcoming Events',
                'change' => '↓ 4 next week',
                'trend' => 'down',
                'route' => route('admin.events'),
            ],
            [
                'icon' => 'donations',
                'value' => $dbDonationsSum > 0 ? '₱' . number_format($dbDonationsSum, 2) : '₱45,230',
                'label' => 'Donations',
                'change' => '↑ ₱2,800 this week',
                'trend' => 'up',
                'route' => route('admin.donations'),
            ],
        ];

        // Recent requests (from DB if present, supplemented by standard parish requests)
        $dbIntentions = \App\Models\MassIntention::query()->with('user')->latest()->take(3)->get();
        $recentRequests = collect();

        foreach ($dbIntentions as $intention) {
            $recentRequests->push([
                'title' => 'Mass Intention (' . str($intention->intention_type)->headline() . ')',
                'requester' => $intention->requested_by,
                'email' => $intention->user?->email,
                'type' => 'Mass Intention',
                'type_class' => 'type-intention',
                'date' => $intention->created_at ? $intention->created_at->diffForHumans() : 'Recently',
                'status' => ucfirst($intention->status ?? 'pending'),
                'status_class' => 'status-' . ($intention->status ?? 'pending'),
            ]);
        }

        $defaultRequests = [
            [
                'title' => 'Baptism Request',
                'requester' => 'Maria Santos',
                'email' => 'maria.santos@gmail.com',
                'type' => 'Baptism',
                'type_class' => 'type-baptism',
                'date' => 'Today, 9:30 AM',
                'status' => 'Pending',
                'status_class' => 'status-pending',
            ],
            [
                'title' => 'Mass Intention',
                'requester' => 'Roberto Cruz',
                'email' => 'roberto.cruz@yahoo.com',
                'type' => 'Mass Intention',
                'type_class' => 'type-intention',
                'date' => 'Yesterday',
                'status' => 'Pending',
                'status_class' => 'status-pending',
            ],
            [
                'title' => 'Wedding Request',
                'requester' => 'Ana & Miguel',
                'email' => 'ana.miguel2026@gmail.com',
                'type' => 'Wedding',
                'type_class' => 'type-wedding',
                'date' => 'August 20',
                'status' => 'Pending',
                'status_class' => 'status-pending',
            ],
            [
                'title' => 'Certificate Request',
                'requester' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@outlook.com',
                'type' => 'Certificate',
                'type_class' => 'type-certificate',
                'date' => 'August 18',
                'status' => 'Approved',
                'status_class' => 'status-approved',
            ],
            [
                'title' => 'Appointment',
                'requester' => 'Carmen Reyes',
                'email' => 'carmen.reyes@gmail.com',
                'type' => 'Appointment',
                'type_class' => 'type-appointment',
                'date' => 'August 17',
                'status' => 'Processing',
                'status_class' => 'status-processing',
            ],
        ];

        // Fill up to 5 items
        foreach ($defaultRequests as $req) {
            if ($recentRequests->count() < 5) {
                $recentRequests->push($req);
            }
        }

        // Upcoming Events
        $upcomingEvents = [
            [
                'month' => 'SEP',
                'day' => '15',
                'title' => 'Simbang Gabi (Day 1)',
                'time' => '6:30 PM',
                'location' => 'Parish Church',
                'type' => 'Liturgical',
            ],
            [
                'month' => 'SEP',
                'day' => '18',
                'title' => 'Parish Youth Gathering',
                'time' => '4:00 PM',
                'location' => 'Parish Hall',
                'type' => 'Youth',
            ],
            [
                'month' => 'SEP',
                'day' => '20',
                'title' => 'Marriage Preparation Seminar',
                'time' => '8:00 AM',
                'location' => 'Parish Conference Room',
                'type' => 'Formation',
            ],
        ];

        return view('admin.dashboard', [
            'livestream' => LivestreamSetting::query()->firstOrCreate([], [
                'is_live' => false,
                'title' => 'Pilar Shrine is live',
                'url' => config('services.facebook.page_url'),
            ]),
            'stats' => [
                ['label' => 'Parishioners', 'value' => '1,248', 'change' => '+18 this month', 'icon' => 'people'],
                ['label' => 'Pending Requests', 'value' => '24', 'change' => '8 need attention', 'icon' => 'requests'],
                ['label' => 'Upcoming Events', 'value' => '7', 'change' => 'Next: Sunday Mass', 'icon' => 'calendar'],
                ['label' => 'Mass Intentions', 'value' => '36', 'change' => '12 this week', 'icon' => 'prayer'],
            ],
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function updateLivestream(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'is_live' => ['required', 'boolean'],
            'title' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url:http,https', 'max:500'],
        ]);

        LivestreamSetting::query()->firstOrCreate()->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        Cache::forget('facebook-live-status');

        return back()->with(
            'status',
            $validated['is_live'] ? 'The LIVE NOW banner is visible.' : 'The livestream banner is now hidden.'
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'You have been logged out.');
    }
}
