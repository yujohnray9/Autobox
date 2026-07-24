<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AUTOBOX') }} - Key Access & Real-Time Monitoring</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome / Lucide Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        .gradient-violet-blue {
            background: linear-gradient(135deg, #6d28d9 0%, #2563eb 100%);
        }
        .gradient-violet-subtle {
            background: linear-gradient(135deg, rgba(109, 40, 217, 0.05) 0%, rgba(37, 99, 235, 0.08) 100%);
        }
        .text-gradient {
            background: linear-gradient(135deg, #6d28d9 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .sidebar-active {
            background: linear-gradient(135deg, #6d28d9 0%, #2563eb 100%);
            color: white !important;
            box-shadow: 0 4px 14px 0 rgba(109, 40, 217, 0.35);
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased flex flex-col md:flex-row min-h-screen">

    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-white border-r border-slate-200 flex-shrink-0 flex flex-col justify-between shadow-sm z-20">
        <div>
            <!-- Brand Logo Header -->
            <div class="p-6 flex items-center justify-between border-b border-slate-100">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl gradient-violet-blue flex items-center justify-center text-white shadow-md shadow-violet-200">
                        <i class="fa-solid fa-key text-lg"></i>
                    </div>
                    <div>
                        <span class="font-heading font-extrabold text-xl tracking-tight text-slate-900">AUTO<span class="text-gradient">BOX</span></span>
                        <p class="text-[10px] uppercase font-bold tracking-widest text-violet-600">CCSICT Key System</p>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid fa-gauge w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('users.*') ? 'sidebar-active' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span>Users & QR</span>
                </a>

                <a href="{{ route('keys.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('keys.*') ? 'sidebar-active' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid fa-key w-5 text-center"></i>
                    <span>Key Slots</span>
                </a>

                <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('schedules.*') ? 'sidebar-active' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid fa-calendar-days w-5 text-center"></i>
                    <span>Schedules</span>
                </a>

                <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('transactions.*') ? 'sidebar-active' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                    <span>Transaction Logs</span>
                </a>

                <a href="{{ route('access-logs.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('access-logs.*') ? 'sidebar-active' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid fa-qrcode w-5 text-center"></i>
                    <span>QR Audit Logs</span>
                </a>

                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('reports.*') ? 'sidebar-active' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Analytics & Reports</span>
                </a>
            </nav>
        </div>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            <div class="flex items-center justify-between p-2 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-violet-100 text-violet-700 font-bold flex items-center justify-center border border-violet-200">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name ?? 'Admin User' }}</p>
                        <p class="text-[10px] text-slate-500 capitalize">{{ Auth::user()->role ?? 'Administrator' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Banner Header -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-heading font-bold text-slate-900">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            <!-- Live Status Pulse & Quick Actions -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>System Active & Real-Time Sync</span>
                </div>
                <span class="text-xs text-slate-400 border-l border-slate-200 pl-4">{{ now()->format('D, M d, Y') }}</span>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-6 mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Main View Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
