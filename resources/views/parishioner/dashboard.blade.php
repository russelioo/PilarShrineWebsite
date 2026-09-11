@extends('layouts.parishioner')
@section('title', 'Parishioner Dashboard')

@section('content')
<div class="dashboard-page">
    <!-- Welcome Header -->
    <section class="welcome-card">
        <div class="welcome-copy">
            <span class="welcome-eyebrow">Pilar Shrine &bull; Parishioner Portal</span>
            <h2>{{ $greeting ?? 'Welcome back, ' . ($user->displayName ?? 'Parishioner') . '!' }}</h2>
            <p>Welcome to your private parish portal. Track your sacramental bookings, mass intentions, and pastoral transactions.</p>
        </div>
        <div class="welcome-date-badge">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>{{ now()->setTimezone('Asia/Manila')->format('l, F j, Y') }}</span>
        </div>
    </section>

    {{-- Incomplete Profile Alert Banner --}}
    @if(!($user->isProfileComplete()))
    <div class="profile-incomplete-banner" role="alert">
        <div class="banner-content">
            <div class="banner-icon-circle">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div>
                <strong>Parish profile is incomplete</strong>
                <p>Please complete your birth date, contact number, and registered residence address to activate all certificate requests and sacramental bookings.</p>
            </div>
        </div>
        <a href="{{ route('parishioner.profile-settings') }}" class="btn-complete-profile">Complete Profile</a>
    </div>
    @endif

    <!-- Quick Stats Grid (4 Metric Cards) -->
    <div class="portal-stats-grid">
        <!-- 1. Sacrament Requests (Disabled / Soon) -->
        <a href="javascript:void(0)" class="stat-card stat-card-disabled" data-disabled-feature="true" data-feature-name="Sacrament Requests" title="This feature will be available soon">
            <span class="stat-card-badge">Soon</span>
            <div class="stat-card-header">
                <div class="stat-icon stat-icon-blue">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2ZM14 3v5h5M8 13h8M8 17h6"/>
                    </svg>
                </div>
            </div>
            <strong class="stat-count">{{ $activeSacramentsCount ?? 0 }}</strong>
            <span class="stat-title">Active Sacrament Requests</span>
        </a>

        <!-- 2. Mass Intentions -->
        <a href="{{ route('parishioner.mass-intentions') }}" class="stat-card stat-card-interactive" title="View your active Mass Intentions">
            <div class="stat-card-header">
                <div class="stat-icon stat-icon-gold">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>
                    </svg>
                </div>
                <span class="stat-arrow">&rarr;</span>
            </div>
            <strong class="stat-count">{{ $activeMassIntentionsCount ?? 0 }}</strong>
            <span class="stat-title">Active Mass Intentions</span>
        </a>

        <!-- 3. Total Ongoing Submissions -->
        <a href="{{ route('parishioner.inquiries') }}" class="stat-card stat-card-interactive" title="View ongoing requests and inquiries">
            <div class="stat-card-header">
                <div class="stat-icon stat-icon-navy">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </div>
                <span class="stat-arrow">&rarr;</span>
            </div>
            <strong class="stat-count">{{ $totalActiveRequests ?? 0 }}</strong>
            <span class="stat-title">Total Ongoing Submissions</span>
        </a>

        <!-- 4. Scheduled Mass Times -->
        <a href="/#/schedule" class="stat-card stat-card-interactive" title="View official parish Mass schedules on the website">
            <div class="stat-card-header">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>
                </div>
                <span class="stat-arrow">&rarr;</span>
            </div>
            <strong class="stat-count">{{ $upcomingMassCount ?? 0 }}</strong>
            <span class="stat-title">Scheduled Mass Times</span>
        </a>
    </div>

    <!-- Quick Action Shortcuts Bar -->
    <div class="quick-actions-bar">
        <span class="actions-label">Parish Services:</span>
        <div class="actions-buttons">
            <a href="{{ route('parishioner.ministries') }}" class="btn-action btn-action-outline">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Ministries</span>
            </a>
            <a href="{{ route('parishioner.messages-inquiries') }}" class="btn-action btn-action-outline">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Messages / Inquiries</span>
            </a>
            <a href="{{ route('parishioner.profile-settings') }}" class="btn-action btn-action-outline">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Profile &amp; Settings</span>
            </a>
        </div>
    </div>

    <!-- Main Dashboard Grid (Recent Activity + Parish Overview) -->
    <div class="dashboard-main-grid">
        <!-- Left Column: Activity & My Ministries -->
        <div class="dashboard-left-col">
            <!-- Recent Submissions & Activity -->
            <div class="activity-panel">
            <div class="panel-header">
                <div>
                    <h3>Recent Submissions &amp; Activity</h3>
                    <p class="panel-subtext">Real-time status of your requests and pastoral records</p>
                </div>
                <a href="{{ route('parishioner.mass-intentions') }}" class="panel-view-all">View All &rarr;</a>
            </div>

            @php
                $hasActivity = ($recentIntentions && $recentIntentions->isNotEmpty()) || ($recentSacraments && $recentSacraments->isNotEmpty());
            @endphp

            @if($hasActivity)
                <div class="activity-list">
                    @foreach($recentSacraments as $req)
                        <div class="activity-item">
                            <div class="activity-meta">
                                <span class="badge-type badge-sacrament">Sacrament</span>
                                <div class="activity-text">
                                    <strong>{{ ucfirst($req->service_type) }} Request</strong>
                                    <small>{{ \Carbon\Carbon::parse($req->preferred_date)->format('F j, Y') }} at {{ \Carbon\Carbon::parse($req->preferred_time)->format('g:i A') }} &bull; {{ $req->timeSlot?->massSchedule?->location ?? 'Parish Office' }}</small>
                                </div>
                            </div>
                            <span class="status-pill status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                        </div>
                    @endforeach

                    @foreach($recentIntentions as $intention)
                        <div class="activity-item">
                            <div class="activity-meta">
                                <span class="badge-type badge-intention">Mass Intention</span>
                                <div class="activity-text">
                                    <strong>{{ ucfirst($intention->intention_type) }} Mass Intention ({{ $intention->names }})</strong>
                                    <small>{{ \Carbon\Carbon::parse($intention->requested_date)->format('F j, Y') }} &bull; {{ $intention->massSchedule?->location ?? 'Main Shrine Church' }}</small>
                                </div>
                            </div>
                            <span class="status-pill status-{{ $intention->status }}">{{ ucfirst($intention->status) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-activity-state">
                    <div class="empty-icon-circle">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <h4>No active requests yet</h4>
                    <p>You currently do not have any pending sacrament bookings or Mass intentions. Use the parish services shortcuts above or visit the parish office to submit a request.</p>
                </div>
            @endif
        </div>

        <!-- Left Column: My Ministries Widget -->
        <div class="activity-panel">
            <div class="panel-header">
                <div class="panel-title-group">
                    <span class="panel-eyebrow">Parish Apostolates</span>
                    <h3>My Ministries</h3>
                </div>
                <div class="ministry-header-badges">
                    <span class="badge-active-pill">{{ $activeMinistriesCount ?? 0 }} Active</span>
                    @if(($pendingMinistriesCount ?? 0) > 0)
                        <span class="badge-pending-pill">{{ $pendingMinistriesCount }} Pending</span>
                    @endif
                </div>
            </div>

            <div class="panel-body" style="padding: 12px 18px;">
                @if(isset($userMinistryMemberships) && $userMinistryMemberships->isNotEmpty())
                    <div class="widget-ministries-list">
                        @foreach($userMinistryMemberships as $mMember)
                            <div class="widget-ministry-row">
                                <div class="widget-row-left">
                                    <span class="widget-icon-box">{{ $mMember->ministry->icon ?: '✝' }}</span>
                                    <div>
                                        <strong class="widget-name">{{ $mMember->ministry->name }}</strong>
                                        <small class="widget-sub">{{ $mMember->ministry->meeting_schedule ?: 'Parish Apostolate' }}</small>
                                    </div>
                                </div>
                                <div class="widget-row-right">
                                    @if($mMember->status === 'approved')
                                        <span class="badge-status-sm badge-act">✓ Active</span>
                                    @elseif($mMember->status === 'pending')
                                        <span class="badge-status-sm badge-pend">🟡 Pending</span>
                                    @elseif($mMember->status === 'rejected')
                                        <span class="badge-status-sm badge-rej">✕ Not Approved</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="widget-empty-box">
                        <p>You have not requested to join any parish ministries yet.</p>
                        <small>Join our vibrant choir, altar servers, youth, or caritas outreach teams.</small>
                    </div>
                @endif
            </div>

            <div class="panel-footer">
                <a href="{{ route('parishioner.ministries') }}" class="link-view-all">
                    <span>Discover &amp; Manage Ministries</span> &rarr;
                </a>
            </div>
        </div>
    </div>

        <!-- Right: Official Website Schedule & Office Hours -->
        <div class="side-info-panel">
            <!-- 1. Official Parish Office Hours (from website) -->
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon icon-gold">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h4>Parish Office Hours</h4>
                </div>
                <div class="info-card-body">
                    <div class="schedule-row">
                        <span class="schedule-label">Monday, Wednesday &ndash; Saturday</span>
                        <div class="schedule-time-block">
                            <strong>8:00 AM &ndash; 11:30 AM</strong>
                            <strong>1:00 PM &ndash; 5:00 PM</strong>
                        </div>
                    </div>
                    <div class="schedule-row">
                        <span class="schedule-label">Sunday</span>
                        <strong>8:30 AM &ndash; 12:00 NN</strong>
                    </div>
                    <div class="schedule-row">
                        <span class="schedule-label">Tuesday &amp; Holidays</span>
                        <span class="badge-closed">Closed (Day Off)</span>
                    </div>
                </div>
                <div class="info-card-footer">
                    <small>For Mass intentions, sacraments, and certificate inquiries, kindly visit during official office hours.</small>
                </div>
            </div>

            <!-- 2. Official Mass Schedules (Modernized UI) -->
            <div class="info-card schedule-modern-card">
                <div class="info-card-header">
                    <div class="info-card-icon icon-navy">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div>
                        <h4>Official Mass Schedules</h4>
                        <span class="info-card-subtitle">Liturgical Celebrations</span>
                    </div>
                </div>
                <div class="info-card-body sched-card-body">
                    <!-- Sunday Masses -->
                    <div class="modern-sched-group">
                        <div class="group-header">
                            <div class="group-title-box">
                                <span class="group-dot dot-sunday"></span>
                                <strong>Sunday Holy Mass</strong>
                            </div>
                            <span class="sched-pill pill-sunday">Lord's Day</span>
                        </div>
                        <div class="sched-slots-container">
                            <div class="sched-slot-row">
                                <div class="slot-time-chip">5:00 AM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Early Morning Mass</span>
                                </div>
                            </div>
                            <div class="sched-slot-row">
                                <div class="slot-time-chip">7:30 AM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Morning Holy Mass</span>
                                </div>
                                <span class="live-pulse-badge">
                                    <span class="pulse-dot"></span> FB Live
                                </span>
                            </div>
                            <div class="sched-slot-row">
                                <div class="slot-time-chip">5:00 PM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Afternoon Holy Mass</span>
                                </div>
                                <span class="live-pulse-badge">
                                    <span class="pulse-dot"></span> FB Live
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Mass -->
                    <div class="modern-sched-group">
                        <div class="group-header">
                            <div class="group-title-box">
                                <span class="group-dot dot-daily"></span>
                                <strong>Daily Mass</strong>
                            </div>
                            <span class="sched-pill pill-daily">Mon &ndash; Sat</span>
                        </div>
                        <div class="sched-slots-container">
                            <div class="sched-slot-row">
                                <div class="slot-time-chip">5:00 PM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Monday &amp; Wednesday</span>
                                </div>
                            </div>
                            <div class="sched-slot-row">
                                <div class="slot-time-chip">6:00 AM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Tuesday, Thursday &amp; Friday</span>
                                </div>
                            </div>
                            <div class="sched-slot-row">
                                <div class="slot-time-chip">6:00 AM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Saturday Morning</span>
                                </div>
                            </div>
                            <div class="sched-slot-row slot-highlight">
                                <div class="slot-time-chip chip-gold">5:00 PM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Anticipated Sunday Mass</span>
                                    <span class="slot-subnote">Saturday Afternoon</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Devotions & Confession -->
                    <div class="modern-sched-group">
                        <div class="group-header">
                            <div class="group-title-box">
                                <span class="group-dot dot-devotion"></span>
                                <strong>Devotions &amp; Confession</strong>
                            </div>
                            <span class="sched-pill pill-devotion">Monthly</span>
                        </div>
                        <div class="sched-slots-container">
                            <div class="sched-slot-row">
                                <div class="slot-time-chip">5:00 PM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Sacrament of Reconciliation</span>
                                    <span class="slot-subnote">Every 1st Thursday of the Month</span>
                                </div>
                            </div>
                            <div class="sched-slot-row">
                                <div class="slot-time-chip chip-marian">5:00 PM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Holy Mass &bull; Our Lady of the Pillar</span>
                                    <span class="slot-subnote">Every 12th of the Month</span>
                                </div>
                            </div>
                            <div class="sched-slot-row">
                                <div class="slot-time-chip chip-marian">6:00 PM</div>
                                <div class="slot-details">
                                    <span class="slot-name">Marian Candlelight Procession</span>
                                    <span class="slot-subnote">Every 12th of the Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="info-card-footer">
                    <a href="/#/schedule" class="link-schedule-modern" title="View official parish Mass schedules on the website">
                        <span>View Full Liturgical Schedule</span>
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.dashboard-page {
    max-width: 1200px;
    margin: 0 auto;
}

/* Welcome Header Card */
.welcome-card {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--line);
}
.welcome-copy {
    min-width: 0;
}
.welcome-eyebrow {
    display: inline-block;
    font-size: 9.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--gold);
    margin-bottom: 5px;
}
.welcome-copy h2 {
    margin: 0 0 6px;
    color: var(--navy);
    font: 700 28px Georgia, serif;
    line-height: 1.2;
}
.welcome-copy p {
    margin: 0;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}
.welcome-date-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 8px;
    font-size: 11px;
    color: var(--navy);
    font-weight: 600;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(6, 47, 120, 0.04);
}
.welcome-date-badge svg {
    color: var(--navy);
}

/* Incomplete Profile Alert */
.profile-incomplete-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-left: 4px solid #d97706;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(217, 119, 6, 0.06);
}
.banner-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.banner-icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fef3c7;
    color: #d97706;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.banner-content strong {
    display: block;
    font-size: 13px;
    font-family: Georgia, serif;
    color: #92400e;
    margin-bottom: 2px;
}
.banner-content p {
    margin: 0;
    font-size: 11.5px;
    color: #b45309;
    line-height: 1.45;
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
    transition: background 0.15s ease, transform 0.15s ease;
}
.btn-complete-profile:hover {
    background: #b45309;
    transform: translateY(-1px);
}

/* Stats Overview Grid (4 Cards) */
.portal-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 22px;
}
.stat-card {
    position: relative;
    padding: 20px;
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #ffffff;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.03);
    transition: all 0.2s ease;
}
.stat-card-interactive:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(6, 47, 120, 0.08);
    border-color: #cbd5e1;
}
.stat-card-interactive:hover .stat-arrow {
    transform: translateX(3px);
    color: var(--navy);
}
.stat-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.stat-arrow {
    font-size: 14px;
    color: #94a3b8;
    transition: all 0.15s ease;
}
.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.stat-icon-blue { background: #eff6ff; color: #2563eb; }
.stat-icon-gold { background: #fffbeb; color: #d97706; }
.stat-icon-navy { background: #eaf2fb; color: var(--navy); }
.stat-icon-green { background: #ecfdf5; color: #059669; }

.stat-count {
    font: 700 32px Georgia, serif;
    color: var(--navy);
    line-height: 1;
    margin-bottom: 6px;
}
.stat-title {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: capitalize;
    line-height: 1.35;
}

/* Disabled Stat Card & Soon Badge */
.stat-card.stat-card-disabled {
    opacity: 0.65;
    cursor: not-allowed;
}
.stat-card.stat-card-disabled:hover {
    transform: none;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.03);
    border-color: var(--line);
}
.stat-card-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    background: #fff8e6;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 2px 7px;
    border-radius: 12px;
}

/* Quick Actions Bar */
.quick-actions-bar {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 10px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.02);
}
.actions-label {
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted);
    white-space: nowrap;
}
.actions-buttons {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.btn-action-gold {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
.btn-action-navy {
    background: #eaf2fb;
    color: var(--navy);
    border: 1px solid #ccd8e4;
}
.btn-action-outline {
    background: #ffffff;
    color: var(--navy);
    border: 1px solid var(--line);
}
.btn-action-outline:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.btn-action-disabled {
    opacity: 0.6;
    cursor: not-allowed !important;
}
.btn-badge-soon {
    font-size: 8px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #b45309;
    color: #ffffff;
    padding: 1px 5px;
    border-radius: 10px;
    margin-left: 2px;
}

/* Two-column Main Dashboard Layout */
.dashboard-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 24px;
    align-items: start;
}
.dashboard-left-col {
    display: flex;
    flex-direction: column;
    gap: 24px;
    min-width: 0;
}
@media (max-width: 1080px) {
    .dashboard-main-grid {
        grid-template-columns: 1fr;
    }
}

/* Recent Activity Panel */
.activity-panel {
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.03);
    overflow: hidden;
}
.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #edf2f7;
    background: #fafcff;
}
.panel-header h3 {
    margin: 0;
    font: 700 17px Georgia, serif;
    color: var(--navy);
}
.panel-subtext {
    margin: 3px 0 0;
    font-size: 11px;
    color: var(--muted);
}
.panel-view-all {
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
    text-decoration: none;
    transition: color 0.15s ease;
}
.panel-view-all:hover {
    color: var(--gold);
}

.activity-list {
    display: flex;
    flex-direction: column;
}
.activity-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
}
.activity-item:last-child {
    border-bottom: none;
}
.activity-item:hover {
    background: #f8fafc;
}
.activity-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.activity-text {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.activity-text strong {
    font-size: 12.5px;
    color: var(--navy);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.activity-text small {
    font-size: 10.5px;
    color: var(--muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.badge-type {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    flex-shrink: 0;
}
.badge-sacrament { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.badge-intention { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

.status-pill {
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    text-transform: capitalize;
    flex-shrink: 0;
}
.status-pending { background: #fff8e6; color: #b45309; border: 1px solid #fde68a; }
.status-approved, .status-confirmed, .status-offered { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.status-completed { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

/* Empty Activity State */
.empty-activity-state {
    padding: 48px 24px;
    text-align: center;
}
.empty-icon-circle {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    display: grid;
    place-items: center;
    margin: 0 auto 14px;
}
.empty-activity-state h4 {
    margin: 0 0 6px;
    font: 700 16px Georgia, serif;
    color: var(--navy);
}
.empty-activity-state p {
    margin: 0 auto;
    font-size: 11.5px;
    color: var(--muted);
    max-width: 440px;
    line-height: 1.5;
}

/* Side Info Cards */
.side-info-panel {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.info-card {
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.03);
    overflow: hidden;
}
.info-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafcff;
}
.info-card-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.icon-gold { background: #fffbeb; color: #d97706; }
.icon-navy { background: #eaf2fb; color: var(--navy); }
.info-card-header h4 {
    margin: 0;
    font: 700 14.5px Georgia, serif;
    color: var(--navy);
}
.info-card-body {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.schedule-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    font-size: 11px;
}
.schedule-row span {
    color: var(--muted);
}
.schedule-row strong {
    color: var(--navy);
    font-weight: 700;
}
.info-card-footer {
    padding: 12px 20px;
    border-top: 1px solid #f1f5f9;
    background: #fafcff;
    font-size: 10.5px;
    color: var(--muted);
}
.link-schedule {
    color: var(--navy);
    font-weight: 700;
    text-decoration: none;
    font-size: 11px;
    transition: color 0.15s ease;
}
.link-schedule:hover {
    color: var(--gold);
}

/* Responsive Breakpoints */
@media (max-width: 1050px) {
    .portal-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .dashboard-main-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 650px) {
    .welcome-card {
        flex-direction: column;
        align-items: flex-start;
    }
    .portal-stats-grid {
        grid-template-columns: 1fr;
    }
    .quick-actions-bar {
        flex-direction: column;
        align-items: flex-start;
    }
    .actions-buttons {
        width: 100%;
    }
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

/* Office Hours & Modern Schedule Styling */
.schedule-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
}
.schedule-time-block {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 3px;
}
.schedule-time-block strong {
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
}
.badge-closed {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    background: #fff8e6;
    color: #b45309;
    border: 1px solid #fde68a;
}
.info-card-subtitle {
    font-size: 10.5px;
    color: var(--muted);
    display: block;
    margin-top: 1px;
    font-weight: 500;
}

/* Modern Liturgical Schedule Card Styling */
.sched-card-body {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px 18px;
}
.modern-sched-group {
    background: #ffffff;
    border: 1px solid #e9edf3;
    border-radius: 9px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: 0 1px 3px rgba(6, 47, 120, 0.02);
}
.group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 7px;
    border-bottom: 1px solid #f1f5f9;
}
.group-title-box {
    display: flex;
    align-items: center;
    gap: 6px;
}
.group-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}
.dot-sunday { background: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2); }
.dot-daily { background: #059669; box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2); }
.dot-devotion { background: #d97706; box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.2); }
.group-title-box strong {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--navy);
    letter-spacing: -0.01em;
}
.sched-pill {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 2px 7px;
    border-radius: 12px;
}
.pill-sunday { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
.pill-daily { background: #ecfdf5; color: #047857; border: 1px solid #d1fae5; }
.pill-devotion { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }

.sched-slots-container {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.sched-slot-row {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 6px 8px;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    transition: all 0.15s ease;
}
.sched-slot-row:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 2px 5px rgba(6, 47, 120, 0.04);
}
.slot-highlight {
    background: #fffdf5;
    border-color: #fef08a;
}
.slot-time-chip {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 2px 6px;
    border-radius: 5px;
    min-width: 60px;
    text-align: center;
    flex-shrink: 0;
    line-height: 1.3;
}
.chip-gold {
    background: #fef3c7;
    color: #92400e;
    border-color: #fde68a;
}
.chip-marian {
    background: #eff6ff;
    color: #1e40af;
    border-color: #bfdbfe;
}
.slot-details {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}
.slot-name {
    font-size: 11px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.3;
}
.slot-subnote {
    font-size: 9.5px;
    color: #64748b;
    line-height: 1.2;
    margin-top: 1px;
}
.live-pulse-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 8.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 2px 6px;
    border-radius: 4px;
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
    flex-shrink: 0;
}
.pulse-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #dc2626;
    display: inline-block;
    animation: pulseRed 1.8s infinite;
}
@keyframes pulseRed {
    0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
    70% { box-shadow: 0 0 0 4px rgba(220, 38, 38, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}
.link-schedule-modern {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
    text-decoration: none;
    padding: 7px 12px;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.15s ease;
}
.link-schedule-modern:hover {
    background: #eaf2fb;
    border-color: #cbd5e1;
    color: #021a47;
}

/* Dashboard Left Column & My Ministries Widget */
.dashboard-left-col {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.panel-title-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.panel-eyebrow {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gold);
}
.ministry-header-badges {
    display: flex;
    align-items: center;
    gap: 6px;
}
.badge-active-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.badge-pending-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}
.widget-ministries-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.widget-ministry-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    background: #fafcff;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    transition: all 0.15s ease;
}
.widget-ministry-row:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(6, 47, 120, 0.04);
}
.widget-row-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.widget-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #eaf2fb;
    color: var(--navy);
    display: grid;
    place-items: center;
    font-size: 16px;
    flex-shrink: 0;
}
.widget-name {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.widget-sub {
    display: block;
    font-size: 11px;
    color: #64748b;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.widget-row-right {
    flex-shrink: 0;
}
.badge-status-sm {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.badge-status-sm.badge-act {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.badge-status-sm.badge-pend {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}
.badge-status-sm.badge-rej {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
.widget-empty-box {
    padding: 24px 16px;
    text-align: center;
    color: #64748b;
}
.widget-empty-box p {
    margin: 0 0 4px;
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
}
.widget-empty-box small {
    font-size: 11px;
    color: #94a3b8;
}
.panel-footer {
    padding: 12px 20px;
    border-top: 1px solid #edf2f7;
    background: #fafcff;
    display: flex;
    justify-content: flex-end;
}
.link-view-all {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--navy);
    text-decoration: none;
    transition: color 0.15s ease;
}
.link-view-all:hover {
    color: var(--gold);
}

</style>
@endpush
