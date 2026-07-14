<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal – WhatsApp Catalog</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    <style>
        /* Force datatables header to not overlap */
        .dataTables_wrapper .grid {
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .dataTables_length label,
        .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dataTables_length select {
            padding: 0.4rem 2rem 0.4rem 0.75rem !important;
            font-size: 0.95rem;
            min-width: 90px;
            cursor: pointer;
        }

        .dataTables_filter label {
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .dataTables_wrapper>.grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 1rem !important;
            }

            .dataTables_filter label,
            .dataTables_length label {
                justify-content: flex-start;
                width: 100%;
            }

            .dataTables_filter input,
            .dataTables_length select {
                width: 100%;
                max-width: 100%;
            }
        }

        :root {
            --green-primary: #16a34a;
            --green-deep: #14532d;
            --green-accent: #22c55e;
            --green-light: #dcfce7;
            --green-muted: #166534;
            --bg-body: #f0fdf4;
            --sidebar-bg: linear-gradient(160deg, #14532d 0%, #166534 50%, #15803d 100%);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
        }

        /* ── Sidebar ───────────────────────────────────────────── */
        .sidebar {
            background: var(--sidebar-bg);
            position: relative;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: -80px;
            left: -80px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(34, 197, 94, .25) 0%, transparent 70%);
            pointer-events: none;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(34, 197, 94, .15) 0%, transparent 70%);
            pointer-events: none;
        }

        .sidebar-logo {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(6px);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .7rem 1.25rem;
            margin: .18rem .75rem;
            border-radius: .6rem;
            color: rgba(255, 255, 255, .78);
            font-size: .875rem;
            font-weight: 500;
            transition: all .22s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            transform: translateX(3px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, .18);
            color: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .18);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            border-radius: 0 4px 4px 0;
            background: #86efac;
        }

        .nav-link svg {
            flex-shrink: 0;
            opacity: .85;
        }

        .nav-link.active svg {
            opacity: 1;
        }

        .logout-btn {
            background: rgba(239, 68, 68, .15);
            border: 1px solid rgba(239, 68, 68, .3);
            color: #fca5a5;
            border-radius: .6rem;
            font-weight: 600;
            transition: all .22s ease;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, .28);
            color: #fff;
        }

        /* ── Top bar ────────────────────────────────────────────── */
        .topbar {
            background: rgba(255, 255, 255, .85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #bbf7d0;
            box-shadow: 0 1px 8px rgba(21, 128, 61, .06);
        }

        /* ── Main content ───────────────────────────────────────── */
        .main-scroll {
            background: var(--bg-body);
        }

        /* ── Alert ──────────────────────────────────────────────── */
        .flash-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-left: 4px solid #16a34a;
            color: #15803d;
            box-shadow: 0 2px 10px rgba(22, 163, 74, .12);
        }

        .flash-error {
            background: linear-gradient(135deg, #fff5f5, #fee2e2);
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            box-shadow: 0 2px 10px rgba(239, 68, 68, .1);
        }

        /* ── Fade-in animation ──────────────────────────────────── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up {
            animation: fadeUp .45s ease both;
        }

        .fade-up-d1 {
            animation-delay: .05s;
        }

        .fade-up-d2 {
            animation-delay: .10s;
        }

        .fade-up-d3 {
            animation-delay: .15s;
        }

        .fade-up-d4 {
            animation-delay: .20s;
        }

        /* ── Badge ──────────────────────────────────────────────── */
        .nav-badge {
            margin-left: auto;
            background: #22c55e;
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            padding: .1rem .45rem;
            border-radius: 99px;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden" style="font-family:'Inter',sans-serif;">

    <!-- ══ Sidebar Overlay for Mobile ══ -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden md:hidden transition-opacity"
        onclick="toggleSidebar()"></div>

    <!-- ══ Sidebar ══ -->
    <aside id="sidebar"
        class="sidebar w-64 flex flex-col shadow-2xl flex-shrink-0 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-50">

        <!-- Logo -->
        <div class="sidebar-logo mx-4 mt-5 mb-2 p-4 rounded-xl">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 no-underline">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                    style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                        <path
                            d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.533 5.855L.057 23.486a.75.75 0 00.914.914l5.631-1.476A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.651-.491-5.185-1.349l-.372-.214-3.851 1.009 1.028-3.752-.23-.384A9.956 9.956 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-white font-bold text-base leading-tight">PizzaTwist</span>
                    <span class="block text-green-300 font-medium text-xs">Whatsapp Ordering</span>
                    <span class="block text-green-300 font-medium text-xs">System</span>
                </div>
            </a>
        </div>

        <!-- Nav label -->
        <p class="px-6 mt-4 mb-1 text-xs font-semibold uppercase tracking-widest" style="color:rgba(255,255,255,.38);">
            Main Menu</p>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto pb-2">
            @auth
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5" style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.reports') }}"
                    class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Reports
                </a>

                <a href="{{ route('categories.index') }}"
                    class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Categories
                </a>

                <a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                    Units
                </a>

                <a href="{{ route('products.index') }}"
                    class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Products
                </a>

                <a href="{{ route('stores.index') }}" class="nav-link {{ request()->routeIs('stores.*') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Stores
                </a>

                <a href="{{ route('notifications.index') }}"
                    class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                </a>

                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Orders
                </a>

                <a href="{{ route('admin.stripe') }}"
                    class="nav-link {{ request()->routeIs('admin.stripe') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Stripe
                </a>

                <a href="{{ route('admin.settings') }}"
                    class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            @endauth
        </nav>

        <!-- Logout -->
        @auth
            <div class="p-4 border-t" style="border-color:rgba(255,255,255,.1);">
                <div class="flex items-center gap-3 mb-3 px-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0"
                        style="background:rgba(34,197,94,.25);color:#86efac;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs truncate" style="color:rgba(255,255,255,.45);">Administrator</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="logout-btn w-full flex justify-center items-center gap-2 py-2 px-3 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <!-- ══ Main area ══ -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top bar -->
        <header class="topbar flex items-center justify-between px-4 md:px-8 py-3 flex-shrink-0">
            <div class="flex items-center">
                <button onclick="toggleSidebar()"
                    class="md:hidden mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-base font-semibold text-gray-800">
                        @yield('page-title', 'Dashboard')
                    </h1>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ now()->format('l, d F Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Green dot status -->
                    <span
                        class="flex items-center gap-1.5 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded-full px-3 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
                        System Online
                    </span>
                </div>
        </header>

        <!-- Page content -->
        <main class="main-scroll flex-1 overflow-x-hidden overflow-y-auto">
            <div class="p-8 max-w-7xl mx-auto">
                @if(session('success'))
                    <div class="flash-success p-4 rounded-xl mb-6 flex items-start gap-3 fade-up">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="flash-error p-4 rounded-xl mb-6 flex items-start gap-3 fade-up">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    @yield('scripts')

    <script>
        $(document).on('init.dt', function (e, settings) {
            var api = new $.fn.dataTable.Api(settings);
            var $table = $(api.table().node());
            if (!$table.parent().hasClass('overflow-x-auto')) {
                $table.wrap('<div class="overflow-x-auto w-full pb-3"></div>');
            }
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>

</html>