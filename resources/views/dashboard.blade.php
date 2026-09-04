@extends('layouts.app')

@section('title', 'Key Management Overview')

@section('content')
<div class="space-y-6">

    <!-- ═══════════════════════════════════
         TOP ROW — MOCKUP STAT CARDS (4 COLUMNS)
         ═══════════════════════════════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Stat Card 1: Total Keys -->
        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon">
                    <i class="fa-solid fa-key"></i>
                </div>
                <span class="text-[10px] font-extrabold text-[var(--purple-primary)] bg-[var(--purple-soft)] px-2 py-0.5 rounded-md uppercase tracking-wider">
                    Total
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Key Slots</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-[var(--text-heading)]">{{ $totalKeys }}</h3>
                <span class="stat-tag stat-tag-negative">
                    <i class="fa-solid fa-arrow-down text-[9px]"></i> -0.70%
                </span>
            </div>
        </div>

        <!-- Stat Card 2: Available Keys -->
        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon stat-badge-icon-emerald">
                    <i class="fa-solid fa-lock-open"></i>
                </div>
                <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md uppercase tracking-wider">
                    Ready
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Available Keys</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-emerald-600">{{ $availableKeys }}</h3>
                <span class="stat-tag stat-tag-positive">
                    <i class="fa-solid fa-arrow-up text-[9px]"></i> +0.70%
                </span>
            </div>
        </div>

        <!-- Stat Card 3: Currently Borrowed -->
        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon stat-badge-icon-amber">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <span class="text-[10px] font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md uppercase tracking-wider">
                    In Use
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Currently Borrowed</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-amber-600">{{ $borrowedKeys }}</h3>
                <span class="stat-tag stat-tag-neutral">
                    <i class="fa-solid fa-clock text-[9px]"></i> Active
                </span>
            </div>
        </div>

        <!-- Stat Card 4: Missing / Alerts (HIGH CONTRAST BRIGHT RED NUMBER) -->
        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon stat-badge-missing-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <span class="text-[10px] font-extrabold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md uppercase tracking-wider">
                    Notice
                </span>
            </div>
            <p class="text-xs font-bold text-rose-600 uppercase tracking-wider">Missing / Alerts</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-rose-600">{{ $missingKeys }}</h3>
                <span class="stat-tag stat-tag-negative shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Alert
                </span>
            </div>
        </div>
    </div>


    <!-- ═══════════════════════════════════
         MIDDLE ROW — SCHEDULES PER DAY BAR CHART
         ═══════════════════════════════════ -->
    <div class="mockup-card">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div>
                <h2 class="mockup-card-title">Active Schedules</h2>
                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Number of active user schedules per day of the week</p>
            </div>
            <div class="flex items-center gap-5 text-xs font-bold text-[var(--text-heading)]">
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#6451a3]"></span>
                    Schedules per Day
                </span>
                <a href="{{ route('schedules.index') }}" class="text-xs font-extrabold text-[var(--purple-primary)] hover:underline flex items-center gap-1">
                    <span>Manage</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        @if($totalSchedules > 0)
            <!-- Chart.js Bar Canvas -->
            <div class="relative w-full h-[260px]">
                <canvas id="dashboardBarChart"></canvas>
            </div>
        @else
            <!-- Beautiful No-Schedule Empty SVG State -->
            <div class="py-8 px-4 text-center flex flex-col items-center justify-center space-y-3 bg-[var(--app-bg)] rounded-2xl border border-dashed border-[var(--border-subtle)]">
                <svg width="90" height="90" style="width: 90px; height: 90px; max-width: 90px; max-height: 90px;" class="mx-auto text-purple-400" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="35" width="110" height="100" rx="16" fill="#F4F0FF" stroke="#C4B5FD" stroke-width="2.5" stroke-dasharray="6 6"/>
                    <rect x="25" y="35" width="110" height="28" rx="16" fill="#6451a3"/>
                    <rect x="25" y="49" width="110" height="14" fill="#6451a3"/>
                    <rect x="48" y="24" width="8" height="20" rx="4" fill="#4c3d82"/>
                    <rect x="104" y="24" width="8" height="20" rx="4" fill="#4c3d82"/>
                    <circle cx="52" cy="84" r="5" fill="#DDD6FE"/>
                    <circle cx="80" cy="84" r="5" fill="#DDD6FE"/>
                    <circle cx="108" cy="84" r="5" fill="#DDD6FE"/>
                    <circle cx="52" cy="108" r="5" fill="#DDD6FE"/>
                    <circle cx="80" cy="108" r="5" fill="#DDD6FE"/>
                    <circle cx="108" cy="108" r="5" fill="#DDD6FE"/>
                    <g filter="drop-shadow(0px 4px 8px rgba(100, 81, 163, 0.2))">
                        <circle cx="112" cy="112" r="22" fill="#ffffff" stroke="#6451a3" stroke-width="2.5"/>
                        <circle cx="112" cy="112" r="17" fill="#EDE9FE"/>
                        <path d="M112 103V112L118 115" stroke="#6451a3" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <path d="M38 18L39.5 22L43.5 23.5L39.5 25L38 29L36.5 25L32.5 23.5L36.5 22L38 18Z" fill="#A78BFA"/>
                    <path d="M136 55L137.5 58.5L141 59.5L137.5 60.5L136 64L134.5 60.5L131 59.5L134.5 58.5L136 55Z" fill="#C4B5FD"/>
                </svg>
                <div class="max-w-sm">
                    <h3 class="font-heading font-extrabold text-base text-[var(--text-heading)]">No Active Schedules</h3>
                    <p class="text-xs text-[var(--text-muted)] mt-1 font-medium leading-relaxed">
                        There are currently no access schedules configured for faculty or staff.
                    </p>
                </div>
                <a href="{{ route('schedules.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow-sm">
                    <i class="fa-solid fa-plus text-[10px]"></i> Create Schedule
                </a>
            </div>
        @endif
    </div>

    <!-- ═══════════════════════════════════
         BOTTOM ROW — HARDWARE KEY SLOTS (LEFT) & RECENT ACTIVITY (RIGHT)
         ═══════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- HARDWARE KEY SLOTS (MONITOR - LEFT 2 COLUMNS) -->
        <div class="lg:col-span-2 mockup-card space-y-5">
            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-[var(--border-subtle)]">
                <div>
                    <h2 class="mockup-card-title flex items-center gap-2 text-base">
                        <i class="fa-solid fa-boxes-stacked text-[var(--purple-primary)]"></i>
                        Hardware Key Slots (Real-Time Monitor)
                    </h2>
                    <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Live status of physical lock box key slots</p>
                </div>
                <a href="{{ route('keys.index') }}" class="text-xs font-extrabold text-[var(--purple-primary)] hover:underline flex items-center gap-1">
                    <span>Manage Slots</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- ULTRASONIC & DC MOTOR SLIDER HARDWARE STATUS -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-purple-50 via-indigo-50/50 to-white border border-purple-200/80 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-[var(--text-heading)] flex items-center gap-2">
                            DC Motor Slider Door
                            <span id="slider-door-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fa-solid fa-hand text-[9px]"></i> Ultrasonic Sensor Active
                            </span>
                        </h4>
                        <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">
                            Hand Detected <i class="fa-solid fa-arrow-right text-[10px] text-[var(--purple-primary)]"></i> Rolls Open &nbsp;|&nbsp; No Hand <i class="fa-solid fa-arrow-right text-[10px] text-[var(--purple-primary)]"></i> Rolls Closed
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grid of Key Slots -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @forelse($keys as $key)
                    @php
                        $borrower = $key->currentBorrower();
                        $badgeClass = match($key->status) { 'available' => 'slot-badge-available', 'borrowed' => 'slot-badge-borrowed', default => 'slot-badge-missing' };
                        $scheduleInfo = $key->getScheduleStatusInfo();
                    @endphp

                    <div class="redesigned-slot-card flex flex-col justify-between">
                        <div>
                            <!-- Slot Top Header -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-[var(--border-subtle)]">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $badgeClass }}">
                                    SLOT #{{ $key->slot_number }}
                                </span>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('keys.edit', $key) }}" title="Edit Key Slot" class="w-7 h-7 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all flex items-center justify-center text-xs">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <span class="slot-status-icon-circle
                                        @if($key->status === 'available') slot-circle-available
                                        @elseif($key->status === 'borrowed') slot-circle-borrowed
                                        @else slot-circle-missing @endif">
                                        @if($key->status === 'available') <i class="fa-solid fa-lock-open text-xs"></i>
                                        @elseif($key->status === 'borrowed') <i class="fa-solid fa-key text-xs"></i>
                                        @else <i class="fa-solid fa-triangle-exclamation text-xs"></i> @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Key details -->
                            <div class="mt-3">
                                <h3 class="font-heading font-extrabold text-sm text-[var(--text-heading)] truncate">{{ $key->key_name }}</h3>
                                <p class="text-xs font-semibold text-[var(--purple-primary)] flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-door-open text-[10px]"></i> {{ $key->room_name }}
                                </p>

                                @if($scheduleInfo && !empty($scheduleInfo['in_grace']))
                                    <div class="mt-2.5 p-2 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between gap-1.5 grace-countdown-container"
                                         data-seconds-left="{{ $scheduleInfo['seconds_left'] }}"
                                         data-slot="{{ $key->slot_number }}">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                            </span>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 truncate">
                                                Return Timer
                                            </span>
                                        </div>
                                        <span class="grace-countdown-display font-mono font-black text-xs text-amber-900 bg-amber-100 px-2 py-0.5 rounded-md border border-amber-300 flex-shrink-0">
                                            --:--
                                        </span>
                                    </div>
                                @elseif($scheduleInfo && $scheduleInfo['state'] === 'active')
                                    <div class="mt-2 text-[10px] font-semibold text-slate-500 flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-[9px] text-[var(--purple-primary)]"></i>
                                        <span>Schedule: {{ $scheduleInfo['schedule_start'] }} - {{ $scheduleInfo['schedule_end'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Status Footer -->
                        <div class="mt-3 pt-2.5 border-t border-[var(--border-subtle)] text-xs flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                @if($key->status === 'borrowed' && $borrower)
                                    <p class="text-[11px] font-bold text-[var(--text-heading)] truncate">{{ $borrower->user->name ?? 'User' }}</p>
                                    <p class="text-[9px] text-[var(--text-muted)]"><i class="fa-regular fa-clock"></i> {{ $borrower->created_at->diffForHumans() }}</p>
                                @elseif($key->status === 'available')
                                    <span class="text-emerald-600 font-bold text-[11px] flex items-center gap-1.5">
                                        Ready in Lock Box
                                    </span>
                                @else
                                    <span class="text-rose-600 font-bold text-[11px] flex items-center gap-1.5">
                                        Flagged Missing
                                    </span>
                                @endif
                            </div>
                            <span class="w-2 h-2 rounded-full @if($key->status === 'available') bg-emerald-500 @elseif($key->status === 'borrowed') bg-amber-500 @else bg-rose-500 animate-ping @endif"></span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-10 text-[var(--text-muted)] text-sm">
                        No key slots registered yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- USERS & SCHEDULES SUMMARY (RIGHT 1 COLUMN) -->
        <div class="lg:col-span-1 mockup-card p-5 md:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--border-subtle)]">
                <h2 class="mockup-card-title text-base flex items-center gap-2">
                    <i class="fa-solid fa-users text-[var(--purple-primary)] text-sm"></i>
                    System Overview
                </h2>
                <a href="{{ route('users.index') }}" class="text-xs font-bold text-[var(--purple-primary)] hover:underline flex items-center gap-1">
                    <span>Manage Users</span>
                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                </a>
            </div>

            <div class="space-y-3">
                <!-- Active Users -->
                <div class="p-3 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-base flex-shrink-0">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <div>
                            <p class="font-heading font-extrabold text-sm text-[var(--text-heading)]">Active Users</p>
                            <p class="text-[10px] text-[var(--text-muted)]">Non-admin accounts</p>
                        </div>
                    </div>
                    <span class="text-2xl font-extrabold text-violet-600">{{ $totalUsers }}</span>
                </div>

                <!-- Active Schedules -->
                <div class="p-3 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base flex-shrink-0">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <p class="font-heading font-extrabold text-sm text-[var(--text-heading)]">Active Schedules</p>
                            <p class="text-[10px] text-[var(--text-muted)]">Assigned access windows</p>
                        </div>
                    </div>
                    <span class="text-2xl font-extrabold text-sky-600">{{ $totalSchedules }}</span>
                </div>

                <!-- Total Key Slots -->
                <div class="p-3 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-base flex-shrink-0">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div>
                            <p class="font-heading font-extrabold text-sm text-[var(--text-heading)]">Total Key Slots</p>
                            <p class="text-[10px] text-[var(--text-muted)]">Registered in system</p>
                        </div>
                    </div>
                    <span class="text-2xl font-extrabold text-[var(--purple-primary)]">{{ $totalKeys }}</span>
                </div>

                <!-- Admins -->
                <div class="p-3 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base flex-shrink-0">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <p class="font-heading font-extrabold text-sm text-[var(--text-heading)]">Administrators</p>
                            <p class="text-[10px] text-[var(--text-muted)]">Full-access accounts</p>
                        </div>
                    </div>
                    <span class="text-2xl font-extrabold text-amber-600">{{ $totalAdmins }}</span>
                </div>

                <div class="pt-1">
                    <a href="{{ route('schedules.index') }}" class="block w-full text-center py-2.5 rounded-xl bg-[var(--purple-soft)] text-[var(--purple-primary)] text-xs font-extrabold hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                        <i class="fa-solid fa-calendar-days mr-1"></i> View All Schedules
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('dashboardBarChart');
        if (!ctx) return;

        const dayLabels      = {!! json_encode($dayLabels ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']) !!};
        const schedulePerDay = {!! json_encode($schedulePerDay ?? []) !!};

        window.dashboardChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dayLabels,
                datasets: [
                    {
                        label: 'Active Schedules',
                        data: schedulePerDay,
                        backgroundColor: '#6451a3',
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 28,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e1938',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Outfit', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} active schedule${ctx.parsed.y !== 1 ? 's' : ''}`,
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: '#847d9c' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0edf7' },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 10 },
                            color: '#847d9c',
                            precision: 0,
                            stepSize: 1,
                        }
                    }
                }
            }
        });

        // ═══════════════════════════════════
        // PUSHER WEBSOCKET REAL-TIME HARDWARE LISTENERS
        // ═══════════════════════════════════
        if (window.Echo) {
            console.log('[Pusher Echo] Subscribing to autobox-hardware channel...');
            window.Echo.channel('autobox-hardware')
                .listen('.SliderStateChanged', (e) => {
                    console.log('[Pusher Echo] Slider State Event:', e);
                    const badge = document.getElementById('slider-door-badge');
                    if (badge) {
                        if (e.state === 'opened') {
                            badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300 animate-pulse';
                            badge.innerHTML = '<i class="fa-solid fa-door-open text-[9px]"></i> SLIDER OPENING / OPEN';
                        } else {
                            badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300';
                            badge.innerHTML = '<i class="fa-solid fa-hand text-[9px]"></i> Ultrasonic Sensor Active';
                        }
                    }
                })
                .listen('.KeyStatusUpdated', (e) => {
                    console.log('[Pusher Echo] Key Status Updated:', e);
                    // Dynamically refresh key status UI
                    setTimeout(() => window.location.reload(), 1000);
                })
                .listen('.AccessLogged', (e) => {
                    console.log('[Pusher Echo] Access Logged:', e);
                    if (e.action === 'security_alert') {
                        if (typeof showToast === 'function') {
                            showToast(`SECURITY ALERT: ${e.userName} — ${e.reason}`, 'error');
                        }
                    }
                });
        }

        // ═══════════════════════════════════
        // LIVE 10-MINUTE RETURN GRACE COUNTDOWN
        // ═══════════════════════════════════
        function updateGraceCountdowns() {
            const containers = document.querySelectorAll('.grace-countdown-container');
            containers.forEach(container => {
                let sec = parseInt(container.getAttribute('data-seconds-left') || '0', 10);
                const displayEl = container.querySelector('.grace-countdown-display');
                if (!displayEl) return;

                if (sec > 0) {
                    const mins = Math.floor(sec / 60);
                    const remainder = sec % 60;
                    displayEl.textContent = String(mins).padStart(2, '0') + ':' + String(remainder).padStart(2, '0');
                    sec--;
                    container.setAttribute('data-seconds-left', sec);
                } else {
                    displayEl.textContent = '00:00';
                    displayEl.className = 'grace-countdown-display font-mono font-black text-xs text-rose-800 bg-rose-100 px-2 py-0.5 rounded-md border border-rose-300 animate-pulse';
                }
            });
        }
        updateGraceCountdowns();
        setInterval(updateGraceCountdowns, 1000);
    });
</script>
@endsection