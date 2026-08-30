<header class="topbar">
  <h1>{{ $title ?? 'Dashboard' }}</h1>
  <div class="profile-actions">
    <div class="profile"><div class="avatar">PC</div><div><b>Parishioner Account</b><br><small>Parishioner</small></div></div>
    <form method="POST" action="{{ route('parishioner.logout') }}">@csrf<button class="logout-button" type="submit">Log out</button></form>
  </div>
</header>