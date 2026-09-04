<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SacramentRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = $request->user()->appointments()
            ->with('timeSlot.massSchedule')
            ->latest()
            ->paginate(10);

        return view('parishioner.sacrament-requests', compact('requests'));
    }

    public function create(): View
    {
        $slots = TimeSlot::query()
            ->with('massSchedule')
            ->whereHas('massSchedule', fn ($query) => $query
                ->where('day_of_week', 'Sunday')
                ->where('is_active', true))
            ->where('is_available', true)
            ->whereColumn('current_bookings', '<', 'max_capacity')
            ->orderBy('slot_time')
            ->get();

        return view('parishioner.request-sacrament', compact('slots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_type' => ['required', 'in:baptism,marriage,funeral,confirmation'],
            'slot_id' => ['required', 'integer', 'exists:time_slots,id'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (! Carbon::parse($validated['preferred_date'])->isSunday()) {
            throw ValidationException::withMessages(['preferred_date' => 'Sacrament requests must use a Sunday date.']);
        }


        DB::transaction(function () use ($request, $validated): void {
            $slot = TimeSlot::query()->lockForUpdate()->findOrFail($validated['slot_id']);
            if (! $slot->is_available || $slot->current_bookings >= $slot->max_capacity) {
                throw ValidationException::withMessages(['slot_id' => 'The selected time slot is no longer available.']);
            }

            Appointment::query()->create([
                ...$validated,
                'user_id' => $request->user()->id,
                'preferred_time' => $slot->slot_time,
                'status' => 'pending',
            ]);
            $slot->increment('current_bookings');
        });

        return redirect()->route('parishioner.sacrament-requests')
            ->with('success', 'Your sacrament request was submitted successfully.');
    }
}
