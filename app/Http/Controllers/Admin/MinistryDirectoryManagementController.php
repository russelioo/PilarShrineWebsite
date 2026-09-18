<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MinistryDirectoryManagementController extends Controller
{
    /**
     * Display a listing of all parish ministries for administration.
     */
    public function index(Request $request): View
    {
        $this->authorizeAction($request->user(), 'view_ministries');

        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();
        $status = $request->string('status', 'all')->trim()->toString();

        $query = Ministry::query()
            ->with(['commission'])
            ->withCount([
                'memberships as active_members_count' => fn ($q) => $q->where('status', 'approved'),
                'memberships as pending_requests_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->orderBy('name');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('coordinator_name', 'like', "%{$search}%")
                    ->orWhere('meeting_location', 'like', "%{$search}%");
            });
        }

        if (!empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($status === 'open') {
            $query->where('is_accepting_members', true);
        } elseif ($status === 'closed') {
            $query->where('is_accepting_members', false);
        }

        $ministries = $query->paginate(12)->withQueryString();

        // Metrics
        $totalCount = Ministry::count();
        $acceptingCount = Ministry::where('is_accepting_members', true)->count();
        $closedCount = Ministry::where('is_accepting_members', false)->count();
        $totalMembersCount = \App\Models\MinistryMembership::where('status', 'approved')->count();

        // Preset Categories
        $categoriesList = [
            'Worship & Liturgy',
            'Sacred Music',
            'Youth Formation',
            'Community Service',
            'Faith Formation',
            'Lay Apostolates',
            'Media & Communications',
            'Parish Pastoral',
        ];

        // Registered staff users for coordinator assignment dropdown
        $staffUsers = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        // Active pastoral commissions for commission affiliation
        $commissions = Commission::where('is_active', true)->orderBy('name')->get();

        return view('admin.ministries', compact(
            'ministries',
            'totalCount',
            'acceptingCount',
            'closedCount',
            'totalMembersCount',
            'categoriesList',
            'staffUsers',
            'commissions',
            'search',
            'category',
            'status'
        ));
    }

    /**
     * Store a newly created parish ministry in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'create_ministries');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'commission_id' => ['nullable', 'exists:commissions,id'],
            'category' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'is_public' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:1000'],
            'about' => ['nullable', 'string', 'max:3000'],
            'activities' => ['nullable', 'string', 'max:3000'],
            'meeting_schedule' => ['nullable', 'string', 'max:255'],
            'meeting_location' => ['nullable', 'string', 'max:255'],
            'coordinator_name' => ['nullable', 'string', 'max:255'],
            'coordinator_email' => ['nullable', 'email', 'max:255'],
            'coordinator_phone' => ['nullable', 'string', 'max:50'],
            'coordinator_user_id' => ['nullable', 'exists:users,id'],
            'requirements' => ['nullable', 'string', 'max:3000'],
            'is_accepting_members' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        // Auto-generate unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Ministry::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        // If coordinator user is selected, prefill details if missing
        if (!empty($validated['coordinator_user_id'])) {
            $staff = User::find($validated['coordinator_user_id']);
            if ($staff) {
                $validated['coordinator_name'] = !empty($validated['coordinator_name']) ? $validated['coordinator_name'] : $staff->displayName;
                $validated['coordinator_email'] = !empty($validated['coordinator_email']) ? $validated['coordinator_email'] : $staff->email;
                $validated['coordinator_phone'] = !empty($validated['coordinator_phone']) ? $validated['coordinator_phone'] : $staff->phone;
            }
        }

        $ministry = Ministry::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'commission_id' => $validated['commission_id'] ?? null,
            'category' => $validated['category'],
            'status' => $validated['status'] ?? 'active',
            'is_public' => $request->has('is_public') ? $request->boolean('is_public') : true,
            'icon' => !empty($validated['icon']) ? $validated['icon'] : '✝',
            'description' => $validated['description'],
            'about' => $validated['about'] ?? null,
            'activities' => $this->parseLinesToArray($validated['activities'] ?? null),
            'meeting_schedule' => $validated['meeting_schedule'] ?? null,
            'meeting_location' => $validated['meeting_location'] ?? null,
            'coordinator_name' => $validated['coordinator_name'] ?? null,
            'coordinator_email' => $validated['coordinator_email'] ?? null,
            'coordinator_phone' => $validated['coordinator_phone'] ?? null,
            'coordinator_user_id' => !empty($validated['coordinator_user_id']) ? $validated['coordinator_user_id'] : null,
            'requirements' => $this->parseLinesToArray($validated['requirements'] ?? null),
            'is_accepting_members' => $request->boolean('is_accepting_members', true),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        if (!empty($validated['redirect_to'])) {
            return redirect($validated['redirect_to'])
                ->with('success', "Ministry '{$validated['name']}' has been created successfully.");
        }

        return redirect()->route('admin.ministries')
            ->with('success', "Ministry '{$validated['name']}' has been created successfully and is now active in the directory.");
    }

    /**
     * Update the specified parish ministry in storage.
     */
    public function update(Request $request, Ministry $ministry): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'edit_ministries');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'commission_id' => ['nullable', 'exists:commissions,id'],
            'category' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'is_public' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:1000'],
            'about' => ['nullable', 'string', 'max:3000'],
            'activities' => ['nullable', 'string', 'max:3000'],
            'meeting_schedule' => ['nullable', 'string', 'max:255'],
            'meeting_location' => ['nullable', 'string', 'max:255'],
            'coordinator_name' => ['nullable', 'string', 'max:255'],
            'coordinator_email' => ['nullable', 'email', 'max:255'],
            'coordinator_phone' => ['nullable', 'string', 'max:50'],
            'coordinator_user_id' => ['nullable', 'exists:users,id'],
            'requirements' => ['nullable', 'string', 'max:3000'],
            'is_accepting_members' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        // If coordinator user is selected, prefill details if missing
        if (!empty($validated['coordinator_user_id'])) {
            $staff = User::find($validated['coordinator_user_id']);
            if ($staff) {
                $validated['coordinator_name'] = !empty($validated['coordinator_name']) ? $validated['coordinator_name'] : $staff->displayName;
                $validated['coordinator_email'] = !empty($validated['coordinator_email']) ? $validated['coordinator_email'] : $staff->email;
                $validated['coordinator_phone'] = !empty($validated['coordinator_phone']) ? $validated['coordinator_phone'] : $staff->phone;
            }
        }

        $ministry->update([
            'name' => $validated['name'],
            'commission_id' => $validated['commission_id'] ?? null,
            'category' => $validated['category'],
            'status' => $validated['status'] ?? $ministry->status ?? 'active',
            'is_public' => $request->has('is_public') ? $request->boolean('is_public') : $ministry->is_public,
            'icon' => !empty($validated['icon']) ? $validated['icon'] : '✝',
            'description' => $validated['description'],
            'about' => $validated['about'] ?? null,
            'activities' => $this->parseLinesToArray($validated['activities'] ?? null),
            'meeting_schedule' => $validated['meeting_schedule'] ?? null,
            'meeting_location' => $validated['meeting_location'] ?? null,
            'coordinator_name' => $validated['coordinator_name'] ?? null,
            'coordinator_email' => $validated['coordinator_email'] ?? null,
            'coordinator_phone' => $validated['coordinator_phone'] ?? null,
            'coordinator_user_id' => !empty($validated['coordinator_user_id']) ? $validated['coordinator_user_id'] : null,
            'requirements' => $this->parseLinesToArray($validated['requirements'] ?? null),
            'is_accepting_members' => $request->boolean('is_accepting_members'),
            'updated_by' => $request->user()?->id,
        ]);

        if (!empty($validated['redirect_to'])) {
            return redirect($validated['redirect_to'])
                ->with('success', "Ministry '{$ministry->name}' details have been updated successfully.");
        }

        return redirect()->route('admin.ministries')
            ->with('success', "Ministry '{$ministry->name}' details have been updated successfully.");
    }

    /**
     * Toggle the accepting members status for the specified ministry.
     */
    public function toggle(Request $request, Ministry $ministry): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'edit_ministries');

        $ministry->is_accepting_members = !$ministry->is_accepting_members;
        $ministry->save();

        $statusText = $ministry->is_accepting_members ? 'now Open for member applications' : 'now Closed for applications';

        return back()->with('success', "Ministry '{$ministry->name}' is {$statusText}.");
    }

    /**
     * Remove the specified parish ministry from storage.
     */
    public function destroy(Request $request, Ministry $ministry): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'delete_ministries');

        $name = $ministry->name;
        $ministry->delete();

        return redirect()->route('admin.ministries')
            ->with('success', "Ministry '{$name}' has been deleted from the directory.");
    }

    /**
     * Convert multi-line textarea string into an array of trimmed lines.
     */
    private function parseLinesToArray(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text)),
            fn ($line) => $line !== ''
        ));
    }

    /**
     * Enforce permission-based authorization.
     */
    private function authorizeAction($user, string $permission): void
    {
        abort_unless(
            $user && ($user->role === 'super_admin' || $user->hasPermission($permission) || $user->hasPermission('manage_ministries')),
            403,
            'You do not have permission to perform this ministry action.'
        );
    }
}

