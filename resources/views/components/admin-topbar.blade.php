<!-- resources/views/components/admin-topbar.blade.php -->
@props(['title' => null])

@php
  use App\Models\Donation;
  use App\Models\InquiryMessage;
  use App\Models\MassIntention;
  use App\Models\MinistryMembership;
  use App\Models\Notification;
  use Illuminate\Support\Facades\Schema;

  $routeName = request()->route()?->getName() ?? '';

  // Clean, standard module titles based on admin routes
  $routeTitles = [
    'admin.dashboard'           => 'Dashboard',
    'admin.parishioners'        => 'Parishioners',
    'admin.staff'               => 'Users',
    'admin.inquiries'           => 'Inquiries',
    'inquiries.index'           => 'Inquiries',
    'admin.ministries'          => 'Ministries',
    'admin.ministry-requests'   => 'Ministry Requests',
    'admin.mass-schedules'      => 'Mass Schedules',
    'admin.mass-intentions'     => 'Mass Intentions',
    'admin.announcements'       => 'Announcements',
    'admin.donations'           => 'Donations',
    'admin.donations.history'   => 'Donations',
    'admin.appointments'        => 'Appointments',
    'admin.sacramental-records' => 'Sacramental Records',
    'admin.form-submissions'    => 'Form Submissions',
    'admin.notifications'       => 'Settings',
    'admin.events'              => 'Events',
    'admin.reports'             => 'Reports',
  ];

  if (isset($routeTitles[$routeName])) {
    $pageTitle = $routeTitles[$routeName];
  } elseif (str_starts_with($routeName, 'admin.')) {
    $sub = explode('.', substr($routeName, 6))[0];
    $pageTitle = match ($sub) {
      'dashboard'                 => 'Dashboard',
      'parishioners'              => 'Parishioners',
      'staff', 'users'            => 'Users',
      'donations'                 => 'Donations',
      'ministries'                => 'Ministries',
      'ministry-requests'         => 'Ministry Requests',
      'announcements'             => 'Announcements',
      'mass-schedules'            => 'Mass Schedules',
      'mass-intentions'           => 'Mass Intentions',
      'appointments'              => 'Appointments',
      'sacramental-records'       => 'Sacramental Records',
      'form-submissions'          => 'Form Submissions',
      'notifications', 'settings' => 'Settings',
      'inquiries'                 => 'Inquiries',
      'events'                    => 'Events',
      'reports'                   => 'Reports',
      default                     => str($sub)->headline()->toString(),
    };
  } elseif (!empty($title) && !in_array($title, ['Admin Portal', 'Pilar Shrine', 'Dashboard'])) {
    $pageTitle = $title;
  } elseif (request()->is('admin/*')) {
    $segment = request()->segment(2);
    $pageTitle = str($segment)->headline()->toString();
  } else {
    $pageTitle = !empty($title) ? $title : 'Dashboard';
  }

  $isMessagesPage = str_contains($routeName, 'inquiries')
    || str_contains($routeName, 'messages')
    || request()->is('admin/inquiries*', 'inquiries*', 'staff/inquiries*')
    || in_array($pageTitle, ['Inquiries', 'Messages', 'Messages & Inquiries'], true);

  // Live notification counters and items with defensive checks
  $pendingMinistryCount = 0;
  $pendingDonationsCount = 0;
  $pendingIntentionsCount = 0;
  $unreadInquiriesCount = 0;
  $recentSystemNotifs = collect();

  try {
    if (Schema::hasTable('ministry_memberships')) {
      $pendingMinistryCount = MinistryMembership::query()->where('status', 'pending')->count();
    }
    if (Schema::hasTable('donations')) {
      $pendingDonationsCount = Donation::query()->where('status', 'pending_verification')->count();
    }
    if (Schema::hasTable('mass_intentions')) {
      $pendingIntentionsCount = MassIntention::query()->where('status', 'pending')->count();
    }
    if (auth()->check() && Schema::hasTable('inquiry_messages')) {
      $unreadInquiriesCount = InquiryMessage::query()->where('recipient_id', auth()->id())->whereNull('read_at')->count();
    }
    if (auth()->check() && Schema::hasTable('notifications')) {
      $recentSystemNotifs = Notification::query()->where('user_id', auth()->id())->latest()->take(4)->get();
    }
  } catch (\Throwable $e) {
    // Graceful fallback if any query fails
  }

  $totalNotificationCount = $pendingMinistryCount + $pendingDonationsCount + $pendingIntentionsCount + $unreadInquiriesCount;
  
  $user = auth()->user();
  if ($user) {
    $user->loadMissing(['commissions', 'ministries']);
  }
  $authName = $user?->name ?? 'Parish Administrator';
  $authEmail = $user?->email ?? 'admin@pilarshrine.test';
  $authRole = $user?->role_badge_label ?? ucfirst($user?->role ?? 'Administrator');
  $authPosition = $user?->position ?: $authRole;
  $authOrganization = $user?->organization_label ?? 'Parish Administration';
  $authResponsibilities = $user?->responsibilities_label ?? '';
  $authAvatar = $user?->avatar;
  $authInitials = strtoupper(substr(trim($authName), 0, 2));
  $availableOrgs = $user ? $user->getAvailableOrganizations() : [];
  $activeContext = $user ? $user->getActiveOrganizationContext() : null;
@endphp

<header class="admin-topbar">
  <div class="topbar-left">
    <!-- Mobile Drawer Hamburger Toggle -->
    <button class="mobile-menu-btn" id="mobile-sidebar-toggle" type="button" aria-label="Open sidebar menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>

    <!-- Dynamic Page / Module Title directly to the left of the search bar -->
    <div class="topbar-title-section">
      <h1 class="topbar-page-title">{{ $pageTitle }}</h1>
    </div>

    @unless($isMessagesPage)
      <!-- Visual separation divider -->
      <div class="topbar-title-divider" aria-hidden="true"></div>

      <!-- Global Search -->
      <div class="global-search-wrap" id="global-search-wrap">
        <div class="search-input-box">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input 
            type="search" 
            id="global-search-input" 
            placeholder="Search modules... (Ctrl+K)" 
            aria-label="Global search across parish records"
            autocomplete="off"
          >
          <div class="search-shortcut" aria-hidden="true">
            <kbd>Ctrl</kbd> + <kbd>K</kbd>
          </div>
        </div>

        <!-- Quick Search Dropdown / Palette -->
        <div class="search-dropdown-palette" id="search-dropdown-palette">
          <div class="search-palette-group">
            <span class="palette-label">Quick Jump to Modules</span>
            <a href="{{ route('admin.parishioners') }}" class="palette-item">
              <span class="palette-icon">👤</span>
              <div>
                <strong>Parishioners Directory</strong>
                <small>Manage registered church members</small>
              </div>
              <span class="palette-tag">User Management</span>
            </a>
            <a href="{{ route('admin.mass-intentions') }}" class="palette-item">
              <span class="palette-icon">🕯</span>
              <div>
                <strong>Mass Intentions</strong>
                <small>Review offerings &amp; liturgical intentions</small>
              </div>
              <span class="palette-tag">Requests</span>
            </a>
            <a href="{{ route('admin.mass-schedules') }}" class="palette-item">
              <span class="palette-icon">⛪</span>
              <div>
                <strong>Mass Schedules</strong>
                <small>Sunday and weekday liturgical times</small>
              </div>
              <span class="palette-tag">Scheduling</span>
            </a>
            <a href="{{ route('admin.ministries') }}" class="palette-item">
              <span class="palette-icon">👥</span>
              <div>
                <strong>Parish Ministries</strong>
                <small>Apostolates, organizations &amp; groups</small>
              </div>
              <span class="palette-tag">Ministries</span>
            </a>
            <a href="{{ route('admin.announcements') }}" class="palette-item">
              <span class="palette-icon">📢</span>
              <div>
                <strong>Announcements</strong>
                <small>Bulletin &amp; portal notices</small>
              </div>
              <span class="palette-tag">Content</span>
            </a>
          </div>
        </div>
      </div>
    @endunless
  </div>

  <div class="topbar-right">
    <!-- Back to Official Website -->
    <a href="/" class="btn-topbar-website" title="Return to Official Parish Website">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
      </svg>
      <span>Official Website</span>
    </a>

    @if(count($availableOrgs) > 1)
      <!-- Organization Context Switcher -->
      <div class="topbar-dropdown-wrap" id="org-context-dropdown-wrap">
        <button class="org-context-switcher-btn" id="org-context-btn" type="button" aria-label="Switch organization context" aria-expanded="false" aria-haspopup="true">
          <span class="org-context-icon">🏛</span>
          <span class="org-context-name">{{ $activeContext['name'] ?? 'Parish Administration' }}</span>
          <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;margin-left:4px;">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>

        <div class="dropdown-popover org-context-popover" id="org-context-popover" role="menu">
          <div class="popover-header">
            <strong>Active Organization</strong>
            <span class="unread-count" style="font-size:10px;">{{ count($availableOrgs) }} Available</span>
          </div>
          <div class="org-context-list">
            @foreach($availableOrgs as $org)
              @php
                $isActive = ($activeContext['type'] === $org['type']) && ((int)($activeContext['id'] ?? 0) === (int)($org['id'] ?? 0));
              @endphp
              <button type="button" 
                      class="org-context-item {{ $isActive ? 'active' : '' }}" 
                      onclick="switchActiveOrganization('{{ $org['type'] }}', {{ $org['id'] ? $org['id'] : 'null' }})">
                <span class="org-context-item-dot dot-{{ $org['type'] }}"></span>
                <div class="org-context-item-details">
                  <div class="org-context-item-title">{{ $org['name'] }}</div>
                  <small class="org-context-item-sub">{{ $org['role'] }} · {{ ucfirst(str_replace('_', ' ', $org['type'])) }}</small>
                </div>
                @if($isActive)
                  <span class="org-context-active-check">✓</span>
                @endif
              </button>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    <!-- Notification Bell with Dropdown -->
    <div class="topbar-dropdown-wrap" id="notification-dropdown-wrap">
      <button class="icon-action-btn" id="notification-btn" type="button" aria-label="Notifications" aria-expanded="false" aria-haspopup="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        @if($totalNotificationCount > 0)
          <span class="notification-badge">{{ $totalNotificationCount }}</span>
        @endif
      </button>

      <!-- Notification Popover -->
      <div class="dropdown-popover notification-popover" id="notification-popover" role="menu">
        <div class="popover-header">
          <div>
            <strong>Notifications</strong>
            @if($totalNotificationCount > 0)
              <span class="unread-count">{{ $totalNotificationCount }} unread</span>
            @endif
          </div>
          <a href="{{ route('admin.notifications') }}" class="mark-read-btn">View all</a>
        </div>
        <div class="notification-list">
          @if($pendingMinistryCount > 0)
            <a href="{{ route('admin.ministry-requests') }}" class="notification-item unread">
              <span class="notif-dot"></span>
              <div>
                <p><strong>{{ $pendingMinistryCount }} Ministry {{ str('Application')->plural($pendingMinistryCount) }}</strong> pending review.</p>
                <small>Requires administrative action</small>
              </div>
            </a>
          @endif

          @if($pendingDonationsCount > 0)
            <a href="{{ route('admin.donations') }}" class="notification-item unread">
              <span class="notif-dot"></span>
              <div>
                <p><strong>{{ $pendingDonationsCount }} Donation {{ str('Submission')->plural($pendingDonationsCount) }}</strong> awaiting verification.</p>
                <small>Payment verification queue</small>
              </div>
            </a>
          @endif

          @if($pendingIntentionsCount > 0)
            <a href="{{ route('admin.mass-intentions') }}" class="notification-item unread">
              <span class="notif-dot"></span>
              <div>
                <p><strong>{{ $pendingIntentionsCount }} Mass {{ str('Intention')->plural($pendingIntentionsCount) }}</strong> pending scheduling.</p>
                <small>Liturgical intentions</small>
              </div>
            </a>
          @endif

          @if($unreadInquiriesCount > 0)
            <a href="{{ route('admin.inquiries') }}" class="notification-item unread">
              <span class="notif-dot"></span>
              <div>
                <p><strong>{{ $unreadInquiriesCount }} Unread {{ str('Message')->plural($unreadInquiriesCount) }}</strong> from parishioners.</p>
                <small>Communication inbox</small>
              </div>
            </a>
          @endif

          @forelse($recentSystemNotifs as $sNotif)
            <a href="{{ route('admin.notifications') }}" class="notification-item {{ $sNotif->status === 'unread' ? 'unread' : '' }}">
              <span class="notif-dot"></span>
              <div>
                <p><strong>{{ $sNotif->subject }}</strong></p>
                <small>{{ $sNotif->created_at ? $sNotif->created_at->diffForHumans() : 'Recently' }}</small>
              </div>
            </a>
          @empty
            @if($totalNotificationCount === 0)
              <div class="notification-empty" style="padding: 16px; text-align: center; color: var(--muted); font-size: 11px;">
                No unread notifications at this time.
              </div>
            @endif
          @endforelse
        </div>
      </div>
    </div>

    <!-- Admin Profile Dropdown -->
    <div class="topbar-dropdown-wrap" id="profile-dropdown-wrap">
      <button class="profile-trigger-btn" id="profile-trigger-btn" type="button" aria-label="Admin account menu" aria-expanded="false" aria-haspopup="true">
        <div class="admin-avatar">
          @if(!empty($authAvatar))
            <img src="{{ $authAvatar }}" alt="{{ $authName }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;" />
          @else
            {{ $authInitials }}
          @endif
        </div>
        <div class="admin-meta">
          <span class="admin-name">{{ $authName }}</span>
          <span class="admin-role">{{ $authPosition }}</span>
        </div>
        <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>

      <!-- Profile Dropdown Menu -->
      <div class="dropdown-popover profile-popover" id="profile-popover" role="menu">
        <div class="profile-popover-header">
          <strong>{{ $authName }}</strong>
          <small>{{ $authEmail }}</small>
          <div class="profile-chips-wrap">
            <span class="role-chip">{{ $authPosition }}</span>
            <span class="org-chip">{{ $authOrganization }}</span>
          </div>
          @if(!empty($authResponsibilities) && $authResponsibilities !== '—')
            <div class="profile-resp-row">
              <span class="resp-label">Responsibilities:</span>
              <span class="resp-val">{{ $authResponsibilities }}</span>
            </div>
          @endif
        </div>
        @if($user && ($user->commissions->isNotEmpty() || $user->ministries->isNotEmpty()))
          <div class="profile-connections-section">
            <span class="connections-heading">Connected Organizations</span>
            @foreach($user->commissions as $uComm)
              <div class="connection-row">
                <span class="connection-dot dot-commission"></span>
                <span class="connection-name">{{ $uComm->name }}</span>
                <span class="connection-role">{{ ucfirst($uComm->pivot->role ?? 'member') }}</span>
              </div>
            @endforeach
            @foreach($user->ministries as $uMin)
              <div class="connection-row">
                <span class="connection-dot dot-ministry"></span>
                <span class="connection-name">{{ $uMin->name }}</span>
                <span class="connection-role">{{ ucfirst($uMin->pivot->role ?? 'member') }}</span>
              </div>
            @endforeach
          </div>
        @endif
        <div class="profile-popover-links">
          <a href="{{ route('admin.staff') }}" class="popover-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>My Profile &amp; Staff</span>
          </a>
          <a href="{{ route('admin.notifications') }}" class="popover-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"></circle>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            <span>Account Settings</span>
          </a>
        </div>
        <div class="profile-popover-footer">
          <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="logout-btn" type="submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span>Log out</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>

<style>
  .admin-topbar {
    height: var(--topbar-height);
    background: #ffffff;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    gap: 20px;
  }

  .topbar-left {
    display: flex;
    align-items: center;
    gap: 18px;
    flex: 1;
    min-width: 0;
    max-width: 760px;
  }

  .topbar-title-section {
    display: flex;
    align-items: center;
    flex-shrink: 0;
  }

  .topbar-page-title {
    margin: 0;
    font-family: 'Libre Baskerville', Georgia, serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--navy);
    line-height: 1.2;
    letter-spacing: -0.01em;
    white-space: nowrap;
  }

  .topbar-title-divider {
    width: 1px;
    height: 24px;
    background-color: var(--border);
    flex-shrink: 0;
  }

  .mobile-menu-btn {
    display: none;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--navy);
    cursor: pointer;
    place-items: center;
    padding: 0;
    flex-shrink: 0;
  }

  .mobile-menu-btn svg {
    width: 18px;
    height: 18px;
  }

  .global-search-wrap {
    position: relative;
    flex: 1;
    min-width: 180px;
    max-width: 440px;
  }

  .search-input-box {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }

  .search-icon {
    position: absolute;
    left: 14px;
    width: 16px;
    height: 16px;
    color: var(--muted);
    pointer-events: none;
  }

  .search-input-box input {
    width: 100%;
    height: 40px;
    padding: 0 70px 0 38px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: #f8fafc;
    color: var(--ink);
    font-size: 12.5px;
    outline: none;
    transition: all 0.2s ease;
  }

  .search-input-box input:focus {
    background: #ffffff;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(21, 92, 180, 0.12);
  }

  .search-shortcut {
    position: absolute;
    right: 12px;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 3px;
  }

  .search-shortcut kbd {
    display: inline-block;
    padding: 2px 5px;
    font-size: 10px;
    font-family: inherit;
    font-weight: 700;
    color: var(--muted);
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
  }

  /* Quick Search Palette */
  .search-dropdown-palette {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    display: none;
    overflow: hidden;
  }

  .search-dropdown-palette.open {
    display: block;
    animation: dropdownSlide 0.18s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .search-palette-group {
    padding: 8px 0;
  }

  .palette-label {
    display: block;
    padding: 6px 14px 4px;
    color: var(--muted);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }

  .palette-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 14px;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease;
  }

  .palette-item:hover {
    background: #f1f5f9;
  }

  .palette-icon {
    font-size: 16px;
    width: 24px;
    text-align: center;
  }

  .palette-item strong {
    display: block;
    color: var(--navy);
    font-size: 12px;
  }

  .palette-item small {
    display: block;
    color: var(--muted);
    font-size: 10.5px;
  }

  .palette-tag {
    margin-left: auto;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--blue);
    background: var(--blue-subtle);
    padding: 2px 7px;
    border-radius: 4px;
  }

  /* Topbar Right Actions */
  .topbar-right {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  /* Official Website button */
  .btn-topbar-website {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 13px;
    border: 1px solid #ccd8e4;
    border-radius: 7px;
    background: #f8fafc;
    color: var(--navy);
    font-size: 11.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.16s ease;
    white-space: nowrap;
  }

  .btn-topbar-website:hover {
    background: #ffffff;
    border-color: var(--gold);
    color: var(--gold-hover);
    box-shadow: 0 2px 6px rgba(216, 170, 60, 0.15);
  }

  .btn-topbar-website svg {
    color: var(--navy);
    transition: transform 0.15s ease, color 0.16s ease;
  }

  .btn-topbar-website:hover svg {
    color: var(--gold-hover);
    transform: translateX(-2px);
  }

  .topbar-dropdown-wrap {
    position: relative;
  }

  .icon-action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--ink-secondary);
    display: grid;
    place-items: center;
    cursor: pointer;
    position: relative;
    transition: all 0.18s ease;
  }

  .icon-action-btn:hover {
    background: #f8fafc;
    color: var(--navy);
    border-color: var(--muted-light);
  }

  .icon-action-btn svg {
    width: 19px;
    height: 19px;
  }

  .notification-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--danger);
    color: #ffffff;
    font-size: 9px;
    font-weight: 800;
    display: grid;
    place-items: center;
    border: 2px solid #ffffff;
  }

  /* Profile Trigger */
  .profile-trigger-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 10px 4px 4px;
    border-radius: 30px;
    border: 1px solid var(--border);
    background: #ffffff;
    cursor: pointer;
    transition: all 0.18s ease;
  }

  .profile-trigger-btn:hover {
    background: #f8fafc;
    border-color: var(--muted-light);
  }

  .admin-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 11.5px;
    display: grid;
    place-items: center;
    box-shadow: 0 2px 6px rgba(6, 47, 120, 0.2);
    overflow: hidden;
  }

  .admin-meta {
    display: flex;
    flex-direction: column;
    text-align: left;
  }

  .admin-name {
    font-size: 12px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
  }

  .admin-role {
    font-size: 10px;
    color: var(--muted);
  }

  .chevron-icon {
    width: 14px;
    height: 14px;
    color: var(--muted);
    transition: transform 0.2s ease;
  }

  .profile-trigger-btn[aria-expanded="true"] .chevron-icon {
    transform: rotate(180deg);
  }

  /* Dropdown Popovers */
  .dropdown-popover {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 300px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    display: none;
    overflow: hidden;
  }

  .dropdown-popover.open {
    display: block;
    animation: dropdownSlide 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }

  @keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Notification Popover */
  .popover-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: #f8fafc;
  }

  .popover-header strong {
    font-size: 12.5px;
    color: var(--navy);
  }

  .unread-count {
    display: inline-block;
    margin-left: 6px;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--danger);
    background: var(--danger-bg);
    padding: 1px 6px;
    border-radius: 10px;
  }

  .mark-read-btn {
    font-size: 11px;
    font-weight: 700;
    color: var(--blue);
    text-decoration: none;
  }

  .notification-list {
    max-height: 280px;
    overflow-y: auto;
  }

  .notification-item {
    display: flex;
    gap: 10px;
    padding: 12px 16px;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid var(--border-subtle);
    transition: background 0.15s ease;
  }

  .notification-item:last-child {
    border-bottom: none;
  }

  .notification-item:hover {
    background: #f8fafc;
  }

  .notification-item.unread {
    background: #fdfefe;
  }

  .notif-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--blue);
    margin-top: 5px;
    flex-shrink: 0;
  }

  .notification-item p {
    margin: 0;
    font-size: 11.5px;
    line-height: 1.4;
    color: var(--ink-secondary);
  }

  .notification-item p strong {
    color: var(--navy);
  }

  .notification-item small {
    display: block;
    margin-top: 3px;
    color: var(--muted);
    font-size: 10px;
  }

  /* Profile Popover */
  .profile-popover-header {
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
    background: #f8fafc;
  }

  .profile-popover-header strong {
    display: block;
    color: var(--navy);
    font-size: 13px;
  }

  .profile-popover-header small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    margin-top: 2px;
  }

  .profile-chips-wrap {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 8px;
  }

  .role-chip {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 2px 7px;
    border-radius: 4px;
  }

  .org-chip {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 2px 7px;
    border-radius: 4px;
  }

  .profile-resp-row {
    margin-top: 8px;
    font-size: 11px;
    color: #475569;
    line-height: 1.35;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 4px 8px;
  }

  .resp-label {
    font-weight: 600;
    color: #64748b;
  }

  .resp-val {
    color: #1e293b;
    font-weight: 500;
  }

  .profile-connections-section {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    background: #fcfdfe;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 160px;
    overflow-y: auto;
  }

  .connections-heading {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 2px;
  }

  .connection-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
  }

  .connection-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .dot-commission { background: #2563eb; }
  .dot-ministry { background: #16a34a; }

  .connection-name {
    color: #1e293b;
    font-weight: 500;
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .connection-role {
    font-size: 10px;
    color: #64748b;
    background: #f1f5f9;
    padding: 1px 6px;
    border-radius: 10px;
  }

  .profile-popover-links {
    padding: 6px 0;
  }

  .popover-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 18px;
    color: var(--ink-secondary);
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: background 0.15s ease;
  }

  .popover-link svg {
    width: 15px;
    height: 15px;
    color: var(--muted);
  }

  .popover-link:hover {
    background: #f1f5f9;
    color: var(--navy);
  }

  .profile-popover-footer {
    padding: 8px 12px;
    border-top: 1px solid var(--border);
    background: #fafbfc;
  }

  .logout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 12px;
    border: 1px solid #fecaca;
    border-radius: var(--radius-sm);
    background: #fff5f5;
    color: #b91c1c;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
  }

  .logout-btn svg {
    width: 14px;
    height: 14px;
  }

  .logout-btn:hover {
    background: #fee2e2;
    border-color: #f87171;
  }

  /* Responsive Adjustments */
  @media (max-width: 1024px) {
    .admin-topbar {
      padding: 0 24px;
      gap: 16px;
    }

    .topbar-left {
      gap: 14px;
      max-width: 620px;
    }

    .topbar-page-title {
      font-size: 19px;
    }

    .global-search-wrap {
      min-width: 160px;
    }
  }

  @media (max-width: 900px) {
    .mobile-menu-btn {
      display: grid;
    }

    .admin-topbar {
      padding: 0 20px;
    }

    .topbar-left {
      max-width: 100%;
    }
  }

  @media (max-width: 768px) {
    .topbar-page-title {
      font-size: 18px;
    }

    .topbar-left {
      gap: 12px;
    }
  }

  @media (max-width: 640px) {
    .admin-topbar {
      padding: 0 16px;
      gap: 10px;
    }

    .topbar-left {
      gap: 10px;
    }

    .topbar-page-title {
      font-size: 16px;
    }

    .topbar-title-divider {
      height: 18px;
    }

    .search-shortcut {
      display: none;
    }

    .admin-meta {
      display: none;
    }

    .btn-topbar-website {
      padding: 7px 9px;
    }

    .btn-topbar-website span {
      display: none;
    }

    .profile-trigger-btn {
      padding: 4px;
      border-radius: 50%;
    }

    .chevron-icon {
      display: none;
    }

    .global-search-wrap {
      min-width: 110px;
    }

    .search-input-box input {
      padding: 0 12px 0 34px;
      font-size: 12px;
    }
  }

  @media (max-width: 480px) {
    .admin-topbar {
      padding: 0 12px;
    }

    .topbar-page-title {
      font-size: 15px;
    }

    .topbar-title-divider {
      display: none;
    }

    .topbar-left {
      gap: 8px;
    }
  }

  /* Organization Context Switcher */
  .org-context-switcher-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: 1px solid var(--border, #cbd5e1);
    border-radius: 20px;
    background: #f8fafc;
    font-size: 12px;
    font-weight: 600;
    color: var(--navy, #062f78);
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    max-width: 220px;
  }
  .org-context-switcher-btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
  }
  .org-context-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .org-context-popover {
    width: 280px;
    right: 0;
    left: auto;
  }
  .org-context-list {
    display: flex;
    flex-direction: column;
    max-height: 280px;
    overflow-y: auto;
    padding: 6px;
    gap: 4px;
  }
  .org-context-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border: 1px solid transparent;
    border-radius: 8px;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s ease;
  }
  .org-context-item:hover {
    background: #f1f5f9;
  }
  .org-context-item.active {
    background: #eff6ff;
    border-color: #bfdbfe;
  }
  .org-context-item-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .org-context-item-dot.dot-parish_administration { background: #9d174d; }
  .org-context-item-dot.dot-commission { background: #1d4ed8; }
  .org-context-item-dot.dot-ministry { background: #15803d; }
  .org-context-item-details {
    flex: 1;
    min-width: 0;
  }
  .org-context-item-title {
    font-size: 12px;
    font-weight: 600;
    color: var(--ink, #1e293b);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .org-context-item-sub {
    font-size: 10px;
    color: var(--muted, #64748b);
    display: block;
  }
  .org-context-active-check {
    color: #1d4ed8;
    font-weight: 700;
    font-size: 14px;
  }
</style>

<script>
  (() => {
    // Search Quick Palette Toggle
    const searchInput = document.getElementById('global-search-input');
    const searchPalette = document.getElementById('search-dropdown-palette');

    searchInput?.addEventListener('focus', () => {
      searchPalette?.classList.add('open');
    });

    document.addEventListener('click', (e) => {
      const searchWrap = document.getElementById('global-search-wrap');
      if (!searchWrap?.contains(e.target)) {
        searchPalette?.classList.remove('open');
      }
    });

    // Dropdowns (Notification & Profile & Org Switcher)
    const setupDropdown = (btnId, popoverId) => {
      const btn = document.getElementById(btnId);
      const popover = document.getElementById(popoverId);
      if (!btn || !popover) return;

      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = popover.classList.contains('open');
        document.querySelectorAll('.dropdown-popover.open').forEach(p => p.classList.remove('open'));
        document.querySelectorAll('[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));

        if (!isOpen) {
          popover.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    };

    setupDropdown('notification-btn', 'notification-popover');
    setupDropdown('profile-trigger-btn', 'profile-popover');
    setupDropdown('org-context-btn', 'org-context-popover');

    window.switchActiveOrganization = async function(type, id) {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          || '{{ csrf_token() }}';

        const response = await fetch('/admin/organization-context/switch', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            organization_type: type,
            organization_id: id
          })
        });

        if (response.ok) {
          window.location.reload();
        } else {
          const data = await response.json();
          alert(data.message || 'Failed to switch active organization.');
        }
      } catch (err) {
        console.error(err);
        alert('Network error while switching organization context.');
      }
    };

    document.addEventListener('click', (e) => {
      if (!e.target.closest('.dropdown-popover') && !e.target.closest('.icon-action-btn') && !e.target.closest('.profile-trigger-btn') && !e.target.closest('.org-context-switcher-btn')) {
        document.querySelectorAll('.dropdown-popover.open').forEach(p => p.classList.remove('open'));
        document.querySelectorAll('[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));
      }
    });
  })();
</script>