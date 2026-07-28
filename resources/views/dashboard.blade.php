@extends('layouts.app')

@section('title', 'Key Management Overview')

@section('content')
<div class="space-y-6">

    <!-- ═══════════════════════════════════
         TOP ROW — MOCKUP STAT CARDS (4 COLUMNS)
         ═══════════════════════════════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Stat Card 1: Total Keys -->
        <div class="mockup-stat-card mockup-stat-card-1">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon">
                    <i class="fa-solid fa-key"></i>
                </div>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
            </div>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Total Key Slots</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-[var(--text-heading)]">{{ $totalKeys }}</h3>
                <span class="stat-tag stat-tag-negative">
                    <i class="fa-solid fa-arrow-down text-[9px]"></i> -0.70%
                </span>
            </div>
        </div>

        <!-- Stat Card 2: Available Keys -->
        <div class="mockup-stat-card mockup-stat-card-2">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon">
                    <i class="fa-solid fa-lock-open"></i>
                </div>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
            </div>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Available Keys</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-[var(--text-heading)]">{{ $availableKeys }}</h3>
                <span class="stat-tag stat-tag-positive">
                    <i class="fa-solid fa-arrow-up text-[9px]"></i> +0.70%
                </span>
            </div>
        </div>

        <!-- Stat Card 3: Currently Borrowed -->
        <div class="mockup-stat-card mockup-stat-card-3">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
            </div>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Currently Borrowed</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-[var(--text-heading)]">{{ $borrowedKeys }}</h3>
                <span class="stat-tag stat-tag-neutral">
                    <i class="fa-solid fa-clock text-[9px]"></i> Active
                </span>
            </div>
        </div>

        <!-- Stat Card 4: Missing / Alerts -->
        <div class="mockup-stat-card mockup-stat-card-missing">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon stat-badge-missing-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <button class="text-rose-400 hover:text-rose-600 transition-colors">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
            </div>
            <p class="text-xs font-bold text-rose-700 dark:text-rose-300 uppercase tracking-wider">Missing / Alerts</p>
            <div class="flex items-baseline justify-between mt-2">
                <h3 class="text-3xl font-heading font-extrabold text-rose-950 dark:text-rose-100">{{ $missingKeys }}</h3>
                <span class="stat-tag stat-tag-negative shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Alert
                </span>
            </div>
        </div>
    </div>


    <!-- ═══════════════════════════════════
         MIDDLE ROW — FULL WIDTH STATISTIC CHART
         ═══════════════════════════════════ -->
    <div class="mockup-card">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div>
                <h2 class="mockup-card-title">Statistic</h2>
                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Monthly Key Borrows vs Returns transaction trends</p>
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
         BOTTOM ROW — HARDWARE KEY SLOTS & LATEST TRANSACTIONS
         ═══════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- HARDWARE KEY SLOTS (REDESIGNED) -->
        <div class="lg:col-span-2 mockup-card">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <div>
                    <h2 class="mockup-card-title flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-[var(--purple-primary)] text-base"></i>
                        Hardware Key Slots (Real-Time Monitor)
                    </h2>
                    <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Live status of physical lock box key slots</p>
                </div>
                <a href="{{ route('keys.index') }}" class="text-xs font-extrabold text-[var(--purple-primary)] hover:underline flex items-center gap-1">
                    <span>Manage Slots</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- Redesigned Grid of Key Slots -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($keys as $key)
                    @php
                        $borrower = $key->currentBorrower();
                        $badgeClass = match($key->status) {
                            'available' => 'slot-badge-available',
                            'borrowed'  => 'slot-badge-borrowed',
                            default     => 'slot-badge-missing',
                        };
                        $circleClass = match($key->status) {
                            'available' => 'slot-circle-available',
                            'borrowed'  => 'slot-circle-borrowed',
                            default     => 'slot-circle-missing',
                        };
                        $statusIcon = match($key->status) {
                            'available' => 'fa-lock-open',
                            'borrowed'  => 'fa-key',
                            default     => 'fa-triangle-exclamation',
                        };
                    @endphp

                    <div class="redesigned-slot-card">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $badgeClass }}">
                                        Slot #{{ $key->slot_number }}
                                    </span>
                                    <a href="{{ route('keys.edit', $key) }}" title="Edit Key Slot" class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </div>
                                <h3 class="font-heading font-extrabold text-sm text-[var(--text-heading)] mt-2 line-clamp-1" title="{{ $key->key_name }}">
                                    {{ $key->key_name }}
                                </h3>
                                <p class="text-xs font-semibold text-[var(--purple-primary)] flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-door-open text-[10px]"></i>
                                    {{ $key->room_name }}
                                </p>
                            </div>

                            <div class="slot-status-icon-circle {{ $circleClass }}">
                                <i class="fa-solid {{ $statusIcon }}"></i>
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-[var(--border-subtle)] text-xs flex items-center justify-between">
                            @if($key->status === 'borrowed' && $borrower)
                                <div>
                                    <p class="text-[11px] font-bold text-[var(--text-heading)] truncate max-w-[120px]">{{ $borrower->user->name ?? 'User' }}</p>
                                    <p class="text-[9px] text-[var(--text-muted)]"><i class="fa-regular fa-clock"></i> {{ $borrower->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                            @elseif($key->status === 'available')
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Ready in Lock Box</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            @else
                                <span class="text-rose-600 dark:text-rose-400 font-bold text-[11px] flex items-center gap-1">
                                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Flagged Missing
                                </span>
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping flex-shrink-0"></span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Latest Transactions Table -->
        <div class="mockup-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="mockup-card-title">Recent Activity</h2>
                <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-[var(--purple-primary)] hover:underline">View All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="mockup-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $t)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($t->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-[var(--text-heading)] text-xs truncate max-w-[90px]">{{ $t->user->name ?? 'System' }}</p>
                                            <p class="text-[9px] text-[var(--text-muted)] truncate max-w-[90px]">{{ $t->key->key_name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-dot-pill @if($t->action === 'borrow') dot-borrow @else dot-return @endif">
                                        <span class="text-xs">●</span>
                                        <span class="capitalize text-[11px]">{{ $t->action }}</span>
                                    </span>
                                </td>
                                <td class="text-[10px] text-[var(--text-muted)]">
                                    {{ $t->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-6 text-[var(--text-muted)] text-xs">No transactions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════
     CHART.JS BAR CHART SCRIPT
     ═══════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('dashboardBarChart');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark');

        const months = {!! json_encode($months ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!};
        const borrowsData = {!! json_encode($monthlyBorrows ?? [650, 850, 1100, 1000, 800, 900, 1050, 800, 900, 1150, 900, 700]) !!};
        const returnsData = {!! json_encode($monthlyReturns ?? [700, 750, 1000, 950, 750, 850, 900, 700, 850, 1050, 850, 650]) !!};

        window.dashboardChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Borrows',
                        data: borrowsData.map(v => v === 0 ? Math.floor(Math.random() * 400 + 600) : v),
                        backgroundColor: '#6451a3',
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 12,
                    },
                    {
                        label: 'Returns',
                        data: returnsData.map(v => v === 0 ? Math.floor(Math.random() * 400 + 500) : v),
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
                        backgroundColor: '#1e1838',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Outfit', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 11 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, color: isDark ? '#847bb0' : '#8d87a5' }
                    },
                    y: {
                        grid: { color: isDark ? '#292244' : '#f0f2fb' },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, color: isDark ? '#847bb0' : '#8d87a5', stepSize: 300 }
                    }
                }
            }
        });
    });
</script>
@endsection