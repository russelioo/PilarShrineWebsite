<!-- resources/views/components/admin-topbar.blade.php -->
<header class="admin-topbar">
  <div class="topbar-left">
    <!-- Mobile Drawer Hamburger Toggle -->
    <button class="mobile-menu-btn" id="mobile-sidebar-toggle" type="button" aria-label="Open sidebar menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>

    <!-- Global Search -->
    <div class="global-search-wrap" id="global-search-wrap">
      <div class="search-input-box">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input 
          type="search" 
          id="global-search-input" 
          placeholder="Search parishioners, requests, or events..." 
          aria-label="Global search across parish records"
          autocomplete="off"
        >
        <div class="search-shortcut" aria-hidden="true">
          <kbd>Ctrl</kbd> + <kbd>K</kbd>
        </div>
      </div>

      <!-- Quick Search Dropdown / Palette -->
      <div class="search-dropdown-palette" id="search-dropdown-palette">
        <div class="search-palette-group">
          <span class="palette-label">Quick Jump to Modules</span>
          <a href="{{ route('admin.parishioners') }}" class="palette-item">
            <span class="palette-icon">👤</span>
            <div>
              <strong>Parishioners Directory</strong>
              <small>Manage registered church members</small>
            </div>
            <span class="palette-tag">User Management</span>
          </a>
          <a href="{{ route('admin.mass-intentions') }}" class="palette-item">
            <span class="palette-icon">🕯</span>
            <div>
              <strong>Mass Intentions</strong>
              <small>Review offerings &amp; liturgical intentions</small>
            </div>
            <span class="palette-tag">Requests</span>
          </a>
          <a href="{{ route('admin.mass-schedules') }}" class="palette-item">
            <span class="palette-icon">⛪</span>
            <div>
              <strong>Mass Schedules</strong>
              <small>Sunday and weekday liturgical times</small>
            </div>
            <span class="palette-tag">Scheduling</span>
          </a>
          <a href="{{ route('admin.ministries') }}" class="palette-item">
            <span class="palette-icon">👥</span>
            <div>
              <strong>Parish Ministries</strong>
              <small>Apostolates, organizations &amp; groups</small>
            </div>
            <span class="palette-tag">Ministries</span>
          </a>
          <a href="{{ route('admin.announcements') }}" class="palette-item">
            <span class="palette-icon">📢</span>
            <div>
              <strong>Announcements</strong>
              <small>Bulletin &amp; portal notices</small>
            </div>
            <span class="palette-tag">Content</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="topbar-right">
    <!-- Notification Bell with Dropdown -->
    <div class="topbar-dropdown-wrap" id="notification-dropdown-wrap">
      <button class="icon-action-btn" id="notification-btn" type="button" aria-label="Notifications" aria-expanded="false" aria-haspopup="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span class="notification-badge">3</span>
      </button>

      <!-- Notification Popover -->
      <div class="dropdown-popover notification-popover" id="notification-popover" role="menu">
        <div class="popover-header">
          <div>
            <strong>Notifications</strong>
            <span class="unread-count">3 unread</span>
          </div>
          <a href="{{ route('admin.notifications') }}" class="mark-read-btn">View all</a>
        </div>
        <div class="notification-list">
          <a href="{{ route('admin.mass-intentions') }}" class="notification-item unread">
            <span class="notif-dot"></span>
            <div>
              <p><strong>New Mass Intention</strong> submitted by Maria Santos for Thanksgiving.</p>
              <small>15 minutes ago</small>
            </div>
          </a>
          <a href="{{ route('admin.appointments') }}" class="notification-item unread">
            <span class="notif-dot"></span>
            <div>
              <p><strong>Baptism Interview Request</strong> scheduled for Sept 12.</p>
              <small>1 hour ago</small>
            </div>
          </a>
          <a href="{{ route('admin.dashboard') }}#livestream-panel" class="notification-item unread">
            <span class="notif-dot"></span>
            <div>
              <p><strong>Sunday Mass Livestream</strong> status is currently offline.</p>
              <small>3 hours ago</small>
            </div>
          </a>
        </div>
      </div>
    </div>

    <!-- Admin Profile Dropdown -->
    <div class="topbar-dropdown-wrap" id="profile-dropdown-wrap">
      <button class="profile-trigger-btn" id="profile-trigger-btn" type="button" aria-label="Admin account menu" aria-expanded="false" aria-haspopup="true">
        <div class="admin-avatar">PA</div>
        <div class="admin-meta">
          <span class="admin-name">Parish Administrator</span>
          <span class="admin-role">Administrator</span>
        </div>
        <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>

      <!-- Profile Dropdown Menu -->
      <div class="dropdown-popover profile-popover" id="profile-popover" role="menu">
        <div class="profile-popover-header">
          <strong>Parish Administrator</strong>
          <small>admin@pilarshrine.test</small>
          <span class="role-chip">System Admin</span>
        </div>
        <div class="profile-popover-links">
          <a href="{{ route('admin.staff') }}" class="popover-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>My Profile &amp; Staff</span>
          </a>
          <a href="{{ route('admin.notifications') }}" class="popover-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"></circle>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            <span>Account Settings</span>
          </a>
        </div>
        <div class="profile-popover-footer">
          <form method="POST" action="{{ route('admin.logout') }}">
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
  .admin-topbar {
    height: var(--topbar-height);
    background: #ffffff;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
  }

  .topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
    max-width: 580px;
  }

  .mobile-menu-btn {
    display: none;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--navy);
    cursor: pointer;
    place-items: center;
    padding: 0;
  }

  .mobile-menu-btn svg {
    width: 18px;
    height: 18px;
  }

  .global-search-wrap {
    position: relative;
    width: 100%;
  }

  .search-input-box {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }

  .search-icon {
    position: absolute;
    left: 14px;
    width: 16px;
    height: 16px;
    color: var(--muted);
    pointer-events: none;
  }

  .search-input-box input {
    width: 100%;
    height: 40px;
    padding: 0 70px 0 38px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: #f8fafc;
    color: var(--ink);
    font-size: 12.5px;
    outline: none;
    transition: all 0.2s ease;
  }

  .search-input-box input:focus {
    background: #ffffff;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(21, 92, 180, 0.12);
  }

  .search-shortcut {
    position: absolute;
    right: 12px;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 3px;
  }

  .search-shortcut kbd {
    display: inline-block;
    padding: 2px 5px;
    font-size: 10px;
    font-family: inherit;
    font-weight: 700;
    color: var(--muted);
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
  }

  /* Quick Search Palette */
  .search-dropdown-palette {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    display: none;
    overflow: hidden;
  }

  .search-dropdown-palette.open {
    display: block;
    animation: dropdownSlide 0.18s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .search-palette-group {
    padding: 8px 0;
  }

  .palette-label {
    display: block;
    padding: 6px 14px 4px;
    color: var(--muted);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }

  .palette-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 14px;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease;
  }

  .palette-item:hover {
    background: #f1f5f9;
  }

  .palette-icon {
    font-size: 16px;
    width: 24px;
    text-align: center;
  }

  .palette-item strong {
    display: block;
    color: var(--navy);
    font-size: 12px;
  }

  .palette-item small {
    display: block;
    color: var(--muted);
    font-size: 10.5px;
  }

  .palette-tag {
    margin-left: auto;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--blue);
    background: var(--blue-subtle);
    padding: 2px 7px;
    border-radius: 4px;
  }

  /* Topbar Right Actions */
  .topbar-right {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .topbar-dropdown-wrap {
    position: relative;
  }

  .icon-action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--ink-secondary);
    display: grid;
    place-items: center;
    cursor: pointer;
    position: relative;
    transition: all 0.18s ease;
  }

  .icon-action-btn:hover {
    background: #f8fafc;
    color: var(--navy);
    border-color: var(--muted-light);
  }

  .icon-action-btn svg {
    width: 19px;
    height: 19px;
  }

  .notification-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--danger);
    color: #ffffff;
    font-size: 9px;
    font-weight: 800;
    display: grid;
    place-items: center;
    border: 2px solid #ffffff;
  }

  /* Profile Trigger */
  .profile-trigger-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 10px 4px 4px;
    border-radius: 30px;
    border: 1px solid var(--border);
    background: #ffffff;
    cursor: pointer;
    transition: all 0.18s ease;
  }

  .profile-trigger-btn:hover {
    background: #f8fafc;
    border-color: var(--muted-light);
  }

  .admin-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 11.5px;
    display: grid;
    place-items: center;
    box-shadow: 0 2px 6px rgba(6, 47, 120, 0.2);
  }

  .admin-meta {
    display: flex;
    flex-direction: column;
    text-align: left;
  }

  .admin-name {
    font-size: 12px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
  }

  .admin-role {
    font-size: 10px;
    color: var(--muted);
  }

  .chevron-icon {
    width: 14px;
    height: 14px;
    color: var(--muted);
    transition: transform 0.2s ease;
  }

  .profile-trigger-btn[aria-expanded="true"] .chevron-icon {
    transform: rotate(180deg);
  }

  /* Dropdown Popovers */
  .dropdown-popover {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 300px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    display: none;
    overflow: hidden;
  }

  .dropdown-popover.open {
    display: block;
    animation: dropdownSlide 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }

  @keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Notification Popover */
  .popover-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: #f8fafc;
  }

  .popover-header strong {
    font-size: 12.5px;
    color: var(--navy);
  }

  .unread-count {
    display: inline-block;
    margin-left: 6px;
    font-size: 9.5px;
    font-weight: 700;
    color: var(--danger);
    background: var(--danger-bg);
    padding: 1px 6px;
    border-radius: 10px;
  }

  .mark-read-btn {
    font-size: 11px;
    font-weight: 700;
    color: var(--blue);
    text-decoration: none;
  }

  .notification-list {
    max-height: 280px;
    overflow-y: auto;
  }

  .notification-item {
    display: flex;
    gap: 10px;
    padding: 12px 16px;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid var(--border-subtle);
    transition: background 0.15s ease;
  }

  .notification-item:last-child {
    border-bottom: none;
  }

  .notification-item:hover {
    background: #f8fafc;
  }

  .notification-item.unread {
    background: #fdfefe;
  }

  .notif-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--blue);
    margin-top: 5px;
    flex-shrink: 0;
  }

  .notification-item p {
    margin: 0;
    font-size: 11.5px;
    line-height: 1.4;
    color: var(--ink-secondary);
  }

  .notification-item p strong {
    color: var(--navy);
  }

  .notification-item small {
    display: block;
    margin-top: 3px;
    color: var(--muted);
    font-size: 10px;
  }

  /* Profile Popover */
  .profile-popover-header {
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
    background: #f8fafc;
  }

  .profile-popover-header strong {
    display: block;
    color: var(--navy);
    font-size: 13px;
  }

  .profile-popover-header small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    margin-top: 2px;
  }

  .role-chip {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 2px 7px;
    border-radius: 4px;
    margin-top: 8px;
  }

  .profile-popover-links {
    padding: 6px 0;
  }

  .popover-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 18px;
    color: var(--ink-secondary);
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: background 0.15s ease;
  }

  .popover-link svg {
    width: 15px;
    height: 15px;
    color: var(--muted);
  }

  .popover-link:hover {
    background: #f1f5f9;
    color: var(--navy);
  }

  .profile-popover-footer {
    padding: 8px 12px;
    border-top: 1px solid var(--border);
    background: #fafbfc;
  }

  .logout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 12px;
    border: 1px solid #fecaca;
    border-radius: var(--radius-sm);
    background: #fff5f5;
    color: #b91c1c;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
  }

  .logout-btn svg {
    width: 14px;
    height: 14px;
  }

  .logout-btn:hover {
    background: #fee2e2;
    border-color: #f87171;
  }

  /* Responsive Adjustments */
  @media (max-width: 900px) {
    .mobile-menu-btn {
      display: grid;
    }

    .admin-topbar {
      padding: 0 20px;
    }
  }

  @media (max-width: 640px) {
    .search-shortcut {
      display: none;
    }

    .admin-meta {
      display: none;
    }

    .profile-trigger-btn {
      padding: 4px;
      border-radius: 50%;
    }

    .chevron-icon {
      display: none;
    }
  }
</style>

<script>
  (() => {
    // Search Quick Palette Toggle
    const searchInput = document.getElementById('global-search-input');
    const searchPalette = document.getElementById('search-dropdown-palette');

    searchInput?.addEventListener('focus', () => {
      searchPalette?.classList.add('open');
    });

    document.addEventListener('click', (e) => {
      const searchWrap = document.getElementById('global-search-wrap');
      if (!searchWrap?.contains(e.target)) {
        searchPalette?.classList.remove('open');
      }
    });

    // Dropdowns (Notification & Profile)
    const setupDropdown = (btnId, popoverId) => {
      const btn = document.getElementById(btnId);
      const popover = document.getElementById(popoverId);
      if (!btn || !popover) return;

      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = popover.classList.contains('open');
        document.querySelectorAll('.dropdown-popover.open').forEach(p => p.classList.remove('open'));
        document.querySelectorAll('[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));

        if (!isOpen) {
          popover.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    };

    setupDropdown('notification-btn', 'notification-popover');
    setupDropdown('profile-trigger-btn', 'profile-popover');

    document.addEventListener('click', (e) => {
      if (!e.target.closest('.dropdown-popover') && !e.target.closest('.icon-action-btn') && !e.target.closest('.profile-trigger-btn')) {
        document.querySelectorAll('.dropdown-popover.open').forEach(p => p.classList.remove('open'));
        document.querySelectorAll('[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));
      }
    });
  })();
</script>