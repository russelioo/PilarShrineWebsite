<!-- resources/views/components/admin-sidebar.blade.php -->
<aside class="sidebar">
  <div class="brand">
    <div class="brand-mark"><img src="/images/pilar-shrine-logo.png" alt=""></div>
    <div><b>Pilar Shrine</b><small>Admin Portal</small></div>
  </div>

  <nav class="nav">
    <a class="nav-top {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
       href="{{ route('admin.dashboard') }}">
      <b>⌂</b><span>Dashboard</span>
    </a>

    <div class="nav-group">
      <p class="nav-group-label">User Management</p>
      <a href="{{ route('admin.parishioners') }}" 
         class="{{ request()->routeIs('admin.parishioners*') ? 'active' : '' }}">
        <b>♙</b><span>Parishioners</span>
      </a>
      <a href="{{ route('admin.staff') }}" 
         class="{{ request()->routeIs('admin.staff*') ? 'active' : '' }}">
        <b>♜</b><span>Staff Management</span>
      </a>
      <a href="#"><b>♚</b><span>Roles &amp; Permissions</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Requests</p>
      <a href="#"><b>▤</b><span>All Mass Intentions</span></a>
      <a href="#"><b>♢</b><span>Sacrament Requests</span></a>
      <a href="#"><b>✉</b><span>Inquiries</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Scheduling System</p>
      <a href="#"><b>▣</b><span>Events &amp; Schedule</span></a>
      <a href="#"><b>▦</b><span>Calendar Management</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Content Management</p>
      <a href="#"><b>✦</b><span>Announcements</span></a>
      <a href="#"><b>▥</b><span>Website Content</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Financial</p>
      <a href="#"><b>$</b><span>Donations</span></a>
      <a href="#"><b>⛁</b><span>Transactions</span></a>
      <a href="#"><b>◔</b><span>Reports</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">Records</p>
      <a href="#"><b>✎</b><span>Sacramental Records</span></a>
      <a href="#"><b>✓</b><span>Certificates</span></a>
    </div>

    <div class="nav-group">
      <p class="nav-group-label">System</p>
      <a href="#"><b>▲</b><span>Reports &amp; Analytics</span></a>
      <a href="#"><b>⚙</b><span>Settings</span></a>
    </div>
  </nav>
</aside>