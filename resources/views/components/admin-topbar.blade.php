<!-- resources/views/components/admin-topbar.blade.php -->
<header class="topbar">
  <h1>{{ $title ?? 'Dashboard' }}</h1>
  <div class="profile-actions">
    <div class="profile">
      <div class="avatar">PA</div>
      <div>
        <b>Parish Administrator</b><br>
        <small>Administrator</small>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button class="logout-button" type="submit">Log out</button>
    </form>
  </div>
</header>