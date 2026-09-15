@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')

    <div class="page-header">
        <h2>Audit Logs</h2>
        <div class="actions">
            <span class="audit-live-badge">
                <span class="live-dot"></span> Live Records
            </span>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <form class="toolbar" method="GET" action="{{ route('admin.audit-logs') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by description, user, or action...">

        <select name="action">
            <option value="all">All Actions</option>
            @foreach($actions as $value => $label)
                <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        @if($actor->hasParishWideAccess())
        <select name="actor_id">
            <option value="all">All Actors</option>
            @foreach($actors as $a)
                <option value="{{ $a->id }}" @selected(request('actor_id') == $a->id)>{{ $a->name }}</option>
            @endforeach
        </select>

        <select name="commission_id">
            <option value="all">All Commissions</option>
            @foreach($commissions as $commission)
                <option value="{{ $commission->id }}" @selected(request('commission_id') == $commission->id)>{{ $commission->name }}</option>
            @endforeach
        </select>
        @endif

        <select name="date_range">
            <option value="">Any Time</option>
            <option value="today" @selected(request('date_range') === 'today')>Today</option>
            <option value="yesterday" @selected(request('date_range') === 'yesterday')>Yesterday</option>
            <option value="7days" @selected(request('date_range') === '7days')>Last 7 Days</option>
            <option value="30days" @selected(request('date_range') === '30days')>Last 30 Days</option>
            <option value="this_month" @selected(request('date_range') === 'this_month')>This Month</option>
        </select>

        <button class="btn btn-primary" type="submit">Apply</button>
        @if(request()->hasAny(['search', 'action', 'actor_id', 'commission_id', 'date_range']) && !in_array(request('action'), ['all', '']) || request()->hasAny(['search', 'actor_id', 'commission_id', 'date_range']))
            <a class="btn btn-outline" href="{{ route('admin.audit-logs') }}">Clear</a>
        @endif
    </form>

    {{-- Audit Log Table --}}
    <div class="table-wrap">
        <table id="audit-log-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date &amp; Time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Target</th>
                    @if($actor->hasParishWideAccess())
                    <th>Commission</th>
                    @endif
                    <th>Description</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                <tr>
                    <td class="row-index">{{ $logs->firstItem() + $index }}</td>
                    <td class="audit-date-cell">
                        <span class="audit-date">{{ $log->created_at->format('M d, Y') }}</span>
                        <span class="audit-time">{{ $log->created_at->format('h:i A') }}</span>
                    </td>
                    <td>
                        <div class="audit-actor-cell">
                            <strong class="audit-actor-name">{{ $log->user_name ?: 'System' }}</strong>
                            @if($log->user_email)
                                <span class="audit-actor-email">{{ $log->user_email }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="action-badge action-{{ str_replace('_', '-', $log->action) }}">
                            {{ $log->action_label }}
                        </span>
                    </td>
                    <td>
                        @if($log->target_name)
                            <span class="audit-target">{{ $log->target_name }}</span>
                            @if($log->target_type)
                                <span class="audit-target-type">{{ $log->target_type }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @if($actor->hasParishWideAccess())
                    <td>
                        @if($log->commission_name)
                            <span class="commission-badge commission-specific">{{ $log->commission_name }}</span>
                        @else
                            <span class="commission-badge commission-all">Parish-wide</span>
                        @endif
                    </td>
                    @endif
                    <td class="audit-desc-cell">
                        <span class="audit-desc-text" title="{{ $log->description }}">{{ Str::limit($log->description, 80) }}</span>
                    </td>
                    <td>
                        @if($log->old_values || $log->new_values)
                            <button type="button" class="btn-detail" onclick="openDetailPopover(this)"
                                data-old="{{ htmlspecialchars(json_encode($log->old_values ?? []), ENT_QUOTES) }}"
                                data-new="{{ htmlspecialchars(json_encode($log->new_values ?? []), ENT_QUOTES) }}"
                                aria-label="View change details">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                View
                            </button>
                        @else
                            <span class="text-muted text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $actor->hasParishWideAccess() ? 8 : 7 }}" class="empty-cell">
                        No audit log entries found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;font-size:11px;color:var(--muted)">
        <span>Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries</span>
        {{ $logs->onEachSide(1)->links() }}
    </div>

    {{-- Detail Popover / Modal --}}
    <div id="detail-modal-backdrop" class="detail-modal-backdrop" onclick="closeDetailPopover()" style="display:none;"></div>
    <div id="detail-modal" class="detail-modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="detail-modal-title">
        <div class="detail-modal-header">
            <h3 id="detail-modal-title" class="detail-modal-title">Change Details</h3>
            <button type="button" class="drawer-close-btn" onclick="closeDetailPopover()" aria-label="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="detail-modal-body" id="detail-modal-body">
            <div class="detail-section">
                <h4 class="detail-section-title">Before (Old Values)</h4>
                <pre id="detail-old" class="detail-pre detail-old"></pre>
            </div>
            <div class="detail-section">
                <h4 class="detail-section-title">After (New Values)</h4>
                <pre id="detail-new" class="detail-pre detail-new"></pre>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* ===== Audit Logs Page ===== */
    .audit-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        color: #15803d;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .live-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 6px rgba(34, 197, 94, 0.7);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .toolbar { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
    .toolbar input { flex:1; min-width:200px; padding:10px 14px; border:1px solid var(--border); border-radius:7px; font-size:12px; background:#fff; }
    .toolbar select { padding:10px 14px; border:1px solid var(--border); border-radius:7px; font-size:12px; background:#fff; }

    .table-wrap { overflow-x:auto; border:1px solid var(--border); border-radius:11px; background:#fff; }
    table { width:100%; border-collapse:collapse; font-size:12px; }
    th { text-align:left; padding:14px 16px; background:#f8fafc; color:var(--muted); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border); white-space:nowrap; }
    td { padding:12px 16px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#fafcfe; }
    .empty-cell { text-align:center; padding:28px; color:var(--muted); }

    .audit-date-cell { white-space:nowrap; }
    .audit-date { display:block; font-weight:600; color:#1e293b; font-size:11px; }
    .audit-time { display:block; font-size:10px; color:var(--muted); }

    .audit-actor-cell { display:flex; flex-direction:column; gap:1px; }
    .audit-actor-name { font-weight:600; font-size:12px; color:#1e293b; }
    .audit-actor-email { font-size:10px; color:var(--muted); }

    .audit-target { display:block; font-weight:600; font-size:11px; color:#1e293b; }
    .audit-target-type { display:block; font-size:10px; color:var(--muted); }

    .audit-desc-cell { max-width:260px; }
    .audit-desc-text { display:block; font-size:11px; color:#475569; line-height:1.5; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px; }

    .text-muted { color:var(--muted); }
    .text-xs { font-size:10px; }

    /* Action Badges */
    .action-badge { padding:3px 10px; border-radius:20px; font-size:9px; font-weight:700; display:inline-block; white-space:nowrap; background:#e0e7ff; color:#4338ca; }
    .action-badge.action-user-created { background:#dbeafe; color:#1e40af; }
    .action-badge.action-user-updated { background:#fef9c3; color:#854d0e; }
    .action-badge.action-user-deactivated, .action-badge.action-user-deleted { background:#fee2e2; color:#b91c1c; }
    .action-badge.action-user-activated { background:#dcfce7; color:#15803d; }
    .action-badge.action-login { background:#f0fdf4; color:#15803d; }
    .action-badge.action-logout { background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
    .action-badge.action-password-reset { background:#fef3c7; color:#92400e; }
    .action-badge.action-role-changed { background:#f3e8ff; color:#7e22ce; }
    .action-badge.action-commission-assigned, .action-badge.action-commission-changed { background:#e0f2fe; color:#0369a1; }
    .action-badge.action-failed-login { background:#fee2e2; color:#991b1b; }

    /* Commission Badge */
    .commission-badge { padding:3px 10px; border-radius:20px; font-size:9px; font-weight:600; display:inline-block; white-space:nowrap; }
    .commission-all { background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; }
    .commission-specific { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }

    /* Detail button */
    .btn-detail { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border:1px solid var(--border); border-radius:6px; background:#fff; color:var(--navy); font-size:11px; font-weight:600; cursor:pointer; transition:all 0.15s ease; }
    .btn-detail:hover { background:#f0f7ff; border-color:#93c5fd; }
    .btn-detail svg { flex-shrink:0; }

    /* Detail Modal */
    .detail-modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,0.45); backdrop-filter:blur(2px); z-index:1050; }
    .detail-modal { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:min(560px,94vw); max-height:80vh; background:#fff; border-radius:14px; box-shadow:0 20px 60px rgba(0,0,0,0.22); z-index:1051; display:flex; flex-direction:column; overflow:hidden; }
    .detail-modal-header { display:flex; justify-content:space-between; align-items:center; padding:18px 22px 14px; border-bottom:1px solid var(--border); }
    .detail-modal-title { font-size:15px; font-weight:700; color:var(--navy); margin:0; }
    .detail-modal-body { flex:1; overflow-y:auto; padding:18px 22px; display:flex; flex-direction:column; gap:18px; }
    .detail-section {}
    .detail-section-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin:0 0 8px; }
    .detail-pre { font-size:11px; font-family:'Courier New',Courier,monospace; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; overflow-x:auto; white-space:pre-wrap; word-break:break-all; margin:0; line-height:1.6; min-height:40px; }
    .detail-old { border-color:#fecaca; background:#fff5f5; }
    .detail-new { border-color:#bbf7d0; background:#f0fdf4; }

    @media(max-width:620px) { .toolbar input { min-width:100%; } }
</style>
@endpush

@push('scripts')
<script>
    function openDetailPopover(btn) {
        const oldRaw = btn.getAttribute('data-old');
        const newRaw = btn.getAttribute('data-new');

        let oldVal, newVal;
        try { oldVal = JSON.parse(oldRaw); } catch { oldVal = oldRaw; }
        try { newVal = JSON.parse(newRaw); } catch { newVal = newRaw; }

        const fmt = (v) => {
            if (v === null || v === undefined) return '(none)';
            if (typeof v === 'object') return JSON.stringify(v, null, 2);
            return String(v);
        };

        document.getElementById('detail-old').textContent = fmt(oldVal);
        document.getElementById('detail-new').textContent = fmt(newVal);

        document.getElementById('detail-modal').style.display = 'flex';
        document.getElementById('detail-modal-backdrop').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeDetailPopover() {
        document.getElementById('detail-modal').style.display = 'none';
        document.getElementById('detail-modal-backdrop').style.display = 'none';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDetailPopover();
    });
</script>
@endpush

