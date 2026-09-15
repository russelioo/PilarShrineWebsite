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
        // 1. Live Parishioner Counts
        $dbParishioners = \App\Models\User::query()->where('role', 'user')->count();
        $verifiedParishioners = \App\Models\User::query()->where('role', 'user')->where('is_verified', true)->count();
        $newThisMonth = \App\Models\User::query()->where('role', 'user')->where('created_at', '>=', now()->startOfMonth())->count();

        // 2. Live Ministry Counts
        $dbMinistries = \App\Models\Ministry::query()->count();
        $acceptingMinistries = \App\Models\Ministry::query()->where('is_accepting_members', true)->count();

        // 3. Live Announcement Counts
        $dbAnnouncements = \App\Models\Announcement::query()->count();
        $pinnedAnnouncements = \App\Models\Announcement::query()->where('is_pinned', true)->count();

        // 4. Live Donations Sum & Verification Counts
        $dbDonationsSum = (float) \App\Models\Donation::query()
            ->where(function ($q) {
                $q->whereIn('status', ['verified', 'receipt_ready'])
                  ->orWhere('payment_status', 'completed');
            })
            ->sum('amount');
        $pendingDonations = \App\Models\Donation::query()->where('status', 'pending_verification')->count();
        $verifiedDonations = \App\Models\Donation::query()->whereIn('status', ['verified', 'receipt_ready'])->count();

        // 5. Accurate, Live KPI Cards
        $stats = [
            [
                'icon' => 'parishioners',
                'value' => number_format($dbParishioners),
                'label' => 'Parishioners',
                'change' => $newThisMonth > 0 ? "+{$newThisMonth} this month" : ($dbParishioners > 0 ? "{$verifiedParishioners} active" : "0 registered"),
                'trend' => 'up',
                'route' => route('admin.parishioners'),
            ],
            [
                'icon' => 'ministries',
                'value' => (string) $dbMinistries,
                'label' => 'Parish Ministries',
                'change' => $acceptingMinistries > 0 ? "{$acceptingMinistries} open for join" : "Apostolates & groups",
                'trend' => 'up',
                'route' => route('admin.ministries'),
            ],
            [
                'icon' => 'announcements',
                'value' => (string) $dbAnnouncements,
                'label' => 'Announcements',
                'change' => $pinnedAnnouncements > 0 ? "{$pinnedAnnouncements} pinned" : "Published bulletins",
                'trend' => 'blue',
                'route' => route('admin.announcements'),
            ],
            [
                'icon' => 'donations',
                'value' => '₱' . number_format($dbDonationsSum, 2),
                'label' => 'Donations',
                'change' => $pendingDonations > 0 ? "{$pendingDonations} pending review" : ($verifiedDonations > 0 ? "{$verifiedDonations} verified" : "Total offerings"),
                'trend' => $pendingDonations > 0 ? 'amber' : 'up',
                'route' => route('admin.donations'),
            ],
        ];

        // 6. Real Live Recent Requests (Ministry Applications, Mass Intentions, Donations)
        $recentRequests = collect();

        // Ministry Applications
        $ministryRequests = \App\Models\MinistryMembership::query()
            ->with(['user', 'ministry'])
            ->latest()
            ->take(6)
            ->get();

        foreach ($ministryRequests as $mem) {
            $recentRequests->push([
                'title' => 'Ministry Application (' . ($mem->ministry?->name ?? 'Ministry') . ')',
                'requester' => $mem->user?->name ?? 'Applicant',
                'email' => $mem->user?->email ?? 'Parishioner',
                'type' => 'Ministry',
                'type_class' => 'type-ministry',
                'date' => $mem->created_at ? $mem->created_at->diffForHumans() : 'Recently',
                'status' => ucfirst($mem->status ?? 'pending'),
                'status_class' => 'status-' . ($mem->status ?? 'pending'),
                'ref' => '#MEM-' . str_pad((string) $mem->id, 4, '0', STR_PAD_LEFT),
                'url' => route('admin.ministry-requests'),
                'created_at' => $mem->created_at ?? now(),
            ]);
        }

        // Mass Intentions
        $massIntentions = \App\Models\MassIntention::query()
            ->with(['user'])
            ->latest()
            ->take(6)
            ->get();

        foreach ($massIntentions as $intention) {
            $recentRequests->push([
                'title' => 'Mass Intention (' . str($intention->intention_type)->headline() . ')',
                'requester' => $intention->requested_by ?: ($intention->user?->name ?? 'Requester'),
                'email' => $intention->user?->email ?? 'Parishioner',
                'type' => 'Intention',
                'type_class' => 'type-intention',
                'date' => $intention->created_at ? $intention->created_at->diffForHumans() : 'Recently',
                'status' => ucfirst($intention->status ?? 'pending'),
                'status_class' => 'status-' . ($intention->status ?? 'pending'),
                'ref' => '#INT-' . str_pad((string) $intention->id, 4, '0', STR_PAD_LEFT),
                'url' => route('admin.mass-intentions'),
                'created_at' => $intention->created_at ?? now(),
            ]);
        }

        // Donations / Offerings
        $donations = \App\Models\Donation::query()
            ->with(['user'])
            ->latest()
            ->take(6)
            ->get();

        foreach ($donations as $don) {
            $recentRequests->push([
                'title' => 'Offering (₱' . number_format($don->amount, 2) . ' - ' . ($don->purpose ?? 'General') . ')',
                'requester' => $don->donor_name ?: ($don->user?->name ?? 'Donor'),
                'email' => $don->email ?: ($don->user?->email ?? 'Parishioner'),
                'type' => 'Donation',
                'type_class' => 'type-donation',
                'date' => $don->created_at ? $don->created_at->diffForHumans() : 'Recently',
                'status' => ucfirst(str_replace('_', ' ', $don->status ?? 'pending')),
                'status_class' => 'status-' . ($don->status ?? 'pending'),
                'ref' => '#DON-' . str_pad((string) $don->id, 4, '0', STR_PAD_LEFT),
                'url' => route('admin.donations'),
                'created_at' => $don->created_at ?? now(),
            ]);
        }

        // Sort combined requests by created_at descending and take top 5
        $recentRequests = $recentRequests->sortByDesc('created_at')->values()->take(5);

        // 7. Live Announcements
        $latestAnnouncements = \App\Models\Announcement::query()
            ->latest('published_at')
            ->take(3)
            ->get();

        // 8. Live Active Mass Schedules
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
