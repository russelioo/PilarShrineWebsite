<!-- resources/views/layouts/admin.blade.php -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Pilar Shrine Parishioner')</title>
    <link rel="icon" href="/images/pilar-shrine-logo.png">

    <style>
        :root{--navy:#062f78;--blue:#0b58b5;--gold:#d6aa3e;--ink:#1b2b40;--muted:#718096;--bg:#f3f7fb;--line:#dce5ee;--side-top:#052b69;--side-bottom:#073f94}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--ink);font-family:Arial,sans-serif}
        .layout{min-height:100vh;display:grid;grid-template-columns:280px 1fr;transition:grid-template-columns .25s ease}
        .layout.sidebar-collapsed{grid-template-columns:76px 1fr}

        /* ===== Sidebar ===== */
        .sidebar{position:sticky;top:0;height:100vh;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;display:flex;flex-direction:column;padding:26px 18px;background:linear-gradient(180deg,var(--side-top),var(--side-bottom));color:#fff;transition:padding .25s ease}
        .sidebar::-webkit-scrollbar{display:none}
        .brand{display:flex;align-items:center;gap:11px;padding:4px 8px 18px}
        .brand-copy{min-width:0;white-space:nowrap}
        .sidebar-toggle{width:30px;height:30px;flex:none;display:grid;place-items:center;margin-left:auto;padding:0;border:1px solid #ffffff35;border-radius:7px;background:#ffffff16;color:#fff;cursor:pointer}
        .sidebar-toggle:hover{background:#ffffff2b}
        .sidebar-toggle svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;transition:transform .25s ease}
        .brand-mark{width:42px;height:42px;flex:none;border-radius:50%;background:#fff;display:grid;place-items:center;overflow:hidden}
        .brand-mark img{width:26px;height:32px;object-fit:contain}
        .brand b{display:block;font:700 14px Georgia,serif;color:#fff}
        .brand small{display:block;margin-top:2px;color:#c7c2ec;font-size:9px;text-transform:none}

        .nav{padding:0 2px}
        .nav-top{display:flex;gap:12px;align-items:center;padding:12px 14px;margin:0 2px 20px;border-radius:9px;background:#ffffff26;color:#fff;font-size:12px;font-weight:700;text-decoration:none}
        .nav-top b{font-size:15px}

        .nav-group{margin-bottom:18px}
        .nav-group-label{margin:0 12px 8px;color:#8fb1dc;font-size:9px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
        .nav-group a{display:flex;gap:12px;align-items:center;padding:10px 12px;margin:2px 0;border-radius:7px;color:#dce9f8;font-size:12px;text-decoration:none}
        .nav-group a b{width:16px;text-align:center;font-size:13px;font-style:normal}
        .nav-icon{width:16px;height:16px;flex:none;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
        .nav-group a.active,.nav-group a:hover{background:#ffffff1c;color:#fff}
        .nav-group a.active{border-left:3px solid var(--gold);padding-left:9px}
        .sidebar-collapsed .sidebar{padding-left:10px;padding-right:10px}
        .sidebar-collapsed .brand{justify-content:center;padding-left:0;padding-right:0}
        .sidebar-collapsed .brand-mark,.sidebar-collapsed .brand-copy,.sidebar-collapsed .nav-group-label,.sidebar-collapsed .nav a span{display:none}
        .sidebar-collapsed .sidebar-toggle{margin-left:0}
        .sidebar-collapsed .sidebar-toggle svg{transform:rotate(180deg)}
        .sidebar-collapsed .nav-top,.sidebar-collapsed .nav-group a{justify-content:center;padding-left:12px;padding-right:12px}
        .sidebar-collapsed .nav-group a.active{padding-left:9px}
        .sidebar-tooltip{position:fixed;z-index:1000;padding:7px 10px;border-radius:6px;background:#152238;color:#fff;box-shadow:0 6px 18px #0003;font-size:11px;font-weight:700;white-space:nowrap;pointer-events:none;opacity:0;transform:translateX(-4px);transition:opacity .12s ease,transform .12s ease}
        .sidebar-tooltip.visible{opacity:1;transform:translateX(0)}

        /* ===== Main / topbar / content shell ===== */
        .main{min-width:0}
        .topbar{height:78px;display:flex;align-items:center;justify-content:space-between;padding:0 36px;border-bottom:1px solid var(--line);background:#fff}
        .topbar h1{margin:0;color:var(--navy);font-size:22px;font-family:Georgia,serif}
        .profile-actions{display:flex;align-items:center;gap:16px}
        .profile-link{display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;cursor:pointer;transition:opacity .15s ease}
        .profile-link:hover{opacity:0.85}
        .avatar-wrap{width:38px;height:38px;min-width:38px;min-height:38px;max-width:38px;max-height:38px;border-radius:50%;border:2px solid var(--gold);overflow:hidden;display:grid;place-items:center;background:#eaf2fb;flex-shrink:0;box-sizing:border-box}
        .avatar-wrap .avatar-img{width:100%;height:100%;max-width:100%;max-height:100%;object-fit:cover;display:block;border-radius:50%}
        .avatar-wrap .avatar-initials{font-size:13px;font-weight:800;color:var(--navy);font-family:Arial,sans-serif}
        .profile-meta{display:flex;flex-direction:column;min-width:0;line-height:1.25}
        .profile-name{font-size:12px;font-weight:700;color:var(--navy);white-space:nowrap;max-width:170px;overflow:hidden;text-overflow:ellipsis}
        .profile-role{font-size:10px;color:var(--muted);white-space:nowrap}
        .avatar{width:36px;height:36px;display:grid;place-items:center;border-radius:50%;background:#eaf2fb;color:var(--navy);font-weight:800;overflow:hidden;flex-shrink:0}
        
          .btn-topbar-website{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border:1px solid #ccd8e4;border-radius:6px;background:#f8fafc;color:var(--navy);font-size:11px;font-weight:700;text-decoration:none;transition:all .15s ease}
          .btn-topbar-website:hover{background:#fff;border-color:var(--gold);color:var(--gold)}
          .btn-topbar-website svg{color:var(--navy)}
          .btn-topbar-website:hover svg{color:var(--gold)}

          .logout-button{padding:8px 13px;border:1px solid #d5dfe9;border-radius:6px;background:#fff;color:#8b2635;font-size:9px;font-weight:700;text-transform:uppercase;cursor:pointer;transition:background .15s,border-color .15s}
        .logout-button:hover{border-color:#b64555;background:#fff7f8}

        /* ===== Disabled navigation items & Available Soon elements ===== */
        .nav-group a.nav-item-disabled{opacity:0.55;cursor:not-allowed;position:relative}
        .nav-group a.nav-item-disabled:hover{background:rgba(255,255,255,0.06);color:#dce9f8}
        .badge-soon{margin-left:auto;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;background:rgba(214,170,62,0.25);color:#f7d58b;border:1px solid rgba(214,170,62,0.45);padding:1px 6px;border-radius:10px;line-height:1.4;flex-shrink:0}
        .sidebar-collapsed .badge-soon{display:none}

        .stat-card.stat-card-disabled{opacity:0.65;cursor:not-allowed;position:relative}
        .stat-card.stat-card-disabled:hover{transform:none;box-shadow:none;border-color:var(--line)}
        .stat-card-badge{position:absolute;top:12px;right:14px;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;background:#fff8e6;color:#b45309;border:1px solid #fde68a;padding:2px 7px;border-radius:12px}

        .btn-action-disabled{opacity:0.6;cursor:not-allowed!important;position:relative}
        .btn-action-disabled:hover{transform:none!important;box-shadow:none!important}
        .btn-badge-soon{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;background:rgba(0,0,0,0.15);padding:2px 6px;border-radius:10px;margin-left:6px}

        /* ===== Feature Available Soon Toast ===== */
        .feature-soon-toast{position:fixed;top:24px;left:50%;transform:translateX(-50%) translateY(-30px);z-index:9999;background:var(--navy);color:#fff;border:1.5px solid var(--gold);border-radius:10px;box-shadow:0 12px 35px rgba(6,47,120,0.25);padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;max-width:460px;width:calc(100% - 36px);opacity:0;pointer-events:none;transition:all .28s cubic-bezier(0.16,1,0.3,1)}
        .feature-soon-toast.show{transform:translateX(-50%) translateY(0);opacity:1;pointer-events:auto}
        .toast-content{display:flex;align-items:center;gap:12px}
        .toast-content svg{flex-shrink:0;color:var(--gold)}
        .toast-content strong{display:block;font-size:13px;font-family:Georgia,serif;color:#fff;margin-bottom:2px}
        .toast-content p{margin:0;font-size:11px;color:#dce9f8;line-height:1.4}
        .toast-close{background:none;border:none;color:#dce9f8;font-size:20px;line-height:1;cursor:pointer;padding:0 4px;margin-left:auto}
        .toast-close:hover{color:#fff}
        .content{padding:32px 36px}

        .welcome{display:flex;justify-content:space-between;align-items:end;margin-bottom:25px}
        .welcome h2{margin:0 0 6px;color:var(--navy);font-size:26px;font-family:Georgia,serif}
        .welcome p,.date{margin:0;color:var(--muted);font-size:11px}

        /* ===== Generic buttons used across admin pages ===== */
        .btn{padding:10px 16px;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer}
        .btn-primary{border:1px solid var(--navy);background:var(--navy);color:#fff}
        .btn-outline{border:1px solid var(--line);background:#fff;color:var(--ink)}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .page-header h2{margin:0;color:var(--navy);font-family:Georgia,serif}
        .page-header .actions{display:flex;gap:10px}

        @media(max-width:900px){
            .layout{grid-template-columns:76px 1fr}
            .brand div,.nav-group-label,.nav-group a span,.nav-top span{display:none}
        }
        @media(max-width:620px){
            .layout{display:block}
            .sidebar{display:none}
            .topbar,.content{padding-left:18px;padding-right:18px}
            .welcome{display:block}
            .date{margin-top:12px}
            .profile>div:last-child{display:none}
            .profile-actions{gap:8px}
            
          .btn-topbar-website{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border:1px solid #ccd8e4;border-radius:6px;background:#f8fafc;color:var(--navy);font-size:11px;font-weight:700;text-decoration:none;transition:all .15s ease}
          .btn-topbar-website:hover{background:#fff;border-color:var(--gold);color:var(--gold)}
          .btn-topbar-website svg{color:var(--navy)}
          .btn-topbar-website:hover svg{color:var(--gold)}

          .logout-button{padding:8px 10px}
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <x-parishioner-sidebar />

        <main class="main">
            <!-- Top Bar -->
            <x-parishioner-topbar :title="$title ?? 'Dashboard'" />

            <!-- Content -->
            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>


    <div class="sidebar-tooltip" id="sidebar-tooltip" role="tooltip"></div>

    <script>
        (() => {
            const layout = document.querySelector('.layout');
            const toggle = document.getElementById('sidebar-toggle');
            const tooltip = document.getElementById('sidebar-tooltip');
            const sidebar = document.querySelector('.sidebar');
            if (!layout || !toggle || !tooltip) return;

            const hideTooltip = () => tooltip.classList.remove('visible');
            const showTooltip = (link) => {
                if (!layout.classList.contains('sidebar-collapsed')) return;
                const label = link.querySelector('span')?.textContent.trim();
                if (!label) return;
                const rect = link.getBoundingClientRect();
                tooltip.textContent = label;
                tooltip.style.left = `${rect.right + 10}px`;
                tooltip.style.top = `${rect.top + (rect.height / 2)}px`;
                tooltip.style.transform = 'translateY(-50%)';
                tooltip.classList.add('visible');
            };

            document.querySelectorAll('.nav a').forEach((link) => {
                link.addEventListener('mouseenter', () => showTooltip(link));
                link.addEventListener('mouseleave', hideTooltip);
                link.addEventListener('focus', () => showTooltip(link));
                link.addEventListener('blur', hideTooltip);
            });
            sidebar?.addEventListener('scroll', hideTooltip);

            const setCollapsed = (collapsed) => {
                layout.classList.toggle('sidebar-collapsed', collapsed);
                toggle.setAttribute('aria-expanded', String(!collapsed));
                toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            };

            setCollapsed(localStorage.getItem('parishioner-sidebar-collapsed') === 'true');
            toggle.addEventListener('click', () => {
                hideTooltip();
                const collapsed = !layout.classList.contains('sidebar-collapsed');
                setCollapsed(collapsed);
                localStorage.setItem('parishioner-sidebar-collapsed', String(collapsed));
            });
        })();
    </script>
    <div id="feature-soon-toast" class="feature-soon-toast" role="alert" aria-live="polite">
        <div class="toast-content">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
                <strong id="toast-title">This feature will be available soon</strong>
                <p id="toast-desc">Online submissions for this parish service are currently being prepared. Thank you for your patience!</p>
            </div>
        </div>
        <button type="button" class="toast-close" onclick="window.hideFeatureSoonToast()" aria-label="Close">&times;</button>
    </div>

    <script>
        window.showFeatureSoonNotice = function(featureName) {
            const toast = document.getElementById('feature-soon-toast');
            const titleEl = document.getElementById('toast-title');
            if (titleEl) {
                titleEl.textContent = featureName ? `${featureName} — Available Soon` : 'This feature will be available soon';
            }
            if (!toast) return;
            toast.classList.add('show');
            clearTimeout(window._toastTimeout);
            window._toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3800);
        };
        window.hideFeatureSoonToast = function() {
            const toast = document.getElementById('feature-soon-toast');
            if (toast) toast.classList.remove('show');
        };
        document.addEventListener('click', function(e) {
            const disabledTarget = e.target.closest('[data-disabled-feature="true"]');
            if (disabledTarget) {
                e.preventDefault();
                e.stopPropagation();
                const name = disabledTarget.getAttribute('data-feature-name') || disabledTarget.getAttribute('title') || '';
                window.showFeatureSoonNotice(name.replace('This feature will be available soon', '').trim());
            }
        });
    </script>
    <!-- Scripts -->
    @stack('scripts')
</body>
</html>