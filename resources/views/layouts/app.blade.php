<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AUTOBOX') }} — Key Access & Real-Time Monitoring</title>
    <meta name="description" content="AUTOBOX CCSICT — Physical key management, access control, and real-time monitoring dashboard.">
    <link rel="icon" type="image/jpeg" href="{{ asset('build/assets/logo.jpg') }}">

    <!-- Enforce Light Theme -->
    <script>
        document.documentElement.classList.remove('dark');
        localStorage.setItem('autobox_theme', 'light');
    </script>

    <!-- Google Fonts: Outfit (headings) + Plus Jakarta Sans (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased flex flex-col md:flex-row min-h-screen bg-[var(--app-bg)] text-[var(--text-body)]" x-data="{ sidebarOpen: false }">

    <!-- ═══════════════════════════════════
         FLOATING TOAST NOTIFICATION CONTAINER
         ═══════════════════════════════════ -->
    <div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none px-4 sm:px-0"></div>

    <!-- ═══════════════════════════════════
         MOBILE TOP APP BAR (VISIBLE ON MOBILE ONLY)
         ═══════════════════════════════════ -->
    <div class="md:hidden flex items-center justify-between px-4 py-3 bg-gradient-to-r from-[var(--purple-primary)] to-[var(--purple-dark)] text-white shadow-md sticky top-0 z-30 flex-shrink-0">
        <div class="flex items-center gap-3">
            <button type="button"
                    @click="sidebarOpen = true"
                    class="w-9 h-9 rounded-xl bg-white/15 text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition-all focus:outline-none"
                    aria-label="Open Navigation Menu">
                <i class="fa-solid fa-bars text-base"></i>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-white shadow overflow-hidden p-0.5 flex items-center justify-center border border-white/50 flex-shrink-0">
                    <img src="{{ asset('build/assets/logo.jpg') }}" alt="AutoBox Logo" class="w-full h-full object-contain rounded-full">
                </div>
                <div class="leading-tight">
                    <span class="font-heading font-extrabold text-base tracking-tight text-white block">AUTOBOX</span>
                    <span class="text-[9px] font-bold text-purple-200 uppercase tracking-wider block">CCSICT ISU</span>
                </div>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-white/20 text-white font-extrabold text-xs flex items-center justify-center ring-2 ring-white/30">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════
         MOBILE OFF-CANVAS SIDEBAR DRAWER
         ═══════════════════════════════════ -->
    <!-- Backdrop Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden"
         @click="sidebarOpen = false"
         x-cloak></div>

    <!-- Slide-over Navigation Panel -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] mockup-sidebar p-5 text-white flex flex-col justify-between shadow-2xl md:hidden overflow-y-auto"
         x-cloak>
        <div>
            <!-- Drawer Brand Header with Close Button -->
            <div class="px-2 py-3 mb-4 flex items-center justify-between border-b border-white/10 pb-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="sidebar-brand-logo w-10 h-10 rounded-full bg-white shadow-lg overflow-hidden p-0.5 flex items-center justify-center border-2 border-white/50 flex-shrink-0">
                        <img src="{{ asset('build/assets/logo.jpg') }}" alt="AutoBox Logo" class="w-full h-full object-contain rounded-full">
                    </div>
                    <div>
                        <span class="font-heading font-extrabold text-xl tracking-tight text-white block leading-none">AUTOBOX</span>
                        <span class="text-[9px] font-bold text-purple-200 uppercase tracking-widest block mt-1">CCSICT ISU</span>
                    </div>
                </a>
                <button type="button"
                        @click="sidebarOpen = false"
                        class="w-8 h-8 rounded-lg bg-white/15 text-white/80 hover:text-white hover:bg-white/25 flex items-center justify-center transition-all"
                        aria-label="Close Sidebar">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Mobile Navigation Links -->
            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
                    <i class="fa-solid fa-table-cells-large w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('keys.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('keys.*') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
                    <i class="fa-solid fa-key w-5 text-center"></i>
                    <span>Key Slots</span>
                </a>

                <a href="{{ route('users.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span>Users & QR</span>
                </a>

                <a href="{{ route('schedules.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('schedules.*') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
                    <i class="fa-solid fa-calendar-days w-5 text-center"></i>
                    <span>Schedules</span>
                </a>

                <a href="{{ route('transactions.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                    <span>Transactions</span>
                </a>

                <a href="{{ route('access-logs.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('access-logs.*') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
                    <i class="fa-solid fa-qrcode w-5 text-center"></i>
                    <span>QR Audit Logs</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
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
    </div>

    <!-- ═══════════════════════════════════
         DESKTOP PERMANENT SIDEBAR (MD+ SCREENS)
         ═══════════════════════════════════ -->
    <aside class="mockup-sidebar hidden md:flex md:w-64 flex-shrink-0 flex-col justify-between p-5 text-white z-20 min-h-screen sticky top-0 h-screen overflow-y-auto">
        <div>
            <!-- Brand Logo Header -->
            <div class="px-2 py-3 mb-4 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="sidebar-brand-logo w-11 h-11 rounded-full bg-white shadow-lg overflow-hidden p-0.5 flex items-center justify-center border-2 border-white/50 flex-shrink-0">
                        <img src="{{ asset('build/assets/logo.jpg') }}" alt="AutoBox Logo" class="w-full h-full object-contain rounded-full">
                    </div>
                    <div>
                        <span class="font-heading font-extrabold text-2xl tracking-tight text-white group-hover:text-purple-100 transition-colors block leading-none">AUTOBOX</span>
                        <span class="text-[10px] font-bold text-purple-200 uppercase tracking-widest block mt-1">CCSICT ISU</span>
                    </div>
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
        <div class="px-4 sm:px-6 md:px-8 pt-4 sm:pt-6 pb-2">
            <header class="top-header-card flex items-center justify-between flex-wrap gap-3 p-3.5 sm:p-5">
                <!-- Left: Welcome Title with Purple Branded Badge -->
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-sm sm:text-base flex-shrink-0 shadow-sm border border-[var(--border-subtle)]">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-base sm:text-lg font-heading font-extrabold text-[var(--text-heading)] tracking-tight truncate">
                                Welcome back, {{ Auth::user()->name ?? 'Admin' }}!
                            </h1>
                            <span class="px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] uppercase tracking-wider flex-shrink-0">
                                Active Session
                            </span>
                        </div>
                        <p class="text-[11px] sm:text-xs text-[var(--text-muted)] font-medium mt-0.5 truncate">AUTOBOX Key Access & Real-Time Monitor</p>
                    </div>
                </div>

                <!-- Right: Admin Profile Chip -->
                <div class="flex items-center gap-3 ml-auto sm:ml-0">
                    <!-- User Profile Chip -->
                    <div class="flex items-center gap-2 sm:gap-3 p-1 sm:p-1.5 pr-2.5 sm:pr-3.5 rounded-full bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <div class="relative">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-500 text-white font-extrabold text-xs flex items-center justify-center shadow-md">
                                {{ strtoupper(substr(Auth::user()->name ?? 'W', 0, 1)) }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                        </div>
                        <div class="hidden xs:block sm:block">
                            <p class="text-xs font-extrabold text-[var(--text-heading)] leading-none">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-[10px] text-[var(--purple-primary)] font-bold mt-0.5 capitalize">{{ Auth::user()->role ?? 'Admin' }}</p>
                        </div>
                    </div>
                </div>
            </header>
        </div>

        <!-- Main View Content -->
        <main class="px-4 sm:px-6 md:px-8 pb-8 flex-1">
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