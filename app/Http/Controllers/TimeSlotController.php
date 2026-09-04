<?php

namespace App\Http\Controllers;

use App\Models\MassSchedule;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TimeSlotController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $this->ensureSundaySlots();
        $schedules = MassSchedule::query()->where('day_of_week', 'Sunday')->where('is_active', true)->orderBy('start_time')->get();
        $slots = TimeSlot::query()->with('massSchedule')->withCount('appointments')->orderBy('slot_time')->get();

        return view('admin.time-slots', [
            'slots' => $slots,
            'schedules' => $schedules,
            'editing' => $request->integer('edit') ? $slots->firstWhere('id', $request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        TimeSlot::query()->create($this->validated($request));
        return redirect()->route('admin.time-slots')->with('success', 'Time slot created successfully.');
    }

    public function update(Request $request, TimeSlot $timeSlot): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $validated = $this->validated($request, $timeSlot);
        if ($validated['max_capacity'] < $timeSlot->current_bookings) {
            throw ValidationException::withMessages(['max_capacity' => 'Capacity cannot be lower than current bookings.']);
        }
        $timeSlot->update($validated);
        return redirect()->route('admin.time-slots')->with('success', 'Time slot updated successfully.');
    }

    public function destroy(Request $request, TimeSlot $timeSlot): RedirectResponse
    {
        $this->authorizeAdmin($request);
        if ($timeSlot->appointments()->exists()) {
            throw ValidationException::withMessages(['slot' => 'This slot has sacrament requests. Disable it instead.']);
        }
        $timeSlot->delete();
        return redirect()->route('admin.time-slots')->with('success', 'Time slot deleted.');
    }

    private function validated(Request $request, ?TimeSlot $timeSlot = null): array
    {
        $validated = $request->validate([
            'mass_schedule_id' => ['required', Rule::exists('mass_schedules', 'id')->where(fn ($query) => $query->where('day_of_week', 'Sunday')->where('is_active', true))],
            'slot_time' => ['required', 'date_format:H:i', Rule::unique('time_slots')->where(fn ($query) => $query->where('mass_schedule_id', $request->input('mass_schedule_id')))->ignore($timeSlot)],
            'max_capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_available' => ['nullable', 'boolean'],
        ]);
        $validated['is_available'] = $request->boolean('is_available');
        return $validated;
    }

    private function ensureSundaySlots(): void
    {
        foreach ([
            ['title' => 'Sunday Morning Mass', 'start_time' => '07:30', 'end_time' => '08:30'],
            ['title' => 'Sunday Evening Mass', 'start_time' => '18:00', 'end_time' => '19:00'],
        ] as $default) {
            $schedule = MassSchedule::query()->firstOrCreate(
                ['title' => $default['title'], 'day_of_week' => 'Sunday'],
                [...$default, 'location' => 'Main Church', 'priest_in_charge' => 'Parish Priest', 'is_active' => true]
            );
            TimeSlot::query()->firstOrCreate(
                ['mass_schedule_id' => $schedule->id, 'slot_time' => $default['start_time']],
                ['max_capacity' => 50, 'current_bookings' => 0, 'is_available' => true]
            );
        }
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
