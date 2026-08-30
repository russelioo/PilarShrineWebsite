<aside class="sidebar">
  <svg aria-hidden="true" style="display:none">
    <symbol id="i-dashboard" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 3.5a4 4 0 0 1 0 7.5M18 15a6 6 0 0 1 3 5v1"/></symbol>
    <symbol id="i-staff" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2M18 8h4M20 6v4"/></symbol>
    <symbol id="i-heart" viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></symbol>
    <symbol id="i-form" viewBox="0 0 24 24"><path d="M6 3h12a2 2 0 0 1 2 2v16H4V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></symbol>
    <symbol id="i-money" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M7 9H6v1M17 15h1v-1"/></symbol>
    <symbol id="i-record" viewBox="0 0 24 24"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2ZM14 3v5h5M8 13h8M8 17h6"/></symbol>
    <symbol id="i-megaphone" viewBox="0 0 24 24"><path d="m3 11 15-6v14L3 13v-2ZM7 15l1 5h4l-2-4M21 9v6"/></symbol>
  </svg>
  <div class="brand">
    <div class="brand-mark"><img src="/images/pilar-shrine-logo.png" alt="Pilar Shrine"></div>
    <div class="brand-copy"><b>Pilar Shrine</b><small>Staff Portal</small></div>
    <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Collapse sidebar" aria-expanded="true">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
    </button>
  </div>
  <nav class="nav">
    <a class="nav-top {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}"><svg class="nav-icon"><use href="#i-dashboard"/></svg><span>Dashboard</span></a>

    <div class="nav-group">
      <p class="nav-group-label">Requests Management</p>
      <a href="{{ route('staff.mass-intentions') }}" class="{{ request()->routeIs('staff.mass-intentions') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-heart"/></svg><span>Mass Intentions</span></a>
      <a href="{{ route('staff.sacrament-requests') }}" class="{{ request()->routeIs('staff.sacrament-requests') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-record"/></svg><span>Sacrament Requests</span></a>
      <a href="{{ route('staff.inquiries') }}" class="{{ request()->routeIs('staff.inquiries') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-form"/></svg><span>Inquiries</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Scheduling</p>
      <a href="{{ route('staff.mass-schedules') }}" class="{{ request()->routeIs('staff.mass-schedules') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-calendar"/></svg><span>Mass Schedule</span></a>
      <a href="{{ route('staff.sacrament-schedules') }}" class="{{ request()->routeIs('staff.sacrament-schedules') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-clock"/></svg><span>Sacraments Schedule</span></a>
      <a href="{{ route('staff.events-calendar') }}" class="{{ request()->routeIs('staff.events-calendar') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-calendar"/></svg><span>Events Calendar</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Records</p>
      <a href="{{ route('staff.baptism-records') }}" class="{{ request()->routeIs('staff.baptism-records') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-users"/></svg><span>Baptism Records</span></a>
      <a href="{{ route('staff.marriage-records') }}" class="{{ request()->routeIs('staff.marriage-records') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-heart"/></svg><span>Marriage Records</span></a>
      <a href="{{ route('staff.confirmation-records') }}" class="{{ request()->routeIs('staff.confirmation-records') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-record"/></svg><span>Confirmation Records</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Content</p>
      <a href="{{ route('staff.announcements') }}" class="{{ request()->routeIs('staff.announcements') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-megaphone"/></svg><span>Announcements</span></a>
      <a href="{{ route('staff.events') }}" class="{{ request()->routeIs('staff.events') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-calendar"/></svg><span>Events</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Others</p>
      <a href="{{ route('staff.parishioners') }}" class="{{ request()->routeIs('staff.parishioners') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-users"/></svg><span>Parishioners</span></a>
      <a href="{{ route('staff.reports') }}" class="{{ request()->routeIs('staff.reports') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-form"/></svg><span>Reports</span></a>
      <a href="{{ route('staff.profile-settings') }}" class="{{ request()->routeIs('staff.profile-settings') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-staff"/></svg><span>Profile / Settings</span></a>
    </div>
  </nav>
</aside>