<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Parishioners', 'value' => '1,248', 'change' => '+18 this month', 'icon' => 'people'],
                ['label' => 'Pending Requests', 'value' => '24', 'change' => '8 need attention', 'icon' => 'requests'],
                ['label' => 'Upcoming Events', 'value' => '7', 'change' => 'Next: Sunday Mass', 'icon' => 'calendar'],
                ['label' => 'Mass Intentions', 'value' => '36', 'change' => '12 this week', 'icon' => 'prayer'],
            ],
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
