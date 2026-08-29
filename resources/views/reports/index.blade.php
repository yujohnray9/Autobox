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
            <p class="text-xs text-[var(--text-muted)] mt-0.5 font-medium">System overview — Keys, Users, and Schedules.</p>
        </div>
    </div>

    <!-- ── KPI Summary Cards (6 columns) ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- Total Keys -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-[var(--purple-primary)]">
            <div class="w-12 h-12 rounded-2xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Total Key Slots</p>
                <p class="text-3xl font-heading font-extrabold text-[var(--purple-primary)] leading-none">{{ $totalKeys }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">Registered in lockbox system</p>
            </div>
        </div>

        <!-- Active Users -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-violet-500">
            <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Active Users</p>
                <p class="text-3xl font-heading font-extrabold text-violet-600 leading-none">{{ $totalUsers }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">{{ $inactiveUsers }} inactive / {{ $totalAdmins }} admin(s)</p>
            </div>
        </div>

        <!-- Active Schedules -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-sky-500">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Active Schedules</p>
                <p class="text-3xl font-heading font-extrabold text-sky-600 leading-none">{{ $totalSchedules }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">{{ $inactiveSchedules }} inactive schedules</p>
            </div>
        </div>

        <!-- Available Keys -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-emerald-500">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-lock-open"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Available Keys</p>
                <p class="text-3xl font-heading font-extrabold text-emerald-600 leading-none">{{ $availableCount }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">Ready in lockbox</p>
            </div>
        </div>

        <!-- Borrowed Keys -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-amber-500">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Borrowed Keys</p>
                <p class="text-3xl font-heading font-extrabold text-amber-600 leading-none">{{ $borrowedCount }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">Currently checked out</p>
            </div>
        </div>

        <!-- Missing Keys -->
        <div class="mockup-card p-5 flex items-center gap-4 border-l-4 border-rose-500">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest mb-0.5">Missing / Alerts</p>
                <p class="text-3xl font-heading font-extrabold text-rose-600 leading-none">{{ $missingCount }}</p>
                <p class="text-[10px] text-[var(--text-muted)] mt-1">Flagged as missing</p>
            </div>
        </div>
    </div>

    <!-- ── Charts Row: Schedules per Day (2/3) + Key Status Donut (1/3) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Schedules per Day Bar Chart — takes 2/3 -->
        <div class="lg:col-span-2 mockup-card p-6">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <div>
                    <h3 class="mockup-card-title text-base flex items-center gap-2">
                        <i class="fa-solid fa-calendar-week text-[var(--purple-primary)] text-sm"></i>
                        Schedules Per Day of Week
                    </h3>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Active assigned schedules broken down by day</p>
                </div>
                <a href="{{ route('schedules.index') }}" class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] uppercase tracking-wider hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                    Manage Schedules
                </a>
            </div>
            <div class="relative h-[220px]">
                <canvas id="schedulesBarChart"></canvas>
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

            <!-- Donut canvas -->
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
                    <span class="text-xs font-extrabold text-emerald-600">{{ $availableCount }}</span>
                </div>
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 flex-shrink-0"></span>
                        <span class="text-xs font-semibold text-[var(--text-body)]">Borrowed</span>
                    </div>
                    <span class="text-xs font-extrabold text-amber-600">{{ $borrowedCount }}</span>
                </div>
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 flex-shrink-0"></span>
                        <span class="text-xs font-semibold text-[var(--text-body)]">Missing</span>
                    </div>
                    <span class="text-xs font-extrabold text-rose-600">{{ $missingCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Bottom Row: Most Scheduled Keys + Top Scheduled Users ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Most Scheduled Keys -->
        <div class="mockup-card p-6">
            <div class="flex items-center gap-2 mb-5 pb-4 border-b border-[var(--border-subtle)]">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="mockup-card-title text-sm">Most Scheduled Keys</h3>
                    <p class="text-[10px] text-[var(--text-muted)]">Top 5 keys with the most active schedules</p>
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
                                <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-extrabold text-[10px] flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                                <span class="text-xs font-bold text-[var(--text-heading)] truncate">{{ $pk->key->key_name ?? 'Unknown' }}</span>
                                <span class="text-[10px] text-[var(--text-muted)] flex-shrink-0">({{ $pk->key->room_name ?? '—' }})</span>
                            </div>
                            <span class="text-xs font-extrabold text-[var(--purple-primary)] ml-2 flex-shrink-0">{{ $pk->total }} schedules</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full {{ $bar }} transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-[var(--text-muted)] text-sm text-center py-6">No schedule data available yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Top Scheduled Users -->
        <div class="mockup-card p-6">
            <div class="flex items-center gap-2 mb-5 pb-4 border-b border-[var(--border-subtle)]">
                <div class="w-8 h-8 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-ranking-star"></i>
                </div>
                <div>
                    <h3 class="mockup-card-title text-sm">Most Scheduled Users</h3>
                    <p class="text-[10px] text-[var(--text-muted)]">Users with the most active access schedules</p>
                </div>
            </div>

            <div class="space-y-2.5">
                @forelse($topScheduledUsers as $i => $row)
                    <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[var(--purple-soft)] transition-colors">
                        <!-- Rank -->
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-extrabold flex-shrink-0
                            {{ $i === 0 ? 'bg-amber-400 text-amber-900' : ($i === 1 ? 'bg-slate-300 text-slate-800' : ($i === 2 ? 'bg-orange-200 text-orange-900' : 'bg-slate-100 text-slate-600')) }}">
                            {{ $i + 1 }}
                        </span>
                        <!-- Avatar -->
                        <div class="w-8 h-8 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                            {{ strtoupper(substr($row->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <!-- Name & Role -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-[var(--text-heading)] truncate">{{ $row->user->name ?? 'Unknown User' }}</p>
                            <p class="text-[10px] text-[var(--text-muted)] capitalize">{{ $row->user->role ?? '—' }}</p>
                        </div>
                        <!-- Count badge -->
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] flex-shrink-0">
                            {{ $row->total }} schedules
                        </span>
                    </div>
                @empty
                    <p class="text-[var(--text-muted)] text-sm text-center py-6">No user schedule data available yet.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Schedules per Day Bar Chart ─────────────────────────────
    const dayLabels      = {!! json_encode($dayLabels) !!};
    const schedulePerDay = {!! json_encode($schedulePerDay) !!};

    new Chart(document.getElementById('schedulesBarChart'), {
        type: 'bar',
        data: {
            labels: dayLabels,
            datasets: [{
                label: 'Active Schedules',
                data: schedulePerDay,
                backgroundColor: '#6451a3',
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 30,
            }]
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
                    titleFont: { family: 'Outfit', weight: '700', size: 12 },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                    padding: 10,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} active schedule${ctx.parsed.y !== 1 ? 's' : ''}`,
                    }
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 11 },
                        color: '#847d9c',
                    }
                },
                y: {
                    min: 0,
                    grid: { color: '#f0edf7' },
                    border: { display: false },
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

    // ── Key Status Donut Chart ──────────────────────────────
    new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Borrowed', 'Missing'],
            datasets: [{
                data: [{{ $availableCount }}, {{ $borrowedCount }}, {{ $missingCount }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderColor: ['#ffffff'],
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
                    backgroundColor: '#1e1938',
                    titleColor: '#ffffff',
                    bodyColor: '#e2e8f0',
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