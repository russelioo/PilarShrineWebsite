@extends('layouts.parishioner')
@section('title', 'Dashboard')
@section('title', 'Parishioner Dashboard')

@section('content')
<section class="welcome"><div><h2>Parishioner Dashboard</h2><p>Track your requests and stay connected with Pilar Shrine.</p></div><div class="date">{{ now()->format('l, F j, Y') }}</div></section>
<div class="portal-stats"><article><strong>2</strong><span>Active Requests</span></article><article><strong>1</strong><span>Upcoming Schedule</span></article><article><strong>1</strong><span>Unread Message</span></article></div>
<div class="portal-panel"><h3>Recent activity</h3><p><b>Mass intention received</b><span>Pending parish office review</span></p><p><b>Baptism inquiry answered</b><span>Reply received August 28, 2026</span></p><p><b>Sunday Mass</b><span>September 6, 2026 at 7:30 AM</span></p></div>
<section class="welcome">
    <div>
        <h2>{{ $greeting ?? 'Welcome back, ' . ($user->displayName ?? 'Parishioner') . '!' }}</h2>
        <p>Welcome back to the Pilar Shrine Parishioner Portal. Track your sacraments, intentions, and pastoral services.</p>
    </div>
    <div class="date-badge">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span>{{ now()->setTimezone('Asia/Manila')->format('l, F j, Y') }}</span>
    </div>
</section>

@if(!($user->isProfileComplete()))
<div class="profile-incomplete-banner">
    <div class="banner-content">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#d97706" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div>
            <strong>Parish profile is incomplete</strong>
            <p>Please complete your birth date, contact number, and residence address to enable all parish certificates and sacramental bookings.</p>
        </div>
    </div>
    <a href="{{ route('parishioner.profile-settings') }}" class="btn-complete-profile">Complete Profile</a>
</div>
@endif

<!-- Quick Stats Overview -->
<div class="portal-stats">
    <a href="javascript:void(0)" class="stat-card stat-card-disabled" data-disabled-feature="true" data-feature-name="Sacrament Requests" title="This feature will be available soon">
        <span class="stat-card-badge">Soon</span>
        <div class="stat-icon stat-icon-blue">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2ZM14 3v5h5M8 13h8M8 17h6"/>
            </svg>
        </div>
        <strong>{{ $activeSacramentsCount ?? 0 }}</strong>
        <span>Active Sacrament Requests</span>
    </a>

    <a href="{{ route('parishioner.mass-intentions') }}" class="stat-card">
        <div class="stat-icon stat-icon-gold">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>
            </svg>
        </div>
        <strong>{{ $activeMassIntentionsCount ?? 0 }}</strong>
        <span>Active Mass Intentions</span>
    </a>

    <div class="stat-card">
        <div class="stat-icon stat-icon-navy">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
        </div>
        <strong>{{ $totalActiveRequests ?? 0 }}</strong>
        <span>Total Ongoing Submissions</span>
    </div>

    <a href="{{ route('parishioner.events-schedule') }}" class="stat-card">
        <div class="stat-icon stat-icon-green">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>
            </svg>
        </div>
        <strong>{{ $upcomingMassCount ?? 0 }}</strong>
        <span>Scheduled Mass Times</span>
    </a>
</div>

<!-- Quick Action Shortcuts -->
<div class="quick-actions-bar">
    <span class="actions-label">Parish Services:</span>
    <a href="javascript:void(0)" class="btn-action btn-action-gold btn-action-disabled" data-disabled-feature="true" data-feature-name="Request Mass Intention" title="This feature will be available soon">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
        <span>Request Mass Intention</span>
        <span class="btn-badge-soon">Soon</span>
    </a>
    <a href="javascript:void(0)" class="btn-action btn-action-navy btn-action-disabled" data-disabled-feature="true" data-feature-name="Request a Sacrament" title="This feature will be available soon">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2ZM14 3v5h5M8 13h8M8 17h6"/></svg>
        <span>Request a Sacrament</span>
        <span class="btn-badge-soon">Soon</span>
    </a>
    <a href="{{ route('parishioner.profile-settings') }}" class="btn-action btn-action-outline">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Profile &amp; Settings</span>
    </a>
</div>

<!-- Recent Activity / History -->
<div class="portal-panel">
    <div class="panel-header">
        <h3>Recent Submissions &amp; Activity</h3>
        <span class="panel-subtext">Real-time status of your submissions under your account</span>
    </div>

    @php
        $hasActivity = ($recentIntentions && $recentIntentions->isNotEmpty()) || ($recentSacraments && $recentSacraments->isNotEmpty());
    @endphp

    @if($hasActivity)
        <div class="activity-list">
            @foreach($recentSacraments as $req)
                <div class="activity-row">
                    <div class="activity-left">
                        <span class="activity-badge badge-sacrament">Sacrament</span>
                        <div>
                            <b>{{ ucfirst($req->service_type) }} Request</b>
                            <small>Preferred Date: {{ \Carbon\Carbon::parse($req->preferred_date)->format('F j, Y') }} &bull; {{ \Carbon\Carbon::parse($req->preferred_time)->format('g:i A') }}</small>
                        </div>
                    </div>
                    <span class="status-pill status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                </div>
            @endforeach

            @foreach($recentIntentions as $intention)
                <div class="activity-row">
                    <div class="activity-left">
                        <span class="activity-badge badge-intention">Mass Intention</span>
                        <div>
                            <b>{{ ucfirst($intention->intention_type) }} Mass Intention ({{ $intention->names }})</b>
                            <small>Requested Date: {{ \Carbon\Carbon::parse($intention->requested_date)->format('F j, Y') }} &bull; {{ $intention->massSchedule?->location ?? 'Shrine' }}</small>
                        </div>
                    </div>
                    <span class="status-pill status-{{ $intention->status }}">{{ ucfirst($intention->status) }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-activity">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#94a3b8" stroke-width="1.8">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <h4>No active requests yet</h4>
            <p>You currently do not have any pending sacrament bookings or Mass intentions. Use the buttons above to submit a new request anytime.</p>
        </div>
    @endif
</div>
@endsection
@push('styles')<style>.portal-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.portal-stats article,.portal-panel{padding:21px;border:1px solid var(--line);border-radius:11px;background:#fff}.portal-stats strong,.portal-stats span{display:block}.portal-stats strong{color:var(--navy);font:700 28px Georgia}.portal-stats span{margin-top:6px;color:var(--muted);font-size:10px}.portal-panel{margin-top:20px}.portal-panel h3{margin:0 0 12px;color:var(--navy);font-family:Georgia,serif}.portal-panel p{display:flex;justify-content:space-between;margin:0;padding:13px 0;border-top:1px solid #edf2f7;font-size:11px}.portal-panel p span{color:var(--muted)}@media(max-width:620px){.portal-stats{grid-template-columns:1fr}.portal-panel p{display:block}.portal-panel p span{display:block;margin-top:4px}}</style>@endpush

@push('styles')
<style>
.welcome {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
}
.welcome h2 {
    margin: 0 0 6px;
    color: var(--navy);
    font: 700 26px Georgia, serif;
}
.welcome p {
    margin: 0;
    color: var(--muted);
    font-size: 12px;
}
.date-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 13px;
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 8px;
    font-size: 11px;
    color: var(--navy);
    font-weight: 600;
}

/* Incomplete Profile Banner */
.profile-incomplete-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    background: #fffbeb;
    border: 1px solid #fef3c7;
    border-left: 4px solid #d97706;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 22px;
}
.banner-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.banner-content strong {
    color: #92400e;
    font-size: 13px;
    display: block;
    margin-bottom: 2px;
}
.banner-content p {
    color: #b45309;
    font-size: 11.5px;
    margin: 0;
}
.btn-complete-profile {
    padding: 8px 16px;
    background: #d97706;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 6px;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s ease;
}
.btn-complete-profile:hover {
    background: #b45309;
}

/* Stats Cards */
.portal-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 22px;
}
.stat-card {
    padding: 18px;
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #ffffff;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(6, 47, 120, 0.08);
    border-color: #cbd5e1;
}
.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    margin-bottom: 12px;
}
.stat-icon-blue { background: #eff6ff; color: #2563eb; }
.stat-icon-gold { background: #fefce8; color: #ca8a04; }
.stat-icon-navy { background: #eaf2fb; color: var(--navy); }
.stat-icon-green { background: #f0fdf4; color: #16a34a; }
.stat-card strong {
    color: var(--navy);
    font: 700 28px Georgia, serif;
    display: block;
    line-height: 1;
}
.stat-card span {
    margin-top: 8px;
    color: var(--muted);
    font-size: 11px;
    font-weight: 600;
}

/* Quick Actions Bar */
.quick-actions-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.actions-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-right: 4px;
}
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 15px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-action-gold {
    background: #d6aa3e;
    color: #ffffff;
}
.btn-action-gold:hover {
    background: #c29528;
}
.btn-action-navy {
    background: var(--navy);
    color: #ffffff;
}
.btn-action-navy:hover {
    background: #042054;
}
.btn-action-outline {
    background: #ffffff;
    border: 1px solid var(--line);
    color: var(--ink);
}
.btn-action-outline:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}

/* Portal Panel */
.portal-panel {
    padding: 24px;
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--line);
}
.panel-header h3 {
    margin: 0;
    color: var(--navy);
    font: 700 18px Georgia, serif;
}
.panel-subtext {
    color: var(--muted);
    font-size: 11px;
}

.activity-list {
    display: flex;
    flex-direction: column;
}
.activity-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 0;
    border-bottom: 1px solid #f1f5f9;
}
.activity-row:last-child {
    border-bottom: none;
}
.activity-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.activity-badge {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.badge-sacrament { background: #eff6ff; color: #1d4ed8; }
.badge-intention { background: #fefce8; color: #a16207; }
.activity-left b {
    display: block;
    color: var(--ink);
    font-size: 12.5px;
}
.activity-left small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    margin-top: 2px;
}
.status-pill {
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: capitalize;
}
.status-pending { background: #fef3c7; color: #92400e; }
.status-confirmed, .status-approved { background: #dcfce7; color: #166534; }
.status-completed { background: #e0f2fe; color: #0369a1; }
.status-cancelled, .status-rejected { background: #fee2e2; color: #991b1b; }

.empty-activity {
    text-align: center;
    padding: 36px 18px;
}
.empty-icon {
    margin-bottom: 12px;
}
.empty-activity h4 {
    margin: 0 0 6px;
    color: var(--ink);
    font-size: 15px;
}
.empty-activity p {
    margin: 0 auto;
    max-width: 440px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}

@media(max-width: 960px) {
    .portal-stats { grid-template-columns: repeat(2, 1fr); }
}
@media(max-width: 620px) {
    .portal-stats { grid-template-columns: 1fr; }
    .welcome { display: block; }
    .date-badge { margin-top: 10px; }
    .profile-incomplete-banner { flex-direction: column; align-items: flex-start; }
    .activity-row { flex-direction: column; align-items: flex-start; gap: 8px; }
}
</style>
@endpush