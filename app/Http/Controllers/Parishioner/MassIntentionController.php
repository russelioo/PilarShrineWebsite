<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\MassSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MassIntentionController extends Controller
{
    public function index(Request $request): View
    {
        $intentions = $request->user()
            ->massIntentions()
            ->with('massSchedule')
            ->latest('requested_date')
            ->latest('id')
            ->paginate(10);

        return view('parishioner.mass-intentions', compact('intentions'));
    }

    public function create(): View
    {
        $schedules = MassSchedule::query()
            ->where('is_active', true)
            ->orderByRaw("CASE day_of_week WHEN 'Sunday' THEN 1 WHEN 'Monday' THEN 2 WHEN 'Tuesday' THEN 3 WHEN 'Wednesday' THEN 4 WHEN 'Thursday' THEN 5 WHEN 'Friday' THEN 6 WHEN 'Saturday' THEN 7 ELSE 8 END")
            ->orderBy('start_time')
            ->get();

        return view('parishioner.request-mass-intention', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mass_schedule_id' => ['required', 'integer', 'exists:mass_schedules,id'],
            'intention_type' => ['required', 'in:living,deceased,thanksgiving,special'],
            'names' => ['required', 'string', 'max:1000'],
            'offering_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $scheduleIsActive = MassSchedule::query()
            ->whereKey($validated['mass_schedule_id'])
            ->where('is_active', true)
            ->exists();

        if (! $scheduleIsActive) {
            return back()->withErrors(['mass_schedule_id' => 'The selected Mass schedule is no longer available.'])
                ->withInput();
        }

        $request->user()->massIntentions()->create([
            ...$validated,
            'requested_by' => $request->user()->name,
            'offering_amount' => $validated['offering_amount'] ?? 0,
            'status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        return redirect()->route('parishioner.mass-intentions')
            ->with('success', 'Your Mass intention request was submitted successfully.');
    }
}
