<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $donations = Donation::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('parishioner.donations', [
            'user' => $user,
            'donations' => $donations,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('parishioner.request-donation-receipt', [
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$request->has('method') && $request->has('payment_method')) {
            $request->merge(['method' => $request->input('payment_method')]);
        }
        if (!$request->has('payment_reference') && $request->has('reference_number')) {
            $request->merge(['payment_reference' => $request->input('reference_number')]);
        }

        $validated = $request->validate([
            'donor_name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'donation_date' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'proof_of_payment' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'email' => ['required', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $proofPath = $request->file('proof_of_payment')->store('donations/proofs', 'public');
        }

        $transactionId = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        Donation::create([
            'user_id' => $user->id,
            'donor_name' => $validated['donor_name'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'] ?? null,
            'purpose' => $validated['purpose'],
            'amount' => $validated['amount'],
            'donation_date' => $validated['donation_date'],
            'method' => $validated['method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_status' => 'pending',
            'status' => 'pending_verification',
            'proof_of_payment' => $proofPath,
            'notes' => $validated['notes'] ?? null,
            'transaction_id' => $transactionId,
        ]);

        return redirect()->route('parishioner.donations')->with('success', 'Your donation details have been submitted successfully. The parish will verify your donation and process your acknowledgment/receipt.');
    }
}
