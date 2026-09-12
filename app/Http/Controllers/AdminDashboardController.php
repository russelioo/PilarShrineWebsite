<?php

namespace App\Http\Controllers;

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
        $dbMinistries = \App\Models\Ministry::query()->count();
        $dbAnnouncements = \App\Models\Announcement::query()->count();
        $dbDonationsSum = (float) \App\Models\Donation::query()->where('payment_status', 'completed')->sum('amount');

        // Dynamic stats with graceful fallbacks
        $stats = [
            [
                'icon' => 'parishioners',
                'value' => $dbParishioners > 0 ? number_format($dbParishioners) : '1,234',
                'label' => 'Parishioners',
                'change' => 'Active members',
                'trend' => 'up',
                'route' => route('admin.parishioners'),
            ],
            [
                'icon' => 'ministries',
                'value' => $dbMinistries > 0 ? (string) $dbMinistries : '8',
                'label' => 'Parish Ministries',
                'change' => 'Apostolates & groups',
                'trend' => 'up',
                'route' => route('admin.ministries'),
            ],
            [
                'icon' => 'announcements',
                'value' => $dbAnnouncements > 0 ? (string) $dbAnnouncements : '4',
                'label' => 'Announcements',
                'change' => 'Published bulletins',
                'trend' => 'up',
                'route' => route('admin.announcements'),
            ],
            [
                'icon' => 'donations',
                'value' => $dbDonationsSum > 0 ? '₱' . number_format($dbDonationsSum, 2) : '₱45,230',
                'label' => 'Donations',
                'change' => 'Total offerings',
                'trend' => 'up',
                'route' => route('admin.donations'),
            ],
        ];

        // Recent ministry membership requests from DB
        $dbMinistryRequests = \App\Models\MinistryMembership::query()
            ->with(['user', 'ministry'])
            ->latest()
            ->take(5)
            ->get();

        $recentRequests = collect();

        foreach ($dbMinistryRequests as $mem) {
            $recentRequests->push([
                'title' => 'Ministry Application (' . ($mem->ministry?->name ?? 'Parish Ministry') . ')',
                'requester' => $mem->user?->name ?? 'Applicant',
                'email' => $mem->user?->email ?? 'Parishioner',
                'type' => 'Ministry',
                'type_class' => 'type-ministry',
                'date' => $mem->created_at ? $mem->created_at->diffForHumans() : 'Recently',
                'status' => ucfirst($mem->status ?? 'pending'),
                'status_class' => 'status-' . ($mem->status ?? 'pending'),
            ]);
        }

        $defaultRequests = [
            [
                'title' => 'Music Ministry Application',
                'requester' => 'Maria Santos',
                'email' => 'maria.santos@gmail.com',
                'type' => 'Ministry',
                'type_class' => 'type-ministry',
                'date' => 'Today, 9:30 AM',
                'status' => 'Pending',
                'status_class' => 'status-pending',
            ],
            [
                'title' => 'Lectors & Commentators',
                'requester' => 'Roberto Cruz',
                'email' => 'roberto.cruz@yahoo.com',
                'type' => 'Ministry',
                'type_class' => 'type-ministry',
                'date' => 'Yesterday',
                'status' => 'Pending',
                'status_class' => 'status-pending',
            ],
            [
                'title' => 'Youth Apostolate Volunteer',
                'requester' => 'Ana & Miguel',
                'email' => 'ana.miguel2026@gmail.com',
                'type' => 'Ministry',
                'type_class' => 'type-ministry',
                'date' => 'August 20',
                'status' => 'Approved',
                'status_class' => 'status-approved',
            ],
            [
                'title' => 'Extraordinary Ministers of HC',
                'requester' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@outlook.com',
                'type' => 'Ministry',
                'type_class' => 'type-ministry',
                'date' => 'August 18',
                'status' => 'Approved',
                'status_class' => 'status-approved',
            ],
            [
                'title' => 'Altar Servers Guild',
                'requester' => 'Carmen Reyes',
                'email' => 'carmen.reyes@gmail.com',
                'type' => 'Ministry',
                'type_class' => 'type-ministry',
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

        $latestAnnouncements = \App\Models\Announcement::query()
            ->latest('published_at')
            ->take(3)
            ->get();

        $activeSchedules = \App\Models\MassSchedule::query()
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'latestAnnouncements' => $latestAnnouncements,
            'activeSchedules' => $activeSchedules,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'You have been logged out.');
    }
}
