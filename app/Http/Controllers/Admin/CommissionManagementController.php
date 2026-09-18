<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CommissionDocument;
use App\Models\CommissionMembership;
use App\Models\CommissionProject;
use App\Models\Ministry;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommissionManagementController extends Controller
{
    /**
     * Display a listing of all pastoral commissions (Super Admin & leadership).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Commission::class);

        $actor = $request->user();
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status', 'all')->toString();

        $query = Commission::query()
            ->with(['coordinator', 'members', 'officers', 'ministries', 'projects', 'documents'])
            ->orderBy('name');

        // Scoping for users without parish-wide access
        if ($actor && ! $actor->hasParishWideAccess()) {
            $userCommIds = $actor->commissions()->pluck('commissions.id')->toArray();
            if ($actor->commission_id) {
                $userCommIds[] = $actor->commission_id;
            }
            $query->whereIn('id', array_unique($userCommIds));
        }

        if (! empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $commissions = $query->get();

        // Calculate real, dynamic KPI counters from database
        $kpi = [
            'total_commissions' => Commission::count(),
            'active_commissions' => Commission::where('is_active', true)->count(),
            'total_members' => CommissionMembership::count(),
            'total_officers' => CommissionMembership::where('is_officer', true)->count(),
            'total_projects' => CommissionProject::count(),
            'active_projects' => CommissionProject::whereIn('status', ['planning', 'ongoing'])->count(),
            'total_documents' => CommissionDocument::count(),
            'total_ministries' => Ministry::whereNotNull('commission_id')->count(),
        ];

        // All users list for modals (assigning coordinator or member)
        $allUsers = User::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->select(['id', 'name', 'email', 'avatar', 'role', 'position'])
            ->get();

        return view('admin.commissions.index', [
            'commissions' => $commissions,
            'kpi'         => $kpi,
            'allUsers'    => $allUsers,
            'actor'       => $actor,
        ]);
    }

    /**
     * Display a specific commission detail page with full tabs.
     */
    public function show(Request $request, Commission $commission): View
    {
        Gate::authorize('view', $commission);

        $actor = $request->user();
        $tab = $request->string('tab', 'overview')->toString();

        // Eager load all relations
        $commission->loadMissing([
            'coordinator',
            'members' => fn ($q) => $q->orderBy('name'),
            'officers' => fn ($q) => $q->orderBy('name'),
            'ministries' => fn ($q) => $q->orderBy('name'),
            'projects' => fn ($q) => $q->latest(),
            'documents' => fn ($q) => $q->latest(),
        ]);

        // Audit logs for this commission
        $auditLogs = AuditLog::query()
            ->where('commission_id', $commission->id)
            ->latest()
            ->take(25)
            ->get();

        // Non-member users for "Add Member" dropdown
        $existingMemberIds = $commission->members()->pluck('users.id')->toArray();
        $availableUsers = User::query()
            ->whereNotIn('id', $existingMemberIds)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->select(['id', 'name', 'email', 'avatar', 'role', 'position'])
            ->get();

        // All available ministries for assignment
        $availableMinistries = Ministry::query()
            ->whereNull('commission_id')
            ->orWhere('commission_id', $commission->id)
            ->orderBy('name')
            ->get();

        return view('admin.commissions.show', [
            'commission'          => $commission,
            'tab'                 => $tab,
            'auditLogs'           => $auditLogs,
            'availableUsers'      => $availableUsers,
            'availableMinistries' => $availableMinistries,
            'actor'               => $actor,
        ]);
    }

    /**
     * Store a newly created commission.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Commission::class);

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255', 'unique:commissions,name'],
            'code'         => ['nullable', 'string', 'max:50', 'unique:commissions,code'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'icon'         => ['nullable', 'string', 'max:50'],
            'head_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $slug = Str::slug($validated['name']);
        $uniqueSlug = $slug;
        $counter = 1;
        while (Commission::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = "{$slug}-{$counter}";
            $counter++;
        }

        $code = ! empty($validated['code'])
            ? strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', $validated['code']))
            : strtoupper(substr(str_replace(' ', '', $validated['name']), 0, 8));

        $commission = Commission::create([
            'name'         => $validated['name'],
            'slug'         => $uniqueSlug,
            'code'         => $code,
            'description'  => $validated['description'] ?? null,
            'icon'         => $validated['icon'] ?? 'cross',
            'head_user_id' => $validated['head_user_id'] ?? null,
            'is_active'    => true,
        ]);

        // If coordinator assigned, ensure commission_membership
        if (! empty($validated['head_user_id'])) {
            $coord = User::find($validated['head_user_id']);
            if ($coord) {
                if (! $coord->commission_id) {
                    $coord->update(['commission_id' => $commission->id]);
                }
                CommissionMembership::updateOrCreate(
                    ['commission_id' => $commission->id, 'user_id' => $coord->id],
                    [
                        'position'   => 'Commission Coordinator',
                        'is_officer' => true,
                        'role'       => 'head',
                        'status'     => 'active',
                        'joined_at'  => now(),
                    ]
                );
            }
        }

        AuditLogger::log(
            action: 'commission_created',
            description: "Created new commission: {$commission->name} ({$commission->code}).",
            target: $commission,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('admin.commissions.show', $commission)
            ->with('success', "Commission \"{$commission->name}\" created successfully.");
    }

    /**
     * Update an existing commission.
     */
    public function update(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('update', $commission);

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255', "unique:commissions,name,{$commission->id}"],
            'code'         => ['nullable', 'string', 'max:50', "unique:commissions,code,{$commission->id}"],
            'description'  => ['nullable', 'string', 'max:2000'],
            'icon'         => ['nullable', 'string', 'max:50'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        $oldValues = $commission->only(['name', 'code', 'description', 'head_user_id', 'is_active', 'icon']);

        $commission->update([
            'name'         => $validated['name'],
            'code'         => ! empty($validated['code']) ? strtoupper($validated['code']) : $commission->code,
            'description'  => $validated['description'] ?? null,
            'icon'         => $validated['icon'] ?? $commission->icon,
            'head_user_id' => $validated['head_user_id'] ?? null,
            'is_active'    => $request->has('is_active') ? $request->boolean('is_active') : $commission->is_active,
        ]);

        // Sync coordinator if updated
        if (! empty($validated['head_user_id'])) {
            $coord = User::find($validated['head_user_id']);
            if ($coord) {
                if (! $coord->commission_id) {
                    $coord->update(['commission_id' => $commission->id]);
                }
                CommissionMembership::updateOrCreate(
                    ['commission_id' => $commission->id, 'user_id' => $coord->id],
                    [
                        'position'   => 'Commission Coordinator',
                        'is_officer' => true,
                        'role'       => 'head',
                        'status'     => 'active',
                        'joined_at'  => now(),
                    ]
                );
            }
        }

        AuditLogger::log(
            action: 'commission_updated',
            description: "Updated details for commission: {$commission->name}.",
            target: $commission,
            commissionId: $commission->id,
            oldValues: $oldValues,
            newValues: $commission->only(['name', 'code', 'description', 'head_user_id', 'is_active', 'icon']),
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Commission \"{$commission->name}\" updated successfully.");
    }

    /**
     * Activate or deactivate a commission.
     */
    public function toggle(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('toggle', $commission);

        $newStatus = ! $commission->is_active;
        $commission->update(['is_active' => $newStatus]);
        $statusStr = $newStatus ? 'activated' : 'deactivated';

        AuditLogger::log(
            action: $newStatus ? 'commission_activated' : 'commission_deactivated',
            description: "Commission \"{$commission->name}\" was {$statusStr}.",
            target: $commission,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Commission \"{$commission->name}\" is now {$statusStr}.");
    }

    /**
     * Delete / Archive a commission.
     */
    public function destroy(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('delete', $commission);

        $name = $commission->name;
        $commId = $commission->id;

        // Disassociate users and clean memberships
        User::where('commission_id', $commId)->update(['commission_id' => null]);
        CommissionMembership::where('commission_id', $commId)->delete();
        Ministry::where('commission_id', $commId)->update(['commission_id' => null]);

        AuditLogger::log(
            action: 'commission_deleted',
            description: "Deleted commission: {$name} (ID: {$commId}).",
            target: null,
            commissionId: null,
            actor: $request->user(),
            category: 'commission'
        );

        $commission->delete();

        return redirect()->route('admin.commissions')
            ->with('success', "Commission \"{$name}\" has been deleted.");
    }

    /**
     * Super Admin creates a dedicated authenticated Commission Account.
     */
    public function createAccount(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('createAccount', $commission);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8'],
            'position'   => ['required', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'is_officer' => ['nullable', 'boolean'],
        ]);

        $isCoordinator = str_contains(strtolower($validated['position']), 'coordinator');
        $role = $isCoordinator ? 'commission_admin' : 'commission_member';
        $isOfficer = $request->boolean('is_officer') || $isCoordinator;

        // Create the user record
        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password_hash'     => Hash::make($validated['password']),
            'role'              => $role,
            'organization'      => 'commission',
            'position'          => $validated['position'],
            'responsibilities'  => "{$commission->name} — {$validated['position']}",
            'commission_id'     => $commission->id,
            'phone'             => $validated['phone'] ?? null,
            'is_verified'       => true,
            'email_verified_at' => now(),
            'permissions'       => User::getDefaultPermissionsForRole($role),
        ]);

        // Create the commission membership relationship
        CommissionMembership::create([
            'user_id'       => $user->id,
            'commission_id' => $commission->id,
            'position'      => $validated['position'],
            'is_officer'    => $isOfficer,
            'role'          => $isCoordinator ? 'head' : ($isOfficer ? 'admin' : 'member'),
            'status'        => 'active',
            'joined_at'     => now(),
        ]);

        // If this is a Coordinator and commission has no head user, assign
        if ($isCoordinator && empty($commission->head_user_id)) {
            $commission->update(['head_user_id' => $user->id]);
        }

        AuditLogger::log(
            action: 'commission_account_created',
            description: "Created commission account for {$user->name} ({$user->email}) in {$commission->name} as {$validated['position']}.",
            target: $user,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()
            ->with('success', "Account for \"{$user->name}\" ({$user->email}) created successfully for {$commission->name}.");
    }

    /**
     * Add an existing user as a member of this commission.
     */
    public function addMember(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('manageMembers', $commission);

        $validated = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'position'   => ['required', 'string', 'max:100'],
            'is_officer' => ['nullable', 'boolean'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $isOfficer = $request->boolean('is_officer') || str_contains(strtolower($validated['position']), 'coordinator');

        CommissionMembership::updateOrCreate(
            ['commission_id' => $commission->id, 'user_id' => $user->id],
            [
                'position'   => $validated['position'],
                'is_officer' => $isOfficer,
                'role'       => $isOfficer ? 'admin' : 'member',
                'status'     => 'active',
                'joined_at'  => now(),
                'notes'      => $validated['notes'] ?? null,
            ]
        );

        // Update primary commission_id if user doesn't have one
        if (empty($user->commission_id)) {
            $user->update(['commission_id' => $commission->id]);
        }

        AuditLogger::log(
            action: 'commission_member_added',
            description: "Added {$user->name} as {$validated['position']} in {$commission->name}.",
            target: $user,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "{$user->name} added to {$commission->name} as {$validated['position']}.");
    }

    /**
     * Update an existing commission membership (position, officer status, active).
     */
    public function updateMember(Request $request, Commission $commission, CommissionMembership $membership): RedirectResponse
    {
        Gate::authorize('manageMembers', $commission);

        abort_unless((int) $membership->commission_id === (int) $commission->id, 403);

        $validated = $request->validate([
            'position'   => ['required', 'string', 'max:100'],
            'is_officer' => ['nullable', 'boolean'],
            'status'     => ['required', 'in:active,inactive'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $oldPos = $membership->position;
        $isOfficer = $request->boolean('is_officer') || str_contains(strtolower($validated['position']), 'coordinator');

        $membership->update([
            'position'   => $validated['position'],
            'is_officer' => $isOfficer,
            'status'     => $validated['status'],
            'notes'      => $validated['notes'] ?? null,
        ]);

        $userName = $membership->user?->name ?? 'Member';

        AuditLogger::log(
            action: 'commission_member_updated',
            description: "Updated {$userName} in {$commission->name}: position {$oldPos} → {$validated['position']}, status: {$validated['status']}.",
            target: $membership->user,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Updated member details for {$userName}.");
    }

    /**
     * Remove a member from the commission.
     */
    public function removeMember(Request $request, Commission $commission, CommissionMembership $membership): RedirectResponse
    {
        Gate::authorize('manageMembers', $commission);

        abort_unless((int) $membership->commission_id === (int) $commission->id, 403);

        $userName = $membership->user?->name ?? 'Member';
        $userId = $membership->user_id;

        // If this member was the commission coordinator, clear head_user_id
        if ((int) $commission->head_user_id === (int) $userId) {
            $commission->update(['head_user_id' => null]);
        }

        // Clean user's direct commission_id if it pointed here
        User::where('id', $userId)->where('commission_id', $commission->id)->update(['commission_id' => null]);

        $membership->delete();

        AuditLogger::log(
            action: 'commission_member_removed',
            description: "Removed {$userName} from {$commission->name}.",
            target: null,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "{$userName} has been removed from {$commission->name}.");
    }

    /**
     * Store a new ministry under this commission.
     */
    public function storeMinistry(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('manageMinistries', $commission);

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['required', 'string', 'max:1000'],
            'category'             => ['nullable', 'string', 'max:100'],
            'icon'                 => ['nullable', 'string', 'max:50'],
            'status'               => ['nullable', 'string', 'in:active,inactive'],
            'is_public'            => ['nullable', 'boolean'],
            'meeting_schedule'     => ['nullable', 'string', 'max:255'],
            'meeting_location'     => ['nullable', 'string', 'max:255'],
            'coordinator_name'     => ['nullable', 'string', 'max:255'],
            'coordinator_email'    => ['nullable', 'email', 'max:255'],
            'coordinator_phone'    => ['nullable', 'string', 'max:50'],
            'is_accepting_members' => ['nullable', 'boolean'],
        ]);

        $baseSlug = \Illuminate\Support\Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (\App\Models\Ministry::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $ministry = \App\Models\Ministry::create([
            'name'                 => $validated['name'],
            'slug'                 => $slug,
            'commission_id'        => $commission->id,
            'category'             => $validated['category'] ?? $commission->name,
            'icon'                 => ! empty($validated['icon']) ? $validated['icon'] : ($commission->icon ?: '✝'),
            'description'          => $validated['description'],
            'status'               => $validated['status'] ?? 'active',
            'is_public'            => $request->has('is_public') ? $request->boolean('is_public') : true,
            'meeting_schedule'     => $validated['meeting_schedule'] ?? null,
            'meeting_location'     => $validated['meeting_location'] ?? null,
            'coordinator_name'     => $validated['coordinator_name'] ?? null,
            'coordinator_email'    => $validated['coordinator_email'] ?? null,
            'coordinator_phone'    => $validated['coordinator_phone'] ?? null,
            'is_accepting_members' => $request->boolean('is_accepting_members', true),
            'created_by'           => $request->user()->id,
            'updated_by'           => $request->user()->id,
        ]);

        AuditLogger::log(
            action: 'commission_ministry_created',
            description: "Created ministry \"{$ministry->name}\" in {$commission->name}.",
            target: $ministry,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Ministry \"{$ministry->name}\" created under {$commission->name}.");
    }

    /**
     * Store a new project for this commission.
     */
    public function storeProject(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('manageProjects', $commission);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'status'       => ['required', 'in:planning,ongoing,completed,on_hold,cancelled'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget'       => ['nullable', 'numeric', 'min:0'],
            'lead_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $project = CommissionProject::create([
            'commission_id' => $commission->id,
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'status'        => $validated['status'],
            'start_date'    => $validated['start_date'] ?? null,
            'end_date'      => $validated['end_date'] ?? null,
            'budget'        => $validated['budget'] ?? null,
            'lead_user_id'  => $validated['lead_user_id'] ?? null,
            'created_by'    => $request->user()->id,
        ]);

        AuditLogger::log(
            action: 'commission_project_created',
            description: "Created project \"{$project->title}\" in {$commission->name}.",
            target: $project,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Project \"{$project->title}\" created successfully.");
    }

    /**
     * Update an existing project.
     */
    public function updateProject(Request $request, Commission $commission, CommissionProject $project): RedirectResponse
    {
        Gate::authorize('manageProjects', $commission);

        abort_unless((int) $project->commission_id === (int) $commission->id, 403);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'status'       => ['required', 'in:planning,ongoing,completed,on_hold,cancelled'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget'       => ['nullable', 'numeric', 'min:0'],
            'lead_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $project->update($validated);

        AuditLogger::log(
            action: 'commission_project_updated',
            description: "Updated project \"{$project->title}\" in {$commission->name}.",
            target: $project,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Project \"{$project->title}\" updated successfully.");
    }

    /**
     * Delete a project.
     */
    public function destroyProject(Request $request, Commission $commission, CommissionProject $project): RedirectResponse
    {
        Gate::authorize('manageProjects', $commission);

        abort_unless((int) $project->commission_id === (int) $commission->id, 403);

        $title = $project->title;
        $project->delete();

        AuditLogger::log(
            action: 'commission_project_deleted',
            description: "Deleted project \"{$title}\" from {$commission->name}.",
            target: null,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Project \"{$title}\" deleted.");
    }

    /**
     * Upload and store a commission document.
     */
    public function storeDocument(Request $request, Commission $commission): RedirectResponse
    {
        Gate::authorize('manageDocuments', $commission);

        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:Minutes,Guidelines,Financial,Report,Proposal,General'],
            'file'     => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,webp'],
        ]);

        $uploadedFile = $request->file('file');
        $origName = $uploadedFile->getClientOriginalName();
        $storedPath = $uploadedFile->store("commissions/{$commission->id}/documents", 'public');

        $doc = CommissionDocument::create([
            'commission_id' => $commission->id,
            'title'         => $validated['title'],
            'file_path'     => $storedPath,
            'file_name'     => $origName,
            'file_type'     => $uploadedFile->getClientMimeType(),
            'file_size'     => $uploadedFile->getSize(),
            'category'      => $validated['category'],
            'uploaded_by'   => $request->user()->id,
        ]);

        AuditLogger::log(
            action: 'commission_document_uploaded',
            description: "Uploaded document \"{$doc->title}\" ({$origName}) to {$commission->name}.",
            target: $doc,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Document \"{$doc->title}\" uploaded successfully.");
    }

    /**
     * Delete a document.
     */
    public function destroyDocument(Request $request, Commission $commission, CommissionDocument $document): RedirectResponse
    {
        Gate::authorize('manageDocuments', $commission);

        abort_unless((int) $document->commission_id === (int) $commission->id, 403);

        $title = $document->title;
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        AuditLogger::log(
            action: 'commission_document_deleted',
            description: "Deleted document \"{$title}\" from {$commission->name}.",
            target: null,
            commissionId: $commission->id,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()->with('success', "Document \"{$title}\" deleted.");
    }
}

