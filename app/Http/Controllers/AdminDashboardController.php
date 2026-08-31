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
