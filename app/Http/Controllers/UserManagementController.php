<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CommissionMembership;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function parishioners(Request $request): View
    {
        $parishioners = $this->filteredUsers($request, ['user'])->paginate(10)->withQueryString();
        return view('admin.parishioners', compact('parishioners'));
    }

    public function staff(Request $request): View
    {
        $actor = $request->user();
        abort_unless($actor && ($actor->hasParishWideAccess() || $actor->isCommissionMember()), 403, 'Unauthorized access to staff management.');

        $roles = ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'commission_member', 'staff'];
        $staff = $this->filteredUsers($request, $roles)->with('commission')->paginate(10)->withQueryString();

        $commissions = $actor->hasParishWideAccess()
            ? Commission::where('is_active', true)->orderByRaw('LOWER(name) ASC')->get()
            : Commission::where('id', $actor->commission_id)->get();

        return view('admin.staff', compact('staff', 'commissions', 'actor'));
    }

    public function storeStaff(Request $request): JsonResponse|RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor && ($actor->hasParishWideAccess() || $actor->isCommissionAdmin()), 403, 'Unauthorized to create staff accounts.');

        // Commission-scoped authorization rule: Commission admins can only assign users to their own commission
        if ($actor->isCommissionAdmin() && ! $actor->hasParishWideAccess()) {
            if ($request->filled('commission_id') && (int) $request->input('commission_id') !== (int) $actor->commission_id) {
                abort(403, 'Commission Admins can only create accounts for their assigned commission.');
            }
        }

        $allowedRoles = $actor->hasParishWideAccess()
            ? ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'staff', 'commission_member']
            : ['staff', 'commission_member'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^(09|\+639)\d{2}[\s-]?\d{3}[\s-]?\d{4}$/'],
            'role' => ['required', 'string', 'in:' . implode(',', $allowedRoles)],
            'commission_id' => [
                'nullable',
                'exists:commissions,id',
                function ($attribute, $value, $fail) use ($actor, $request) {
                    $selectedRole = $request->input('role');
                    $isCommissionRole = in_array($selectedRole, ['staff', 'commission_member', 'commission_admin'], true);

                    if ($isCommissionRole && empty($value)) {
                        $fail('Commission is required for commission staff and member roles.');
                    }

                    if ($actor->isCommissionAdmin() && ! $actor->hasParishWideAccess() && (int) $value !== (int) $actor->commission_id) {
                        $fail('You cannot assign members to other commissions.');
                    }
                },
            ],
            'status' => ['required', 'string', 'in:active,inactive'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Full name cannot be empty.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Please enter a valid Philippine mobile number (e.g., 09XX XXX XXXX or +639XX XXX XXXX).',
            'role.required' => 'Please select a role.',
            'role.in' => 'Selected role is invalid or unauthorized.',
            'commission_id.exists' => 'The selected commission does not exist.',
            'status.required' => 'Please select an account status.',
            'status.in' => 'Status must be either Active or Inactive.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'profile_photo.image' => 'The uploaded file must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, webp.',
            'profile_photo.max' => 'Profile photo must not exceed 2MB.',
        ]);

        $fullName = trim($validated['name']);
        $nameParts = preg_split('/\s+/', $fullName) ?: [];
        $firstName = array_shift($nameParts) ?? $fullName;
        $lastName = !empty($nameParts) ? implode(' ', $nameParts) : $firstName;

        $avatarPath = null;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('avatars', 'public');
            $avatarPath = '/storage/' . $path;
        }

        $isActive = ($validated['status'] === 'active');
        $isParishWideRole = in_array($validated['role'], ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary'], true);
        $commissionId = $isParishWideRole ? null : ($validated['commission_id'] ?? ($actor->isCommissionAdmin() ? $actor->commission_id : null));

        $user = User::create([
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower(trim($validated['email'])),
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
            'role' => $validated['role'],
            'commission_id' => $commissionId,
            'is_verified' => $isActive,
            'email_verified_at' => $isActive ? now() : null,
            'password_hash' => Hash::make($validated['password']),
            'avatar' => $avatarPath,
        ]);

        // Create membership record if assigned to commission
        if ($commissionId) {
            CommissionMembership::updateOrCreate(
                ['user_id' => $user->id, 'commission_id' => $commissionId],
                [
                    'role' => $user->role === 'commission_admin' ? 'admin' : 'staff',
                    'status' => $isActive ? 'active' : 'inactive',
                    'joined_at' => now(),
                ]
            );
        }

        $user->load('commission');

        // Record Audit Log
        AuditLogger::log(
            action: 'user_created',
            description: "Created staff account for {$user->name} ({$user->role_badge_label}) assigned to " . ($user->commission?->name ?? 'All Commissions') . '.',
            target: $user,
            commissionId: $commissionId,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'commission_id' => $commissionId,
                'commission_name' => $user->commission?->name,
                'status' => $isActive ? 'active' : 'inactive',
            ],
            actor: $actor
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff account created successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?: '—',
                    'role' => $user->role_badge_label,
                    'role_raw' => $user->role,
                    'commission' => $user->commission?->name ?? 'All Commissions',
                    'commission_id' => $user->commission_id,
                    'status' => $user->is_verified ? 'Active' : 'Inactive',
                    'status_raw' => $user->is_verified ? 'active' : 'inactive',
                    'last_login' => 'Never',
                    'avatar' => $user->avatar,
                    'initials' => $user->initials,
                ],
            ], 201);
        }

        return redirect()->route('admin.staff')->with('success', 'Staff account created successfully.');
    }

    /**
     * Get live activity audit history for a specific staff member.
     */
    public function activity(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor, 403);

        // Commission scoping check
        if (! $actor->hasParishWideAccess()) {
            if ($user->hasParishWideAccess() || (int) $user->commission_id !== (int) $actor->commission_id) {
                abort(403, 'You are not authorized to view activity for this user.');
            }
        }

        $activities = AuditLog::query()
            ->where('target_id', $user->id)
            ->where('target_type', 'User')
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id'              => $log->id,
                'action'          => $log->action,
                'action_label'    => $log->action_label,
                'description'     => $log->description,
                'actor_name'      => $log->user_name ?: 'System',
                'commission_name' => $log->commission_name,
                'ip_address'      => $log->ip_address,
                'created_at'      => $log->created_at?->format('M d, Y h:i A') ?? '—',
                'time_ago'        => $log->created_at?->diffForHumans() ?? 'Recently',
                'new_values'      => $log->new_values,
                'old_values'      => $log->old_values,
            ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role_badge_label,
                'commission' => $user->commission?->name ?? 'All Commissions',
                'status' => $user->is_verified ? 'Active' : 'Inactive',
                'avatar' => $user->avatar,
                'initials' => $user->initials,
            ],
            'activities' => $activities,
        ]);
    }

    /**
     * View members of a specific commission.
     */
    public function commissionMembers(Request $request, Commission $commission): View|JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor, 403);

        // Security check: Commission user cannot view another commission's members
        if (! $actor->hasParishWideAccess() && (int) $actor->commission_id !== (int) $commission->id) {
            abort(403, 'You are not authorized to access this commission.');
        }

        $members = User::query()
            ->where('commission_id', $commission->id)
            ->whereNull('deleted_at')
            ->latest('created_at')
            ->paginate(15);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'commission' => $commission,
                'members' => $members,
            ]);
        }

        return view('admin.commissions.members', compact('commission', 'members', 'actor'));
    }

    private function filteredUsers(Request $request, array $roles): Builder
    {
        $actor = $request->user();
        $query = User::query()->whereIn('role', $roles)->whereNull('deleted_at');

        // Strict Server-Side Commission Scoping
        if ($actor && ! $actor->hasParishWideAccess()) {
            if (! $actor->commission_id) {
                $query->whereRaw('1 = 0');
            } else {
                if ($request->filled('commission_id') && (int) $request->input('commission_id') !== (int) $actor->commission_id) {
                    abort(403, 'You are not authorized to view members from another commission.');
                }
                $query->where('commission_id', $actor->commission_id);
            }
            // Cannot discover or manage higher-level accounts
            $query->whereNotIn('role', ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary']);
        } else {
            // Parish-wide access: optional filter by commission
            if ($request->filled('commission_id') && $request->input('commission_id') !== 'all') {
                $query->where('commission_id', $request->input('commission_id'));
            }
        }

        $query->when($request->filled('search'), function (Builder $query) use ($request): void {
            $search = $request->string('search')->trim()->toString();
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        });

        $role = $request->string('role')->toString();
        $query->when($role !== '' && in_array($role, $roles, true), fn (Builder $query) => $query->where('role', $role));
        $query->when($request->input('status') === 'active', fn (Builder $query) => $query->where('is_verified', true));
        $query->when(in_array($request->input('status'), ['inactive', 'pending'], true), fn (Builder $query) => $query->where('is_verified', false));

        return match ($request->input('sort')) {
            'name' => $query->orderBy('name'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };
    }
}
