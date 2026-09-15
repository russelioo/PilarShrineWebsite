<!-- resources/views/components/staff-topbar.blade.php -->
@props(['title' => null])

@php
  $authUser = auth()->user() ?? \App\Models\User::where('email', 'soredajohnrussel15@gmail.com')->first() ?? \App\Models\User::where('role', 'staff')->latest()->first();

  $authName = $authUser?->displayName ?: ($authUser?->name ?: 'John Russel Soreda');
  $authEmail = $authUser?->email ?: 'soredajohnrussel15@gmail.com';
  $authRole = ucfirst($authUser?->role ?: 'staff');
  $authAvatar = $authUser?->avatar;
  $authInitials = $authUser?->initials ?: 'JS';
@endphp

<header class="topbar">
  <h1>{{ $title ?? 'Dashboard' }}</h1>
  <div class="profile-actions">
    <div class="profile">
      <div class="avatar" title="{{ $authName }}">
        @if(!empty($authAvatar))
          <img src="{{ $authAvatar }}" alt="{{ $authName }}" class="avatar-img" />
        @else
          <span class="avatar-initials">{{ $authInitials }}</span>
        @endif
      </div>
      <div class="profile-info">
        <b>{{ $authName }}</b>
        <small>{{ $authEmail }}</small>
      </div>
    </div>
    <form method="POST" action="{{ route('staff.logout') }}">
      @csrf
      <button class="logout-button" type="submit">Log out</button>
    </form>
  </div>
</header>