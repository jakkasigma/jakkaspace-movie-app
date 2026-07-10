<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — Jakka Space')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0a0a;
            --panel: rgba(255,255,255,0.04);
            --border: rgba(255,255,255,0.08);
            --text: #fff;
            --muted: rgba(255,255,255,0.5);
            --accent: #40E0D0;
            --danger: #ff4444;
            --topbar-h: 52px;
            --sidebar-w: 200px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
        body.no-scroll { overflow: hidden; }

        .admin-topbar {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--topbar-h);
            background: #0d0d0d;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 100;
        }
        .admin-topbar-left { display: flex; align-items: center; gap: 12px; }
        .admin-hamburger {
            display: none;
            background: none; border: none; color: var(--text);
            font-size: 1.3rem; cursor: pointer; padding: 4px; line-height: 1;
        }
        .admin-topbar-logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.15rem; letter-spacing: 0.08em;
            text-decoration: none; color: var(--text); white-space: nowrap;
        }
        .admin-topbar-logo span { color: var(--accent); }
        .admin-topbar-nav { display: flex; gap: 2px; overflow-x: auto; scrollbar-width: none; }
        .admin-topbar-nav::-webkit-scrollbar { display: none; }
        .admin-topbar-link {
            padding: 6px 10px; border-radius: 6px;
            font-size: 0.78rem; color: var(--muted);
            text-decoration: none; white-space: nowrap;
            transition: background 0.2s, color 0.2s;
        }
        .admin-topbar-link:hover { background: var(--panel); color: var(--text); }
        .admin-topbar-link.active { background: rgba(64,224,208,0.1); color: var(--accent); }
        .admin-topbar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .admin-topbar-user { font-size: 0.78rem; color: var(--muted); display: none; }
        .admin-topbar-logout {
            font-size: 0.72rem; color: var(--muted); text-decoration: none;
            padding: 4px 8px; border-radius: 4px;
            border: 1px solid var(--border); white-space: nowrap;
            transition: border-color 0.2s;
        }
        .admin-topbar-logout:hover { border-color: var(--danger); color: var(--danger); }

        .admin-body {
            display: flex;
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }

        .admin-sidebar-overlay {
            display: none;
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            z-index: 150;
        }

        .admin-sidebar {
            width: var(--sidebar-w); flex-shrink: 0;
            background: rgba(255,255,255,0.02);
            border-right: 1px solid var(--border);
            padding: 20px 12px;
            position: sticky; top: var(--topbar-h);
            height: calc(100vh - var(--topbar-h));
            overflow-y: auto;
        }
        .admin-sidebar-section { margin-bottom: 20px; }
        .admin-sidebar-label {
            font-size: 0.65rem; text-transform: uppercase;
            letter-spacing: 0.1em; color: rgba(255,255,255,0.25);
            margin-bottom: 6px; padding: 0 8px;
        }
        .admin-sidebar-link {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 10px; border-radius: 6px;
            color: var(--muted); text-decoration: none;
            font-size: 0.82rem; transition: background 0.2s, color 0.2s;
        }
        .admin-sidebar-link:hover { background: var(--panel); color: var(--text); }
        .admin-sidebar-link.active { background: rgba(64,224,208,0.1); color: var(--accent); }
        .admin-sidebar-link.disabled { opacity: 0.35; cursor: not-allowed; }
        .admin-sidebar-link.disabled:hover { background: none; color: var(--muted); }
        .admin-sidebar-divider { height: 1px; background: var(--border); margin: 12px 0; }

        .admin-main {
            flex: 1; min-width: 0;
            padding: 24px 28px;
            max-width: 960px;
        }
        .admin-header { margin-bottom: 24px; }
        .admin-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.5rem; letter-spacing: 0.05em;
        }
        .admin-subtitle { color: var(--muted); font-size: 0.82rem; margin-top: 4px; }
        .admin-toast {
            background: rgba(0,200,83,0.15);
            border: 1px solid rgba(0,200,83,0.3);
            color: #4caf50; padding: 12px 16px;
            border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem;
        }
        .admin-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 20px;
        }
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
        .admin-stat-value { font-size: 1.6rem; font-weight: 700; display: block; }
        .admin-stat-label { font-size: 0.76rem; color: var(--muted); margin-top: 4px; }
        .admin-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        .admin-table th { text-align: left; padding: 9px 10px; border-bottom: 1px solid var(--border); color: var(--muted); font-weight: 600; white-space: nowrap; }
        .admin-table td { padding: 9px 10px; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .admin-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 6px 12px; border-radius: 6px;
            font-size: 0.8rem; font-weight: 600;
            text-decoration: none; border: 1px solid var(--border);
            background: rgba(255,255,255,0.06); color: var(--text);
            cursor: pointer; transition: background 0.2s;
        }
        .admin-btn:hover { background: rgba(255,255,255,0.12); }
        .admin-btn-primary { background: var(--accent); border-color: var(--accent); color: #000; }
        .admin-btn-primary:hover { opacity: 0.9; }
        .admin-btn-danger { border-color: var(--danger); color: var(--danger); }
        .admin-btn-sm { padding: 3px 7px; font-size: 0.76rem; }
        .admin-form { display: flex; flex-direction: column; gap: 14px; max-width: 500px; }
        .admin-form-label { font-size: 0.8rem; font-weight: 600; color: var(--muted); }
        .admin-form-input {
            width: 100%; padding: 8px 11px;
            border-radius: 6px; border: 1px solid var(--border);
            background: rgba(255,255,255,0.04); color: var(--text);
            font-size: 0.86rem; font-family: inherit; color-scheme: dark;
        }
        select.admin-form-input {
            appearance: auto; -webkit-appearance: auto; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center;
            padding-right: 30px;
        }
        .admin-form-input option { background: #1a1a1a; color: #fff; }
        .admin-form-input:focus { outline: none; border-color: var(--accent); }
        .admin-form-textarea { min-height: 70px; resize: vertical; }
        .admin-form-actions { display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap; }
        .admin-pagination { margin-top: 16px; }
        .admin-pagination a, .admin-pagination span {
            display: inline-block; padding: 5px 9px; border-radius: 6px;
            font-size: 0.76rem; color: var(--muted); text-decoration: none;
            border: 1px solid var(--border); margin-right: 3px;
        }
        .admin-pagination span { background: var(--accent); color: #000; border-color: var(--accent); }

        .admin-modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.6);
            display: none; align-items: flex-start; justify-content: center;
            padding: 20px; overflow-y: auto;
            z-index: 200;
        }
        .admin-modal-overlay.active { display: flex !important; }
        .admin-modal {
            background: #111; border: 1px solid var(--border);
            border-radius: 14px; padding: 24px;
            width: 100%; max-width: 440px;
            margin: auto;
        }
        .admin-modal-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.15rem; letter-spacing: 0.05em; margin-bottom: 16px;
        }
        .admin-modal-actions { display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap; }

        .admin-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* ── Hamburger & Drawer ── */
        .admin-hamburger { display: none; }

        @media (max-width: 900px) {
            .admin-topbar-link { font-size: 0.74rem; padding: 5px 8px; }
        }

        @media (max-width: 768px) {
            .admin-hamburger { display: inline-flex; }
            .admin-topbar-nav { display: none; }
            .admin-topbar-user { display: inline; }
            .admin-sidebar-overlay.open { display: block; }
            .admin-sidebar {
                position: fixed; top: var(--topbar-h); left: 0; bottom: 0;
                width: 260px; z-index: 160;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                background: #0d0d0d;
                border-right: 1px solid var(--border);
            }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-main { padding: 20px 14px; }
            .admin-card { padding: 16px; }
            .admin-stat-value { font-size: 1.4rem; }
            .admin-grid { gap: 10px; }
            .admin-modal { padding: 20px; }
        }

        @media (max-width: 480px) {
            .admin-main { padding: 14px 10px; }
            .admin-title { font-size: 1.2rem; }
            .admin-card { padding: 12px; border-radius: 10px; }
            .admin-table { font-size: 0.76rem; }
            .admin-table th, .admin-table td { padding: 7px 6px; }
            .admin-btn { font-size: 0.75rem; padding: 5px 9px; }
            .admin-btn-sm { font-size: 0.7rem; padding: 3px 6px; }
            .admin-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .admin-stat-value { font-size: 1.2rem; }
            .admin-form { max-width: 100%; }
            .admin-form [style*="grid-template-columns"][style*="1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            .admin-modal { padding: 16px; }
            td .admin-btn, td form { display: block; width: 100%; }
            td form + form { margin-top: 4px; }
            .admin-modal-actions { flex-direction: column; }
            .admin-modal-actions .admin-btn { width: 100%; justify-content: center; }
            .admin-pagination a, .admin-pagination span {
                padding: 4px 7px; font-size: 0.72rem;
            }
            .admin-topbar-right { gap: 6px; }
            .admin-topbar-user { display: none; }
        }

        @media (max-width: 360px) {
            .admin-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <button class="admin-hamburger" id="adminHamburger" onclick="toggleSidebar()" aria-label="Toggle menu">☰</button>
            <a href="{{ route('admin.dashboard') }}" class="admin-topbar-logo">JAKKA <span>ADMIN</span></a>
            <nav class="admin-topbar-nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-topbar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.themes.index') }}" class="admin-topbar-link {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}">Themes</a>
                <a href="{{ route('admin.plans.index') }}" class="admin-topbar-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">Plans</a>
                <a href="{{ route('admin.promo-redeem.index') }}" class="admin-topbar-link {{ request()->routeIs('admin.promo-redeem.*') || request()->routeIs('admin.promos.*') || request()->routeIs('admin.redeem-codes.*') ? 'active' : '' }}">Promo &amp; Redeem</a>
                <a href="{{ route('admin.subscriptions.index') }}" class="admin-topbar-link {{ request()->routeIs('admin.subscriptions.*') && ! request()->routeIs('admin.subscriptions.transactions') ? 'active' : '' }}">Subscriptions</a>
                <a href="{{ route('admin.subscriptions.transactions') }}" class="admin-topbar-link {{ request()->routeIs('admin.subscriptions.transactions') ? 'active' : '' }}">Transactions</a>
                <a href="{{ route('admin.users.index') }}" class="admin-topbar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
            </nav>
        </div>
        <div class="admin-topbar-right">
            <span class="admin-topbar-user">{{ auth()->user()->name }}</span>
            <a href="{{ route('logout') }}" class="admin-topbar-logout"
               onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">Keluar</a>
            <form id="admin-logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
        </div>
    </header>

    <div class="admin-sidebar-overlay" id="adminSidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="admin-body">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-section">
                <p class="admin-sidebar-label">Management</p>
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
                <a href="{{ route('admin.themes.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}">🎨 Themes</a>
                <a href="{{ route('admin.plans.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">📋 Plans</a>
                <a href="{{ route('admin.promo-redeem.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.promo-redeem.*') || request()->routeIs('admin.promos.*') || request()->routeIs('admin.redeem-codes.*') ? 'active' : '' }}">🎯 Promo &amp; Redeem</a>
                <a href="{{ route('admin.subscriptions.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.subscriptions.*') && ! request()->routeIs('admin.subscriptions.transactions') ? 'active' : '' }}">💳 Subscriptions</a>
                <a href="{{ route('admin.subscriptions.transactions') }}" class="admin-sidebar-link {{ request()->routeIs('admin.subscriptions.transactions') ? 'active' : '' }}">📜 Transactions</a>
                <a href="{{ route('admin.users.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">👥 Users</a>
            </div>

            <div class="admin-sidebar-divider"></div>

            <div class="admin-sidebar-section">
                <p class="admin-sidebar-label">System</p>
                <a href="{{ route('movies.index') }}" class="admin-sidebar-link">← Back to App</a>
            </div>
        </aside>

        <main class="admin-main">
            @if (session('success'))
                <div class="admin-toast">{{ session('success') }}</div>
            @endif

            <div class="admin-header">
                <h1 class="admin-title">@yield('title', 'Dashboard')</h1>
                @hasSection('subtitle')
                    <p class="admin-subtitle">@yield('subtitle')</p>
                @endif
            </div>

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('adminSidebarOverlay');
            const body = document.body;
            const isOpen = sidebar.classList.contains('open');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
            body.classList.toggle('no-scroll', !isOpen);
        }
    </script>
</body>
</html>
