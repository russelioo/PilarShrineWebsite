<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $request->user();
        abort_unless($actor && ($actor->hasParishWideAccess() || $actor->isCommissionAdmin()), 403, 'Unauthorized access to audit logs.');

        $query = AuditLog::query()->with(['user', 'commission']);

        // Commission scoping
        if ($actor->hasParishWideAccess()) {
            if ($request->filled('commission_id') && $request->input('commission_id') !== 'all') {
                $query->where('commission_id', $request->input('commission_id'));
            }
        } else {
            // Strictly scope to actor's assigned commission
            if (! $actor->commission_id) {
                $query->whereRaw('1 = 0');
            } else {
                if ($request->filled('commission_id') && (int) $request->input('commission_id') !== (int) $actor->commission_id) {
                    abort(403, 'You are not authorized to view audit logs from other commissions.');
                }
                $query->where('commission_id', $actor->commission_id);
            }
        }

        // Search keyword
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function (Builder $q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('target_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // Action filter
        if ($request->filled('action') && $request->input('action') !== 'all') {
            $query->where('action', $request->input('action'));
        }

        // User actor filter
        if ($request->filled('actor_id') && $request->input('actor_id') !== 'all') {
            $query->where('user_id', $request->input('actor_id'));
        }

        // Date range filter
        if ($request->filled('date_range')) {
            match ($request->input('date_range')) {
                'today' => $query->whereDate('created_at', today()),
                'yesterday' => $query->whereDate('created_at', today()->subDay()),
                '7days' => $query->where('created_at', '>=', now()->subDays(7)),
                '30days' => $query->where('created_at', '>=', now()->subDays(30)),
                'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default => null,
            };
        }

        $logs = $query->latest('created_at')->paginate(15)->withQueryString();

        // Pass filter dropdown datasets based on actor scope
        $commissions = $actor->hasParishWideAccess()
            ? Commission::where('is_active', true)->orderByRaw('LOWER(name) ASC')->get()
            : Commission::where('id', $actor->commission_id)->get();

        $actions = [
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_deactivated' => 'User Deactivated',
            'user_activated' => 'User Activated',
            'user_deleted' => 'User Deleted',
            'role_changed' => 'Role Changed',
            'commission_assigned' => 'Commission Assigned',
            'commission_changed' => 'Commission Changed',
            'password_reset' => 'Password Reset',
            'login' => 'User Login',
            'logout' => 'User Logout',
            'failed_login' => 'Failed Login',
            'commission_created' => 'Commission Created',
        ];

        $actors = $actor->hasParishWideAccess()
            ? User::whereIn('role', ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'staff', 'commission_member'])->orderBy('name')->get(['id', 'name', 'email', 'role'])
            : User::where('commission_id', $actor->commission_id)->orderBy('name')->get(['id', 'name', 'email', 'role']);

        return view('admin.audit-logs', compact('logs', 'commissions', 'actions', 'actors', 'actor'));
    }
}

