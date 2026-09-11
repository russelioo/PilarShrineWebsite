<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Ministry;
use App\Models\MinistryMembership;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinistryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $search = $request->filled('search')
            ? $request->string('search')->trim()->toString()
            : $request->string('q')->trim()->toString();
        $filter = $request->filled('filter')
            ? $request->string('filter')->trim()->toString()
            : $request->string('tab', 'all')->trim()->toString();

        // Load all user memberships keyed by ministry_id
        $userMemberships = $user->ministryMemberships()
            ->with('ministry')
            ->get()
            ->keyBy('ministry_id');

        // Summary counts
        $availableCount = Ministry::where('is_accepting_members', true)->count();
        $myMinistriesCount = $userMemberships->where('status', 'approved')->count();
        $pendingRequestsCount = $userMemberships->where('status', 'pending')->count();

        $query = Ministry::query()->orderBy('name');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('coordinator_name', 'like', "%{$search}%");
            });
        }

        if ($filter === 'open') {
            $query->where('is_accepting_members', true);
        } elseif ($filter === 'my-ministries') {
            $approvedMinistryIds = $userMemberships->where('status', 'approved')->keys();
            $query->whereIn('id', $approvedMinistryIds);
        } elseif ($filter === 'pending') {
            $pendingMinistryIds = $userMemberships->where('status', 'pending')->keys();
            $query->whereIn('id', $pendingMinistryIds);
        }

        $ministries = $query->get();

        // Also prepare list of user's active & pending ministries for "My Ministries" section
        $myMembershipsList = $user->ministryMemberships()
            ->with('ministry')
            ->latest('updated_at')
            ->get();

        return view('parishioner.ministries', compact(
            'user',
            'ministries',
            'userMemberships',
            'availableCount',
            'myMinistriesCount',
            'pendingRequestsCount',
            'search',
            'filter',
            'myMembershipsList'
        ));
    }

    public function join(Request $request, Ministry $ministry): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'application_message' => ['required', 'string', 'min:5', 'max:1000'],
            'experience' => ['nullable', 'string', 'max:1000'],
            'additional_info' => ['nullable', 'string', 'max:1000'],
            'agreed_terms' => ['required', 'accepted'],
        ], [
            'application_message.required' => 'Please share why you would like to join this ministry.',
            'application_message.min' => 'Please provide a few sentences explaining your motivation (at least 5 characters).',
            'agreed_terms.required' => 'You must acknowledge that membership is subject to review and approval.',
            'agreed_terms.accepted' => 'You must acknowledge that membership is subject to review and approval.',
        ]);

        $existing = MinistryMembership::where('user_id', $user->id)
            ->where('ministry_id', $ministry->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'pending') {
                return back()->with('error', 'Your membership request is currently under review.');
            }

            if ($existing->status === 'approved') {
                return back()->with('info', 'You are already an official member of this ministry.');
            }

            // If rejected or inactive, allow re-application
            $existing->update([
                'status' => 'pending',
                'application_message' => $validated['application_message'],
                'experience' => $validated['experience'] ?? null,
                'additional_info' => $validated['additional_info'] ?? null,
                'agreed_terms' => true,
                'reviewed_by' => null,
                'reviewer_notes' => null,
                'reviewed_at' => null,
                'joined_at' => null,
            ]);
        } else {
            MinistryMembership::create([
                'user_id' => $user->id,
                'ministry_id' => $ministry->id,
                'status' => 'pending',
                'application_message' => $validated['application_message'],
                'experience' => $validated['experience'] ?? null,
                'additional_info' => $validated['additional_info'] ?? null,
                'agreed_terms' => true,
            ]);
        }

        // Send receipt notification to the applicant
        Notification::create([
            'user_id' => $user->id,
            'type' => 'email',
            'subject' => "Membership Request Submitted: {$ministry->name}",
            'message' => "Your application to join {$ministry->name} has been received and is currently under review by the ministry coordinator.",
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        // Send notification to coordinator if coordinator_user_id is present, or to admins
        $recipientUserIds = [];
        if ($ministry->coordinator_user_id) {
            $recipientUserIds[] = $ministry->coordinator_user_id;
        }

        $adminIds = User::where('role', 'admin')->pluck('id')->toArray();
        $recipientUserIds = array_unique(array_merge($recipientUserIds, $adminIds));

        foreach ($recipientUserIds as $adminId) {
            Notification::create([
                'user_id' => $adminId,
                'type' => 'email',
                'subject' => "New Ministry Membership Request: {$ministry->name}",
                'message' => "{$user->displayName} has submitted a request to join the {$ministry->name}.",
                'sent_at' => now(),
                'status' => 'sent',
            ]);
        }

        return redirect()->route('parishioner.ministries', ['filter' => 'pending'])
            ->with('success', "Your request to join {$ministry->name} has been submitted for review. Current status: Pending Review.");
    }
}

