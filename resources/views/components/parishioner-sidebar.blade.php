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
    <div class="brand-copy"><b>Pilar Shrine</b><small>Parishioner Portal</small></div>
    <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Collapse sidebar" aria-expanded="true">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
    </button>
  </div>
  <nav class="nav">
    <a class="nav-top {{ request()->routeIs('parishioner.dashboard') ? 'active' : '' }}" href="{{ route('parishioner.dashboard') }}"><svg class="nav-icon"><use href="#i-dashboard"/></svg><span>Dashboard</span></a>

    <div class="nav-group">
      <p class="nav-group-label">My Requests</p>
      <a href="{{ route('parishioner.mass-intentions') }}" class="{{ request()->routeIs('parishioner.mass-intentions') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-heart"/></svg><span>Mass Intentions</span></a>
      <a href="{{ route('parishioner.sacrament-requests') }}" class="{{ request()->routeIs('parishioner.sacrament-requests') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-record"/></svg><span>Sacrament Requests</span></a>
      <a href="{{ route('parishioner.inquiries') }}" class="{{ request()->routeIs('parishioner.inquiries') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-form"/></svg><span>My Inquiries</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Submit Request</p>
      <a href="{{ route('parishioner.request-mass-intention') }}" class="{{ request()->routeIs('parishioner.request-mass-intention') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-heart"/></svg><span>Request Mass Intention</span></a>
      <a href="{{ route('parishioner.request-sacrament') }}" class="{{ request()->routeIs('parishioner.request-sacrament') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-record"/></svg><span>Baptism / Wedding / Funeral</span></a>
      <a href="{{ route('parishioner.other-requests') }}" class="{{ request()->routeIs('parishioner.other-requests') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-form"/></svg><span>Other Requests</span></a>
    </div>

    <div class="nav-group parishioner-services">
      <a href="{{ route('parishioner.events-schedule') }}" class="{{ request()->routeIs('parishioner.events-schedule') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-calendar"/></svg><span>Events &amp; Schedule</span></a>
      <a href="{{ route('parishioner.announcements') }}" class="{{ request()->routeIs('parishioner.announcements') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-megaphone"/></svg><span>Announcements</span></a>
      <a href="{{ route('parishioner.donations') }}" class="{{ request()->routeIs('parishioner.donations') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-money"/></svg><span>Donations</span></a>
      <a href="{{ route('parishioner.ministries') }}" class="{{ request()->routeIs('parishioner.ministries') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-users"/></svg><span>Ministries</span></a>
      <a href="{{ route('parishioner.messages-inquiries') }}" class="{{ request()->routeIs('parishioner.messages-inquiries') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-form"/></svg><span>Messages / Inquiries</span></a>
      <a href="{{ route('parishioner.profile-settings') }}" class="{{ request()->routeIs('parishioner.profile-settings') ? 'active' : '' }}"><svg class="nav-icon"><use href="#i-staff"/></svg><span>Profile / Settings</span></a>
    </div>
  </nav>
</aside>