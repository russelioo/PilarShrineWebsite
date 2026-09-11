<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileSettingsController extends Controller
{
    /**
     * Show the authenticated parishioner's profile details and preferences.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->load(['ministryMemberships.ministry']);

        return view('parishioner.profile-settings', compact('user'));
    }

    /**
     * Update the parishioner's personal details and residence.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'municipality_city' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->name = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $user->date_of_birth = $validated['date_of_birth'];
        $user->phone = $validated['phone'];
        $user->country = !empty($validated['country']) ? $validated['country'] : 'Philippines';
        $user->region = $validated['region'];
        $user->province = $validated['province'] ?? null;
        $user->municipality_city = $validated['municipality_city'];
        $user->barangay = $validated['barangay'];
        $user->save();

        return redirect()->route('parishioner.profile-settings')
            ->with('success', 'Your parishioner profile has been updated successfully.');
    }
}
