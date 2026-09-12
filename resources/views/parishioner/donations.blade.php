@extends('layouts.parishioner')
@section('title', 'My Donations & Receipts')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Parish Stewardship</p>
        <h2>My Donations &amp; Receipts</h2>
        <p>Review your submitted offerings, verification statuses, and official parish acknowledgment receipts.</p>
    </div>
    <div class="heading-actions">
        <a class="btn btn-outline" href="/#/donations" target="_blank" title="View official parish donation channels">
            <span>Donation Channels</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
        </a>
        <a class="btn btn-primary" href="{{ route('parishioner.donations.request') }}">
            <span>+ Request Acknowledgment / Receipt</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" role="alert">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <div>
            <strong>Submitted Successfully</strong>
            <p>{{ session('success') }}</p>
        </div>
    </div>
@endif

<div class="donations-table-card">
    <div class="table-responsive">
        <table class="donations-table">
            <thead>
                <tr>
                    <th>REFERENCE / DATE</th>
                    <th>PURPOSE / FUND</th>
                    <th>AMOUNT</th>
                    <th>METHOD</th>
                    <th>STATUS</th>
                    <th>ACKNOWLEDGMENT / RECEIPT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($donations as $donation)
                    <tr>
                        <td>
                            <strong class="ref-code">{{ $donation->payment_reference ?: ($donation->transaction_id ?: 'DON-' . $donation->id) }}</strong>
                            <small class="ref-date">{{ $donation->donation_date ? $donation->donation_date->format('M d, Y') : $donation->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <span class="purpose-text">{{ $donation->purpose ?: 'General Parish Support' }}</span>
                            @if($donation->donor_name)
                                <small class="donor-sub">Donor: {{ $donation->donor_name }}</small>
                            @endif
                        </td>
                        <td>
                            <strong class="amount-val">{{ $donation->formatted_amount }}</strong>
                        </td>
                        <td>
                            <span class="method-badge">{{ $donation->method }}</span>
                        </td>
                        <td>
                            <span class="status-badge {{ $donation->status_badge_class }}">
                                @if($donation->status === 'pending_verification')
                                    ⏳ Pending Verification
                                @elseif($donation->status === 'verified')
                                    ✓ Verified
                                @elseif($donation->status === 'receipt_ready')
                                    ★ Receipt Ready
                                @elseif($donation->status === 'rejected')
                                    ✕ Rejected
                                @else
                                    {{ $donation->status_label }}
                                @endif
                            </span>
                            @if($donation->status === 'rejected' && $donation->admin_notes)
                                <small class="rejection-hint" title="{{ $donation->admin_notes }}">Note: {{ \Illuminate\Support\Str::limit($donation->admin_notes, 36) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($donation->status === 'receipt_ready')
                                <div class="receipt-box">
                                    <span class="receipt-num">No: {{ $donation->receipt_number ?: 'ACK-' . date('Y') . '-' . str_pad($donation->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <small class="receipt-verified">Verified on {{ $donation->verified_at ? $donation->verified_at->format('M d, Y') : 'Official' }}</small>
                                </div>
                            @elseif($donation->status === 'verified')
                                <span class="receipt-pending-note">Verified &bull; Preparing Receipt</span>
                            @else
                                <span class="receipt-pending-note">Awaiting Verification</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state-cell">
                            <div class="empty-donations-box">
                                <div class="empty-icon" aria-hidden="true">🕊</div>
                                <h3>No donation records found</h3>
                                <p>If you have recently made an offering via GCash or InstaPay, you can submit your transaction details to request an official acknowledgment or receipt.</p>
                                <a href="{{ route('parishioner.donations.request') }}" class="btn btn-primary btn-sm">
                                    + Submit Donation for Acknowledgment
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($donations->hasPages())
        <div class="table-pagination">
            {{ $donations->links() }}
        </div>
    @endif
</div>

<style>
    .page-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .eyebrow {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gold, #d6aa3e);
        margin: 0 0 4px;
    }

    .page-heading h2 {
        font-family: Georgia, serif;
        font-size: 24px;
        color: var(--navy, #062f78);
        margin: 0 0 6px;
    }

    .page-heading p {
        margin: 0;
        font-size: 13px;
        color: var(--muted, #718096);
    }

    .heading-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-outline {
        background: #ffffff;
        color: var(--navy, #062f78);
        border: 1.5px solid #cbd5e1;
    }

    .btn-outline:hover {
        border-color: var(--navy, #062f78);
        background: #f8fafc;
    }

    .btn-primary {
        background: var(--navy, #062f78);
        color: #ffffff;
        border: 1px solid var(--navy, #062f78);
        box-shadow: 0 4px 12px rgba(6, 47, 120, 0.2);
    }

    .btn-primary:hover {
        background: #0b45a6;
    }

    .btn-sm {
        padding: 7px 14px;
        font-size: 12px;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 22px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 13px;
    }

    .alert-success svg {
        color: #16a34a;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-success strong {
        display: block;
        margin-bottom: 2px;
    }

    .alert-success p {
        margin: 0;
    }

    .donations-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .donations-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .donations-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 12px 18px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .donations-table td {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .donations-table tbody tr:hover {
        background: #fafcff;
    }

    .ref-code {
        display: block;
        font-size: 13px;
        color: var(--navy, #062f78);
    }

    .ref-date {
        font-size: 11px;
        color: #64748b;
    }

    .purpose-text {
        display: block;
        font-weight: 600;
        color: #1e293b;
    }

    .donor-sub {
        font-size: 11px;
        color: #64748b;
    }

    .amount-val {
        font-size: 14px;
        color: var(--navy, #062f78);
        font-weight: 700;
    }

    .method-badge {
        font-size: 11px;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
        padding: 3px 8px;
        border-radius: 4px;
    }

    .status-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 9px;
        border-radius: 20px;
    }

    .badge-status-pending {
        background: #fef3c7;
        color: #b45309;
    }

    .badge-status-verified {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-status-ready {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-status-rejected {
        background: #fee2e2;
        color: #b91c1c;
    }

    .rejection-hint {
        display: block;
        font-size: 10px;
        color: #dc2626;
        margin-top: 2px;
    }

    .receipt-box {
        display: flex;
        flex-direction: column;
    }

    .receipt-num {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        color: #15803d;
        font-size: 12px;
    }

    .receipt-verified {
        font-size: 10.5px;
        color: #64748b;
    }

    .receipt-pending-note {
        font-size: 11.5px;
        color: #94a3b8;
        font-style: italic;
    }

    .empty-state-cell {
        padding: 48px 24px;
        text-align: center;
    }

    .empty-donations-box {
        max-width: 440px;
        margin: 0 auto;
    }

    .empty-icon {
        font-size: 34px;
        margin-bottom: 8px;
    }

    .empty-donations-box h3 {
        margin: 0 0 6px;
        font-size: 16px;
        color: var(--navy, #062f78);
    }

    .empty-donations-box p {
        margin: 0 0 16px;
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.5;
    }

    .table-pagination {
        padding: 12px 18px;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
    }
</style>
@endsection