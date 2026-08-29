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
         MIDDLE ROW — FULL WIDTH STATISTIC CHART (MAX 100 SCALE)
         ═══════════════════════════════════ -->
    <div class="mockup-card">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div>
                <h2 class="mockup-card-title">Statistic</h2>
                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Monthly Key Borrows vs Returns transaction trends (Scale 0 &mdash; 100)</p>
            </div>
            <div class="flex items-center gap-5 text-xs font-bold text-[var(--text-heading)]">
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#6451a3]"></span>
                    Borrows
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#e5a84b]"></span>
                    Returns
                </span>
            </div>
        </div>

        <!-- Chart.js Dual Bar Canvas -->
        <div class="relative w-full h-[260px]">
            <canvas id="dashboardBarChart"></canvas>
        </div>
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

        <!-- RECENT ACTIVITY (RIGHT 1 COLUMN) -->
        <div class="lg:col-span-1 mockup-card p-5 md:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--border-subtle)]">
                <h2 class="mockup-card-title text-base flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[var(--purple-primary)] text-sm"></i>
                    Recent Activity
                </h2>
                <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-[var(--purple-primary)] hover:underline flex items-center gap-1">
                    <span>View All</span>
                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentTransactions as $t)
                    <div class="p-3 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] hover:border-[var(--purple-primary)]/50 transition-all flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Large Avatar -->
                            <div class="w-9 h-9 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] font-extrabold text-xs flex items-center justify-center flex-shrink-0 shadow-sm ring-2 ring-[var(--purple-primary)]/20">
                                {{ strtoupper(substr($t->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <!-- User & Key Details -->
                            <div class="min-w-0">
                                <p class="font-heading font-extrabold text-sm text-[var(--text-heading)] truncate">
                                    {{ $t->user->name ?? 'System User' }}
                                </p>
                                <p class="text-xs font-medium text-[var(--text-muted)] truncate flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-key text-[10px] text-[var(--purple-primary)]"></i>
                                    {{ $t->key->key_name ?? 'N/A' }} ({{ $t->key->room_name ?? 'N/A' }})
                                </p>
                            </div>
                        </div>

                        <!-- Action Badge & Time -->
                        <div class="text-right flex-shrink-0">
                            @if($t->action === 'borrow')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-arrow-up-from-bracket text-[10px]"></i> Borrow
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-rotate-left text-[10px]"></i> Return
                                </span>
                            @endif
                            <p class="text-[10px] font-semibold text-[var(--text-muted)] mt-1">
                                {{ $t->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-[var(--text-muted)] text-sm">
                        <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 block opacity-20"></i>
                        No recent activity recorded yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════
     CHART.JS BAR CHART SCRIPT (0 - 100 SCALE)
     ═══════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('dashboardBarChart');
        if (!ctx) return;

        const months = {!! json_encode($months ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!};
        const borrowsData = {!! json_encode($monthlyBorrows ?? []) !!};
        const returnsData = {!! json_encode($monthlyReturns ?? []) !!};

        window.dashboardChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Borrows',
                        data: borrowsData,
                        backgroundColor: '#6451a3',
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 12,
                    },
                    {
                        label: 'Returns',
                        data: returnsData,
                        backgroundColor: '#e5a84b',
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 12,
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
                        bodyFont: { family: 'Plus Jakarta Sans', size: 11 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, color: '#847d9c' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0edf7' },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 10 },
                            color: '#847d9c',
                            precision: 0
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
    });
</script>
@endsection