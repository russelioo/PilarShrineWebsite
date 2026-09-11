<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\MinistryMembership;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinistryManagementController extends Controller
{
    public function requests(Request $request): View
    {
        $user = $request->user();
        $this->authorizeAdminOrCoordinator($user);

        $status = $request->string('status', 'all')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        $ministryId = $request->input('ministry_id');

        $query = MinistryMembership::query()->with(['user', 'ministry', 'reviewer']);

        // Scoped permissions: staff coordinators can ONLY view requests for their assigned ministries
        if ($user->role !== 'admin') {
            $coordinatedMinistryIds = Ministry::where('coordinator_user_id', $user->id)->pluck('id');
            $query->whereIn('ministry_id', $coordinatedMinistryIds);
        } elseif (!empty($ministryId)) {
            $query->where('ministry_id', $ministryId);
        }

        if (!empty($search)) {
            $query->whereHas('user', function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        // Count queries with same scoping
        $countsQuery = MinistryMembership::query();
        if ($user->role !== 'admin') {
            $coordinatedMinistryIds = Ministry::where('coordinator_user_id', $user->id)->pluck('id');
            $countsQuery->whereIn('ministry_id', $coordinatedMinistryIds);
        }
        $pendingCount = (clone $countsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $countsQuery)->where('status', 'approved')->count();
        $rejectedCount = (clone $countsQuery)->where('status', 'rejected')->count();
        $totalCount = $countsQuery->count();

        // Available ministries for filter dropdown
        $ministriesList = $user->role === 'admin'
            ? Ministry::orderBy('name')->get()
            : Ministry::where('coordinator_user_id', $user->id)->orderBy('name')->get();

        return view('admin.ministry-requests', compact(
            'user',
            'requests',
            'status',
            'search',
            'ministryId',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount',
            'ministriesList'
        ));
    }

    public function approve(Request $request, MinistryMembership $membership): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeMinistryReview($user, $membership);

        $membership->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'joined_at' => $membership->joined_at ?: now(),
            'reviewer_notes' => $request->input('notes'),
        ]);

        // Notify parishioner
        Notification::create([
            'user_id' => $membership->user_id,
            'type' => 'email',
            'subject' => "Ministry Membership Approved: {$membership->ministry->name}",
            'message' => "Congratulations! Your request to join the {$membership->ministry->name} has been officially approved. Welcome to the ministry!",
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        return back()->with('success', "Membership request for {$membership->user->displayName} approved.");
    }

    public function reject(Request $request, MinistryMembership $membership): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeMinistryReview($user, $membership);

        $reason = $request->input('reason') ?: $request->input('review_notes') ?: $request->input('notes');
        $reason = is_string($reason) ? trim($reason) : null;

        $membership->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'reviewer_notes' => $reason ?: null,
        ]);

        // Notify parishioner
        Notification::create([
            'user_id' => $membership->user_id,
            'type' => 'email',
            'subject' => "Ministry Membership Request Update: {$membership->ministry->name}",
            'message' => "Your request to join the {$membership->ministry->name} was not approved." . ($reason ? " Reason: {$reason}" : ''),
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        return back()->with('success', "Membership request rejected.");
    }

    private function authorizeAdminOrCoordinator($user): void
    {
        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'staff') {
            // Check if they coordinate any ministry
            $hasAssignedMinistry = Ministry::where('coordinator_user_id', $user->id)->exists();
            if ($hasAssignedMinistry) {
                return;
            }
        }

        abort(403, 'Unauthorized. Only administrators and assigned ministry coordinators can manage membership requests.');
    }

    private function authorizeMinistryReview($user, MinistryMembership $membership): void
    {
        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'staff' && $membership->ministry->coordinator_user_id === $user->id) {
            return;
        }

        abort(403, 'Unauthorized. You can only manage membership requests for your assigned ministry.');
    }
}

