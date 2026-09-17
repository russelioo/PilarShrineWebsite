<?php

namespace App\Http\Controllers;

use App\Models\MassSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MassScheduleController extends Controller
{
    /**
     * Public API endpoint returning live Mass & Confession schedules for the website.
     */
    public function publicIndex(): JsonResponse
    {
        $schedules = MassSchedule::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByRaw("CASE day_of_week WHEN 'Sunday' THEN 1 WHEN 'Monday' THEN 2 WHEN 'Tuesday' THEN 3 WHEN 'Wednesday' THEN 4 WHEN 'Thursday' THEN 5 WHEN 'Friday' THEN 6 WHEN 'Saturday' THEN 7 ELSE 8 END")
            ->orderBy('start_time')
            ->get();

        // 5 standard liturgical category groups matching the public website design
        $categories = [
            'Daily Mass' => [
                'title' => 'Daily Mass',
                'tag' => 'Monday to Saturday',
                'icon' => '◷',
                'items' => [],
            ],
            'Sunday Mass' => [
                'title' => 'Sunday Mass',
                'tag' => "Lord's Day Celebrations",
                'icon' => '✝',
                'items' => [],
            ],
            'Sacrament of Reconciliation' => [
                'title' => 'Sacrament of Reconciliation',
                'tag' => 'Confession & Spiritual Healing',
                'icon' => '✦',
                'items' => [],
            ],
            'Monthly Devotion to Our Lady of the Pillar' => [
                'title' => 'Monthly Devotion to Our Lady of the Pillar',
                'tag' => 'Patronal Devotional Day',
                'icon' => '♛',
                'items' => [],
            ],
            'Special Liturgical Activities' => [
                'title' => 'Special Liturgical Activities',
                'tag' => 'Monthly Observances & Chapels',
                'icon' => '▦',
                'items' => [],
            ],
        ];

        foreach ($schedules as $s) {
            $cat = $s->category ?: ($s->day_of_week === 'Sunday' ? 'Sunday Mass' : 'Daily Mass');
            if (!isset($categories[$cat])) {
                $categories[$cat] = [
                    'title' => $cat,
                    'tag' => $s->schedule_type ?: 'Liturgical Service',
                    'icon' => '✝',
                    'items' => [],
                ];
            }

            $timeDisplay = $s->time_display;
            if (empty($timeDisplay)) {
                $startTimeStr = $s->start_time ? Carbon::parse($s->start_time)->format('g:i A') : '';
                $typeStr = $s->schedule_type ?: $s->title;
                $timeDisplay = $startTimeStr ? "{$startTimeStr} — {$typeStr}" : $typeStr;
            }

            $categories[$cat]['items'][] = [
                'id' => $s->id,
                'title' => $s->title,
                'day' => $s->day_of_week,
                'time' => $timeDisplay,
                'time_display' => $timeDisplay,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'location' => $s->location,
                'type' => $s->schedule_type ?: 'Holy Mass',
                'notes' => $s->notes,
                'highlighted' => (bool) $s->is_highlighted,
                'live' => (bool) $s->is_livestreamed,
                'is_active' => (bool) $s->is_active,
            ];
        }

        return response()->json([
            'categories' => array_values($categories),
            'schedules' => $schedules,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Administrator & Staff schedule management index.
     */
    public function index(Request $request): View
    {
        $portal = $this->authorizePortal($request);
        $this->ensureSundayMassExists();

        $query = MassSchedule::query();

        if ($request->filled('category') && $request->query('category') !== 'all') {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('day_of_week', 'like', "%{$search}%")
                  ->orWhere('schedule_type', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $schedules = $query
            ->orderBy('sort_order')
            ->orderByRaw("CASE day_of_week WHEN 'Sunday' THEN 1 WHEN 'Monday' THEN 2 WHEN 'Tuesday' THEN 3 WHEN 'Wednesday' THEN 4 WHEN 'Thursday' THEN 5 WHEN 'Friday' THEN 6 WHEN 'Saturday' THEN 7 ELSE 8 END")
            ->orderBy('start_time')
            ->get();

        $editing = $request->integer('edit') ? MassSchedule::query()->find($request->integer('edit')) : null;

        return view('mass-schedules.index', [
            'schedules' => $schedules,
            'portal' => $portal,
            'editing' => $editing,
            'selectedCategory' => $request->query('category', 'all'),
            'search' => $request->query('search', ''),
        ]);
    }

    /**
     * Create a new schedule entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizePortal($request);
        MassSchedule::query()->create($this->validated($request));

        return back()->with('success', 'Schedule created successfully.');
    }

    /**
     * Update an existing schedule entry.
     */
    public function update(Request $request, MassSchedule $massSchedule): RedirectResponse
    {
        $portal = $this->authorizePortal($request);
        $validated = $this->validated($request);
        $this->protectLastSundayMass($massSchedule, $validated);
        $massSchedule->update($validated);

        return redirect()->route($portal.'.mass-schedules')->with('success', "Schedule '{$massSchedule->title}' updated successfully.");
    }

    /**
     * Delete an existing schedule entry.
     */
    public function destroy(Request $request, MassSchedule $massSchedule): RedirectResponse
    {
        $this->authorizePortal($request);
        $this->protectLastSundayMass($massSchedule, ['day_of_week' => '', 'is_active' => false]);

        if ($massSchedule->intentions()->exists()) {
            throw ValidationException::withMessages([
                'schedule' => 'This schedule has Mass intentions assigned to it. Deactivate it instead.',
            ]);
        }

        $title = $massSchedule->title;
        $massSchedule->delete();

        return back()->with('success', "Schedule '{$title}' deleted successfully.");
    }

    /**
     * Validate request input for store and update operations.
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'schedule_type' => ['nullable', 'string', 'max:100'],
            'day_of_week' => ['required', 'string', 'max:150'],
            'start_time' => ['nullable', 'string', 'max:50'],
            'end_time' => ['nullable', 'string', 'max:50'],
            'time_display' => ['nullable', 'string', 'max:150'],
            'location' => ['required', 'string', 'max:150'],
            'priest_in_charge' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_highlighted' => ['nullable', 'boolean'],
            'is_livestreamed' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_highlighted'] = $request->boolean('is_highlighted');
        $validated['is_livestreamed'] = $request->boolean('is_livestreamed');
        $validated['sort_order'] = (int) ($request->input('sort_order', 0));

        if (empty($validated['category'])) {
            $validated['category'] = (stripos($validated['day_of_week'], 'Sunday') !== false) ? 'Sunday Mass' : 'Daily Mass';
        }

        if (empty($validated['schedule_type'])) {
            $validated['schedule_type'] = 'Holy Mass';
        }

        if (empty($validated['priest_in_charge'])) {
            $validated['priest_in_charge'] = 'Parish Priest';
        }

        if (empty($validated['start_time']) && !empty($validated['time_display'])) {
            // Attempt extracting time if formatted like "5:00 PM"
            if (preg_match('/(\d{1,2}:\d{2}\s*(?:AM|PM))/i', $validated['time_display'], $m)) {
                try {
                    $validated['start_time'] = Carbon::parse($m[1])->format('H:i:s');
                } catch (\Exception $e) {
                    $validated['start_time'] = '00:00:00';
                }
            } else {
                $validated['start_time'] = '00:00:00';
            }
        } elseif (empty($validated['start_time'])) {
            $validated['start_time'] = '07:00:00';
        }

        if (empty($validated['end_time'])) {
            try {
                $validated['end_time'] = Carbon::parse($validated['start_time'])->addHour()->format('H:i:s');
            } catch (\Exception $e) {
                $validated['end_time'] = '08:00:00';
            }
        }

        if (empty($validated['time_display'])) {
            try {
                $formattedTime = Carbon::parse($validated['start_time'])->format('g:i A');
                $validated['time_display'] = "{$formattedTime} — {$validated['schedule_type']}";
            } catch (\Exception $e) {
                $validated['time_display'] = $validated['schedule_type'];
            }
        }

        return $validated;
    }

    /**
     * Ensure Sunday Mass protection.
     */
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

    /**
     * Default fallback Sunday Mass creation if missing.
     */
    private function ensureSundayMassExists(): void
    {
        MassSchedule::query()->firstOrCreate(
            ['day_of_week' => 'Sunday', 'is_active' => true],
            [
                'title' => 'Sunday Mass',
                'category' => 'Sunday Mass',
                'schedule_type' => 'Holy Mass',
                'start_time' => '07:30',
                'end_time' => '08:30',
                'time_display' => '7:30 AM — Holy Mass',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'notes' => 'FB Live',
                'is_livestreamed' => true,
            ]
        );
    }

    /**
     * Authorize portal access (admin or staff).
     */
    private function authorizePortal(Request $request): string
    {
        $actor = $request->user();
        abort_unless(
            $actor && ($actor->role === 'super_admin' || $actor->hasPermission('mass_schedules')),
            403,
            'You do not have permission to manage mass schedules.'
        );

        return $request->routeIs('staff.*') ? 'staff' : 'admin';
    }
}
