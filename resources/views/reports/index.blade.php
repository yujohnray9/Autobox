@extends('layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<div class="space-y-6">

    <!-- ── Page Header ────────────────────────────────── -->
    <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-2xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-lg shadow-sm border border-[var(--border-subtle)]">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div>
            <h2 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Analytics & Reports</h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5 font-medium">System usage insights and key activity summaries.</p>
        </div>
    </div>

    <!-- ── KPI Summary Cards (4 columns, color-coded with left accent bar) ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total Borrows -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-emerald-500">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-arrow-up-from-bracket"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Total Borrows</p>
                <p class="text-3xl font-heading font-extrabold text-emerald-400 leading-none">{{ $totalBorrows }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">All-time borrow events</p>
            </div>
        </div>

        <!-- Total Returns -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-sky-500">
            <div class="w-12 h-12 rounded-2xl bg-sky-500/15 text-sky-400 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Total Returns</p>
                <p class="text-3xl font-heading font-extrabold text-sky-400 leading-none">{{ $totalReturns }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">All-time return events</p>
            </div>
        </div>

        <!-- QR Access Granted -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-violet-500">
            <div class="w-12 h-12 rounded-2xl bg-violet-500/15 text-violet-400 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">QR Granted</p>
                <p class="text-3xl font-heading font-extrabold text-violet-400 leading-none">{{ $totalGranted }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">Successful QR scans</p>
            </div>
        </div>

        <!-- QR Access Denied -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-rose-500">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">QR Denied</p>
                <p class="text-3xl font-heading font-extrabold text-rose-400 leading-none">{{ $totalDenied }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">Rejected QR attempts</p>
            </div>
        </div>
    </div>

    <!-- ── Charts Row: Line chart (2/3) + Donut (1/3) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Daily Borrows Line Chart — takes 2/3 -->
        <div class="lg:col-span-2 mockup-card p-6">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <div>
                    <h3 class="mockup-card-title text-base flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-[var(--purple-primary)] text-sm"></i>
                        Daily Key Borrows
                    </h3>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Last 30 days borrow activity</p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] uppercase tracking-wider">
                    Last 30 Days
                </span>
            </div>
            <div class="relative h-[220px]">
                <canvas id="dailyBorrowsChart"></canvas>
            </div>
        </div>

        <!-- Key Status Donut — takes 1/3 -->
        <div class="mockup-card p-6 flex flex-col">
            <div class="mb-4">
                <h3 class="mockup-card-title text-base flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-[var(--purple-primary)] text-sm"></i>
                    Key Status
                </h3>
                <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Current slot breakdown</p>
            </div>

            <!-- Donut canvas — constrained size -->
            <div class="relative h-[160px] flex items-center justify-center">
                <canvas id="statusPieChart"></canvas>
            </div>

            <!-- Manual legend pills -->
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                        <span class="text-xs font-semibold text-[var(--text-body)]">Available</span>
                    </div>
                    <span class="text-xs font-extrabold text-emerald-400">{{ $availableCount }}</span>
                </div>
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 flex-shrink-0"></span>
                        <span class="text-xs font-semibold text-[var(--text-body)]">Borrowed</span>
                    </div>
                    <span class="text-xs font-extrabold text-amber-400">{{ $borrowedCount }}</span>
                </div>
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 flex-shrink-0"></span>
                        <span class="text-xs font-semibold text-[var(--text-body)]">Missing</span>
                    </div>
                    <span class="text-xs font-extrabold text-rose-400">{{ $missingCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Bottom Row: Most Borrowed Keys + Top Borrowers ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Most Borrowed Keys -->
        <div class="mockup-card p-6">
            <div class="flex items-center gap-2 mb-5 pb-4 border-b border-[var(--border-subtle)]">
                <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="mockup-card-title text-sm">Most Borrowed Keys</h3>
                    <p class="text-[10px] text-[var(--text-muted)]">Top 5 most-requested slots</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($popularKeys as $i => $pk)
                    @php
                        $maxTotal = $popularKeys->first()->total ?: 1;
                        $pct = round(($pk->total / $maxTotal) * 100);
                        $barColors = ['bg-violet-500','bg-sky-500','bg-emerald-500','bg-amber-500','bg-rose-500'];
                        $bar = $barColors[$i] ?? 'bg-violet-500';
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-5 h-5 rounded-full bg-[var(--border-subtle)] text-[var(--text-muted)] font-extrabold text-[10px] flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                                <span class="text-xs font-bold text-[var(--text-heading)] truncate">{{ $pk->key->key_name ?? 'Unknown' }}</span>
                                <span class="text-[10px] text-[var(--text-muted)] flex-shrink-0">({{ $pk->key->room_name ?? '—' }})</span>
                            </div>
                            <span class="text-xs font-extrabold text-[var(--purple-primary)] ml-2 flex-shrink-0">{{ $pk->total }}×</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-[var(--border-subtle)]">
                            <div class="h-1.5 rounded-full {{ $bar }} transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-[var(--text-muted)] text-sm text-center py-6">No borrow data available yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Top 10 Borrowers -->
        <div class="mockup-card p-6">
            <div class="flex items-center gap-2 mb-5 pb-4 border-b border-[var(--border-subtle)]">
                <div class="w-8 h-8 rounded-xl bg-violet-500/15 text-violet-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-ranking-star"></i>
                </div>
                <div>
                    <h3 class="mockup-card-title text-sm">Top Borrowers</h3>
                    <p class="text-[10px] text-[var(--text-muted)]">Users with the most borrow events</p>
                </div>
            </div>

            <div class="space-y-2.5">
                @forelse($topBorrowers as $i => $borrower)
                    <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[var(--purple-soft)] transition-colors">
                        <!-- Rank -->
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-extrabold flex-shrink-0
                            {{ $i === 0 ? 'bg-amber-400 text-amber-900' : ($i === 1 ? 'bg-slate-400 text-slate-900' : ($i === 2 ? 'bg-orange-600 text-white' : 'bg-[var(--border-subtle)] text-[var(--text-muted)]')) }}">
                            {{ $i + 1 }}
                        </span>
                        <!-- Avatar -->
                        <div class="w-8 h-8 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                            {{ strtoupper(substr($borrower->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <!-- Name & Role -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-[var(--text-heading)] truncate">{{ $borrower->user->name ?? 'Unknown User' }}</p>
                            <p class="text-[10px] text-[var(--text-muted)] capitalize">{{ $borrower->user->role ?? '—' }}</p>
                        </div>
                        <!-- Count badge -->
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] flex-shrink-0">
                            {{ $borrower->total }} borrows
                        </span>
                    </div>
                @empty
                    <p class="text-[var(--text-muted)] text-sm text-center py-6">No borrower data available yet.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Daily Borrows Line Chart ─────────────────────────────
    const dailyLabels = {!! json_encode($dailyLabels) !!};
    const dailyData   = {!! json_encode($dailyData) !!};
    const maxVal      = Math.max(...dailyData, 1);

    const dailyCanvas = document.getElementById('dailyBorrowsChart');
    const dCtx = dailyCanvas.getContext('2d');

    const areaGrad = dCtx.createLinearGradient(0, 0, 0, 220);
    areaGrad.addColorStop(0, 'rgba(139, 120, 212, 0.25)');
    areaGrad.addColorStop(1, 'rgba(139, 120, 212, 0)');

    new Chart(dailyCanvas, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Borrows',
                data: dailyData,
                borderColor: '#8b78d4',
                backgroundColor: areaGrad,
                borderWidth: 2.5,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#8b78d4',
                pointBorderColor: '#19142c',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#19142c',
                    borderColor: '#292244',
                    borderWidth: 1,
                    titleFont: { family: 'Outfit', weight: '700', size: 12 },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                    padding: 10,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        title: ctx => ctx[0].label,
                        label: ctx => ` ${ctx.parsed.y} borrow${ctx.parsed.y !== 1 ? 's' : ''}`,
                    }
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 9 },
                        color: '#847bb0',
                        maxTicksLimit: 10,
                    }
                },
                y: {
                    min: 0,
                    // Always show at least 5 steps even if max is small
                    max: Math.max(maxVal + 1, 5),
                    grid: { color: '#292244' },
                    border: { display: false },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 10 },
                        color: '#847bb0',
                        // Force integer ticks only
                        callback: val => Number.isInteger(val) ? val : null,
                        stepSize: 1,
                        precision: 0,
                    }
                }
            }
        }
    });

    // ── Key Status Donut Chart ──────────────────────────────
    new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Borrowed', 'Missing'],
            datasets: [{
                data: [{{ $availableCount }}, {{ $borrowedCount }}, {{ $missingCount }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderColor: ['#19142c'],
                borderWidth: 3,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#19142c',
                    borderColor: '#292244',
                    borderWidth: 1,
                    cornerRadius: 10,
                    padding: 10,
                    titleFont: { family: 'Outfit', weight: '700', size: 12 },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
                }
            }
        }
    });
});
</script>
@endsection