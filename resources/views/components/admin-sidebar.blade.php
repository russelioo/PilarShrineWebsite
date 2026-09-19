<!-- resources/views/components/admin-sidebar.blade.php -->
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin Navigation">
  <!-- Brand Header -->
  <div class="sidebar-header">
    <a href="{{ route('admin.dashboard') }}" class="brand-link" title="Our Lady of the Pillar Shrine Admin Portal">
      <div class="brand-logo-wrap">
        <img src="/images/pilar-shrine-logo.png" alt="Our Lady of the Pillar Parish Shrine Full Logo" class="brand-logo-img">
      </div>
      <div class="brand-info">
        <span class="brand-title">Pilar Shrine</span>
        <span class="brand-badge">ADMIN PORTAL</span>
      </div>
    </a>
    <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Collapse sidebar" aria-expanded="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
    </button>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav" aria-label="Sidebar Menu">
    <!-- DASHBOARD -->
    @if(!auth()->check() || auth()->user()->hasPermission('dashboard') || auth()->user()->hasPermission('view_dashboard'))
    <div class="nav-section">
      <span class="nav-section-title">DASHBOARD</span>
      <a href="{{ route('admin.dashboard') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
         data-title="Dashboard">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
          </svg>
        </span>
        <span class="nav-label">Dashboard</span>
      </a>
    </div>
    @endif

    @php
      $authUser = auth()->user();
      $isParishLeader = $authUser && ($authUser->hasParishWideAccess() || $authUser->isSuperAdmin());
      $isCommissionCoord = $authUser && ! $isParishLeader && ($authUser->role === 'commission_coordinator' || $authUser->commission_id);
    @endphp

    {{-- SUPER ADMIN / PARISH-WIDE LEADERSHIP: PPC & COMMISSIONS --}}
    @if($isParishLeader)
    <!-- PARISH PASTORAL COUNCIL (PPC) -->
    <div class="nav-section">
      <span class="nav-section-title">PASTORAL COUNCIL</span>
      <a href="{{ route('admin.ppc.index') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.ppc*') ? 'active' : '' }}"
         data-title="Parish Pastoral Council">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </span>
        <span class="nav-label">Parish Pastoral Council</span>
      </a>
    </div>

    <!-- COMMISSIONS -->
    @php
      try {
          $sidebarCommissions = \Illuminate\Support\Facades\Schema::hasTable('commissions')
              ? \App\Models\Commission::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug', 'code'])
              : collect();
      } catch (\Throwable $e) {
          $sidebarCommissions = collect();
      }
      $currentRouteComm = request()->route('commission');
      $isAnyCommChildActive = false;
      foreach ($sidebarCommissions as $comm) {
          if (request()->routeIs('admin.commissions.show') && (
              (is_object($currentRouteComm) && ($currentRouteComm->slug === $comm->slug || $currentRouteComm->id == $comm->id)) ||
              (is_string($currentRouteComm) && ($currentRouteComm === $comm->slug || $currentRouteComm == $comm->id))
          )) {
              $isAnyCommChildActive = true;
              break;
          }
      }
    @endphp
    <div class="nav-section nav-dropdown-section {{ $isAnyCommChildActive ? 'expanded' : '' }}" id="commissions-nav-dropdown">
      <div class="nav-section-header-row" style="display:flex;align-items:center;justify-content:space-between;padding-right:8px;">
        <span class="nav-section-title">COMMISSIONS</span>
      </div>
      
      <div class="nav-dropdown-trigger-row" style="display:flex;align-items:center;position:relative;">
        <a href="{{ route('admin.commissions.index') }}" 
           class="admin-nav-item {{ request()->routeIs('admin.commissions.index') ? 'active' : '' }}"
           style="flex:1;padding-right:32px;"
           data-title="All Commissions">
          <span class="nav-icon-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
              <path d="M2 17l10 5 10-5"></path>
              <path d="M2 12l10 5 10-5"></path>
            </svg>
          </span>
          <span class="nav-label">All Commissions</span>
        </a>
        <button type="button" 
                class="nav-dropdown-toggle-btn" 
                aria-label="Toggle Commissions submenu" 
                onclick="toggleCommissionsDropdown(event)"
                title="Toggle commissions list"
                style="position:absolute;right:6px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#8bb3e8;cursor:pointer;padding:6px;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:all 0.2s ease;">
          <svg class="dropdown-arrow-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="transition:transform 0.25s cubic-bezier(0.4, 0, 0.2, 1); transform: {{ $isAnyCommChildActive ? 'rotate(180deg)' : 'rotate(0deg)' }};">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
      </div>

      <div class="nav-subitems-container" id="commissions-subitems-wrap" style="display: {{ $isAnyCommChildActive ? 'flex' : 'none' }}; flex-direction:column; gap:2px; overflow:hidden; transition:max-height 0.25s ease;">
        @foreach($sidebarCommissions as $comm)
        @php
          $isCurrentCommActive = request()->routeIs('admin.commissions.show') && (
              (is_object($currentRouteComm) && ($currentRouteComm->slug === $comm->slug || $currentRouteComm->id == $comm->id)) ||
              (is_string($currentRouteComm) && ($currentRouteComm === $comm->slug || $currentRouteComm == $comm->id))
          );
        @endphp
        <a href="{{ route('admin.commissions.show', $comm->slug) }}" 
           class="admin-nav-item admin-nav-subitem {{ $isCurrentCommActive ? 'active' : '' }}"
           data-title="{{ $comm->name }}" title="{{ $comm->name }}">
          <span class="nav-subitem-bullet"></span>
          <span class="nav-label">{{ $comm->name }}</span>
        </a>
        @endforeach
      </div>
    </div>
    @endif

    {{-- COMMISSION COORDINATOR / OFFICER WORKSPACE --}}
    @if($isCommissionCoord)
    <div class="nav-section">
      <span class="nav-section-title">MY COMMISSION</span>
      <a href="{{ route('commission.overview') }}" 
         class="admin-nav-item {{ request()->routeIs('commission.overview', 'commission.dashboard') ? 'active' : '' }}"
         data-title="Overview">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
          </svg>
        </span>
        <span class="nav-label">Overview</span>
      </a>
      <a href="{{ route('commission.members') }}" 
         class="admin-nav-item {{ request()->routeIs('commission.members*') ? 'active' : '' }}"
         data-title="Commission Members">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
          </svg>
        </span>
        <span class="nav-label">Members Roster</span>
      </a>
      <a href="{{ route('commission.officers') }}" 
         class="admin-nav-item {{ request()->routeIs('commission.officers*') ? 'active' : '' }}"
         data-title="Commission Officers">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <line x1="19" y1="8" x2="19" y2="14"></line>
            <line x1="22" y1="11" x2="16" y2="11"></line>
          </svg>
        </span>
        <span class="nav-label">Officers</span>
      </a>
      <a href="{{ route('commission.ministries') }}" 
         class="admin-nav-item {{ request()->routeIs('commission.ministries*') ? 'active' : '' }}"
         data-title="Ministries">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
        </span>
        <span class="nav-label">Ministries</span>
      </a>
      <a href="{{ route('commission.projects') }}" 
         class="admin-nav-item {{ request()->routeIs('commission.projects*') ? 'active' : '' }}"
         data-title="Projects & Activities">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
        </span>
        <span class="nav-label">Projects &amp; Activities</span>
      </a>
      <a href="{{ route('commission.documents') }}" 
         class="admin-nav-item {{ request()->routeIs('commission.documents*') ? 'active' : '' }}"
         data-title="Documents & Reports">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
        </span>
        <span class="nav-label">Documents &amp; Reports</span>
      </a>
    </div>
    @endif

    @if(!auth()->check() || auth()->user()->hasPermission('messages') || auth()->user()->hasPermission('view_messages'))
    <div class="nav-section">
      <span class="nav-section-title">COMMUNICATION</span>
      <a href="{{ route('admin.inquiries') }}" class="admin-nav-item {{ request()->routeIs('admin.inquiries', 'inquiries.*') ? 'active' : '' }}" data-title="Messages">
        <span class="nav-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 15a3 3 0 0 1-3 3H8l-5 3V6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3z"/></svg></span>
        <span class="nav-label">Messages</span>
      </a>
    </div>
    @endif

    <!-- USER MANAGEMENT -->
    @php
      $canParishioners = !auth()->check() || auth()->user()->hasPermission('parishioners') || auth()->user()->hasPermission('view_users');
      $canStaff = !auth()->check() || auth()->user()->hasPermission('staff_management') || auth()->user()->hasPermission('view_users');
      $canAudit = auth()->check() && (auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('view_audit_logs') || auth()->user()->hasPermission('audit_logs'));
    @endphp
    @if($canParishioners || $canStaff || $canAudit)
    <div class="nav-section">
      <span class="nav-section-title">USER MANAGEMENT</span>
      @if($canParishioners)
      <a href="{{ route('admin.parishioners') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.parishioners') ? 'active' : '' }}"
         data-title="Parishioners">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </span>
        <span class="nav-label">Parishioners</span>
      </a>
      @endif
      @if($canStaff)
      <a href="{{ route('admin.staff') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.staff') ? 'active' : '' }}"
         data-title="Staff Management">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <line x1="19" y1="8" x2="19" y2="14"></line>
            <line x1="22" y1="11" x2="16" y2="11"></line>
          </svg>
        </span>
        <span class="nav-label">Staff Management</span>
      </a>
      @endif
      @if($canAudit)
      <a href="{{ route('admin.audit-logs') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}"
         data-title="Audit Logs">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </span>
        <span class="nav-label">Audit Logs</span>
      </a>
      @endif
    </div>
    @endif

    <!-- PARISH MINISTRIES -->
    @php
      $canMinistries = !auth()->check() || auth()->user()->hasPermission('manage_ministries') || auth()->user()->hasPermission('view_ministries');
      $canMinistryRequests = !auth()->check() || auth()->user()->hasPermission('ministry_requests') || auth()->user()->hasPermission('view_requests');
    @endphp
    @if($canMinistries || $canMinistryRequests)
    <div class="nav-section">
      <span class="nav-section-title">PARISH MINISTRIES</span>
      @if($canMinistries)
      <a href="{{ route('admin.ministries') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.ministries*') ? 'active' : '' }}"
         data-title="Manage Ministries">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
        </span>
        <span class="nav-label">Manage Ministries</span>
      </a>
      @endif
      @if($canMinistryRequests)
      <a href="{{ route('admin.ministry-requests') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.ministry-requests*') ? 'active' : '' }}"
         data-title="Ministry Requests">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </span>
        <span class="nav-label">Ministry Requests</span>
      </a>
      @endif
    </div>
    @endif

    <!-- LITURGY & RECORDS -->
    @php
      $canMass = !auth()->check() || auth()->user()->hasPermission('mass_schedules');
      $canAnnouncements = !auth()->check() || auth()->user()->hasPermission('announcements') || auth()->user()->hasPermission('view_announcements');
      $canDonations = !auth()->check() || auth()->user()->hasPermission('donations') || auth()->user()->hasPermission('view_reports');
    @endphp
    @if($canMass || $canAnnouncements || $canDonations)
    <div class="nav-section">
      <span class="nav-section-title">LITURGY &amp; RECORDS</span>
      @if($canMass)
      <a href="{{ route('admin.mass-schedules') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.mass-schedules') ? 'active' : '' }}"
         data-title="Mass &amp; Confession Schedule">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </span>
        <span class="nav-label">Mass &amp; Confession Schedule</span>
      </a>
      @endif
      @if($canAnnouncements)
      <a href="{{ route('admin.announcements') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.announcements') ? 'active' : '' }}"
         data-title="Announcements">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
          </svg>
        </span>
        <span class="nav-label">Announcements</span>
      </a>
      @endif
      @if($canDonations)
      <a href="{{ route('admin.donations') }}" 
         class="admin-nav-item {{ request()->routeIs('admin.donations') ? 'active' : '' }}"
         data-title="Donations">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </span>
        <span class="nav-label">Donations</span>
      </a>
      @endif
    </div>
    @endif

    <!-- SYSTEM -->
    @if(!auth()->check() || auth()->user()->hasPermission('view_settings') || auth()->user()->hasPermission('edit_settings'))
    <div class="nav-section">
      <span class="nav-section-title">SYSTEM</span>
      <a href="{{ route('admin.settings') }}"
         class="admin-nav-item {{ request()->routeIs('admin.settings', 'admin.settings.*') ? 'active' : '' }}"
         data-title="Settings &amp; Notifications">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
          </svg>
        </span>
        <span class="nav-label">Settings</span>
      </a>
    </div>
    @endif

    <!-- UPCOMING SERVICES (COMING SOON AT THE BOTTOM) -->
    <div class="nav-section nav-section-soon">
      <span class="nav-section-title">UPCOMING SERVICES</span>
      <div class="admin-nav-item nav-item-disabled" data-title="Mass Intentions (Available Soon)" title="Feature Available Soon">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
        </span>
        <span class="nav-label">Mass Intentions</span>
        <span class="nav-badge-soon">Soon</span>
      </div>

      <div class="admin-nav-item nav-item-disabled" data-title="Appointments (Available Soon)" title="Feature Available Soon">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
        </span>
        <span class="nav-label">Appointments</span>
        <span class="nav-badge-soon">Soon</span>
      </div>

      <div class="admin-nav-item nav-item-disabled" data-title="Form Submissions (Available Soon)" title="Feature Available Soon">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </span>
        <span class="nav-label">Form Submissions</span>
        <span class="nav-badge-soon">Soon</span>
      </div>

      <div class="admin-nav-item nav-item-disabled" data-title="Sacramental Schedule (Available Soon)" title="Feature Available Soon">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
        </span>
        <span class="nav-label">Sacramental Schedule</span>
        <span class="nav-badge-soon">Soon</span>
      </div>

      <div class="admin-nav-item nav-item-disabled" data-title="Sacramental Records (Available Soon)" title="Feature Available Soon">
        <span class="nav-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
          </svg>
        </span>
        <span class="nav-label">Sacramental Records</span>
        <span class="nav-badge-soon">Soon</span>
      </div>
    </div>
  </nav>

  <!-- Sidebar Footer / Status Pill -->
  <div class="sidebar-footer">
    <div class="footer-status-card">
      <div class="status-indicator-dot"></div>
      <div class="status-text">
        <span class="status-title">Diocesan Shrine &amp; Parish</span>
        <span class="status-subtitle">Pilar, Sorsogon · Online</span>
      </div>
    </div>
  </div>
</aside>

<style>
  /* ===== Premium 2026 SaaS Sidebar ===== */
  .admin-sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    display: flex;
    flex-direction: column;
    padding: 20px 14px 24px;
    background: linear-gradient(180deg, #051d45 0%, #062657 40%, #051e44 100%);
    color: #ffffff;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), padding 0.25s ease;
    z-index: 999;
  }

  .admin-sidebar::-webkit-scrollbar {
    display: none;
  }

  /* Header / Brand */
  .sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 4px 6px 20px;
    margin-bottom: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.09);
  }

  .brand-link {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    min-width: 0;
    transition: opacity 0.18s ease;
  }

  .brand-link:hover {
    opacity: 0.92;
  }

  .brand-logo-wrap {
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    filter: drop-shadow(0 3px 8px rgba(0, 0, 0, 0.3));
    transition: transform 0.2s ease;
  }

  .brand-link:hover .brand-logo-wrap {
    transform: scale(1.06);
  }

  .brand-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
  }

  .brand-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .brand-title {
    font-family: var(--font-heading);
    font-weight: 700;
    font-size: 15px;
    color: #ffffff;
    white-space: nowrap;
    letter-spacing: -0.01em;
    line-height: 1.2;
  }

  .brand-badge {
    color: #e2ba64;
    font-size: 8.5px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-top: 2px;
  }

  .sidebar-toggle {
    width: 28px;
    height: 28px;
    display: grid;
    place-items: center;
    padding: 0;
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 7px;
    background: rgba(255, 255, 255, 0.06);
    color: #c9def5;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s ease;
  }

  .sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.14);
    border-color: rgba(255, 255, 255, 0.3);
    color: #ffffff;
  }

  .sidebar-toggle svg {
    width: 14px;
    height: 14px;
    transition: transform 0.25s ease;
  }

  /* Navigation Structure */
  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 14px;
    flex: 1;
  }

  .nav-section {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .nav-section-title {
    padding: 6px 12px 4px;
    color: #8bb3e8;
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    user-select: none;
  }

  /* Nav Items */
  .admin-nav-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 8px 12px;
    border-radius: 8px;
    color: #c9ddf5;
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 500;
    transition: all 0.16s ease;
    position: relative;
    user-select: none;
  }

  .admin-nav-item:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    transform: translateX(2px);
  }

  /* Active Item: Sleek 2026 SaaS Pill */
  .admin-nav-item.active {
    background: rgba(255, 255, 255, 0.13);
    color: #ffffff;
    font-weight: 600;
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.12), 0 2px 6px rgba(0, 0, 0, 0.15);
  }

  /* Sleek Gold Left Accent Indicator */
  .admin-nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 6px;
    bottom: 6px;
    width: 3.5px;
    border-radius: 0 3px 3px 0;
    background: #d8aa3c;
    box-shadow: 0 0 8px rgba(216, 170, 60, 0.6);
  }

  .admin-nav-subitem {
    font-size: 11.5px !important;
    padding: 6px 12px 6px 30px !important;
    color: #a8caea;
  }

  .nav-subitem-bullet {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.35);
    flex-shrink: 0;
    transition: all 0.16s ease;
  }

  .admin-nav-subitem:hover .nav-subitem-bullet,
  .admin-nav-subitem.active .nav-subitem-bullet {
    background: #d8aa3c;
    box-shadow: 0 0 6px rgba(216, 170, 60, 0.8);
    transform: scale(1.2);
  }

  .admin-nav-subitem.active::before {
    left: 10px;
    width: 2.5px;
  }

  .nav-icon-box {
    width: 20px;
    height: 20px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
  }

  .nav-icon-box svg {
    width: 17px;
    height: 17px;
    transition: color 0.16s ease;
  }

  .admin-nav-item.active .nav-icon-box svg {
    color: #f7d27e;
  }

  .nav-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -0.01em;
  }

  .nav-badge-pill {
    margin-left: auto;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #f7d27e;
    background: rgba(216, 170, 60, 0.16);
    border: 1px solid rgba(216, 170, 60, 0.35);
    padding: 1px 6px;
    border-radius: 10px;
    text-transform: uppercase;
  }

  /* Disabled Upcoming Services */
  .admin-nav-item.nav-item-disabled {
    opacity: 0.5;
    cursor: not-allowed !important;
    pointer-events: none;
    color: #8daed5;
    user-select: none;
  }

  .admin-nav-item.nav-item-disabled:hover {
    background: transparent !important;
    transform: none !important;
  }

  .nav-badge-soon {
    margin-left: auto;
    font-size: 8.5px;
    font-weight: 800;
    letter-spacing: 0.06em;
    color: #f7d27e;
    background: rgba(216, 170, 60, 0.15);
    border: 1px solid rgba(216, 170, 60, 0.35);
    padding: 1.5px 7px;
    border-radius: 10px;
    text-transform: uppercase;
  }

  .nav-section-soon {
    padding-top: 10px;
    margin-top: 6px;
    border-top: 1px dashed rgba(255, 255, 255, 0.1);
  }

  /* Footer Status Card */
  .sidebar-footer {
    padding-top: 14px;
    margin-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }

  .footer-status-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
  }

  .status-indicator-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 6px rgba(34, 197, 94, 0.7);
    flex-shrink: 0;
  }

  .status-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .status-title {
    font-size: 10px;
    font-weight: 700;
    color: #e2eeff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .status-subtitle {
    font-size: 9px;
    color: #8bb3e8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* ===== Collapsed Sidebar Mode ===== */
  .sidebar-collapsed .admin-sidebar {
    padding-left: 8px;
    padding-right: 8px;
  }

  .sidebar-collapsed .brand-info,
  .sidebar-collapsed .nav-section-title,
  .sidebar-collapsed .nav-label,
  .sidebar-collapsed .nav-badge-pill,
  .sidebar-collapsed .sidebar-footer {
    display: none;
  }

  .sidebar-collapsed .sidebar-header {
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding-bottom: 14px;
  }

  .sidebar-collapsed .brand-link {
    display: flex;
    justify-content: center;
  }

  .sidebar-collapsed .brand-logo-wrap {
    width: 38px;
    height: 38px;
  }

  .sidebar-collapsed .sidebar-toggle {
    margin: 0 auto;
  }

  .sidebar-collapsed .sidebar-toggle svg {
    transform: rotate(180deg);
  }

  .sidebar-collapsed .admin-nav-item {
    justify-content: center;
    padding: 10px 0;
    border-radius: 9px;
  }

  .sidebar-collapsed .admin-nav-item.active::before {
    display: none;
  }

  .sidebar-collapsed .admin-nav-item.active {
    background: rgba(255, 255, 255, 0.18);
    box-shadow: 0 0 0 2px var(--gold);
  }

  /* ===== Mobile Drawer Mode ===== */
  @media (max-width: 900px) {
    .admin-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: 275px;
      transform: translateX(-100%);
      transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 0 35px rgba(0, 0, 0, 0.5);
    }

    .admin-sidebar.mobile-open {
      transform: translateX(0);
    }

    .sidebar-toggle {
      display: none;
    }
  }

  /* Nav Dropdown Toggle Button Hover */
  .nav-dropdown-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
  }
</style>

<script>
  function toggleCommissionsDropdown(event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    const container = document.getElementById('commissions-subitems-wrap');
    const dropdown = document.getElementById('commissions-nav-dropdown');
    const arrow = dropdown ? dropdown.querySelector('.dropdown-arrow-icon') : null;
    if (!container) return;

    const isVisible = window.getComputedStyle(container).display !== 'none';
    if (isVisible) {
      container.style.display = 'none';
      if (arrow) arrow.style.transform = 'rotate(0deg)';
      if (dropdown) dropdown.classList.remove('expanded');
      try { localStorage.setItem('sidebar_commissions_expanded', '0'); } catch(e) {}
    } else {
      container.style.display = 'flex';
      if (arrow) arrow.style.transform = 'rotate(180deg)';
      if (dropdown) dropdown.classList.add('expanded');
      try { localStorage.setItem('sidebar_commissions_expanded', '1'); } catch(e) {}
    }
  }

  // Restore preferred state on page load unless current route actively overrides
  document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('commissions-subitems-wrap');
    const dropdown = document.getElementById('commissions-nav-dropdown');
    if (!container || !dropdown) return;
    
    // If a subitem is active, keep it expanded
    const hasActiveChild = container.querySelector('.admin-nav-item.active');
    if (hasActiveChild) {
      container.style.display = 'flex';
      const arrow = dropdown.querySelector('.dropdown-arrow-icon');
      if (arrow) arrow.style.transform = 'rotate(180deg)';
      dropdown.classList.add('expanded');
      return;
    }

    try {
      const saved = localStorage.getItem('sidebar_commissions_expanded');
      if (saved === '1') {
        container.style.display = 'flex';
        const arrow = dropdown.querySelector('.dropdown-arrow-icon');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
        dropdown.classList.add('expanded');
      }
    } catch(e) {}
  });
</script>
