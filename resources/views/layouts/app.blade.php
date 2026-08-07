<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AUTOBOX') }} — Key Access & Real-Time Monitoring</title>
    <meta name="description" content="AUTOBOX CCSICT — Physical key management, access control, and real-time monitoring dashboard.">

    <!-- Enforce Dark Theme -->
    <script>
        document.documentElement.classList.add('dark');
        localStorage.setItem('autobox_theme', 'dark');
    </script>

    <!-- Google Fonts: Outfit (headings) + Plus Jakarta Sans (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased flex flex-col md:flex-row min-h-screen">

    <!-- ═══════════════════════════════════
         FLOATING TOAST NOTIFICATION CONTAINER
         ═══════════════════════════════════ -->
    <div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none"></div>

    <!-- ═══════════════════════════════════
         MOCKUP PURPLE SIDEBAR
         ═══════════════════════════════════ -->
    <aside class="mockup-sidebar w-full md:w-64 flex-shrink-0 flex flex-col justify-between p-5 text-white z-20">
        <div>
            <!-- Brand Logo Header -->
            <div class="px-2 py-3 mb-4 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="sidebar-brand-logo shadow-lg">
                        <i class="fa-solid fa-key text-lg text-white"></i>
                    </div>
                    <span class="font-heading font-extrabold text-2xl tracking-tight text-white">AUTOBOX</span>
                </a>
            </div>

            <!-- Primary Navigation Links -->
            <nav class="space-y-1.5">
                <a href="{{ route('dashboard') }}"
                   class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-cells-large w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('keys.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('keys.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-key w-5 text-center"></i>
                    <span>Key Slots</span>
                </a>

                <a href="{{ route('users.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span>Users & QR</span>
                </a>

                <a href="{{ route('schedules.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days w-5 text-center"></i>
                    <span>Schedules</span>
                </a>

                <a href="{{ route('transactions.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                    <span>Transactions</span>
                </a>

                <a href="{{ route('access-logs.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('access-logs.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-qrcode w-5 text-center"></i>
                    <span>QR Audit Logs</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Analytics</span>
                </a>
            </nav>

            <!-- Support Section -->
            <div class="mt-8 pt-4 border-t border-white/10 space-y-1.5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-nav-link w-full text-left">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ═══════════════════════════════════
         MAIN CONTENT & TOP NAVBAR
         ═══════════════════════════════════ -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

        <!-- Top Header Bar Container -->
        <div class="px-8 pt-6 pb-2">
            <header class="top-header-card flex items-center justify-between flex-wrap gap-4">
                <!-- Left: Welcome Title with Purple Branded Badge -->
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-base shadow-sm border border-[var(--border-subtle)]">
                        <i class="fa-solid fa-crown text-[var(--purple-primary)]"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-heading font-extrabold text-[var(--text-heading)] tracking-tight">
                                Welcome back, {{ Auth::user()->name ?? 'Admin' }}!
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] uppercase tracking-wider">
                                Active Session
                            </span>
                        </div>
                        <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">AUTOBOX Key Access & Real-Time Monitor</p>
                    </div>
                </div>


                <!-- Right: Admin Profile Chip -->
                <div class="flex items-center gap-4">
                    <!-- User Profile Chip -->
                    <div class="flex items-center gap-3 p-1.5 pr-3.5 rounded-full bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <div class="relative">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-500 text-white font-extrabold text-xs flex items-center justify-center shadow-md">
                                {{ strtoupper(substr(Auth::user()->name ?? 'W', 0, 1)) }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#19142c]"></span>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-xs font-extrabold text-[var(--text-heading)] leading-none">{{ Auth::user()->name ?? 'William Jake' }}</p>
                            <p class="text-[10px] text-[var(--purple-primary)] font-bold mt-0.5 capitalize">{{ Auth::user()->role ?? 'Admin' }}</p>
                        </div>
                    </div>
                </div>
            </header>
        </div>

        <!-- Main View Content -->
        <main class="px-8 pb-8">
            @yield('content')
        </main>
    </div>

    <!-- ═══════════════════════════════════
         GLOBAL SCRIPTS: TOASTS & SPINNERS
         ═══════════════════════════════════ -->
    <script>
        // Global Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const isSuccess = type === 'success';
            const bgClass = isSuccess ? 'bg-emerald-600 text-white shadow-emerald-500/20' : 'bg-rose-600 text-white shadow-rose-500/20';
            const iconClass = isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation';

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center justify-between gap-3 p-4 rounded-2xl shadow-xl transition-all transform duration-300 translate-x-10 opacity-0 ${bgClass}`;
            toast.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <i class="fa-solid ${iconClass} text-lg flex-shrink-0"></i>
                    <span class="text-xs font-bold leading-snug">${message}</span>
                </div>
                <button type="button" class="text-white/70 hover:text-white transition-colors ml-2" onclick="this.parentElement.remove()">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-10', 'opacity-0');
            }, 50);

            setTimeout(() => {
                toast.classList.add('translate-x-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if(session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif

            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        const originalHtml = submitBtn.innerHTML;
                        submitBtn.dataset.originalContent = originalHtml;
                        submitBtn.disabled = true;

                        submitBtn.innerHTML = `
                            <i class="fa-solid fa-spinner animate-spin text-xs"></i>
                            <span>Processing...</span>
                        `;
                        submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                    }
                });
            });
        });
    </script>
</body>
</html>