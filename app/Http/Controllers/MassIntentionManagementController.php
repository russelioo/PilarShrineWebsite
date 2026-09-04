<?php

namespace App\Http\Controllers;

use App\Models\MassIntention;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MassIntentionManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $query = MassIntention::query()->with(['user', 'massSchedule']);
        $query->when($request->filled('search'), function (Builder $query) use ($request): void {
            $search = $request->string('search')->trim()->toString();
            $query->where(function (Builder $query) use ($search): void {
                $query->where('requested_by', 'like', "%{$search}%")
                    ->orWhere('names', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', "%{$search}%"));
            });
        });
        $query->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')));
        $query->when($request->filled('type'), fn (Builder $query) => $query->where('intention_type', $request->input('type')));
        $intentions = $query->latest('requested_date')->latest('id')->paginate(10)->withQueryString();
        $counts = MassIntention::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.mass-intentions', compact('intentions', 'counts'));
    }

    public function updateStatus(Request $request, MassIntention $massIntention): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $validated = $request->validate(['status' => ['required', Rule::in(['pending', 'offered', 'completed'])]]);
        $massIntention->update([
            'status' => $validated['status'],
            'offered_date' => $validated['status'] === 'pending' ? null : ($massIntention->offered_date ?? now()->toDateString()),
        ]);

        return back()->with('success', 'Mass intention status updated.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
