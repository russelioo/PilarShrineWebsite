<?php

namespace App\Http\Controllers\Commission;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CommissionDocument;
use App\Models\CommissionMembership;
use App\Models\CommissionProject;
use App\Models\Ministry;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CommissionWorkspaceController extends Controller
{
    /**
     * Resolve the authenticated user's commission with strict authorization.
     */
    protected function resolveCommission(Request $request): Commission
    {
        $actor = $request->user();

        if (! $actor) {
            abort(401);
        }

        $commissionId = null;

        // If Super Admin or parish-wide user specifies a commission via query param
        if ($actor->hasParishWideAccess() && $request->filled('commission_id')) {
            $commissionId = $request->integer('commission_id');
        } elseif ($actor->commission_id) {
            $commissionId = $actor->commission_id;
        } else {
            // Check if user is a member of any commission
            $firstMembership = $actor->commissions()->first();
            if ($firstMembership) {
                $commissionId = $firstMembership->id;
            } elseif ($actor->hasParishWideAccess()) {
                $commissionId = Commission::where('is_active', true)->value('id');
            }
        }

        if (! $commissionId) {
            abort(403, 'You do not have an assigned pastoral commission.');
        }

        $commission = Commission::findOrFail($commissionId);

        Gate::authorize('view', $commission);

        return $commission;
    }

    /**
     * Helper to render the shared commission workspace view.
     */
    protected function renderWorkspace(Request $request, string $tab): View
    {
        $commission = $this->resolveCommission($request);
        $actor = $request->user();

        // Eager load relations
        $commission->loadMissing([
            'coordinator',
            'members' => fn ($q) => $q->orderBy('name'),
            'officers' => fn ($q) => $q->orderBy('name'),
            'ministries' => fn ($q) => $q->orderBy('name'),
            'projects' => fn ($q) => $q->latest(),
            'documents' => fn ($q) => $q->latest(),
        ]);

        $auditLogs = AuditLog::query()
            ->where('commission_id', $commission->id)
            ->latest()
            ->take(25)
            ->get();

        $existingMemberIds = $commission->members()->pluck('users.id')->toArray();
        $availableUsers = User::query()
            ->whereNotIn('id', $existingMemberIds)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->select(['id', 'name', 'email', 'avatar', 'role', 'position'])
            ->get();

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
            'isWorkspace'         => true,
        ]);
    }

    public function dashboard(Request $request): View
    {
        return $this->renderWorkspace($request, 'overview');
    }

    public function overview(Request $request): View
    {
        return $this->renderWorkspace($request, 'overview');
    }

    public function members(Request $request): View
    {
        return $this->renderWorkspace($request, 'members');
    }

    public function officers(Request $request): View
    {
        return $this->renderWorkspace($request, 'officers');
    }

    public function projects(Request $request): View
    {
        return $this->renderWorkspace($request, 'projects');
    }

    public function documents(Request $request): View
    {
        return $this->renderWorkspace($request, 'documents');
    }

    public function ministries(Request $request): View
    {
        return $this->renderWorkspace($request, 'ministries');
    }

    /**
     * Add a member to the user's commission.
     */
    public function storeMember(Request $request): RedirectResponse
    {
        $commission = $this->resolveCommission($request);
        Gate::authorize('manageMembers', $commission);

        $validated = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'position'   => ['required', 'string', 'max:100'],
            'is_officer' => ['nullable', 'boolean'],
            'role'       => ['nullable', 'string', 'in:head,officer,member'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (! $user->commission_id) {
            $user->update(['commission_id' => $commission->id]);
        }

        $isCoordinator = str_contains(strtolower($validated['position']), 'coordinator') || ($validated['role'] ?? '') === 'head' || ($validated['role'] ?? '') === 'coordinator';
        $isOfficer = ! empty($validated['is_officer']) || ($validated['role'] ?? '') === 'officer' || $isCoordinator;

        $membership = CommissionMembership::updateOrCreate(
            [
                'commission_id' => $commission->id,
                'user_id'       => $user->id,
            ],
            [
                'position'   => $validated['position'],
                'is_officer' => $isOfficer,
                'role'       => $isCoordinator ? 'head' : ($isOfficer ? 'officer' : 'member'),
                'status'     => 'active',
                'notes'      => $validated['notes'] ?? null,
                'joined_at'  => now(),
            ]
        );

        if ($isCoordinator) {
            $commission->update(['head_user_id' => $user->id]);
        }

        AuditLogger::log(
            action: 'commission.member_added',
            description: "Added {$user->name} as {$membership->position} to {$commission->name}.",
            target: $membership,
            commissionId: $commission->id,
            newValues: $membership->toArray(),
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.members')
            ->with('status', "{$user->name} successfully added to {$commission->name}.");
    }

    /**
     * Update an existing commission membership.
     */
    public function updateMember(Request $request, CommissionMembership $membership): RedirectResponse
    {
        $commission = $this->resolveCommission($request);

        if ($membership->commission_id !== $commission->id) {
            abort(403, 'Unauthorized access to this member record.');
        }

        Gate::authorize('manageOfficers', $commission);

        $validated = $request->validate([
            'position'   => ['required', 'string', 'max:100'],
            'is_officer' => ['nullable', 'boolean'],
            'status'     => ['required', 'in:active,inactive'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $isCoordinator = str_contains(strtolower($validated['position']), 'coordinator');
        $isOfficer = ! empty($validated['is_officer']) || $isCoordinator;

        $oldValues = $membership->toArray();
        $membership->update([
            'position'   => $validated['position'],
            'is_officer' => $isOfficer,
            'role'       => $isCoordinator ? 'head' : ($isOfficer ? 'officer' : 'member'),
            'status'     => $validated['status'],
            'notes'      => $validated['notes'] ?? null,
        ]);

        if ($isCoordinator) {
            $commission->update(['head_user_id' => $membership->user_id]);
        }

        AuditLogger::log(
            action: 'commission.member_updated',
            description: "Updated membership for {$membership->user->name} in {$commission->name}.",
            target: $membership,
            commissionId: $commission->id,
            oldValues: $oldValues,
            newValues: $membership->toArray(),
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->back()
            ->with('status', "Member record for {$membership->user->name} updated successfully.");
    }

    /**
     * Remove a member from the commission.
     */
    public function removeMember(Request $request, CommissionMembership $membership): RedirectResponse
    {
        $commission = $this->resolveCommission($request);

        if ($membership->commission_id !== $commission->id) {
            abort(403, 'Unauthorized access to this member record.');
        }

        Gate::authorize('manageMembers', $commission);

        $userName = $membership->user->name ?? 'User';
        $oldValues = $membership->toArray();

        // If this user was set as head_user_id on the commission, clear it
        if ($commission->head_user_id === $membership->user_id) {
            $commission->update(['head_user_id' => null]);
        }

        $membership->delete();

        AuditLogger::log(
            action: 'commission.member_removed',
            description: "Removed {$userName} from {$commission->name}.",
            target: null,
            commissionId: $commission->id,
            oldValues: $oldValues,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.members')
            ->with('status', "{$userName} has been removed from {$commission->name}.");
    }

    /**
     * Create a new ministry under the coordinator's commission.
     */
    public function storeMinistry(Request $request): RedirectResponse
    {
        $commission = $this->resolveCommission($request);
        Gate::authorize('manageMinistries', $commission);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'is_public' => ['nullable', 'boolean'],
            'meeting_schedule' => ['nullable', 'string', 'max:255'],
            'meeting_location' => ['nullable', 'string', 'max:255'],
            'coordinator_name' => ['nullable', 'string', 'max:255'],
            'coordinator_email' => ['nullable', 'email', 'max:255'],
            'coordinator_phone' => ['nullable', 'string', 'max:50'],
            'is_accepting_members' => ['nullable', 'boolean'],
        ]);

        $baseSlug = \Illuminate\Support\Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Ministry::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $ministry = Ministry::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'commission_id' => $commission->id,
            'category' => $validated['category'] ?? $commission->name,
            'icon' => !empty($validated['icon']) ? $validated['icon'] : ($commission->icon ?: '✝'),
            'description' => $validated['description'],
            'status' => $validated['status'] ?? 'active',
            'is_public' => $request->has('is_public') ? $request->boolean('is_public') : true,
            'meeting_schedule' => $validated['meeting_schedule'] ?? null,
            'meeting_location' => $validated['meeting_location'] ?? null,
            'coordinator_name' => $validated['coordinator_name'] ?? null,
            'coordinator_email' => $validated['coordinator_email'] ?? null,
            'coordinator_phone' => $validated['coordinator_phone'] ?? null,
            'is_accepting_members' => $request->boolean('is_accepting_members', true),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        AuditLogger::log(
            action: 'commission.ministry_created',
            description: "Created ministry '{$ministry->name}' in {$commission->name}.",
            target: $ministry,
            commissionId: $commission->id,
            newValues: $ministry->toArray(),
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.ministries')
            ->with('status', "Ministry '{$ministry->name}' created successfully under {$commission->name}.");
    }

    /**
     * Create a new project for this commission.
     */
    public function storeProject(Request $request): RedirectResponse
    {
        $commission = $this->resolveCommission($request);
        Gate::authorize('manageProjects', $commission);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:3000'],
            'lead_user_id' => ['nullable', 'exists:users,id'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget'       => ['nullable', 'numeric', 'min:0'],
            'status'       => ['required', 'in:planning,ongoing,completed,on_hold,cancelled'],
        ]);

        $project = CommissionProject::create([
            'commission_id' => $commission->id,
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'lead_user_id'  => $validated['lead_user_id'] ?? null,
            'created_by'    => $request->user()->id,
            'start_date'    => $validated['start_date'] ?? null,
            'end_date'      => $validated['end_date'] ?? null,
            'budget'        => $validated['budget'] ?? null,
            'status'        => $validated['status'],
        ]);

        AuditLogger::log(
            action: 'commission.project_created',
            description: "Created project '{$project->title}' in {$commission->name}.",
            target: $project,
            commissionId: $commission->id,
            newValues: $project->toArray(),
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.projects')
            ->with('status', "Project '{$project->title}' created successfully.");
    }

    /**
     * Update a project.
     */
    public function updateProject(Request $request, CommissionProject $project): RedirectResponse
    {
        $commission = $this->resolveCommission($request);

        if ($project->commission_id !== $commission->id) {
            abort(403, 'Unauthorized access to this project.');
        }

        Gate::authorize('manageProjects', $commission);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:3000'],
            'lead_user_id' => ['nullable', 'exists:users,id'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget'       => ['nullable', 'numeric', 'min:0'],
            'status'       => ['required', 'in:planning,ongoing,completed,on_hold,cancelled'],
        ]);

        $oldValues = $project->toArray();
        $project->update($validated);

        AuditLogger::log(
            action: 'commission.project_updated',
            description: "Updated project '{$project->title}' in {$commission->name}.",
            target: $project,
            commissionId: $commission->id,
            oldValues: $oldValues,
            newValues: $project->toArray(),
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.projects')
            ->with('status', "Project '{$project->title}' updated successfully.");
    }

    /**
     * Delete a project.
     */
    public function destroyProject(Request $request, CommissionProject $project): RedirectResponse
    {
        $commission = $this->resolveCommission($request);

        if ($project->commission_id !== $commission->id) {
            abort(403, 'Unauthorized access to this project.');
        }

        Gate::authorize('manageProjects', $commission);

        $title = $project->title;
        $oldValues = $project->toArray();
        $project->delete();

        AuditLogger::log(
            action: 'commission.project_deleted',
            description: "Deleted project '{$title}' from {$commission->name}.",
            target: null,
            commissionId: $commission->id,
            oldValues: $oldValues,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.projects')
            ->with('status', "Project '{$title}' deleted successfully.");
    }

    /**
     * Upload a document for this commission.
     */
    public function storeDocument(Request $request): RedirectResponse
    {
        $commission = $this->resolveCommission($request);
        Gate::authorize('manageDocuments', $commission);

        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'file'     => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'max:20480'], // max 20MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();

        $path = $file->store("commission_documents/{$commission->slug}", 'public');

        $document = CommissionDocument::create([
            'commission_id' => $commission->id,
            'title'         => $validated['title'],
            'file_name'     => $originalName,
            'file_path'     => $path,
            'file_type'     => $extension,
            'file_size'     => $size,
            'category'      => ucfirst(strtolower($validated['category'])),
            'uploaded_by'   => $request->user()->id,
        ]);

        AuditLogger::log(
            action: 'commission.document_uploaded',
            description: "Uploaded document '{$document->title}' ({$originalName}) to {$commission->name}.",
            target: $document,
            commissionId: $commission->id,
            newValues: $document->toArray(),
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.documents')
            ->with('status', "Document '{$document->title}' uploaded successfully.");
    }

    /**
     * Delete a document.
     */
    public function destroyDocument(Request $request, CommissionDocument $document): RedirectResponse
    {
        $commission = $this->resolveCommission($request);

        if ($document->commission_id !== $commission->id) {
            abort(403, 'Unauthorized access to this document.');
        }

        Gate::authorize('manageDocuments', $commission);

        $title = $document->title;
        $oldValues = $document->toArray();

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        AuditLogger::log(
            action: 'commission.document_deleted',
            description: "Deleted document '{$title}' from {$commission->name}.",
            target: null,
            commissionId: $commission->id,
            oldValues: $oldValues,
            actor: $request->user(),
            category: 'commission'
        );

        return redirect()->route('commission.documents')
            ->with('status', "Document '{$title}' deleted successfully.");
    }
}
