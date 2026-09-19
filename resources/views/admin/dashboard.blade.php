@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <!-- Status Flash Notification -->
    @if(session('status'))
        <div class="notice-banner notice-success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- 1. DASHBOARD WELCOME BANNER -->
    <section class="dashboard-welcome-banner" aria-label="Welcome Banner">
        <div class="banner-content">
            <span class="banner-eyebrow">DIOCESAN SHRINE AND PARISH OF OUR LADY OF THE PILLAR</span>
            <h1 class="banner-heading">Welcome back, Admin!</h1>
            @if(auth()->check())
                <div class="banner-user-meta">
                    <span class="banner-user-name">{{ auth()->user()->name }}</span>
                    <span class="banner-user-pill">{{ auth()->user()->position ?: auth()->user()->role_badge_label }}</span>
                    <span class="banner-user-pill pill-org">{{ auth()->user()->organization_label }}</span>
                </div>
            @endif
            <p class="banner-lead">Let us continue to serve with faith, hope, and love.</p>
            <blockquote class="banner-scripture">
                “For where two or three are gathered in my name, there am I with them.”
                <cite>— Matthew 18:20</cite>
            </blockquote>
        </div>
        <div class="banner-date-widget" aria-label="Current Date">
            <div class="date-icon-circle" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            </div>
            <div class="date-meta">
                <span class="date-day">{{ now()->timezone('Asia/Manila')->format('l') }}</span>
                <strong class="date-full">{{ now()->timezone('Asia/Manila')->format('F j, Y') }}</strong>
                <small class="date-liturgical">Diocese of Sorsogon</small>
            </div>
        </div>
    </section>

    <!-- 1.5 MY ORGANIZATIONS SWITCHER -->
    @if(isset($userOrganizations) && count($userOrganizations) > 0)
    <section class="org-switcher-section" aria-label="My Organizations Switcher">
        <div class="org-switcher-card">
            <div class="org-switcher-header">
                <div class="org-switcher-title-group">
                    <span class="org-switcher-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        MY ORGANIZATIONS
                    </span>
                    <span class="org-switcher-hint">Switch between your connected commissions, ministries, and parish roles:</span>
                </div>
                @if(isset($currentOrgDetails) && $currentOrgDetails)
                    <div class="current-context-tag">
                        Viewing: <strong>{{ $currentOrgDetails['name'] }}</strong> <span class="tag-role">({{ $currentOrgDetails['role'] }})</span>
                    </div>
                @endif
            </div>
            <div class="org-switcher-tabs">
                @foreach($userOrganizations as $uOrg)
                    <a href="{{ route('admin.dashboard', ['org' => $uOrg['id']]) }}" class="org-switcher-tab {{ $uOrg['active'] ? 'active' : '' }}">
                        <span class="org-tab-dot org-dot-{{ $uOrg['type'] }}"></span>
                        <div class="org-tab-text">
                            <span class="org-tab-name">{{ $uOrg['name'] }}</span>
                            <span class="org-tab-role">{{ $uOrg['role'] }}</span>
                        </div>
                        @if($uOrg['active'])
                            <span class="org-tab-active-check">✓</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 2. KPI / STATISTICS CARDS -->
    <section class="kpi-grid" aria-label="Key Performance Indicators">
        <!-- Card 1: Parishioners -->
        <a href="{{ $stats[0]['route'] }}" class="kpi-card" aria-label="View Parishioners">
            <div class="kpi-top">
                <div class="kpi-icon-wrap icon-parishioners" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <span class="kpi-trend trend-up">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    {{ $stats[0]['change'] }}
                </span>
            </div>
            <div class="kpi-body">
                <strong class="kpi-value">{{ $stats[0]['value'] }}</strong>
                <span class="kpi-label">{{ $stats[0]['label'] }}</span>
            </div>
            <div class="kpi-footer">
                <svg class="kpi-sparkline" viewBox="0 0 80 20" aria-hidden="true">
                    <path d="M0 16 Q 15 12, 30 14 T 60 7 T 80 4" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="kpi-action-link" aria-hidden="true">
                    <span>Manage</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </span>
            </div>
        </a>

        <!-- Card 2: Parish Ministries -->
        <a href="{{ $stats[1]['route'] }}" class="kpi-card" aria-label="View Parish Ministries">
            <div class="kpi-top">
                <div class="kpi-icon-wrap icon-ministries" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <span class="kpi-trend trend-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    {{ $stats[1]['change'] }}
                </span>
            </div>
            <div class="kpi-body">
                <strong class="kpi-value">{{ $stats[1]['value'] }}</strong>
                <span class="kpi-label">{{ $stats[1]['label'] }}</span>
            </div>
            <div class="kpi-footer">
                <svg class="kpi-sparkline" viewBox="0 0 80 20" aria-hidden="true">
                    <path d="M0 14 Q 20 18, 40 10 T 65 6 T 80 3" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="kpi-action-link" aria-hidden="true">
                    <span>Manage</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </span>
            </div>
        </a>

        <!-- Card 3: Announcements -->
        <a href="{{ $stats[2]['route'] }}" class="kpi-card" aria-label="View Announcements">
            <div class="kpi-top">
                <div class="kpi-icon-wrap icon-announcements" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <span class="kpi-trend trend-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    {{ $stats[2]['change'] }}
                </span>
            </div>
            <div class="kpi-body">
                <strong class="kpi-value">{{ $stats[2]['value'] }}</strong>
                <span class="kpi-label">{{ $stats[2]['label'] }}</span>
            </div>
            <div class="kpi-footer">
                <svg class="kpi-sparkline" viewBox="0 0 80 20" aria-hidden="true">
                    <path d="M0 6 Q 20 4, 40 12 T 60 9 T 80 15" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="kpi-action-link" aria-hidden="true">
                    <span>Bulletins</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </span>
            </div>
        </a>

        <!-- Card 4: Donations -->
        <a href="{{ $stats[3]['route'] }}" class="kpi-card" aria-label="View Donations">
            <div class="kpi-top">
                <div class="kpi-icon-wrap icon-donations" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <span class="kpi-trend trend-up">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    {{ $stats[3]['change'] }}
                </span>
            </div>
            <div class="kpi-body">
                <strong class="kpi-value">{{ $stats[3]['value'] }}</strong>
                <span class="kpi-label">{{ $stats[3]['label'] }}</span>
            </div>
            <div class="kpi-footer">
                <svg class="kpi-sparkline" viewBox="0 0 80 20" aria-hidden="true">
                    <path d="M0 16 Q 20 14, 45 11 T 65 8 T 80 3" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="kpi-action-link" aria-hidden="true">
                    <span>Finance</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </span>
            </div>
        </a>
    </section>

    <!-- 3. MAIN DASHBOARD CONTENT GRID (Left Main + Right Sidebar) -->
    <div class="dashboard-main-grid">
        <!-- LEFT / MAIN: Recent Requests -->
        <section class="dashboard-card recent-requests-card" aria-label="Recent Requests Section">
            <div class="card-header-bar">
                <div>
                    <h2 class="card-title">Recent requests</h2>
                    <p class="card-subtitle">Parish ministry applications and volunteer requests</p>
                </div>
                <a href="{{ route('admin.ministry-requests') }}" class="view-all-link">
                    <span>View all</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>

            <!-- Modern Table (Desktop View) -->
            <div class="table-responsive-desktop">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th scope="col">REQUEST</th>
                            <th scope="col">REQUESTER</th>
                            <th scope="col">TYPE</th>
                            <th scope="col">DATE</th>
                            <th scope="col">STATUS</th>
                            <th scope="col" class="text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $req)
                            <tr>
                                <td class="request-cell">
                                    <div class="request-icon-badge {{ $req['type_class'] }}">
                                        @if($req['type'] === 'Baptism')
                                            💧
                                        @elseif($req['type'] === 'Wedding')
                                            💍
                                        @elseif($req['type'] === 'Certificate')
                                            📜
                                        @elseif($req['type'] === 'Ministry')
                                            🤝
                                        @elseif($req['type'] === 'Appointment')
                                            📅
                                        @elseif($req['type'] === 'Donation')
                                            ₱
                                        @else
                                            🕯
                                        @endif
                                    </div>
                                    <div>
                                        <strong class="request-name">{{ $req['title'] }}</strong>
                                        <small class="request-code">{{ $req['ref'] ?? $req['ref_code'] ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="requester-name">{{ $req['requester'] }}</strong>
                                    <small class="requester-email">{{ $req['email'] ?? 'Parishioner' }}</small>
                                </td>
                                <td>
                                    <span class="type-pill {{ $req['type_class'] }}">
                                        {{ $req['type'] }}
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <span>{{ $req['date'] }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $req['status_class'] }}">
                                        {{ $req['status'] }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="table-action-menu">
                                        <a href="{{ $req['url'] ?? $req['action_url'] ?? '#' }}" class="action-btn-sm" title="View details">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state-cell">No requests recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Requests Cards (Mobile View) -->
            <div class="requests-mobile-list">
                @forelse($recentRequests as $req)
                    <article class="mobile-request-card">
                        <div class="mobile-req-header">
                            <span class="type-pill {{ $req['type_class'] }}">{{ $req['type'] }}</span>
                            <span class="status-pill {{ $req['status_class'] }}">{{ $req['status'] }}</span>
                        </div>
                        <strong class="mobile-req-title">{{ $req['title'] }}</strong>
                        <div class="mobile-req-meta">
                            <span>👤 {{ $req['requester'] }}</span>
                            <span>🕒 {{ $req['date'] }}</span>
                        </div>
                        <div class="mobile-req-footer">
                            <a href="{{ $req['url'] ?? $req['action_url'] ?? '#' }}" class="btn btn-outline btn-sm">Review Request →</a>
                        </div>
                    </article>
                @empty
                    <div class="empty-state-notice">
                        <p>No recent requests recorded yet.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- RIGHT: Active Liturgy & Notices -->
        <div class="dashboard-sidebar-column">
            <!-- Latest Announcements Card -->
            <section class="dashboard-card announcements-card" aria-label="Latest Parish Announcements">
                <div class="card-header-bar">
                    <div>
                        <h2 class="card-title">Announcements</h2>
                        <p class="card-subtitle">Published bulletins &amp; shrine notices</p>
                    </div>
                    <a href="{{ route('admin.announcements') }}" class="view-all-link">
                        <span>View all</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="announcements-summary-list">
                    @forelse($latestAnnouncements as $announcement)
                        <div class="announcement-summary-item">
                            <div class="announcement-meta-top">
                                <span class="badge-tag">{{ $announcement->category ?? 'General' }}</span>
                                @if($announcement->is_pinned)
                                    <span class="pinned-tag">📌 Pinned</span>
                                @endif
                                <span class="announcement-date">
                                    {{ $announcement->published_at ? $announcement->published_at->format('M d, Y') : 'Recent' }}
                                </span>
                            </div>
                            <strong class="announcement-headline">{{ $announcement->title }}</strong>
                        </div>
                    @empty
                        <div class="empty-state-notice">
                            <p>No published bulletins yet.</p>
                            <a href="{{ route('admin.announcements') }}" class="btn btn-outline btn-sm">+ Post Notice</a>
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Active Mass Schedules Card -->
            <section class="dashboard-card mass-schedules-card" aria-label="Mass Schedules Timeline">
                <div class="card-header-bar">
                    <div>
                        <h2 class="card-title">Mass schedules</h2>
                        <p class="card-subtitle">Weekly liturgical calendar</p>
                    </div>
                    <a href="{{ route('admin.mass-schedules') }}" class="view-all-link">
                        <span>Manage</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="events-timeline">
                    @forelse($activeSchedules as $sched)
                        <div class="timeline-item">
                            <div class="date-block" aria-hidden="true">
                                <span class="date-month">{{ strtoupper(substr($sched->day_of_week ?? 'SUN', 0, 3)) }}</span>
                                <strong class="date-number">{{ date('g:i', strtotime($sched->start_time)) }}</strong>
                            </div>
                            <div class="timeline-details">
                                <span class="event-category">{{ date('A', strtotime($sched->start_time)) }} &bull; {{ $sched->location ?? 'Main Church' }}</span>
                                <strong class="event-title">{{ $sched->title }}</strong>
                                <div class="event-submeta">
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        {{ $sched->priest_in_charge ?? 'Parish Priest' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-notice">
                            <p>No active schedules configured.</p>
                            <a href="{{ route('admin.mass-schedules') }}" class="btn btn-outline btn-sm">+ Add Schedule</a>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <!-- 4. QUICK ACTIONS BAR -->
    <section class="quick-actions-section" aria-label="Administrator Quick Actions">
        <div class="quick-actions-header">
            <h3 class="quick-actions-title">Quick Administrative Actions</h3>
            <span class="quick-actions-hint">Fast shortcuts to common parish management tasks</span>
        </div>
        <div class="quick-actions-grid">
            <a href="{{ route('admin.ministries') }}" class="quick-action-card">
                <div class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="qa-text">
                    <strong>+ Manage Ministries</strong>
                    <small>Apostolates &amp; leaders</small>
                </div>
            </a>

            <a href="{{ route('admin.announcements') }}" class="quick-action-card">
                <div class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
                <div class="qa-text">
                    <strong>✦ Post notice</strong>
                    <small>Publish parish bulletin</small>
                </div>
            </a>

            <a href="{{ route('admin.mass-schedules') }}" class="quick-action-card">
                <div class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="qa-text">
                    <strong>▣ Mass schedule</strong>
                    <small>Update liturgy hours</small>
                </div>
            </a>

            <a href="{{ route('admin.parishioners') }}" class="quick-action-card">
                <div class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" y1="8" x2="19" y2="14"></line>
                        <line x1="22" y1="11" x2="16" y2="11"></line>
                    </svg>
                </div>
                <div class="qa-text">
                    <strong>♟ Add member</strong>
                    <small>Register parishioner</small>
                </div>
            </a>
        </div>
    </section>
@endsection

@push('styles')
<style>
    /* ===== 1. Welcome Banner ===== */
    .dashboard-welcome-banner {
        position: relative;
        border-radius: var(--radius-xl);
        overflow: hidden;
        padding: 34px 38px;
        margin-bottom: 28px;
        background: 
            linear-gradient(135deg, rgba(6, 47, 120, 0.94) 0%, rgba(4, 28, 73, 0.92) 100%),
            url('/images/pilar-shrine-sanctuary.jpg') center/cover no-repeat;
        box-shadow: 0 10px 30px rgba(6, 47, 120, 0.16);
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 32px;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .banner-content {
        max-width: 680px;
        position: relative;
        z-index: 2;
    }

    .banner-eyebrow {
        display: inline-block;
        color: #f7d27e;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .banner-heading {
        margin: 0 0 6px;
        font-family: var(--font-heading);
        font-size: 28px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.01em;
    }

    /* Banner user meta */
    .banner-user-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .banner-user-name {
        font-size: 13px;
        font-weight: 700;
        color: #ffffff;
    }
    .banner-user-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #e2e8f0;
    }
    .banner-user-pill.pill-org {
        background: rgba(216, 170, 60, 0.22);
        border-color: rgba(216, 170, 60, 0.45);
        color: #fde68a;
    }

    /* ===== My Organizations Switcher ===== */
    .org-switcher-section {
        margin-bottom: 24px;
    }
    .org-switcher-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .org-switcher-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .org-switcher-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .org-switcher-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef4ff;
        color: #1d4ed8;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        padding: 3px 10px;
        border-radius: 6px;
    }
    .org-switcher-hint {
        font-size: 12px;
        color: var(--muted);
    }
    .current-context-tag {
        font-size: 12px;
        color: #334155;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .current-context-tag strong {
        color: var(--navy);
    }
    .current-context-tag .tag-role {
        color: #64748b;
    }
    .org-switcher-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .org-switcher-tab {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-radius: 9px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        text-decoration: none;
        transition: all 0.18s ease;
        color: #1e293b;
    }
    .org-switcher-tab:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .org-switcher-tab.active {
        background: #eff6ff;
        border-color: #93c5fd;
        box-shadow: 0 2px 6px rgba(29, 78, 216, 0.08);
    }
    .org-tab-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .org-dot-parish { background: #9d174d; }
    .org-dot-commission { background: #2563eb; }
    .org-dot-ministry { background: #16a34a; }
    .org-tab-text {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
    }
    .org-tab-name {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
    }
    .org-switcher-tab.active .org-tab-name {
        color: #1d4ed8;
    }
    .org-tab-role {
        font-size: 10px;
        color: #64748b;
    }
    .org-tab-active-check {
        font-size: 12px;
        font-weight: 800;
        color: #1d4ed8;
    }

    .banner-lead {
        margin: 0 0 14px;
        color: #d8e6f8;
        font-size: 13.5px;
        font-weight: 500;
    }

    .banner-scripture {
        margin: 0;
        padding-left: 14px;
        border-left: 3px solid var(--gold);
        font-style: italic;
        color: #e2eefe;
        font-size: 12.5px;
        line-height: 1.5;
    }

    .banner-scripture cite {
        display: block;
        margin-top: 3px;
        font-style: normal;
        font-weight: 700;
        font-size: 11px;
        color: #f4cf77;
    }

    .banner-date-widget {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 22px;
        border-radius: var(--radius-lg);
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        flex-shrink: 0;
    }

    .date-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(216, 170, 60, 0.22);
        border: 1px solid rgba(216, 170, 60, 0.45);
        color: #fce198;
        display: grid;
        place-items: center;
    }

    .date-icon-circle svg {
        width: 22px;
        height: 22px;
    }

    .date-meta {
        display: flex;
        flex-direction: column;
    }

    .date-day {
        color: #f7d27e;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .date-full {
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
    }

    .date-liturgical {
        color: #c9def5;
        font-size: 10px;
        margin-top: 1px;
    }

    /* ===== 2. KPI Cards Grid ===== */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    .kpi-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 22px 24px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        text-decoration: none;
        color: inherit;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        border-color: #cbd5e1;
    }

    .kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .kpi-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
    }

    .kpi-icon-wrap svg {
        width: 20px;
        height: 20px;
    }

    .icon-parishioners { background: #eff6ff; color: #1d4ed8; }
    .icon-ministries { background: #fef3c7; color: #b45309; }
    .icon-announcements { background: #eff6ff; color: #1e40af; }
    .icon-requests { background: #fef3c7; color: #b45309; }
    .icon-events { background: #f0fdf4; color: #15803d; }
    .icon-donations { background: #fdf2f8; color: #be185d; }

    .kpi-trend {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .kpi-trend svg {
        width: 12px;
        height: 12px;
    }

    .trend-up { background: #dcfce7; color: #166534; }
    .trend-amber { background: #fef3c7; color: #92400e; }
    .trend-blue { background: #dbeafe; color: #1e40af; }

    .kpi-body {
        margin-bottom: 16px;
    }

    .kpi-value {
        display: block;
        font-family: var(--font-heading);
        font-size: 27px;
        font-weight: 700;
        color: var(--navy);
        line-height: 1.15;
    }

    .kpi-label {
        display: block;
        margin-top: 5px;
        color: var(--muted);
        font-size: 11.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .kpi-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid var(--border-subtle);
    }

    .kpi-sparkline {
        width: 70px;
        height: 18px;
    }

    .kpi-action-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        color: var(--blue);
    }

    .kpi-action-link svg {
        width: 14px;
        height: 14px;
        transition: transform 0.18s ease;
    }

    .kpi-card:hover .kpi-action-link svg {
        transform: translateX(3px);
    }

    /* ===== 3. Main Dashboard Grid ===== */
    .dashboard-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(330px, 0.95fr);
        gap: 24px;
        margin-bottom: 28px;
        align-items: start;
    }

    .dashboard-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 24px 26px;
    }

    .card-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .card-title {
        margin: 0;
        font-family: var(--font-heading);
        font-size: 18px;
        font-weight: 700;
        color: var(--navy);
        letter-spacing: -0.01em;
    }

    .card-subtitle {
        margin: 3px 0 0;
        color: var(--muted);
        font-size: 11.5px;
    }

    .view-all-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--blue);
        text-decoration: none;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background 0.15s ease;
    }

    .view-all-link:hover {
        background: var(--blue-subtle);
    }

    .view-all-link svg {
        width: 14px;
        height: 14px;
    }

    /* Recent Requests Table */
    .table-responsive-desktop {
        overflow-x: auto;
    }

    .requests-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .requests-table th {
        background: #f8fafc;
        color: var(--muted);
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 10px 14px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .requests-table td {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border-subtle);
        vertical-align: middle;
    }

    .requests-table tbody tr:last-child td {
        border-bottom: none;
    }

    .requests-table tbody tr:hover {
        background: #fbfdff;
    }

    .request-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .request-icon-badge {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .request-name {
        display: block;
        font-weight: 700;
        color: var(--ink);
        font-size: 12.5px;
    }

    .request-code {
        color: var(--muted);
        font-size: 10px;
    }

    .requester-name {
        display: block;
        color: var(--navy);
        font-weight: 600;
    }

    .requester-email {
        color: var(--muted);
        font-size: 10.5px;
    }

    .date-cell {
        color: var(--muted);
        font-size: 11.5px;
        white-space: nowrap;
    }

    /* Badge & Status Styles */
    .type-pill {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .type-baptism { background: #e0f2fe; color: #0369a1; }
    .type-intention { background: #fef3c7; color: #b45309; }
    .type-wedding { background: #f3e8ff; color: #7e22ce; }
    .type-certificate { background: #ccfbf1; color: #0f766e; }
    .type-appointment { background: #e0e7ff; color: #4338ca; }

    .status-pill {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .status-pending { background: #fef3c7; color: #b45309; }
    .status-approved { background: #dcfce7; color: #15803d; }
    .status-processing { background: #dbeafe; color: #1d4ed8; }
    .status-rejected { background: #fee2e2; color: #b91c1c; }

    .action-btn-sm {
        display: inline-block;
        padding: 5px 11px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: #ffffff;
        color: var(--blue);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .action-btn-sm:hover {
        background: var(--blue);
        color: #ffffff;
        border-color: var(--blue);
    }

    .text-right {
        text-align: right;
    }

    /* Mobile Requests Cards */
    .requests-mobile-list {
        display: none;
        flex-direction: column;
        gap: 12px;
    }

    .mobile-request-card {
        padding: 14px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mobile-req-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mobile-req-title {
        font-size: 13px;
        color: var(--navy);
    }

    .mobile-req-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        color: var(--muted);
        font-size: 11px;
    }

    .mobile-req-footer {
        margin-top: 4px;
    }

    /* ===== Right Column Cards ===== */
    .dashboard-sidebar-column {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Announcements Summary Card */
    .announcements-summary-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .announcement-summary-item {
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        transition: all 0.2s ease;
    }

    .announcement-summary-item:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: var(--shadow-sm);
    }

    .announcement-meta-top {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .badge-tag {
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 2px 8px;
        border-radius: 4px;
        background: #eff6ff;
        color: #1e40af;
    }

    .pinned-tag {
        font-size: 9.5px;
        font-weight: 700;
        color: #b45309;
        background: #fef3c7;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .announcement-date {
        font-size: 10.5px;
        color: var(--muted);
        margin-left: auto;
    }

    .announcement-headline {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--ink);
        line-height: 1.4;
    }

    .empty-state-notice {
        padding: 24px;
        text-align: center;
        color: var(--muted);
        font-size: 12px;
        background: #f8fafc;
        border: 1px dashed var(--border);
        border-radius: var(--radius);
    }

    .empty-state-notice p {
        margin: 0 0 10px;
    }

    /* Upcoming Events Timeline */
    .events-timeline {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .timeline-item {
        display: flex;
        gap: 14px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-subtle);
    }

    .timeline-item:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .date-block {
        width: 48px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #dbeafe;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        text-align: center;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
    }

    .date-month {
        background: var(--navy);
        color: #ffffff;
        font-size: 9px;
        font-weight: 800;
        padding: 2px 0;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .date-number {
        font-size: 17px;
        font-weight: 800;
        color: var(--navy);
        padding: 5px 0 3px;
        font-family: var(--font-heading);
    }

    .timeline-details {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .event-category {
        font-size: 9.5px;
        font-weight: 700;
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .event-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--ink);
        margin: 2px 0 5px;
    }

    .event-submeta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 11px;
        color: var(--muted);
    }

    .event-submeta span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .event-submeta svg {
        width: 13px;
        height: 13px;
        color: var(--muted);
    }

    /* ===== 4. Quick Actions Bar ===== */
    .quick-actions-section {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 22px 26px;
    }

    .quick-actions-header {
        margin-bottom: 16px;
    }

    .quick-actions-title {
        margin: 0;
        font-family: var(--font-heading);
        font-size: 16px;
        font-weight: 700;
        color: var(--navy);
    }

    .quick-actions-hint {
        color: var(--muted);
        font-size: 11.5px;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .quick-action-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: #f8fafc;
        text-decoration: none;
        color: inherit;
        transition: all 0.18s ease;
    }

    .quick-action-card:hover {
        background: #ffffff;
        border-color: var(--blue);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(6, 47, 120, 0.08);
    }

    .qa-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eef5fc;
        color: var(--navy);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .qa-icon svg {
        width: 18px;
        height: 18px;
    }

    .qa-text strong {
        display: block;
        color: var(--navy);
        font-size: 12.5px;
    }

    .qa-text small {
        display: block;
        color: var(--muted);
        font-size: 10.5px;
        margin-top: 1px;
    }

    /* ===== Responsive Breakpoints ===== */
    @media (max-width: 1200px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 980px) {
        .dashboard-main-grid {
            grid-template-columns: 1fr;
        }
        .dashboard-welcome-banner {
            flex-direction: column;
            align-items: flex-start;
            padding: 26px;
        }
        .banner-date-widget {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .table-responsive-desktop {
            display: none;
        }
        .requests-mobile-list {
            display: flex;
        }
    }

    @media (max-width: 600px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
        .dashboard-card {
            padding: 18px;
        }
    }
</style>
@endpush
