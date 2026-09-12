<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonationManagementController extends Controller
{
    /**
     * Display a listing of parish donations with filtering and metrics.
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        $status = $request->string('status', 'all')->trim()->toString();
        $search = $request->string('search')->trim()->toString();

        $query = Donation::query()->with(['user', 'verifier'])->latest();

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        $donations = $query->paginate(15)->withQueryString();

        // Metrics and summary counts
        $counts = [
            'all' => Donation::query()->count(),
            'pending_verification' => Donation::query()->where('status', 'pending_verification')->count(),
            'verified' => Donation::query()->where('status', 'verified')->count(),
            'receipt_ready' => Donation::query()->where('status', 'receipt_ready')->count(),
            'rejected' => Donation::query()->where('status', 'rejected')->count(),
        ];

        $totalOfferingsSum = (float) Donation::query()
            ->whereIn('status', ['verified', 'receipt_ready'])
            ->sum('amount');

        return view('admin.donations', [
            'donations' => $donations,
            'counts' => $counts,
            'totalOfferingsSum' => $totalOfferingsSum,
            'currentStatus' => $status,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Update verification status, remarks, or receipt readiness.
     */
    public function updateStatus(Request $request, Donation $donation): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending_verification', 'verified', 'rejected', 'receipt_ready'])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $validated['status'];
        $updates = [
            'status' => $status,
            'admin_notes' => $validated['admin_notes'] ?? $donation->admin_notes,
        ];

        if ($status === 'verified') {
            $updates['payment_status'] = 'paid';
            $updates['verified_at'] = now();
            $updates['verified_by'] = $request->user()->id;
        } elseif ($status === 'receipt_ready') {
            $updates['payment_status'] = 'paid';
            $updates['receipt_issued'] = true;
            if (empty($validated['receipt_number']) && empty($donation->receipt_number)) {
                $updates['receipt_number'] = 'ACK-' . date('Y') . '-' . str_pad((string) $donation->id, 5, '0', STR_PAD_LEFT);
            } elseif (!empty($validated['receipt_number'])) {
                $updates['receipt_number'] = $validated['receipt_number'];
            }
            if (empty($donation->verified_at)) {
                $updates['verified_at'] = now();
                $updates['verified_by'] = $request->user()->id;
            }
        } elseif ($status === 'rejected') {
            $updates['payment_status'] = 'failed';
        }

        $donation->update($updates);

        $actionName = match ($status) {
            'verified' => 'verified',
            'receipt_ready' => 'marked as Acknowledgment/Receipt Ready',
            'rejected' => 'rejected',
            default => 'updated',
        };

        return redirect()->back()->with('success', "Donation record (#{$donation->id}) for {$donation->donor_name} has been {$actionName}.");
    }

    /**
     * Return historical donations for the donor in JSON format.
     */
    public function donorHistory(Request $request, Donation $donation): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $allDonorDonations = Donation::query()->latest();

        if (!empty($donation->user_id)) {
            $allDonorDonations->where('user_id', $donation->user_id);
        } elseif (!empty($donation->email)) {
            $allDonorDonations->where('email', $donation->email);
        } else {
            $allDonorDonations->where('donor_name', $donation->donor_name);
        }

        $allRecords = $allDonorDonations->get();
        $totalVerifiedAmount = (float) $allRecords->whereIn('status', ['verified', 'receipt_ready'])->sum('amount');
        $totalVerifiedDonations = $allRecords->whereIn('status', ['verified', 'receipt_ready'])->count();

        $history = $allRecords->where('id', '!=', $donation->id)->take(10)->map(fn (Donation $d) => [
            'id' => $d->id,
            'date' => $d->donation_date ? $d->donation_date->format('M d, Y') : $d->created_at->format('M d, Y'),
            'amount' => $d->formatted_amount,
            'purpose' => $d->purpose ?: 'General Fund',
            'method' => $d->method,
            'status' => $d->status_label,
            'status_class' => $d->status_badge_class,
            'receipt_number' => $d->receipt_number,
        ])->values();

        return response()->json([
            'donor_name' => $donation->donor_name,
            'total_verified_amount' => $totalVerifiedAmount,
            'total_verified_donations' => $totalVerifiedDonations,
            'total_past_donations' => $history->count(),
            'history' => $history,
            'donations' => $history,
        ]);
    }

    /**
     * Enforce strict admin role authorization.
     */
    private function authorizeAdmin($user): void
    {
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized. Super Admin access required.');
        }
    }
}
