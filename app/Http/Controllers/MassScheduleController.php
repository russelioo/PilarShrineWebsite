<?php

namespace App\Http\Controllers;

use App\Models\MassSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MassScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $portal = $this->authorizePortal($request);
        $this->ensureSundayMassExists();
        $schedules = MassSchedule::query()
            ->orderByRaw("CASE day_of_week WHEN 'Sunday' THEN 1 WHEN 'Monday' THEN 2 WHEN 'Tuesday' THEN 3 WHEN 'Wednesday' THEN 4 WHEN 'Thursday' THEN 5 WHEN 'Friday' THEN 6 WHEN 'Saturday' THEN 7 ELSE 8 END")
            ->orderBy('start_time')
            ->get();

        return view('mass-schedules.index', [
            'schedules' => $schedules,
            'portal' => $portal,
            'editing' => $request->integer('edit') ? $schedules->firstWhere('id', $request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePortal($request);
        MassSchedule::query()->create($this->validated($request));

        return back()->with('success', 'Mass schedule created successfully.');
    }

    public function update(Request $request, MassSchedule $massSchedule): RedirectResponse
    {
        $portal = $this->authorizePortal($request);
        $validated = $this->validated($request);
        $this->protectLastSundayMass($massSchedule, $validated);
        $massSchedule->update($validated);

        return redirect()->route($portal.'.mass-schedules')->with('success', 'Mass schedule updated successfully.');
    }

    public function destroy(Request $request, MassSchedule $massSchedule): RedirectResponse
    {
        $this->authorizePortal($request);
        $this->protectLastSundayMass($massSchedule, ['day_of_week' => '', 'is_active' => false]);

        if ($massSchedule->intentions()->exists()) {
            throw ValidationException::withMessages([
                'schedule' => 'This schedule has Mass intentions assigned to it. Deactivate it instead.',
            ]);
        }

        $massSchedule->delete();

        return back()->with('success', 'Mass schedule deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'day_of_week' => ['required', 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:150'],
            'priest_in_charge' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function protectLastSundayMass(MassSchedule $schedule, array $replacement): void
    {
        if ($schedule->day_of_week !== 'Sunday' || ! $schedule->is_active) {
            return;
        }

        $willRemainSunday = ($replacement['day_of_week'] ?? null) === 'Sunday' && ($replacement['is_active'] ?? false);
        $anotherSundayExists = MassSchedule::query()
            ->whereKeyNot($schedule->id)
            ->where('day_of_week', 'Sunday')
            ->where('is_active', true)
            ->exists();

        if (! $willRemainSunday && ! $anotherSundayExists) {
            throw ValidationException::withMessages([
                'schedule' => 'At least one active Sunday Mass schedule is required.',
            ]);
        }
    }

    private function ensureSundayMassExists(): void
    {
        MassSchedule::query()->firstOrCreate(
            ['day_of_week' => 'Sunday', 'is_active' => true],
            ['title' => 'Sunday Mass', 'start_time' => '07:30', 'end_time' => '08:30', 'location' => 'Main Church', 'priest_in_charge' => 'Parish Priest']
        );
    }

    private function authorizePortal(Request $request): string
    {
        $portal = $request->routeIs('staff.*') ? 'staff' : 'admin';
        $roles = $portal === 'staff' ? ['admin', 'staff'] : ['admin'];
        abort_unless(in_array($request->user()->role, $roles, true), 403);

        return $portal;
    }
}
