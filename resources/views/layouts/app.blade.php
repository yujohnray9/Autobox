<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AUTOBOX') }} — Key Access & Real-Time Monitoring</title>
    <meta name="description" content="AUTOBOX CCSICT — Physical key management, access control, and real-time monitoring dashboard.">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

    <!-- Enforce Light Theme & Runtime Pusher Config -->
    <script>
        document.documentElement.classList.remove('dark');
        localStorage.setItem('autobox_theme', 'light');
        window.PUSHER_CONFIG = {
            key: @json(config('broadcasting.connections.pusher.key') ?: env('VITE_PUSHER_APP_KEY')),
            cluster: @json(config('broadcasting.connections.pusher.options.cluster') ?: env('VITE_PUSHER_APP_CLUSTER', 'ap1'))
        };
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
    @php
        try {
            $offlinePendingCount = \App\Models\OfflineRecord::pending()->count();
            // Sidebar badge: every record captured while offline (synced or not), so it stays visible after auto-sync
            $offlineTotalCount = \App\Models\OfflineRecord::count();
        } catch (\Throwable $e) {
            $offlinePendingCount = 0; // table not migrated yet
            $offlineTotalCount = 0;
        }
    @endphp

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
                    <img src="{{ asset('images/logo.jpg') }}" alt="AutoBox Logo" class="w-full h-full object-contain rounded-full">
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
                        <img src="{{ asset('images/logo.jpg') }}" alt="AutoBox Logo" class="w-full h-full object-contain rounded-full">
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

                <a href="{{ route('offline.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('offline.*') ? 'active' : '' }}"
                   @click="sidebarOpen = false">
                    <i class="fa-solid fa-cloud-arrow-up w-5 text-center"></i>
                    <span>Offline Records</span>
                    <span class="offline-pending-badge ml-auto {{ $offlineTotalCount > 0 ? '' : 'hidden' }}" title="{{ $offlineTotalCount }} offline record(s){{ $offlinePendingCount ? ", {$offlinePendingCount} not synced" : ', all synced' }}">{{ $offlineTotalCount }}</span>
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
                        <img src="{{ asset('images/logo.jpg') }}" alt="AutoBox Logo" class="w-full h-full object-contain rounded-full">
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

                <a href="{{ route('offline.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('offline.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cloud-arrow-up w-5 text-center"></i>
                    <span>Offline Records</span>
                    <span class="offline-pending-badge ml-auto {{ $offlineTotalCount > 0 ? '' : 'hidden' }}" title="{{ $offlineTotalCount }} offline record(s){{ $offlinePendingCount ? ", {$offlinePendingCount} not synced" : ', all synced' }}">{{ $offlineTotalCount }}</span>
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

                <!-- Right: Manila Clock, Alerts & Admin Profile Chip -->
                <div class="flex items-center gap-2 sm:gap-3 ml-auto sm:ml-0 flex-wrap justify-end">
                    <!-- Live Philippine Standard Time Clock (Asia/Manila) -->
                    <div id="manila-clock" class="manila-clock" title="Philippine Standard Time (Asia/Manila)">
                        <div class="manila-clock-icon">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <div class="leading-none">
                            <div class="flex items-baseline gap-1">
                                <span id="manila-clock-time" class="manila-clock-time">--:--:--</span>
                                <span id="manila-clock-ampm" class="manila-clock-ampm"></span>
                            </div>
                            <div class="manila-clock-date">
                                <span id="manila-clock-date">&nbsp;</span>
                                <span class="manila-clock-zone">PHT</span>
                            </div>
                        </div>
                    </div>

                    <!-- Security Alerts Bell -->
                    <div class="relative" x-data="alertBell()" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" id="alert-bell-button" @click="toggle()"
                                class="alert-bell-btn" :class="{ 'alert-bell-ringing': unread > 0 }"
                                aria-label="Security alerts">
                            <i class="fa-solid fa-bell"></i>
                            <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread" class="alert-bell-badge"></span>
                        </button>

                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="alert-dropdown">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-[var(--border-subtle)] bg-white">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-extrabold text-[var(--text-heading)]">Security Alerts</p>
                                        <span x-show="unread > 0" x-cloak class="px-1.5 py-0.5 rounded-full text-[9px] font-black bg-rose-100 text-rose-700 animate-pulse" x-text="unread + ' unread'"></span>
                                    </div>
                                    <p class="text-[10px] text-[var(--text-muted)] font-semibold"><span x-text="todayCount"></span> unauthorized attempt(s) today</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" x-show="unread > 0" x-cloak @click="markAllAsRead()"
                                            title="Mark all notifications as read"
                                            class="text-[10px] font-extrabold text-slate-500 hover:text-[var(--purple-primary)] transition-colors flex items-center gap-1">
                                        <i class="fa-solid fa-check-double text-[9px]"></i> Mark all read
                                    </button>
                                    <button type="button" x-show="notifyPermission === 'default'" @click="enableDesktop()"
                                            title="Show a Windows/browser notification for new alerts even when this tab is minimized"
                                            class="text-[10px] font-extrabold text-[var(--purple-primary)] hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-desktop"></i> Desktop
                                    </button>
                                    <span x-show="notifyPermission === 'granted'" x-cloak class="text-[10px] font-extrabold text-emerald-600" title="Desktop alerts are on">
                                        <i class="fa-solid fa-circle-check"></i> On
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('offline.index') }}" x-show="pendingOffline > 0" x-cloak
                               class="flex items-center gap-2 px-4 py-2.5 bg-sky-50 border-b border-sky-100 text-xs font-bold text-sky-800 hover:bg-sky-100">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span><span x-text="pendingOffline"></span> offline record(s) not synced yet — retry needed</span>
                            </a>

                            <ul class="max-h-80 overflow-y-auto divide-y divide-[var(--border-subtle)]">
                                <template x-for="a in alerts" :key="a.id">
                                    <li>
                                        <a href="#"
                                           @click.prevent="markAsRead(a.id, '{{ url('/access-logs') }}')"
                                           class="px-4 py-3 flex gap-3 hover:bg-rose-50/50 transition-colors block group cursor-pointer relative"
                                           :class="isUnread(a.id) ? 'bg-rose-50/60' : 'bg-white'">
                                            <div class="flex gap-3 w-full items-start">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs flex-shrink-0"
                                                     :class="a.is_unknown ? 'bg-slate-800 text-white' : 'bg-rose-100 text-rose-700'">
                                                    <i class="fa-solid" :class="a.is_unknown ? 'fa-user-secret' : 'fa-user-xmark'"></i>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-xs font-extrabold text-[var(--text-heading)] group-hover:text-[var(--purple-primary)] transition-colors" x-text="a.who"></span>
                                                        <span class="px-1.5 py-0.5 rounded-full text-[8px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-700" x-text="a.category"></span>
                                                        <span x-show="isUnread(a.id)" x-cloak class="px-1 py-0.2 rounded text-[7px] font-black uppercase bg-rose-600 text-white">Unread</span>
                                                    </div>
                                                    <p class="text-[11px] text-[var(--text-muted)] leading-snug mt-0.5" x-text="a.reason"></p>
                                                    <p class="text-[9px] text-slate-400 font-semibold mt-1" x-text="a.time"></p>
                                                </div>
                                                <div class="flex items-center gap-2 self-center flex-shrink-0">
                                                    <span x-show="isUnread(a.id)" x-cloak class="w-2.5 h-2.5 rounded-full bg-rose-500 ring-4 ring-rose-200" title="Unread notification"></span>
                                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 group-hover:text-[var(--purple-primary)] group-hover:translate-x-0.5 transition-all"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                </template>
                                <li x-show="alerts.length === 0" class="px-4 py-8 text-center">
                                    <i class="fa-solid fa-shield-halved text-2xl text-emerald-400"></i>
                                    <p class="text-xs font-bold text-[var(--text-muted)] mt-2">No unauthorized attempts</p>
                                </li>
                            </ul>

                            <a href="{{ route('access-logs.index') }}" class="block text-center py-2.5 text-xs font-extrabold text-[var(--purple-primary)] border-t border-[var(--border-subtle)] hover:bg-[var(--purple-soft)]">
                                View full audit log
                            </a>
                        </div>
                    </div>

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
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const isSuccess = type === 'success';
            const bgClass = isSuccess ? 'bg-emerald-600 text-white shadow-emerald-500/20' : (type === 'security' ? 'bg-gradient-to-r from-rose-700 to-red-600 text-white shadow-rose-500/30' : 'bg-rose-600 text-white shadow-rose-500/20');
            const iconClass = type === 'security' ? 'fa-user-shield' : (isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation');

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center justify-between gap-3 p-4 rounded-2xl shadow-xl transition-all transform duration-300 translate-x-10 opacity-0 ${type === 'security' ? 'cursor-pointer hover:shadow-2xl' : ''} ${bgClass}`;
            toast.innerHTML = `
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <i class="fa-solid ${iconClass} text-lg flex-shrink-0"></i>
                    <span class="text-xs font-bold leading-snug">${message}</span>
                </div>
                ${type === 'security' ? '<span class="text-[9px] font-black uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-md flex-shrink-0">View Log</span>' : ''}
                <button type="button" class="text-white/70 hover:text-white transition-colors ml-2" onclick="event.stopPropagation(); this.parentElement.remove()">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;

            if (type === 'security') {
                toast.onclick = (e) => {
                    if (e.target.closest('button')) return;
                    window.location.href = "{{ route('access-logs.index') }}";
                };
            }

            container.appendChild(toast);

            let timeoutId;
            const startDismiss = () => {
                timeoutId = setTimeout(() => {
                    toast.classList.add('translate-x-10', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            };

            // Pause auto-dismiss if user hovers their mouse over the alert
            toast.addEventListener('mouseenter', () => clearTimeout(timeoutId));
            toast.addEventListener('mouseleave', () => startDismiss());

            setTimeout(() => {
                toast.classList.remove('translate-x-10', 'opacity-0');
            }, 50);

            startDismiss();
        }

        // ═══════════════════════════════════
        // LIVE ASIA/MANILA CLOCK (independent of the viewer's device timezone)
        // ═══════════════════════════════════
        (function startManilaClock() {
            const timeFmt = new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            const dateFmt = new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Manila', weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });

            // Correct for a wrong device clock using the server time (server runs on Asia/Manila)
            let offsetMs = {{ now()->getTimestampMs() }} - Date.now();
            window.autoboxSetServerTime = (iso) => { const t = Date.parse(iso); if (!isNaN(t)) offsetMs = t - Date.now(); };

            function tick() {
                const now = new Date(Date.now() + offsetMs);
                const parts = timeFmt.formatToParts(now);
                const get = (type) => parts.find(p => p.type === type)?.value ?? '';
                const t = document.getElementById('manila-clock-time');
                if (!t) return;
                t.textContent = `${get('hour')}:${get('minute')}:${get('second')}`;
                document.getElementById('manila-clock-ampm').textContent = get('dayPeriod');
                document.getElementById('manila-clock-date').textContent = dateFmt.format(now);
            }
            tick();
            setInterval(tick, 1000);
        })();

        // ═══════════════════════════════════
        // SECURITY ALERT BELL (polls the server; also reacts instantly to Pusher events)
        // ═══════════════════════════════════
        function alertBell() {
            return {
                open: false,
                alerts: [],
                unread: 0,
                todayCount: 0,
                pendingOffline: {{ $offlinePendingCount }},
                latestId: 0,
                readIds: new Set(),
                notifyPermission: ('Notification' in window) ? Notification.permission : 'unsupported',
                polling: false,
                lastPollAt: 0,
                liveConnected: false,

                init() {
                    try {
                        const stored = JSON.parse(localStorage.getItem('autobox_read_alert_ids') || '[]');
                        this.readIds = new Set(Array.isArray(stored) ? stored : []);
                    } catch (e) {
                        this.readIds = new Set();
                    }

                    this.poll(true);

                    // ── PRIMARY: Pusher real-time push (instant) ──
                    if (window.Echo) {
                        try {
                            const pusher = window.Echo.connector?.pusher;
                            if (pusher) {
                                this.liveConnected = pusher.connection.state === 'connected';
                                pusher.connection.bind('state_change', ({ current }) => {
                                    const wasConnected = this.liveConnected;
                                    this.liveConnected = current === 'connected';
                                    if (this.liveConnected && !wasConnected) this.poll(false);
                                });
                            }

                            window.Echo.channel('autobox-hardware')
                                .listen('.SecurityAlertRaised', (e) => this.receive([e.alert], e.todayCount))
                                .listen('.AccessLogged', (e) => {
                                    if (e.action === 'offline_upload') {
                                        showToast(`<b>Offline data synced</b><br>${this.escape(e.reason)}`, 'success', 8000);
                                        this.poll(false);
                                    }
                                });
                        } catch (err) { console.warn('[AUTOBOX] Pusher unavailable, using polling', err); }
                    }

                    // ── FALLBACK: polling. Every 10s only when Pusher is down; otherwise a 60s safety check. ──
                    setInterval(() => {
                        const interval = this.liveConnected ? 60000 : 10000;
                        if (Date.now() - this.lastPollAt >= interval) this.poll(false);
                    }, 10000);
                },

                updateUnread() {
                    this.unread = this.alerts.filter(a => a && !this.readIds.has(a.id)).length;
                },

                isUnread(id) {
                    return !this.readIds.has(id);
                },

                markAsRead(id, targetUrl) {
                    if (id) {
                        this.readIds.add(id);
                        this.saveReadIds();
                        this.updateUnread();
                    }
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                },

                markAllAsRead() {
                    this.alerts.forEach(a => {
                        if (a && a.id) this.readIds.add(a.id);
                    });
                    this.saveReadIds();
                    this.updateUnread();
                },

                saveReadIds() {
                    try {
                        const arr = Array.from(this.readIds).slice(-300);
                        localStorage.setItem('autobox_read_alert_ids', JSON.stringify(arr));
                    } catch (e) {}
                },

                // Add newly received alerts (from Pusher or polling), skipping ones already shown
                receive(incoming, todayCount) {
                    const known = new Set(this.alerts.map(a => a.id));
                    const fresh = (incoming || []).filter(a => a && a.action !== 'missing' && !known.has(a.id));
                    if (typeof todayCount === 'number') this.todayCount = todayCount;
                    if (!fresh.length) return;

                    this.alerts = [...fresh, ...this.alerts].slice(0, 30);
                    this.latestId = Math.max(this.latestId, ...fresh.map(a => a.id));
                    this.updateUnread();
                    this.announce(fresh, this.todayCount);
                },

                async poll(initial) {
                    if (this.polling) return;
                    this.polling = true;
                    this.lastPollAt = Date.now();
                    try {
                        const url = new URL(@json(route('alerts.feed')), window.location.origin);
                        if (!initial && this.latestId) url.searchParams.set('after_id', this.latestId);
                        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        if (!res.ok) return;
                        const data = await res.json();

                        this.setPendingOffline(data.pending_offline);
                        if (data.server_time && window.autoboxSetServerTime) window.autoboxSetServerTime(data.server_time);

                        if (initial) {
                            this.alerts = (data.alerts || []).filter(a => a && a.action !== 'missing');
                            this.todayCount = data.today_count;
                            this.latestId = Math.max(this.latestId, data.latest_id || 0);
                            this.updateUnread();
                        } else {
                            this.receive(data.alerts, data.today_count);
                        }
                    } catch (err) {
                        console.warn('[AUTOBOX] Alert poll failed', err);
                    } finally {
                        this.polling = false;
                    }
                },

                announce(newAlerts, todayCount) {
                    newAlerts.slice(0, 3).forEach(a => {
                        showToast(`<b>Unauthorized attempt</b> · ${this.escape(a.who)}<br>${this.escape(a.reason || a.category)}`, 'security', 8000, a.id);
                    });
                    if (newAlerts.length > 3) showToast(`+${newAlerts.length - 3} more unauthorized attempt(s)`, 'security', 8000);

                    this.beep();

                    if (this.notifyPermission === 'granted' && document.hidden) {
                        const a = newAlerts[0];
                        new Notification('AUTOBOX Security Alert', { body: `${a.who}: ${a.reason || a.category}`, tag: 'autobox-alert-' + a.id });
                    }

                    document.dispatchEvent(new CustomEvent('autobox:new-alerts', { detail: { alerts: newAlerts, todayCount } }));
                },

                setPendingOffline(count) {
                    this.pendingOffline = count;
                },

                toggle() {
                    // Opening the dropdown leaves unread alerts intact.
                    // Alerts are marked as read when clicked or via 'Mark all read'
                    this.open = !this.open;
                },

                async enableDesktop() {
                    if (!('Notification' in window)) return;
                    this.notifyPermission = await Notification.requestPermission();
                },

                beep() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        [0, 0.22].forEach(offset => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'square';
                            osc.frequency.value = 880;
                            gain.gain.setValueAtTime(0.08, ctx.currentTime + offset);
                            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + offset + 0.18);
                            osc.connect(gain).connect(ctx.destination);
                            osc.start(ctx.currentTime + offset);
                            osc.stop(ctx.currentTime + offset + 0.2);
                        });
                    } catch (err) { /* autoplay blocked until the admin interacts with the page */ }
                },

                escape(s) {
                    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                },
            };
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