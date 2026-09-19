@extends('layouts.admin')
@section('title', 'Ministry Membership Requests')

@section('content')
<div class="page-header">
    <div>
        <h2>Ministry Membership Requests</h2>
        <p>Review and process parishioner applications to serve in parish ministries and apostolates.</p>
    </div>
</div>

@if(session('success'))
    <div class="notice-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="notice-error">{{ session('error') }}</div>
@endif

<!-- Metric Counters -->
<div class="summary">
    <article>
        <strong>{{ $totalCount }}</strong>
        <span>Total Applications</span>
    </article>
    <article class="highlight-pending">
        <strong>{{ $pendingCount }}</strong>
        <span>Pending Review</span>
    </article>
    <article class="highlight-approved">
        <strong>{{ $approvedCount }}</strong>
        <span>Active Members</span>
    </article>
    <article>
        <strong>{{ $rejectedCount }}</strong>
        <span>Rejected</span>
    </article>
</div>

<!-- Filters Toolbar -->
<form class="toolbar" method="GET" action="{{ route('admin.ministry-requests') }}">
    <input type="search" name="search" value="{{ $search }}" placeholder="Search applicant name, email...">

    @if($ministriesList->count() > 1)
    <select name="ministry_id">
        <option value="">All Ministries</option>
        @foreach($ministriesList as $m)
            <option value="{{ $m->id }}" @selected((string)$ministryId === (string)$m->id)>{{ $m->name }}</option>
        @endforeach
    </select>
    @endif

    <select name="status">
        <option value="all" @selected($status === 'all')>All Statuses</option>
        <option value="pending" @selected($status === 'pending')>Pending Review</option>
        <option value="approved" @selected($status === 'approved')>Approved</option>
        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
    </select>

    <button class="btn btn-primary" type="submit">Filter</button>
    @if(request()->hasAny(['search', 'ministry_id', 'status']))
        <a class="btn btn-outline" href="{{ route('admin.ministry-requests') }}">Clear</a>
    @endif
</form>

<!-- Requests Table -->
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Applicant</th>
                <th>Ministry</th>
                <th>Application Details</th>
                <th>Submitted</th>
                <th>Status</th>
                <th style="text-align: right;">Review Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $index => $req)
            <tr>
                <td>{{ $requests->firstItem() + $index }}</td>
                <td>
                    <strong>{{ $req->user->displayName }}</strong>
                    <small>{{ $req->user->email }}</small>
                    @if($req->user->phone)
                        <small style="color: #64748b;">📞 {{ $req->user->phone }}</small>
                    @endif
                </td>
                <td>
                    <span class="ministry-tag">{{ $req->ministry->name }}</span>
                    <small style="color: #64748b;">{{ $req->ministry->category }}</small>
                </td>
                <td style="max-width: 280px;">
                    <div class="detail-block">
                        <b>Motivation:</b>
                        <p>{{ \Illuminate\Support\Str::limit($req->application_message, 120) }}</p>
                    </div>
                    @if($req->experience)
                    <div class="detail-block" style="margin-top: 4px;">
                        <small><b>Experience:</b> {{ \Illuminate\Support\Str::limit($req->experience, 80) }}</small>
                    </div>
                    @endif
                    @if($req->reviewer_notes)
                    <div class="detail-block" style="margin-top: 4px; color: #b91c1c;">
                        <small><b>Reviewer Note:</b> {{ $req->reviewer_notes }}</small>
                    </div>
                    @endif
                </td>
                <td>
                    {{ $req->created_at->format('M j, Y') }}
                    <small>{{ $req->created_at->format('g:i A') }}</small>
                </td>
                <td>
                    @if($req->status === 'pending')
                        <span class="status-pill pill-pending">🟡 Pending Review</span>
                    @elseif($req->status === 'approved')
                        <span class="status-pill pill-approved">✓ Approved</span>
                        @if($req->joined_at)
                            <small>Joined {{ $req->joined_at->format('M j, Y') }}</small>
                        @endif
                    @elseif($req->status === 'rejected')
                        <span class="status-pill pill-rejected">✕ Rejected</span>
                    @else
                        <span class="status-pill pill-inactive">Inactive</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <div class="actions-group">
                        @if($req->status !== 'approved')
                        <form method="POST" action="{{ route('admin.ministry-requests.approve', $req) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-action-sm btn-approve" title="Approve and make official member" onclick="return confirm('Approve this parishioner as an official member of {{ $req->ministry->name }}?')">
                                ✓ Approve
                            </button>
                        </form>
                        @endif

                        @if($req->status !== 'rejected')
                        <button type="button" class="btn-action-sm btn-reject" onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->user->displayName) }}', '{{ addslashes($req->ministry->name) }}')">
                            ✕ Reject
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td class="empty" colspan="7">
                    <div style="padding: 24px; text-align: center;">
                        <p style="margin: 0; font-size: 13px; color: #64748b;">No ministry membership applications found.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($requests->hasPages())
<div class="pagination">
    @if($requests->onFirstPage())
        <span>Previous</span>
    @else
        <a href="{{ $requests->previousPageUrl() }}">Previous</a>
    @endif
    <small>Page {{ $requests->currentPage() }} of {{ $requests->lastPage() }}</small>
    @if($requests->hasMorePages())
        <a href="{{ $requests->nextPageUrl() }}">Next</a>
    @else
        <span>Next</span>
    @endif
</div>
@endif

<!-- Reject Modal -->
<div id="reject-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 id="reject-modal-title">Reject Application</h4>
            <button type="button" class="modal-close" onclick="closeRejectModal()">&times;</button>
        </div>
        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="modal-body">
                <p id="reject-modal-desc" style="font-size: 12px; color: #475569; margin-bottom: 12px;"></p>
                <label for="reject-reason" style="display: block; font-size: 11px; font-weight: 700; margin-bottom: 5px;">
                    Reason or Message for Applicant (Optional):
                </label>
                <textarea id="reject-reason" name="reason" rows="3" placeholder="e.g. Current cohort is full, kindly reapply next quarter." style="width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.page-header p { margin: 6px 0 0; color: var(--muted); font-size: 11px; }
.notice-success { margin-bottom: 16px; padding: 12px; border: 1px solid #b7e4c7; border-radius: 8px; background: #effaf3; color: #176b3a; font-size: 12px; font-weight: 600; }
.notice-error { margin-bottom: 16px; padding: 12px; border: 1px solid #fca5a5; border-radius: 8px; background: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 600; }
.summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
.summary article { padding: 16px 18px; border: 1px solid var(--line); border-radius: 9px; background: #fff; }
.summary strong, .summary span { display: block; }
.summary strong { font-family: var(--font-heading); font-size: 20px; font-weight: 700; color: var(--navy); }
.summary span { font-size: 9px; color: var(--muted); text-transform: uppercase; margin-top: 4px; }
.highlight-pending strong { color: #d97706; }
.highlight-approved strong { color: #059669; }

.toolbar { display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; }
.toolbar input, .toolbar select { padding: 10px 12px; border: 1px solid var(--line); border-radius: 7px; background: #fff; font-size: 11.5px; }
.toolbar input { flex: 1; min-width: 200px; }
.toolbar a { text-decoration: none; }

.table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 11px; background: #fff; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #edf2f7; vertical-align: top; }
th { background: #f7fafc; color: var(--muted); font-size: 9px; text-transform: uppercase; white-space: nowrap; }
td strong { color: var(--navy); display: block; font-size: 11.5px; }
td small { display: block; margin-top: 3px; }
.ministry-tag { font-weight: 700; color: #0f172a; font-size: 11px; }
.detail-block p { margin: 2px 0 0; color: #334155; line-height: 1.4; font-size: 10.5px; }
.detail-block b { color: #475569; font-size: 9px; text-transform: uppercase; }

.status-pill { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 9.5px; font-weight: 700; white-space: nowrap; }
.pill-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.pill-approved { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.pill-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.pill-inactive { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

.actions-group { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }
.btn-action-sm { padding: 5px 9px; border-radius: 5px; font-size: 10px; font-weight: 700; cursor: pointer; border: none; transition: opacity 0.15s; }
.btn-action-sm:hover { opacity: 0.85; }
.btn-approve { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.btn-reject { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
.btn-danger { background: #dc2626; color: #ffffff; border: 1px solid #b91c1c; padding: 8px 14px; border-radius: 6px; font-weight: 700; cursor: pointer; }

/* Modal */
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal-dialog { background: #ffffff; border-radius: 10px; max-width: 440px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #f1f5f9; }
.modal-header h4 { margin: 0; font-size: 14px; color: var(--navy); font-weight: 700; }
.modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b; }
.modal-body { padding: 16px 18px; }
.modal-footer { padding: 12px 18px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px; }

.pagination { display: flex; justify-content: center; align-items: center; gap: 18px; margin-top: 18px; }
.pagination a, .pagination span { padding: 7px 11px; border: 1px solid var(--line); border-radius: 6px; text-decoration: none; }
@media(max-width: 800px) { .summary { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@push('scripts')
<script>
function openRejectModal(id, applicantName, ministryName) {
    document.getElementById('reject-modal-title').innerText = 'Reject Application for ' + ministryName;
    document.getElementById('reject-modal-desc').innerText = 'Are you sure you want to reject ' + applicantName + '\'s application to join ' + ministryName + '?';
    document.getElementById('reject-form').action = '/admin/ministry-requests/' + id + '/reject';
    document.getElementById('reject-modal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('reject-modal').style.display = 'none';
}
</script>
@endpush

