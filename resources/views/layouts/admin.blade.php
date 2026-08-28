<!-- resources/views/layouts/admin.blade.php -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Pilar Shrine Admin')</title>
    <link rel="icon" href="/images/pilar-shrine-logo.png">

    <style>
        :root{--navy:#062f78;--blue:#0b58b5;--gold:#d6aa3e;--ink:#1b2b40;--muted:#718096;--bg:#f3f7fb;--line:#dce5ee;--side-top:#052b69;--side-bottom:#073f94}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--ink);font-family:Arial,sans-serif}
        .layout{min-height:100vh;display:grid;grid-template-columns:280px 1fr}

        /* ===== Sidebar ===== */
        .sidebar{position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;padding:26px 18px 26px;background:linear-gradient(180deg,var(--side-top),var(--side-bottom));color:#fff}
        .brand{display:flex;align-items:center;gap:11px;padding:4px 8px 18px}
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
        .nav-group a.active,.nav-group a:hover{background:#ffffff1c;color:#fff}
        .nav-group a.active{border-left:3px solid var(--gold);padding-left:9px}

        /* ===== Main / topbar / content shell ===== */
        .main{min-width:0}
        .topbar{height:78px;display:flex;align-items:center;justify-content:space-between;padding:0 36px;border-bottom:1px solid var(--line);background:#fff}
        .topbar h1{margin:0;color:var(--navy);font-size:22px;font-family:Georgia,serif}
        .profile{display:flex;align-items:center;gap:11px;font-size:11px}
        .avatar{width:36px;height:36px;display:grid;place-items:center;border-radius:50%;background:#eaf2fb;color:var(--navy);font-weight:800}
        .profile-actions{display:flex;align-items:center;gap:14px}
        .logout-button{padding:9px 13px;border:1px solid #d5dfe9;border-radius:6px;background:#fff;color:#8b2635;font-size:9px;font-weight:700;text-transform:uppercase;cursor:pointer}
        .logout-button:hover{border-color:#b64555;background:#fff7f8}
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
            .logout-button{padding:8px 10px}
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <x-admin-sidebar />

        <main class="main">
            <!-- Top Bar -->
            <x-admin-topbar :title="$title ?? 'Dashboard'" />

            <!-- Content -->
            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>