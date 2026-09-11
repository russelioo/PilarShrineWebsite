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
  <h1>{{ $title ?? 'Dashboard' }}</h1>
  <div class="profile-actions">
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