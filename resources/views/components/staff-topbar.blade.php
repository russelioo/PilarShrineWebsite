<header class="topbar">
  <h1>{{ $title ?? 'Dashboard' }}</h1>
  <div class="profile-actions">
    <div class="profile"><div class="avatar">PS</div><div><b>Parish Staff</b><br><small>Staff Member</small></div></div>
    <form method="POST" action="{{ route('staff.logout') }}">@csrf<button class="logout-button" type="submit">Log out</button></form>
  </div>
</header>