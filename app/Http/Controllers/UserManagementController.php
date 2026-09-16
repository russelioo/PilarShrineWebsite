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
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    public function parishioners(Request $request): View
    {
        $parishioners = $this->filteredUsers($request, ['user'])->paginate(10)->withQueryString();
        return view('admin.parishioners', compact('parishioners'));
    }

    public function staff(Request $request): View|StreamedResponse
    {
        $actor = $request->user();
        abort_unless($actor && ($actor->hasParishWideAccess() || $actor->isCommissionMember() || $actor->isParishAdministration()), 403, 'Unauthorized access to staff management.');

        $roles = ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'commission_member', 'staff'];

        if ($request->query('export') === 'csv') {
            $allStaff = $this->filteredUsers($request, $roles)->with(['commission', 'commissions', 'ministries'])->get();
            $filename = 'pilar_shrine_staff_' . now()->format('Y-m-d') . '.csv';

            return response()->streamDownload(function () use ($allStaff): void {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['#', 'Name', 'Email', 'Phone', 'Role', 'Organization', 'Responsibilities', 'Commission / Ministry', 'Status']);
                foreach ($allStaff as $idx => $s) {
                    fputcsv($handle, [
                        $idx + 1,
                        $s->name,
                        $s->email,
                        $s->phone ?? '',
                        $s->role_badge_label,
                        $s->organization_label,
                        $s->responsibilities_label,
                        $s->hasParishWideCommissionOversight() ? 'All Commissions' : $s->commission_ministry_summary,
                        $s->is_verified ? 'Active' : 'Inactive',
                    ]);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $staff = $this->filteredUsers($request, $roles)->with(['commission', 'commissions', 'ministries'])->paginate(10)->withQueryString();

        $commissions = $actor->hasParishWideAccess()
            ? Commission::where('is_active', true)->orderByRaw('LOWER(name) ASC')->get()
            : Commission::where('id', $actor->commission_id)->get();

        $ministries = \App\Models\Ministry::orderBy('name')->get();

        return view('admin.staff', compact('staff', 'commissions', 'ministries', 'actor'));
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
            ? ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'staff', 'commission_member', 'user']
            : ['staff', 'commission_member'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^(09|\+639)\d{2}[\s-]?\d{3}[\s-]?\d{4}$/'],
            'organization' => ['nullable', 'string', 'in:parish_administration,commission,ministry,parishioner'],
            'role' => ['required', 'string', 'in:' . implode(',', $allowedRoles)],
            'position' => ['nullable', 'string', 'max:255'],
            'responsibilities' => ['nullable', 'string', 'max:1000'],
            'commission_id' => ['nullable', 'exists:commissions,id'],
            'commission_ids' => ['nullable', 'array'],
            'commission_ids.*' => ['integer', 'exists:commissions,id'],
            'commission_roles' => ['nullable', 'array'],
            'ministry_ids' => ['nullable', 'array'],
            'ministry_ids.*' => ['integer', 'exists:ministries,id'],
            'ministry_roles' => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
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

        // Determine organization if not explicitly passed
        $organization = $validated['organization'] ?? null;
        if (! $organization) {
            if (in_array($validated['role'], ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary'], true)) {
                $organization = 'parish_administration';
            } elseif (in_array($validated['role'], ['commission_admin', 'commission_member', 'staff'], true)) {
                $organization = 'commission';
            } else {
                $organization = 'parishioner';
            }
        }

        // Determine default position
        $position = ! empty($validated['position']) ? trim($validated['position']) : match ($validated['role']) {
            'super_admin', 'admin' => 'Parish Administrator',
            'parish_priest' => 'Parish Priest',
            'parochial_vicar' => 'Parochial Vicar',
            'parish_secretary' => 'Parish Secretary',
            'commission_admin' => 'Commission Coordinator',
            'commission_member' => 'Commission Member',
            'staff' => 'Staff',
            default => ucfirst(str_replace('_', ' ', $validated['role'])),
        };

        // Determine default responsibilities
        $responsibilities = ! empty($validated['responsibilities']) ? trim($validated['responsibilities']) : match ($validated['role']) {
            'super_admin', 'admin' => 'Parish Administration & System Management',
            'parish_priest' => 'Parish Oversight & Pastoral Care',
            'parochial_vicar' => 'Liturgical & Pastoral Care',
            'parish_secretary' => 'Administrative Staff',
            'commission_admin' => 'Commission Coordinator',
            'commission_member', 'staff' => 'Commission Member',
            default => 'Parishioner',
        };

        // Determine permissions
        $permissions = $validated['permissions'] ?? [];
        if (in_array($validated['role'], ['super_admin', 'admin', 'parish_priest', 'parochial_vicar'], true)) {
            $permissions = array_unique(array_merge($permissions, ['all_commissions', 'all_ministries', 'parish_oversight']));
        }

        // Commission assignment logic:
        $commissionIds = $validated['commission_ids'] ?? [];
        if (empty($commissionIds) && ! empty($validated['commission_id'])) {
            $commissionIds = [(int) $validated['commission_id']];
        }

        // Set primary commission_id (first connected commission or null)
        $primaryCommissionId = null;
        if (! empty($commissionIds) && $organization !== 'parish_administration') {
            $primaryCommissionId = $commissionIds[0];
        } elseif ($actor->isCommissionAdmin() && ! $actor->hasParishWideAccess()) {
            $primaryCommissionId = $actor->commission_id;
            if (! in_array($actor->commission_id, $commissionIds, true)) {
                $commissionIds[] = $actor->commission_id;
            }
        }

        $user = User::create([
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower(trim($validated['email'])),
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
            'role' => $validated['role'],
            'organization' => $organization,
            'position' => $position,
            'responsibilities' => $responsibilities,
            'permissions' => $permissions,
            'commission_id' => $primaryCommissionId,
            'is_verified' => $isActive,
            'email_verified_at' => $isActive ? now() : null,
            'password_hash' => Hash::make($validated['password']),
            'avatar' => $avatarPath,
        ]);

        // Connect multiple commissions
        foreach ($commissionIds as $cId) {
            $cRole = $request->input("commission_roles.{$cId}", ($user->role === 'commission_admin' ? 'coordinator' : 'member'));
            CommissionMembership::updateOrCreate(
                ['user_id' => $user->id, 'commission_id' => $cId],
                [
                    'role' => $cRole,
                    'status' => $isActive ? 'active' : 'inactive',
                    'joined_at' => now(),
                ]
            );
        }

        // Connect multiple ministries
        $ministryIds = $validated['ministry_ids'] ?? [];
        foreach ($ministryIds as $mId) {
            $mRole = $request->input("ministry_roles.{$mId}", 'member');
            \App\Models\MinistryMembership::updateOrCreate(
                ['user_id' => $user->id, 'ministry_id' => $mId],
                [
                    'role' => $mRole,
                    'status' => 'approved',
                    'joined_at' => now(),
                    'reviewed_at' => now(),
                    'reviewed_by' => $actor->id,
                ]
            );
        }

        $user->load(['commission', 'commissions', 'ministries']);

        // Record Audit Log
        AuditLogger::log(
            action: 'user_created',
            description: "Created account for {$user->name} ({$user->position}) in {$user->organization_label} with {$user->commission_ministry_summary}.",
            target: $user,
            commissionId: $primaryCommissionId,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'organization' => $user->organization,
                'position' => $user->position,
                'responsibilities' => $user->responsibilities,
                'commission_ids' => $commissionIds,
                'ministry_ids' => $ministryIds,
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
                    'organization' => $user->organization_label,
                    'organization_raw' => $user->organization,
                    'position' => $user->position ?: $user->role_badge_label,
                    'responsibilities' => $user->responsibilities_label,
                    'commission_ministry' => $user->commission_ministry_summary,
                    'commission' => $user->commission_ministry_summary,
                    'commission_id' => $user->commission_id,
                    'status' => $user->is_verified ? 'Active' : 'Inactive',
                    'status_raw' => $user->is_verified ? 'active' : 'inactive',
                    'last_login' => 'Never',
                    'avatar' => $user->avatar,
                    'initials' => $user->initials,
                    'has_parish_wide_access' => $user->hasParishWideAccess(),
                    'has_parish_wide_commission_oversight' => $user->hasParishWideCommissionOversight(),
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
                'organization' => $user->organization_label,
                'responsibilities' => $user->responsibilities_label,
                'commission' => $user->commission_ministry_summary,
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
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('responsibilities', 'like', "%{$search}%");
            });
        });

        $organization = $request->string('organization')->toString();
        $query->when($organization !== '', fn (Builder $query) => $query->where('organization', $organization));

        $role = $request->string('role')->toString();
        $query->when($role !== '' && in_array($role, $roles, true), fn (Builder $query) => $query->where('role', $role));
        $query->when($request->input('status') === 'active', fn (Builder $query) => $query->where('is_verified', true));
        $query->when(in_array($request->input('status'), ['inactive', 'pending'], true), fn (Builder $query) => $query->where('is_verified', false));

        return match ($request->input('sort')) {
            'name' => $query->orderByRaw('LOWER(name) ASC'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };
    }
}
