<!-- resources/views/layouts/admin.blade.php -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') — Pilar Shrine</title>
    <link rel="icon" href="/images/pilar-shrine-logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/portal-theme.css'])

    <style>
        :root {
            --navy: #062f78;
            --navy-dark: #041e4d;
            --navy-light: #0d4399;
            --blue: #155cb4;
            --blue-subtle: #eff6ff;
            --gold: #d8aa3c;
            --gold-light: #fef8eb;
            --gold-hover: #c4962c;
            --ink: #0f172a;
            --ink-secondary: #334155;
            --muted: #64748b;
            --muted-light: #94a3b8;
            --border: #e2e8f0;
            --border-subtle: #f1f5f9;
            --bg: #f8fafc;
            --surface: #ffffff;
            --success: #16a34a;
            --success-bg: #dcfce7;
            --warning: #d97706;
            --warning-bg: #fef3c7;
            --danger: #dc2626;
            --danger-bg: #fee2e2;
            --info: #2563eb;
            --info-bg: #dbeafe;
            --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.05);
            --shadow-sm: 0 2px 6px rgba(15, 23, 42, 0.06);
            --shadow: 0 4px 16px rgba(6, 47, 120, 0.07);
            --shadow-lg: 0 12px 28px rgba(6, 47, 120, 0.11);
            --radius-sm: 6px;
            --radius: 10px;
            --radius-lg: 14px;
            --radius-xl: 18px;
            --sidebar-width: 270px;
            --sidebar-collapsed-width: 74px;
            --topbar-height: 72px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: var(--font-body);
            font-size: 13px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== Main Layout Grid ===== */
        .admin-layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            transition: grid-template-columns 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .admin-layout.sidebar-collapsed {
            grid-template-columns: var(--sidebar-collapsed-width) 1fr;
        }

        /* ===== Main Content Region ===== */
        .main-content {
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content-body {
            flex: 1;
            padding: 30px 36px 60px;
            max-width: 1560px;
            width: 100%;
            margin: 0 auto;
        }

        /* ===== Sidebar Backdrop for Mobile Drawer ===== */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }

        .sidebar-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* ===== Floating Tooltip for Collapsed Sidebar ===== */
        .sidebar-tooltip {
            position: fixed;
            z-index: 1005;
            padding: 6px 11px;
            border-radius: var(--radius-sm);
            background: #0f1e36;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.02em;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transform: translateX(-4px);
            transition: opacity 0.12s ease, transform 0.12s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-tooltip.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* ===== Generic Utility Classes ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 15px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.18s ease;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy);
        }

        .btn-primary:hover {
            background: var(--navy-light);
            border-color: var(--navy-light);
            box-shadow: 0 4px 12px rgba(6, 47, 120, 0.2);
        }

        .btn-outline {
            background: #fff;
            color: var(--ink-secondary);
            border-color: var(--border);
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: var(--muted-light);
            color: var(--navy);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            gap: 16px;
        }

        /* The page/module title is now rendered dynamically in the top navbar */
        .page-header h2,
        .inq > h2 {
            display: none !important;
        }

        .page-header .actions,
        .page-header > .btn {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        /* If page-header has no other content besides the title, collapse cleanly */
        .page-header:empty,
        .page-header:has(> h2:only-child),
        .page-header:has(> div:only-child > h2:only-child:not(:has(~ *))) {
            margin-bottom: 0;
            display: none;
        }

        /* ===== Responsive Breakpoints ===== */
        @media (max-width: 1024px) {
            .content-body {
                padding: 24px 20px 48px;
            }
        }

        @media (max-width: 900px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .sidebar-backdrop {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .content-body {
                padding: 18px 14px 40px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="admin-layout" id="admin-layout">
        <!-- Sidebar Backdrop for Mobile Overlay -->
        <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true"></div>

        <!-- Modern Sidebar -->
        <x-admin-sidebar />

        <!-- Main Workspace -->
        <main class="main-content" id="main-content">
            <!-- Modern Topbar -->
            <x-admin-topbar :title="$title ?? (trim($__env->yieldContent('title')) ?: null)" />

            <!-- Content Area -->
            <div class="content-body">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Collapsed Tooltip Target -->
    <div class="sidebar-tooltip" id="sidebar-tooltip" role="tooltip"></div>

    <script>
        (() => {
            const layout = document.getElementById('admin-layout');
            const sidebar = document.querySelector('.admin-sidebar');
            const toggle = document.getElementById('sidebar-toggle');
            const mobileToggle = document.getElementById('mobile-sidebar-toggle');
            const backdrop = document.getElementById('sidebar-backdrop');
            const tooltip = document.getElementById('sidebar-tooltip');

            // --- Desktop Sidebar Collapse ---
            const setCollapsed = (collapsed) => {
                if (window.innerWidth <= 900) return;
                layout?.classList.toggle('sidebar-collapsed', collapsed);
                toggle?.setAttribute('aria-expanded', String(!collapsed));
                toggle?.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            };

            const savedCollapse = localStorage.getItem('pilar-admin-sidebar-collapsed') === 'true';
            if (window.innerWidth > 900 && savedCollapse) {
                setCollapsed(true);
            }

            toggle?.addEventListener('click', () => {
                hideTooltip();
                const willCollapse = !layout.classList.contains('sidebar-collapsed');
                setCollapsed(willCollapse);
                localStorage.setItem('pilar-admin-sidebar-collapsed', String(willCollapse));
            });

            // --- Mobile Drawer Toggle ---
            const openMobileDrawer = () => {
                sidebar?.classList.add('mobile-open');
                backdrop?.classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            const closeMobileDrawer = () => {
                sidebar?.classList.remove('mobile-open');
                backdrop?.classList.remove('active');
                document.body.style.overflow = '';
            };

            mobileToggle?.addEventListener('click', openMobileDrawer);
            backdrop?.addEventListener('click', closeMobileDrawer);

            // --- Tooltip on Collapsed Navigation ---
            const hideTooltip = () => tooltip?.classList.remove('visible');
            const showTooltip = (el) => {
                if (!layout?.classList.contains('sidebar-collapsed') || window.innerWidth <= 900) return;
                const label = el.getAttribute('data-title') || el.querySelector('.nav-label')?.textContent.trim();
                if (!label || !tooltip) return;
                const rect = el.getBoundingClientRect();
                tooltip.textContent = label;
                tooltip.style.left = `${rect.right + 12}px`;
                tooltip.style.top = `${rect.top + (rect.height / 2)}px`;
                tooltip.style.transform = 'translateY(-50%)';
                tooltip.classList.add('visible');
            };

            document.querySelectorAll('.admin-nav-item').forEach((item) => {
                item.addEventListener('mouseenter', () => showTooltip(item));
                item.addEventListener('mouseleave', hideTooltip);
                item.addEventListener('focus', () => showTooltip(item));
                item.addEventListener('blur', hideTooltip);
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 900) closeMobileDrawer();
                });
            });

            sidebar?.addEventListener('scroll', hideTooltip);

            // --- Global Keyboard Shortcuts ---
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    const searchInput = document.getElementById('global-search-input');
                    searchInput?.focus();
                    searchInput?.select();
                }
                if (e.key === 'Escape') {
                    closeMobileDrawer();
                    document.querySelectorAll('.dropdown-popover.open').forEach(menu => menu.classList.remove('open'));
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>