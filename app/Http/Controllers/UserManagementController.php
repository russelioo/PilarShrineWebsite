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
        $actor = $request->user();
        abort_unless(
            $actor && ($actor->role === 'super_admin' || $actor->hasPermission('view_users') || $actor->hasPermission('parishioners')),
            403,
            'You do not have permission to view parishioners.'
        );

        $parishioners = $this->filteredUsers($request, ['user'])->paginate(10)->withQueryString();
        $commissions = Commission::where('is_active', true)->orderByRaw('LOWER(name) ASC')->get();
        $ministries = \App\Models\Ministry::orderBy('name')->get();

        return view('admin.parishioners', compact('parishioners', 'actor', 'commissions', 'ministries'));
    }

    /**
     * Promote a registered parishioner to a staff, commission, or admin role.
     * Restricted strictly to Super Administrators and Parish Administrators.
     */
    public function promoteParishioner(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        // Strict Access Control: Only Super Admin and Parish Administrator can promote
        abort_unless(
            in_array($actor->role, ['super_admin', 'admin'], true),
            403,
            'Only Super Administrators can promote parishioners to staff roles.'
        );

        // Guard: Only regular parishioners can be promoted
        if ($user->role !== 'user') {
            abort(422, "User {$user->name} is already a {$user->role_badge_label} and cannot be promoted as a parishioner.");
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,parish_secretary,commission_admin,staff,commission_member'],
            'organization' => ['required', 'string', 'in:parish_administration,commission,ministry'],
            'position' => ['nullable', 'string', 'max:255'],
            'responsibilities' => ['nullable', 'string', 'max:1000'],
            'commission_id' => ['nullable', 'exists:commissions,id'],
            'commission_ids' => ['nullable', 'array'],
            'commission_ids.*' => ['integer', 'exists:commissions,id'],
            'ministry_ids' => ['nullable', 'array'],
            'ministry_ids.*' => ['integer', 'exists:ministries,id'],
        ], [
            'role.required' => 'Please select an assigned staff role.',
            'role.in' => 'Selected role is invalid.',
            'organization.required' => 'Please select an organization unit.',
            'organization.in' => 'Selected organization is invalid.',
            'commission_id.exists' => 'The selected commission does not exist.',
        ]);

        $oldRole = $user->role;
        $newRole = $validated['role'];
        $newOrg = $validated['organization'];

        // Determine default position label if not provided
        $position = !empty($validated['position']) ? trim($validated['position']) : match ($newRole) {
            'admin' => 'Parish Administrator',
            'parish_secretary' => 'Parish Secretary',
            'commission_admin' => 'Commission Coordinator',
            'staff' => 'Parish Staff',
            'commission_member' => 'Commission Member',
            default => 'Staff Member',
        };

        // Determine default responsibilities if not provided
        $responsibilities = !empty($validated['responsibilities']) ? trim($validated['responsibilities']) : match ($newOrg) {
            'parish_administration' => 'Parish Administration & Pastoral Governance',
            'commission' => 'Commission Operations & Programs',
            'ministry' => 'Ministry Service & Liturgy',
            default => 'Staff Responsibilities',
        };

        $primaryCommissionId = $validated['commission_id'] ?? null;
        if (! $primaryCommissionId && ! empty($validated['commission_ids'])) {
            $primaryCommissionId = $validated['commission_ids'][0];
        }

        // 1. Update user fields
        $user->role = $newRole;
        $user->organization = $newOrg;
        $user->position = $position;
        $user->responsibilities = $responsibilities;
        $user->commission_id = $primaryCommissionId;
        $user->permissions = null; // Revert to role default baseline
        $user->save();

        // 2. Sync commission memberships
        $allCommissionIds = collect($validated['commission_ids'] ?? []);
        if ($primaryCommissionId && ! $allCommissionIds->contains($primaryCommissionId)) {
            $allCommissionIds->push($primaryCommissionId);
        }

        if ($allCommissionIds->isNotEmpty()) {
            foreach ($allCommissionIds as $cid) {
                $isCoord = ($newRole === 'commission_admin' || str_contains(strtolower($position), 'coordinator'));
                $isOff = $isCoord;
                $memberPos = $position ?: ($isCoord ? 'Commission Coordinator' : 'Member');

                \App\Models\CommissionMembership::updateOrCreate(
                    ['user_id' => $user->id, 'commission_id' => $cid],
                    [
                        'role'       => $isCoord ? 'coordinator' : 'member',
                        'position'   => $memberPos,
                        'is_officer' => $isOff,
                        'status'     => 'active',
                        'joined_at'  => now(),
                    ]
                );

                if ($isCoord) {
                    Commission::where('id', $cid)->update(['head_user_id' => $user->id]);
                }
            }
        }

        // 3. Sync ministry memberships
        if (! empty($validated['ministry_ids'])) {
            foreach ($validated['ministry_ids'] as $mid) {
                \App\Models\MinistryMembership::updateOrCreate(
                    ['user_id' => $user->id, 'ministry_id' => $mid],
                    [
                        'role' => 'member',
                        'status' => 'active',
                        'joined_at' => now(),
                    ]
                );
            }
        }

        // 4. Record Audit Log
        AuditLogger::log(
            action: 'parishioner_promoted_to_staff',
            description: "Promoted {$user->name} from Parishioner to {$user->position} ({$user->role_badge_label}) under {$user->organization_label}.",
            target: $user,
            commissionId: $primaryCommissionId,
            oldValues: ['role' => $oldRole, 'organization' => 'parishioner'],
            newValues: [
                'role' => $newRole,
                'organization' => $newOrg,
                'position' => $position,
                'commission_id' => $primaryCommissionId,
            ],
            actor: $actor
        );

        return response()->json([
            'success' => true,
            'message' => "{$user->name} has been promoted to {$user->position} ({$user->role_badge_label}) and added to the staff roster.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'role_label' => $user->role_badge_label,
                'position' => $user->position,
                'organization' => $user->organization_label,
            ],
        ]);
    }

    public function staff(Request $request): View|StreamedResponse
    {
        $actor = $request->user();
        abort_unless(
            $actor && ($actor->role === 'super_admin' || $actor->hasPermission('staff_management') || $actor->hasPermission('view_users')),
            403,
            'You do not have permission to view staff management.'
        );

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
        abort_unless(
            $actor && ($actor->role === 'super_admin' || $actor->hasPermission('create_users')),
            403,
            'You do not have permission to create user accounts.'
        );

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
            $permissions = array_unique(array_merge($permissions, [
                'messages', 'parishioners', 'staff_management', 'audit_logs',
                'manage_ministries', 'ministry_requests', 'mass_schedules',
                'announcements', 'donations', 'all_commissions', 'all_ministries', 'parish_oversight'
            ]));
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
            $isCoordinator = ($cRole === 'coordinator' || $user->role === 'commission_admin' || str_contains(strtolower($position), 'coordinator'));
            $isOfficer = $isCoordinator || ($cRole === 'officer');
            $memberPos = $position ?: ($isCoordinator ? 'Commission Coordinator' : ($isOfficer ? 'Commission Officer' : 'Member'));

            CommissionMembership::updateOrCreate(
                ['user_id' => $user->id, 'commission_id' => $cId],
                [
                    'role'       => $isCoordinator ? 'coordinator' : ($isOfficer ? 'admin' : 'member'),
                    'position'   => $memberPos,
                    'is_officer' => $isOfficer,
                    'status'     => $isActive ? 'active' : 'inactive',
                    'joined_at'  => now(),
                ]
            );

            if ($isCoordinator) {
                Commission::where('id', $cId)->update(['head_user_id' => $user->id]);
            }
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

    /**
     * Get a user's permissions and available permissions catalog.
     */
    public function permissions(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless(
            $actor && ($actor->role === 'super_admin' || $actor->hasPermission('view_permissions') || $actor->hasPermission('modify_permissions') || $actor->hasPermission('assign_permissions') || $actor->canManagePermissions()),
            403,
            'You do not have permission to view permissions.'
        );

        // Security: Super Administrator permissions cannot be viewed or modified
        if ($user->role === 'super_admin') {
            abort(403, 'Super Administrator permissions cannot be modified.');
        }

        // Security: Non-parish-wide users cannot view permissions outside their commission
        if (! $actor->hasParishWideAccess()) {
            if ($user->hasParishWideAccess() || (int) $user->commission_id !== (int) $actor->commission_id) {
                abort(403, 'You are not authorized to view permissions for this user.');
            }
        }

        $currentPermissions = $user->getEffectivePermissions();
        $available = User::getAllAvailablePermissions();
        $defaults = User::getDefaultPermissionsForRole($user->role);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->role_badge_label,
                'organization' => $user->organization,
                'organization_label' => $user->organization_label,
                'position' => $user->position ?: $user->role_badge_label,
                'responsibilities' => $user->responsibilities_label,
                'is_super_admin' => $user->role === 'super_admin',
            ],
            'current_permissions' => $currentPermissions,
            'default_permissions' => $defaults,
            'available_permissions' => $available,
        ]);
    }

    /**
     * Update a user's granular permissions.
     */
    public function updatePermissions(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless(
            $actor && ($actor->role === 'super_admin' || $actor->hasPermission('modify_permissions') || $actor->hasPermission('assign_permissions') || $actor->canManagePermissions()),
            403,
            'You do not have permission to modify permissions.'
        );

        // Security: Super Administrator permissions cannot be modified
        if ($user->role === 'super_admin') {
            abort(403, 'Cannot modify Super Administrator permissions.');
        }

        // Security: Non-parish-wide users cannot modify permissions outside their commission
        if (! $actor->hasParishWideAccess()) {
            if ($user->hasParishWideAccess() || (int) $user->commission_id !== (int) $actor->commission_id) {
                abort(403, 'You are not authorized to modify permissions for this user.');
            }
        }

        $validated = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string'],
        ]);

        $oldPermissions = $user->permissions ?? [];
        $newPermissions = array_values(array_unique($validated['permissions']));

        $user->permissions = $newPermissions;
        $user->save();

        $targetTitle = $user->position ?: $user->role_badge_label;
        $permCount = count($newPermissions);

        // Audit Log entry
        AuditLogger::log(
            action: 'permission_updated',
            description: "Updated permissions for {$user->name} ({$targetTitle}): {$permCount} permissions granted.",
            target: $user,
            commissionId: $user->commission_id,
            oldValues: ['permissions' => $oldPermissions],
            newValues: ['permissions' => $newPermissions],
            actor: $actor
        );

        return response()->json([
            'success' => true,
            'message' => "Permissions for {$user->name} updated successfully.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'permissions_count' => count($newPermissions),
            ],
            'permissions' => $newPermissions,
        ]);
    }

    /**
     * Remove a staff/commission member from staff and revert account to regular parishioner.
     */
    public function revertToParishioner(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        // Security: Actor cannot revert themselves
        if ($actor->id === $user->id) {
            abort(403, 'You cannot remove your own staff account.');
        }

        // Security: Super Admin cannot be reverted
        if ($user->role === 'super_admin') {
            abort(403, 'Super Administrator accounts cannot be reverted to parishioner.');
        }

        // Strict Security: Only Parish Administrator / Super Admin can revert staff to parishioner
        abort_unless(
            in_array($actor->role, ['super_admin', 'admin'], true),
            403,
            'Only Parish Administrators can revert staff members to parishioners.'
        );

        $oldRole = $user->role;
        $oldPosition = $user->position;
        $oldOrg = $user->organization;
        $oldCommissionId = $user->commission_id;

        // 1. Revert user record fields to regular Parishioner
        $user->role = 'user';
        $user->organization = 'parishioner';
        $user->position = 'Parishioner';
        $user->responsibilities = 'Parishioner';
        $user->commission_id = null;
        $user->permissions = null; // Revert to role default permissions
        $user->save();

        // 2. Detach or delete commission memberships
        \App\Models\CommissionMembership::where('user_id', $user->id)->delete();
        Commission::where('head_user_id', $user->id)->update(['head_user_id' => null]);

        // 3. Record Audit Log
        AuditLogger::log(
            action: 'staff_reverted_to_parishioner',
            description: "Removed {$user->name} from {$oldPosition} ({$oldRole}) and commission roles. Account reverted to regular Parishioner.",
            target: $user,
            commissionId: $oldCommissionId,
            oldValues: [
                'role' => $oldRole,
                'organization' => $oldOrg,
                'position' => $oldPosition,
                'commission_id' => $oldCommissionId,
            ],
            newValues: [
                'role' => 'user',
                'organization' => 'parishioner',
                'position' => 'Parishioner',
                'commission_id' => null,
            ],
            actor: $actor
        );

        return response()->json([
            'success' => true,
            'message' => "{$user->name} has been removed from staff and successfully reverted to a regular Parishioner.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => 'user',
                'role_label' => $user->role_badge_label,
            ],
        ]);
    }

    /**
     * Delete/deactivate a staff member account (Soft Delete Option B).
     * The account is removed from the database view, but historical records remain intact.
     * If the person registers again with this email, their account will be reactivated as a parishioner.
     */
    public function deleteStaff(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        // Security: Super Admin cannot be deleted
        if ($user->role === 'super_admin' || $user->isSuperAdmin()) {
            abort(403, 'Super Administrator accounts cannot be deleted.');
        }

        // Security: Cannot delete own account
        if ($user->id === $actor->id) {
            abort(403, 'You cannot delete your own account.');
        }

        // Strict Security: Only Parish Administrator / Super Admin can delete staff accounts
        abort_unless(
            in_array($actor->role, ['super_admin', 'admin'], true),
            403,
            'Only Parish Administrators can delete staff accounts.'
        );

        $userName = $user->name;
        $userEmail = $user->email;
        $oldRole = $user->role;
        $oldCommissionId = $user->commission_id;

        // 1. Detach from commission and ministries
        \App\Models\CommissionMembership::where('user_id', $user->id)->delete();
        \App\Models\MinistryMembership::where('user_id', $user->id)->delete();
        Commission::where('head_user_id', $user->id)->update(['head_user_id' => null]);
        \App\Models\Ministry::where('coordinator_user_id', $user->id)->update(['coordinator_user_id' => null]);

        // 2. Clear commission_id, mark unverified
        $user->commission_id = null;
        $user->is_verified = false;
        $user->save();

        // 3. Soft delete user
        $user->delete();

        // 4. Audit Log
        AuditLogger::log(
            action: 'staff_account_deleted',
            description: "Deleted and deactivated staff account for {$userName} ({$userEmail}). All roles revoked; account eligible for reactivation if re-registered.",
            target: null,
            commissionId: $oldCommissionId,
            oldValues: [
                'name' => $userName,
                'email' => $userEmail,
                'role' => $oldRole,
                'commission_id' => $oldCommissionId,
            ],
            actor: $actor
        );

        return response()->json([
            'success' => true,
            'message' => "Account for {$userName} has been removed and deactivated.",
        ]);
    }

    /**
     * Show detailed user profile and organizational information.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && ($actor->hasParishWideAccess() || $actor->canAccessCommission($user->commission_id)), 403);

        $user->loadMissing(['commissions', 'ministries']);

        $allPerms = [];
        foreach (User::getAllAvailablePermissions() as $group) {
            foreach (array_keys($group['permissions']) as $pKey) {
                $allPerms[] = $pKey;
            }
        }
        $granted = count(array_filter($allPerms, fn($p) => $user->hasPermission($p)));
        $restricted = count($allPerms) - $granted;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: '—',
                'role' => $user->role,
                'role_label' => $user->role_badge_label,
                'organization' => $user->organization,
                'organization_label' => $user->organization_label,
                'position' => $user->position ?: $user->role_badge_label,
                'responsibilities' => $user->responsibilities_label,
                'commission_ministry_summary' => $user->hasParishWideCommissionOversight() ? 'All Commissions' : ($user->commission_ministry_summary === '—' ? 'None' : $user->commission_ministry_summary),
                'status' => $user->is_verified ? 'Active' : 'Inactive',
                'avatar' => $user->avatar,
                'initials' => $user->initials,
                'commissions' => $user->commissions->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'role' => ucfirst($c->pivot->role ?? 'member'),
                ]),
                'ministries' => $user->ministries->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'role' => ucfirst($m->pivot->role ?? 'member'),
                ]),
                'permissions_summary' => [
                    'granted' => $granted,
                    'restricted' => $restricted,
                ],
            ],
        ]);
    }

    /**
     * Switch active organization context in session.
     */
    public function switchOrganization(Request $request): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        $validated = $request->validate([
            'organization_type' => ['required', 'string', 'in:parish_administration,commission,ministry'],
            'organization_id' => ['nullable', 'integer'],
        ]);

        $type = $validated['organization_type'];
        $id = $validated['organization_id'] ?? null;

        $available = $actor->getAvailableOrganizations();
        $targetOrg = collect($available)->first(function ($org) use ($type, $id) {
            if ($type === 'parish_administration') {
                return $org['type'] === 'parish_administration';
            }
            return $org['type'] === $type && (int) ($org['id'] ?? 0) === (int) $id;
        });

        if (! $targetOrg) {
            abort(403, 'You are not a member of the selected organization.');
        }

        session(['active_organization_context' => $targetOrg]);

        return response()->json([
            'success' => true,
            'message' => "Switched context to {$targetOrg['name']}.",
            'context' => $targetOrg,
        ]);
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
