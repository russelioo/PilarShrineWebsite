@php
    $authUser = auth()->user();
    $displayName = $authUser?->displayName ?? 'Parishioner';
    $roleName = match($authUser?->role) {
        'admin' => 'Administrator',
        'staff' => 'Staff',
        default => 'Parishioner',
    };
    $initials = $authUser?->initials ?? 'PA';
    $avatar = $authUser?->avatar;
@endphp
<header class="topbar">
  <div class="topbar-title-wrap">
    <h1>{{ $title ?? 'Dashboard' }}</h1>
  </div>
  <div class="profile-actions">
    <!-- Clean Link Back to Official Public Website -->
    <a href="/" class="btn-topbar-website" title="Return to Official Parish Website">
      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
      </svg>
      <span>Official Website</span>
    </a>

    <a href="{{ route('parishioner.profile-settings') }}" class="profile profile-link" title="View Profile / Settings">
      <div class="avatar-wrap">
        @if(!empty($avatar))
          <img class="avatar-img" src="{{ $avatar }}" alt="{{ $displayName }}" referrerpolicy="no-referrer" />
        @else
          <span class="avatar-initials">{{ $initials }}</span>
        @endif
      </div>
      <div class="profile-meta">
        <strong class="profile-name">{{ $displayName }}</strong>
        <small class="profile-role">{{ $roleName }}</small>
      </div>
    </a>

    <form method="POST" action="{{ route('parishioner.logout') }}">
      @csrf
      <button class="logout-button" type="submit">Log out</button>
    </form>
  </div>
</header>
