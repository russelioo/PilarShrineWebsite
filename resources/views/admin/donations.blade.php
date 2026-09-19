@extends('layouts.admin')
@section('title', 'Parish Donations & Stewardship')

@section('content')
<div class="page-header">
    <div>
        <h2>Parish Donations &amp; Stewardship</h2>
        <p class="page-description">Verify donation submissions, review uploaded proofs of payment, add remarks, issue acknowledgment receipts, and track donor histories.</p>
    </div>
    <div class="actions">
        <a href="/#/donations" target="_blank" class="btn btn-outline" title="Open public donation page">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            <span>Public Donation Page</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="notice-success">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="notice-error">
        <strong>Please check the following error(s):</strong>
        <ul style="margin: 6px 0 0 16px; padding: 0;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Summary KPI Cards -->
<div class="summary-grid">
    <article class="stat-box">
        <strong>₱{{ number_format($totalOfferingsSum, 2) }}</strong>
        <span>Verified Offerings</span>
    </article>
    <article class="stat-box">
        <strong class="stat-amber">{{ $counts['pending_verification'] }}</strong>
        <span>Pending Verification</span>
    </article>
    <article class="stat-box">
        <strong class="stat-blue">{{ $counts['verified'] }}</strong>
        <span>Verified Submissions</span>
    </article>
    <article class="stat-box">
        <strong class="stat-green">{{ $counts['receipt_ready'] }}</strong>
        <span>Receipts Ready</span>
    </article>
</div>

<!-- Filters Bar -->
<div class="filter-card">
    <div class="status-tabs">
        <a href="{{ route('admin.donations', array_merge(request()->query(), ['status' => 'all'])) }}" class="tab-link {{ $currentStatus === 'all' || empty($currentStatus) ? 'active' : '' }}">
            All ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.donations', array_merge(request()->query(), ['status' => 'pending_verification'])) }}" class="tab-link {{ $currentStatus === 'pending_verification' ? 'active' : '' }}">
            Pending ({{ $counts['pending_verification'] }})
        </a>
        <a href="{{ route('admin.donations', array_merge(request()->query(), ['status' => 'verified'])) }}" class="tab-link {{ $currentStatus === 'verified' ? 'active' : '' }}">
            Verified ({{ $counts['verified'] }})
        </a>
        <a href="{{ route('admin.donations', array_merge(request()->query(), ['status' => 'receipt_ready'])) }}" class="tab-link {{ $currentStatus === 'receipt_ready' ? 'active' : '' }}">
            Receipt Ready ({{ $counts['receipt_ready'] }})
        </a>
        <a href="{{ route('admin.donations', array_merge(request()->query(), ['status' => 'rejected'])) }}" class="tab-link {{ $currentStatus === 'rejected' ? 'active' : '' }}">
            Rejected ({{ $counts['rejected'] }})
        </a>
    </div>

    <form method="GET" action="{{ route('admin.donations') }}" class="search-form">
        <input type="hidden" name="status" value="{{ $currentStatus }}">
        <div class="search-input-wrap">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
                type="text"
                name="search"
                value="{{ $searchQuery }}"
                placeholder="Search by donor, ref #, purpose, receipt..."
            />
        </div>
        <button type="submit" class="btn btn-sm btn-outline">Search</button>
        @if(!empty($searchQuery))
            <a href="{{ route('admin.donations', ['status' => $currentStatus]) }}" class="btn-clear-search" title="Clear search">&times; Clear</a>
        @endif
    </form>
</div>

<!-- Table Container -->
<div class="data-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>DONOR &amp; CONTACT</th>
                <th>PURPOSE / FUND</th>
                <th>AMOUNT &amp; METHOD</th>
                <th>DATE &amp; REFERENCE</th>
                <th>STATUS</th>
                <th>RECEIPT NO.</th>
                <th class="th-actions">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donations as $donation)
                <tr>
                    <td>
                        <strong class="donor-name">{{ $donation->donor_name }}</strong>
                        <div class="donor-meta">
                            <span>✉ {{ $donation->email ?: 'No email' }}</span>
                            @if($donation->contact_number)
                                <span>☎ {{ $donation->contact_number }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="purpose-badge">{{ $donation->purpose ?: 'General Parish Support' }}</span>
                        @if($donation->notes)
                            <small class="notes-preview" title="{{ $donation->notes }}">📝 {{ \Illuminate\Support\Str::limit($donation->notes, 30) }}</small>
                        @endif
                    </td>
                    <td>
                        <strong class="amount-text">{{ $donation->formatted_amount }}</strong>
                        <span class="method-pill">{{ $donation->method }}</span>
                    </td>
                    <td>
                        <span class="date-text">{{ $donation->donation_date ? $donation->donation_date->format('M d, Y') : $donation->created_at->format('M d, Y') }}</span>
                        @if($donation->payment_reference)
                            <small class="ref-text">Ref: {{ $donation->payment_reference }}</small>
                        @else
                            <small class="ref-text text-muted">{{ $donation->transaction_id }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $donation->status_badge_class }}">
                            @if($donation->status === 'pending_verification')
                                ⏳ Pending
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
                        @if($donation->admin_notes)
                            <small class="admin-remark-preview" title="{{ $donation->admin_notes }}">Remarks: {{ \Illuminate\Support\Str::limit($donation->admin_notes, 25) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($donation->receipt_number)
                            <code class="receipt-code">{{ $donation->receipt_number }}</code>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="td-actions">
                        <button
                            type="button"
                            class="action-btn btn-review"
                            onclick='openReviewModal(@json($donation), @json($donation->proof_url))'
                            title="Review submission details and take action"
                        >
                            Review &amp; Verify
                        </button>
                        @if($donation->proof_of_payment)
                            <button
                                type="button"
                                class="action-btn btn-proof"
                                onclick='openProofModal(@json($donation->proof_url), @json($donation->donor_name))'
                                title="View uploaded proof of payment"
                            >
                                Proof
                            </button>
                        @endif
                        <button
                            type="button"
                            class="action-btn btn-history"
                            onclick="loadDonorHistory({{ $donation->id }}, @json($donation->donor_name))"
                            title="View donor's giving history"
                        >
                            History
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-state-row">
                        <div class="empty-container">
                            <span class="empty-symbol">🕊</span>
                            <h3>No donation submissions found</h3>
                            <p>No donation verification records match the current status filter or search criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($donations->hasPages())
        <div class="pagination-footer">
            {{ $donations->links() }}
        </div>
    @endif
</div>

<!-- MODAL 1: Review & Verify Modal -->
<div id="review-modal" class="admin-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="review-title">Review Donation Submission</h3>
                <p class="modal-subtitle">Verify payment receipt, record remarks, or mark acknowledgment ready.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeReviewModal()">&times;</button>
        </div>

        <div class="modal-body">
            <div class="review-grid">
                <!-- Left: Details -->
                <div class="review-details-col">
                    <div class="detail-block">
                        <span class="detail-label">Donor Name</span>
                        <strong class="detail-val" id="rev-donor-name">—</strong>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Contact Details</span>
                        <span class="detail-val" id="rev-donor-contact">—</span>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Purpose / Fund</span>
                        <span class="detail-val" id="rev-purpose">—</span>
                    </div>

                    <div class="detail-row">
                        <div class="detail-block">
                            <span class="detail-label">Amount Donated</span>
                            <strong class="detail-val highlight-amount" id="rev-amount">—</strong>
                        </div>
                        <div class="detail-block">
                            <span class="detail-label">Payment Method</span>
                            <span class="detail-val" id="rev-method">—</span>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-block">
                            <span class="detail-label">Date of Donation</span>
                            <span class="detail-val" id="rev-date">—</span>
                        </div>
                        <div class="detail-block">
                            <span class="detail-label">Reference Number</span>
                            <span class="detail-val" id="rev-ref">—</span>
                        </div>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Donor Notes / Intentions</span>
                        <p class="detail-notes-box" id="rev-notes">None provided.</p>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Current Status</span>
                        <div id="rev-status-badge"></div>
                    </div>
                </div>

                <!-- Right: Proof Preview -->
                <div class="review-proof-col">
                    <span class="detail-label">Uploaded Proof of Payment</span>
                    <div class="proof-frame-box" id="proof-container">
                        <img id="rev-proof-img" src="" alt="Proof of Payment Preview" style="display:none;" />
                        <div id="rev-proof-placeholder" class="proof-placeholder">No proof uploaded</div>
                    </div>
                    <div class="proof-actions-row" id="proof-actions" style="display:none;">
                        <a id="proof-external-link" href="" target="_blank" class="btn btn-sm btn-outline">Open Full Image ↗</a>
                    </div>
                </div>
            </div>

            <!-- Action Form -->
            <form id="status-form" method="POST" action="" class="status-action-form">
                @csrf
                @method('PATCH')

                <h4 class="form-subheading">Update Verification &amp; Issue Receipt</h4>

                <div class="form-inputs-grid">
                    <label class="modal-field">
                        <span>Change Status <b>*</b></span>
                        <select name="status" id="form-status-select" onchange="toggleReceiptFields(this.value)" required>
                            <option value="pending_verification">Pending Verification</option>
                            <option value="verified">Verified (Payment Confirmed)</option>
                            <option value="receipt_ready">Acknowledgment / Receipt Ready</option>
                            <option value="rejected">Rejected (Invalid Proof / Unmatched)</option>
                        </select>
                    </label>

                    <label class="modal-field" id="receipt-num-field" style="display: none;">
                        <span>Receipt / Acknowledgment Number</span>
                        <input type="text" name="receipt_number" id="form-receipt-num" placeholder="e.g. ACK-2026-0012">
                        <small class="field-hint">Leave blank to auto-generate official sequence.</small>
                    </label>
                </div>

                <label class="modal-field">
                    <span>Parish Administrator Remarks</span>
                    <textarea name="admin_notes" id="form-admin-notes" rows="2" placeholder="Add verification remarks, notes for the donor, or rejection reason..."></textarea>
                </label>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Status &amp; Remarks</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: Full Image Proof Viewer -->
<div id="proof-modal" class="admin-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="proof-modal-title">Proof of Payment</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeProofModal()">&times;</button>
        </div>
        <div class="modal-body text-center" style="padding: 16px;">
            <img id="full-proof-img" src="" alt="Proof of payment full view" style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px; border: 1px solid #cbd5e1;" />
            <div style="margin-top: 14px;">
                <a id="full-proof-btn" href="" target="_blank" class="btn btn-sm btn-outline">Download / View Original File ↗</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: Donor History Modal -->
<div id="history-modal" class="admin-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="history-donor-name">Donor Contribution History</h3>
                <p class="modal-subtitle">Past offerings submitted by this parishioner.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeHistoryModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="history-loading" class="text-center" style="padding: 30px;">Loading donor records...</div>
            <div id="history-content" style="display:none;">
                <table class="admin-table mini-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>PURPOSE</th>
                            <th>AMOUNT</th>
                            <th>METHOD</th>
                            <th>STATUS</th>
                            <th>RECEIPT NO.</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function openReviewModal(donation, proofUrl) {
        document.getElementById('rev-donor-name').textContent = donation.donor_name || 'Anonymous';
        document.getElementById('rev-donor-contact').textContent = (donation.email || '') + (donation.contact_number ? ' • ' + donation.contact_number : '');
        document.getElementById('rev-purpose').textContent = donation.purpose || 'General Parish Support';
        document.getElementById('rev-amount').textContent = donation.formatted_amount || ('₱' + parseFloat(donation.amount).toFixed(2));
        document.getElementById('rev-method').textContent = donation.method || '—';
        document.getElementById('rev-date').textContent = donation.donation_date ? donation.donation_date.substring(0, 10) : '—';
        document.getElementById('rev-ref').textContent = donation.payment_reference || donation.transaction_id || '—';
        document.getElementById('rev-notes').textContent = donation.notes || 'None provided.';
        
        document.getElementById('rev-status-badge').innerHTML = '<span class="status-badge ' + (donation.status_badge_class || '') + '">' + (donation.status_label || donation.status) + '</span>';

        const imgEl = document.getElementById('rev-proof-img');
        const placeholderEl = document.getElementById('rev-proof-placeholder');
        const actionsEl = document.getElementById('proof-actions');
        const linkEl = document.getElementById('proof-external-link');

        if (proofUrl) {
            imgEl.src = proofUrl;
            imgEl.style.display = 'block';
            placeholderEl.style.display = 'none';
            actionsEl.style.display = 'flex';
            linkEl.href = proofUrl;
        } else {
            imgEl.src = '';
            imgEl.style.display = 'none';
            placeholderEl.style.display = 'flex';
            actionsEl.style.display = 'none';
        }

        const form = document.getElementById('status-form');
        form.action = '/admin/donations/' + donation.id + '/status';

        const statusSelect = document.getElementById('form-status-select');
        statusSelect.value = donation.status || 'pending_verification';
        toggleReceiptFields(statusSelect.value);

        document.getElementById('form-receipt-num').value = donation.receipt_number || '';
        document.getElementById('form-admin-notes').value = donation.admin_notes || '';

        document.getElementById('review-modal').classList.add('open');
    }

    function closeReviewModal() {
        document.getElementById('review-modal').classList.remove('open');
    }

    function toggleReceiptFields(status) {
        const field = document.getElementById('receipt-num-field');
        if (status === 'receipt_ready') {
            field.style.display = 'flex';
        } else {
            field.style.display = 'none';
        }
    }

    function openProofModal(proofUrl, donorName) {
        document.getElementById('proof-modal-title').textContent = 'Proof of Payment — ' + (donorName || 'Donor');
        document.getElementById('full-proof-img').src = proofUrl;
        document.getElementById('full-proof-btn').href = proofUrl;
        document.getElementById('proof-modal').classList.add('open');
    }

    function closeProofModal() {
        document.getElementById('proof-modal').classList.remove('open');
    }

    async function loadDonorHistory(donationId, donorName) {
        document.getElementById('history-donor-name').textContent = 'Giving History: ' + donorName;
        document.getElementById('history-loading').style.display = 'block';
        document.getElementById('history-content').style.display = 'none';
        document.getElementById('history-modal').classList.add('open');

        try {
            const res = await fetch('/admin/donations/' + donationId + '/history');
            const data = await res.json();
            const tbody = document.getElementById('history-tbody');
            tbody.innerHTML = '';

            if (data.history && data.history.length > 0) {
                data.history.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.date}</td>
                        <td>${item.purpose}</td>
                        <td><strong>${item.amount}</strong></td>
                        <td>${item.method}</td>
                        <td><span class="status-badge ${item.status_class}">${item.status}</span></td>
                        <td><code>${item.receipt_number || '—'}</code></td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding: 24px;">No other recorded donations for this donor.</td></tr>';
            }
            document.getElementById('history-loading').style.display = 'none';
            document.getElementById('history-content').style.display = 'block';
        } catch (e) {
            document.getElementById('history-loading').textContent = 'Unable to load donor history.';
        }
    }

    function closeHistoryModal() {
        document.getElementById('history-modal').classList.remove('open');
    }
</script>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .page-header h2 {
        font-family: var(--font-heading);
        font-size: 24px;
        font-weight: 700;
        color: var(--navy, #062f78);
        margin: 0 0 6px;
    }

    .page-description {
        margin: 0;
        font-size: 13px;
        color: var(--muted, #718096);
    }

    .notice-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
    }

    .notice-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 13px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 900px) {
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .stat-box strong {
        font-family: var(--font-heading);
        font-size: 22px;
        font-weight: 700;
        color: var(--navy, #062f78);
    }

    .stat-box span {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-amber { color: #d97706 !important; }
    .stat-blue { color: #2563eb !important; }
    .stat-green { color: #16a34a !important; }

    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .status-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .tab-link {
        font-size: 12.5px;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 6px;
        text-decoration: none;
        color: #64748b;
        transition: all 0.15s ease;
    }

    .tab-link:hover {
        background: #f1f5f9;
        color: var(--navy, #062f78);
    }

    .tab-link.active {
        background: var(--navy, #062f78);
        color: #ffffff;
    }

    .search-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-input-wrap svg {
        position: absolute;
        left: 10px;
        color: #94a3b8;
    }

    .search-input-wrap input {
        padding: 7px 12px 7px 32px;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        font-size: 12.5px;
        width: 240px;
    }

    .btn-clear-search {
        color: #dc2626;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
    }

    .data-table-wrap {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 12.5px;
    }

    .admin-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .admin-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .admin-table tbody tr:hover {
        background: #fafcff;
    }

    .donor-name {
        display: block;
        font-size: 13.5px;
        color: var(--navy, #062f78);
    }

    .donor-meta {
        font-size: 11px;
        color: #64748b;
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-top: 2px;
    }

    .purpose-badge {
        font-weight: 600;
        color: #1e293b;
        display: block;
    }

    .notes-preview {
        display: block;
        font-size: 10.5px;
        color: #64748b;
        margin-top: 2px;
    }

    .amount-text {
        font-size: 14px;
        color: var(--navy, #062f78);
        display: block;
    }

    .method-pill {
        font-size: 10px;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .date-text {
        display: block;
    }

    .ref-text {
        display: block;
        font-size: 10.5px;
        color: #64748b;
    }

    .status-badge {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
        white-space: nowrap;
    }

    .badge-status-pending { background: #fef3c7; color: #b45309; }
    .badge-status-verified { background: #dbeafe; color: #1d4ed8; }
    .badge-status-ready { background: #dcfce7; color: #15803d; }
    .badge-status-rejected { background: #fee2e2; color: #b91c1c; }

    .admin-remark-preview {
        display: block;
        font-size: 10px;
        color: #475569;
        margin-top: 2px;
    }

    .receipt-code {
        font-family: 'Courier New', monospace;
        font-size: 11.5px;
        font-weight: 700;
        color: #15803d;
        background: #f0fdf4;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #bbf7d0;
    }

    .th-actions, .td-actions {
        text-align: right;
        white-space: nowrap;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        padding: 5px 9px;
        border-radius: 5px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }

    .btn-review {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .btn-review:hover {
        background: #1d4ed8;
        color: #ffffff;
    }

    .btn-proof {
        background: #f8fafc;
        color: #475569;
        border-color: #cbd5e1;
    }

    .btn-proof:hover {
        background: #e2e8f0;
    }

    .btn-history {
        background: #faf5ff;
        color: #7e22ce;
        border-color: #e9d5ff;
    }

    .btn-history:hover {
        background: #7e22ce;
        color: #ffffff;
    }

    .empty-state-row {
        text-align: center;
        padding: 48px 24px;
    }

    .empty-symbol {
        font-size: 34px;
        display: block;
        margin-bottom: 8px;
    }

    .pagination-footer {
        padding: 12px 18px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }

    /* Modals */
    .admin-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10000;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(3px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .admin-modal.open {
        opacity: 1;
        pointer-events: auto;
    }

    .modal-dialog {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        max-height: 90vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .modal-md { max-width: 580px; }
    .modal-lg { max-width: 820px; }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .modal-title {
        margin: 0 0 3px;
        font-size: 17px;
        color: var(--navy, #062f78);
    }

    .modal-subtitle {
        margin: 0;
        font-size: 12px;
        color: #64748b;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #94a3b8;
    }

    .modal-close:hover { color: #1e293b; }

    .modal-body {
        padding: 20px 24px;
    }

    .review-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 22px;
        margin-bottom: 22px;
    }

    @media (max-width: 700px) {
        .review-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-block {
        margin-bottom: 12px;
    }

    .detail-label {
        display: block;
        font-size: 10.5px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 2px;
    }

    .detail-val {
        font-size: 13.5px;
        color: #1e293b;
    }

    .highlight-amount {
        font-size: 18px;
        color: var(--navy, #062f78);
        font-weight: 800;
    }

    .detail-row {
        display: flex;
        gap: 16px;
    }

    .detail-row .detail-block {
        flex: 1;
    }

    .detail-notes-box {
        margin: 0;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        color: #475569;
    }

    .proof-frame-box {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        height: 230px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-top: 4px;
    }

    .proof-frame-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .proof-placeholder {
        color: #94a3b8;
        font-size: 12px;
    }

    .proof-actions-row {
        margin-top: 8px;
        display: flex;
        justify-content: flex-end;
    }

    .status-action-form {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 18px;
    }

    .form-subheading {
        margin: 0 0 14px;
        font-size: 13.5px;
        color: var(--navy, #062f78);
    }

    .form-inputs-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin-bottom: 12px;
    }

    @media (max-width: 600px) {
        .form-inputs-grid {
            grid-template-columns: 1fr;
        }
    }

    .modal-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 12px;
    }

    .modal-field span {
        font-size: 11.5px;
        font-weight: 700;
        color: #1e293b;
    }

    .modal-field input, .modal-field select, .modal-field textarea {
        padding: 8px 12px;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        font-size: 12.5px;
        font-family: inherit;
    }

    .field-hint {
        font-size: 10.5px;
        color: #64748b;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }

    .btn-sm { padding: 6px 10px; font-size: 11px; }

    .btn-outline {
        background: #ffffff;
        color: var(--navy, #062f78);
        border-color: #cbd5e1;
    }

    .btn-outline:hover { background: #f8fafc; border-color: var(--navy, #062f78); }

    .btn-secondary { background: #e2e8f0; color: #334155; }
    .btn-secondary:hover { background: #cbd5e1; }

    .btn-primary { background: var(--navy, #062f78); color: #ffffff; }
    .btn-primary:hover { background: #0b45a6; }
</style>
@endsection