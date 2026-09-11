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
                <span class="date-day">{{ now()->format('l') }}</span>
                <strong class="date-full">{{ now()->format('F j, Y') }}</strong>
                <small class="date-liturgical">Diocese of Sorsogon</small>
            </div>
        </div>
    </section>

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

        <!-- Card 2: Pending Requests -->
        <a href="{{ $stats[1]['route'] }}" class="kpi-card" aria-label="View Pending Requests">
            <div class="kpi-top">
                <div class="kpi-icon-wrap icon-requests" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
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
                    <span>Review</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </span>
            </div>
        </a>

        <!-- Card 3: Upcoming Events -->
        <a href="{{ $stats[2]['route'] }}" class="kpi-card" aria-label="View Upcoming Events">
            <div class="kpi-top">
                <div class="kpi-icon-wrap icon-events" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
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
                    <span>Schedule</span>
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
                    <p class="card-subtitle">Latest form submissions and service requests</p>
                </div>
                <a href="{{ route('admin.mass-intentions') }}" class="view-all-link">
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
                                        @elseif($req['type'] === 'Appointment')
                                            📅
                                        @else
                                            🕯
                                        @endif
                                    </div>
                                    <div>
                                        <strong class="request-name">{{ $req['title'] }}</strong>
                                        <small class="request-code">Ref: #REQ-{{ rand(1040, 9999) }}</small>
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
                                        <a href="{{ route('admin.mass-intentions') }}" class="action-btn-sm" title="View details">
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
                @foreach($recentRequests as $req)
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
                            <a href="{{ route('admin.mass-intentions') }}" class="btn btn-outline btn-sm">Review Request →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <!-- RIGHT: Facebook Livestream & Upcoming Events -->
        <div class="dashboard-sidebar-column">
            <!-- Facebook Livestream Card -->
            <section class="dashboard-card livestream-card {{ $livestream->is_live ? 'state-live' : 'state-offline' }}" id="livestream-panel" aria-label="Facebook Livestream Broadcast Controller">
                <div class="livestream-header">
                    <div class="livestream-brand-badge">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="fb-icon" aria-hidden="true">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Facebook Broadcast</span>
                    </div>

                    @if($livestream->is_live)
                        <span class="live-pill live-active">
                            <span class="live-pulse" aria-hidden="true"></span>
                            LIVE NOW
                        </span>
                    @else
                        <span class="live-pill live-inactive">
                            <span class="offline-dot" aria-hidden="true"></span>
                            OFFLINE
                        </span>
                    @endif
                </div>

                <div class="livestream-info">
                    <h3 class="livestream-title">Facebook Livestream</h3>
                    <p class="livestream-desc">
                        @if($livestream->is_live)
                            The public live broadcast banner is currently <strong>ACTIVE</strong> on the homepage. Visitors are directed to the shrine's Facebook stream.
                        @else
                            Your live banner is currently <strong>OFF</strong>. Turn it on when the broadcast begins to alert parishioners.
                        @endif
                    </p>
                </div>

                <!-- Functional Livestream Toggle Form -->
                <form method="POST" action="{{ route('admin.livestream.update') }}" class="livestream-form">
                    @csrf
                    <input type="hidden" name="is_live" value="{{ $livestream->is_live ? 0 : 1 }}">
                    <input type="hidden" name="title" value="{{ $livestream->title }}">
                    <input type="hidden" name="url" value="{{ $livestream->url }}">

                    @if($livestream->is_live)
                        <button type="submit" class="livestream-btn btn-turn-off">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <span>Turn livestream OFF</span>
                        </button>
                    @else
                        <button type="submit" class="livestream-btn btn-turn-on">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M23 7l-7 5 7 5V7z"></path>
                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                            </svg>
                            <span>Turn livestream ON</span>
                        </button>
                    @endif
                </form>
            </section>

            <!-- Upcoming Events Card -->
            <section class="dashboard-card upcoming-events-card" aria-label="Upcoming Events Timeline">
                <div class="card-header-bar">
                    <div>
                        <h2 class="card-title">Upcoming events</h2>
                        <p class="card-subtitle">Parish calendar &amp; liturgical activities</p>
                    </div>
                    <a href="{{ route('admin.events') }}" class="view-all-link">
                        <span>View all</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="events-timeline">
                    @foreach($upcomingEvents as $evt)
                        <div class="timeline-item">
                            <div class="date-block" aria-hidden="true">
                                <span class="date-month">{{ $evt['month'] }}</span>
                                <strong class="date-number">{{ $evt['day'] }}</strong>
                            </div>
                            <div class="timeline-details">
                                <span class="event-category">{{ $evt['type'] }}</span>
                                <strong class="event-title">{{ $evt['title'] }}</strong>
                                <div class="event-submeta">
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        {{ $evt['time'] }}
                                    </span>
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        {{ $evt['location'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
            <a href="{{ route('admin.events') }}" class="quick-action-card">
                <div class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
                <div class="qa-text">
                    <strong>+ Add event</strong>
                    <small>Schedule celebrations</small>
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
        font: 700 28px 'Libre Baskerville', Georgia, serif;
        color: #ffffff;
        letter-spacing: -0.01em;
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
        font-family: 'Libre Baskerville', Georgia, serif;
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
        font: 700 18px 'Libre Baskerville', Georgia, serif;
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

    /* Facebook Livestream Card */
    .livestream-card {
        border-left: 4px solid #718096;
        transition: border-color 0.25s ease;
    }

    .livestream-card.state-live {
        border-left-color: #dc2626;
        background: linear-gradient(180deg, #fffafa 0%, #ffffff 100%);
    }

    .livestream-card.state-offline {
        border-left-color: #64748b;
    }

    .livestream-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .livestream-brand-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        color: #1877f2;
    }

    .fb-icon {
        width: 18px;
        height: 18px;
    }

    .live-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
    }

    .live-active {
        background: #fee2e2;
        color: #b91c1c;
    }

    .live-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #dc2626;
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
        animation: pulseLive 1.6s infinite;
    }

    @keyframes pulseLive {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(220, 38, 38, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }

    .live-inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .offline-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #64748b;
    }

    .livestream-title {
        margin: 0 0 6px;
        font: 700 17px 'Libre Baskerville', Georgia, serif;
        color: var(--navy);
    }

    .livestream-desc {
        margin: 0 0 18px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .livestream-btn {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 11px 18px;
        border-radius: var(--radius);
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-turn-on {
        background: #dc2626;
        color: #ffffff;
        border: 1px solid #b91c1c;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
    }

    .btn-turn-on:hover {
        background: #b91c1c;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.35);
    }

    .btn-turn-off {
        background: #ffffff;
        color: #991b1b;
        border: 1px solid #f87171;
    }

    .btn-turn-off:hover {
        background: #fef2f2;
        border-color: #ef4444;
    }

    .livestream-btn svg {
        width: 17px;
        height: 17px;
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
        font-family: 'Libre Baskerville', Georgia, serif;
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
        font: 700 16px 'Libre Baskerville', Georgia, serif;
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
