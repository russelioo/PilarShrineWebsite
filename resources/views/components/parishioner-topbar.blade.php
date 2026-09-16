@php
    $authUser = auth()->user();
    $displayName = $authUser?->displayName ?? 'Parishioner';
    $roleName = $authUser?->role_badge_label ?? 'Parishioner';
    $initials = $authUser?->initials ?? 'PA';
    $avatar = $authUser?->avatar;
@endphp

<header class="parishioner-topbar">
  <div class="topbar-left">
    <!-- Mobile Drawer Toggle -->
    <button class="mobile-menu-btn" id="mobile-sidebar-toggle" type="button" aria-label="Open sidebar menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>

    <!-- Page Title -->
    <div class="topbar-title-wrap">
      <h1>{{ $title ?? 'Dashboard' }}</h1>
    </div>
  </div>

  <div class="topbar-right">
    <!-- Back to Website -->
    <a href="/" class="btn-topbar-website" title="Return to Official Parish Website">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
      </svg>
      <span>Official Website</span>
    </a>

    <!-- Profile Dropdown -->
    <div class="topbar-dropdown-wrap" id="profile-dropdown-wrap">
      <button class="profile-trigger-btn" id="profile-trigger-btn" type="button"
              aria-label="Account menu" aria-expanded="false" aria-haspopup="true">
        <div class="p-avatar-wrap">
          @if(!empty($avatar))
            <img class="p-avatar-img" src="{{ $avatar }}" alt="{{ $displayName }}" referrerpolicy="no-referrer" />
          @else
            <span class="p-avatar-initials">{{ $initials }}</span>
          @endif
        </div>
        <div class="p-meta">
          <span class="p-name">{{ $displayName }}</span>
          <span class="p-role">{{ $roleName }}</span>
        </div>
        <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>

      <!-- Profile Dropdown Menu -->
      <div class="dropdown-popover profile-popover" id="profile-popover" role="menu">
        <div class="profile-popover-header">
          <strong>{{ $displayName }}</strong>
          <small>{{ $authUser?->email ?? '' }}</small>
          <span class="role-chip">{{ $roleName }}</span>
        </div>
        <div class="profile-popover-links">
          <a href="{{ route('parishioner.profile-settings') }}" class="popover-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Profile &amp; Settings</span>
          </a>
          <a href="{{ route('parishioner.ministries') }}" class="popover-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>My Ministries</span>
          </a>
        </div>
        <div class="profile-popover-footer">
          <form method="POST" action="{{ route('parishioner.logout') }}">
            @csrf
            <button class="logout-btn" type="submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span>Log out</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>

<style>
  /* ===== Parishioner Topbar ===== */
  :root {
    --topbar-height-p: 68px;
  }

  .parishioner-topbar {
    height: var(--topbar-height-p);
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    gap: 16px;
  }

  .parishioner-topbar .topbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1;
    min-width: 0;
  }

  .parishioner-topbar .topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
  }

  /* Mobile menu button */
  .parishioner-topbar .mobile-menu-btn {
    display: none;
    width: 36px;
    height: 36px;
    border-radius: 7px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #062f78;
    cursor: pointer;
    place-items: center;
    padding: 0;
    flex-shrink: 0;
  }

  .parishioner-topbar .mobile-menu-btn svg {
    width: 18px;
    height: 18px;
  }

  /* Page title */
  .parishioner-topbar .topbar-title-wrap h1 {
    margin: 0;
    color: #062f78;
    font-size: 20px;
    font-family: 'Libre Baskerville', Georgia, serif;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Website button */
  .parishioner-topbar .btn-topbar-website {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 13px;
    border: 1px solid #ccd8e4;
    border-radius: 7px;
    background: #f8fafc;
    color: #062f78;
    font-size: 11.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.16s ease;
    white-space: nowrap;
  }

  .parishioner-topbar .btn-topbar-website:hover {
    background: #ffffff;
    border-color: #d8aa3c;
    color: #c4962c;
    box-shadow: 0 2px 6px rgba(216, 170, 60, 0.15);
  }

  /* Profile trigger button */
  .parishioner-topbar .profile-trigger-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px 10px 5px 5px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.16s ease;
  }

  .parishioner-topbar .profile-trigger-btn:hover {
    background: #ffffff;
    border-color: #94a3b8;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.08);
  }

  /* Avatar */
  .parishioner-topbar .p-avatar-wrap {
    width: 34px;
    height: 34px;
    min-width: 34px;
    border-radius: 50%;
    border: 2px solid #d8aa3c;
    overflow: hidden;
    display: grid;
    place-items: center;
    background: #eaf2fb;
    flex-shrink: 0;
  }

  .parishioner-topbar .p-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 50%;
  }

  .parishioner-topbar .p-avatar-initials {
    font-size: 12px;
    font-weight: 800;
    color: #062f78;
  }

  /* Name/role */
  .parishioner-topbar .p-meta {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.25;
  }

  .parishioner-topbar .p-name {
    font-size: 12px;
    font-weight: 700;
    color: #062f78;
    white-space: nowrap;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .parishioner-topbar .p-role {
    font-size: 10px;
    color: #64748b;
    white-space: nowrap;
  }

  .parishioner-topbar .chevron-icon {
    width: 14px;
    height: 14px;
    color: #64748b;
    transition: transform 0.2s ease;
    flex-shrink: 0;
  }

  .parishioner-topbar .profile-trigger-btn[aria-expanded="true"] .chevron-icon {
    transform: rotate(180deg);
  }

  /* ===== Dropdown Wrapper ===== */
  .parishioner-topbar .topbar-dropdown-wrap {
    position: relative;
  }

  /* ===== Dropdown Popover ===== */
  .parishioner-topbar .dropdown-popover {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    z-index: 500;
    min-width: 230px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(6, 47, 120, 0.14), 0 4px 10px rgba(0,0,0,0.06);
    overflow: hidden;
    opacity: 0;
    transform: translateY(-6px) scale(0.97);
    pointer-events: none;
    transition: opacity 0.18s ease, transform 0.18s ease;
  }

  .parishioner-topbar .dropdown-popover.open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
  }

  /* Profile Popover Header */
  .parishioner-topbar .profile-popover-header {
    padding: 14px 16px 12px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .parishioner-topbar .profile-popover-header strong {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .parishioner-topbar .profile-popover-header small {
    font-size: 11px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .parishioner-topbar .role-chip {
    margin-top: 4px;
    display: inline-block;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #1d4ed8;
    background: #dbeafe;
    border: 1px solid #bfdbfe;
    padding: 2px 7px;
    border-radius: 10px;
    width: fit-content;
  }

  /* Popover Links */
  .parishioner-topbar .profile-popover-links {
    padding: 6px 8px;
  }

  .parishioner-topbar .popover-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 7px;
    color: #334155;
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 500;
    transition: background 0.14s ease, color 0.14s ease;
  }

  .parishioner-topbar .popover-link:hover {
    background: #f1f5f9;
    color: #062f78;
  }

  .parishioner-topbar .popover-link svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    color: #64748b;
  }

  .parishioner-topbar .popover-link:hover svg { color: #062f78; }

  /* Popover Footer / Logout */
  .parishioner-topbar .profile-popover-footer {
    padding: 6px 8px 8px;
    border-top: 1px solid #f1f5f9;
  }

  .parishioner-topbar .logout-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 10px;
    border: none;
    border-radius: 7px;
    background: none;
    color: #dc2626;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.14s ease;
    text-align: left;
  }

  .parishioner-topbar .logout-btn:hover { background: #fee2e2; }

  .parishioner-topbar .logout-btn svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
  }

  /* ===== Responsive ===== */
  @media (max-width: 900px) {
    .parishioner-topbar .mobile-menu-btn { display: grid; }
    .parishioner-topbar { padding: 0 18px; }
  }

  @media (max-width: 560px) {
    .parishioner-topbar .btn-topbar-website span { display: none; }
    .parishioner-topbar .p-meta { display: none; }
    .parishioner-topbar .chevron-icon { display: none; }
  }
</style>

<script>
  (() => {
    const wrap = document.getElementById('profile-dropdown-wrap');
    const btn = document.getElementById('profile-trigger-btn');
    const popover = document.getElementById('profile-popover');
    if (!wrap || !btn || !popover) return;

    const open = () => {
      popover.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
    };
    const close = () => {
      popover.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    };

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      popover.classList.contains('open') ? close() : open();
    });

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) close();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });

    // Mobile sidebar toggle
    const mobileToggle = document.getElementById('mobile-sidebar-toggle');
    const sidebar = document.querySelector('.parishioner-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');

    mobileToggle?.addEventListener('click', () => {
      sidebar?.classList.add('mobile-open');
      backdrop?.classList.add('active');
      document.body.style.overflow = 'hidden';
    });

    backdrop?.addEventListener('click', () => {
      sidebar?.classList.remove('mobile-open');
      backdrop?.classList.remove('active');
      document.body.style.overflow = '';
    });
  })();
</script>
