<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\PpcMember;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PpcManagementController extends Controller
{
    /**
     * Display the Parish Pastoral Council (PPC) board and members.
     */
    public function index(Request $request): View
    {
        $actor = $request->user();

        if (! $actor || (! $actor->hasParishWideAccess() && ! $actor->isSuperAdmin())) {
            abort(403, 'Unauthorized access to Parish Pastoral Council.');
        }

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status', 'active')->toString();

        $query = PpcMember::query()
            ->with(['user', 'commission'])
            ->orderByRaw("
                CASE 
                    WHEN role_title = 'Parish Priest' THEN 1
                    WHEN role_title = 'Parochial Vicar' THEN 2
                    WHEN role_title = 'Lay Co-Chair' THEN 3
                    WHEN role_title = 'PPC Secretary' THEN 4
                    WHEN role_title = 'PPC Assistant Secretary' THEN 5
                    WHEN role_title = 'PPC Treasurer' THEN 6
                    WHEN role_title = 'PPC Auditor' THEN 7
                    WHEN commission_id IS NOT NULL THEN 8
                    ELSE 9
                END ASC
            ")
            ->orderBy('id');

        if ($status === 'active') {
            $query->where('status', 'active');
        } elseif ($status === 'inactive') {
            $query->where('status', 'inactive');
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('role_title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('commission', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $ppcMembers = $query->get();

        // Categorized groups
        $executiveOfficers = $ppcMembers->filter(function ($m) {
            return in_array($m->role_title, [
                'Parish Priest',
                'Parochial Vicar',
                'Lay Co-Chair',
                'PPC Secretary',
                'PPC Assistant Secretary',
                'PPC Treasurer',
                'PPC Auditor',
            ], true) && empty($m->commission_id);
        });

        $commissionChairs = $ppcMembers->filter(function ($m) {
            return ! empty($m->commission_id);
        });

        $otherMembers = $ppcMembers->reject(function ($m) use ($executiveOfficers, $commissionChairs) {
            return $executiveOfficers->contains('id', $m->id) || $commissionChairs->contains('id', $m->id);
        });

        // Real dynamic KPIs
        $kpi = [
            'total_active'       => PpcMember::where('status', 'active')->count(),
            'executive_officers' => PpcMember::where('status', 'active')
                ->whereIn('role_title', [
                    'Parish Priest', 'Parochial Vicar', 'Lay Co-Chair', 'PPC Secretary', 'PPC Treasurer', 'PPC Auditor',
                ])->count(),
            'commissions_count'  => Commission::where('is_active', true)->count(),
            'represented_commissions' => PpcMember::where('status', 'active')
                ->whereNotNull('commission_id')
                ->distinct('commission_id')
                ->count('commission_id'),
        ];

        // All Commissions for dropdown
        $commissions = Commission::where('is_active', true)->orderBy('name')->get();

        // Available Users for adding
        $allUsers = User::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->select(['id', 'name', 'email', 'avatar', 'role', 'position'])
            ->get();

        return view('admin.ppc.index', [
            'ppcMembers'        => $ppcMembers,
            'executiveOfficers' => $executiveOfficers,
            'commissionChairs'  => $commissionChairs,
            'otherMembers'      => $otherMembers,
            'kpi'               => $kpi,
            'commissions'       => $commissions,
            'allUsers'          => $allUsers,
            'actor'             => $actor,
        ]);
    }

    /**
     * Store a new PPC member.
     */
    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor || (! $actor->hasParishWideAccess() && ! $actor->isSuperAdmin())) {
            abort(403, 'Unauthorized to add PPC members.');
        }

        $validated = $request->validate([
            'user_id'       => ['required', 'exists:users,id'],
            'role_title'    => ['required', 'string', 'max:100'],
            'commission_id' => ['nullable', 'exists:commissions,id'],
            'term_start'    => ['nullable', 'date'],
            'term_end'      => ['nullable', 'date', 'after_or_equal:term_start'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $member = PpcMember::create([
            'user_id'       => $validated['user_id'],
            'role_title'    => $validated['role_title'],
            'commission_id' => $validated['commission_id'] ?? null,
            'term_start'    => $validated['term_start'] ?? null,
            'term_end'      => $validated['term_end'] ?? null,
            'status'        => 'active',
            'notes'         => $validated['notes'] ?? null,
        ]);

        $user = User::find($validated['user_id']);

        AuditLogger::log(
            action: 'ppc.member_added',
            description: "Appointed {$user->name} to PPC as '{$member->role_title}'.",
            target: $member,
            commissionId: $member->commission_id,
            newValues: $member->toArray(),
            actor: $actor,
            category: 'ppc'
        );

        return redirect()->route('admin.ppc.index')
            ->with('status', "{$user->name} has been appointed to the Parish Pastoral Council as {$member->role_title}.");
    }

    /**
     * Update an existing PPC member.
     */
    public function update(Request $request, PpcMember $ppcMember): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor || (! $actor->hasParishWideAccess() && ! $actor->isSuperAdmin())) {
            abort(403, 'Unauthorized to update PPC members.');
        }

        $validated = $request->validate([
            'role_title'    => ['required', 'string', 'max:100'],
            'commission_id' => ['nullable', 'exists:commissions,id'],
            'term_start'    => ['nullable', 'date'],
            'term_end'      => ['nullable', 'date', 'after_or_equal:term_start'],
            'status'        => ['required', 'in:active,inactive'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $oldValues = $ppcMember->toArray();
        $ppcMember->update([
            'role_title'    => $validated['role_title'],
            'commission_id' => $validated['commission_id'] ?? null,
            'term_start'    => $validated['term_start'] ?? null,
            'term_end'      => $validated['term_end'] ?? null,
            'status'        => $validated['status'],
            'notes'         => $validated['notes'] ?? null,
        ]);

        AuditLogger::log(
            action: 'ppc.member_updated',
            description: "Updated PPC member record for {$ppcMember->user->name}.",
            target: $ppcMember,
            commissionId: $ppcMember->commission_id,
            oldValues: $oldValues,
            newValues: $ppcMember->toArray(),
            actor: $actor,
            category: 'ppc'
        );

        return redirect()->route('admin.ppc.index')
            ->with('status', "PPC member record updated successfully.");
    }

    /**
     * Remove a member from the PPC.
     */
    public function destroy(Request $request, PpcMember $ppcMember): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor || (! $actor->hasParishWideAccess() && ! $actor->isSuperAdmin())) {
            abort(403, 'Unauthorized to remove PPC members.');
        }

        $userName = $ppcMember->user->name ?? 'Member';
        $roleTitle = $ppcMember->role_title;
        $commissionId = $ppcMember->commission_id;
        $oldValues = $ppcMember->toArray();

        $ppcMember->delete();

        AuditLogger::log(
            action: 'ppc.member_removed',
            description: "Removed {$userName} ({$roleTitle}) from the Parish Pastoral Council.",
            target: null,
            commissionId: $commissionId,
            oldValues: $oldValues,
            actor: $actor,
            category: 'ppc'
        );

        return redirect()->route('admin.ppc.index')
            ->with('status', "{$userName} has been removed from the Parish Pastoral Council.");
    }
}
