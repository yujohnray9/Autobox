@extends('layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div>
        <h2 class="mockup-card-title text-xl flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-[var(--purple-primary)] text-lg"></i>
            Analytics & Reports
        </h2>
        <p class="text-xs text-[var(--text-muted)] mt-0.5">System usage insights and activity summaries.</p>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="mockup-card p-5 text-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 text-lg flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-2xl font-heading font-extrabold text-emerald-600">{{ $totalBorrows }}</p>
            <p class="text-[11px] text-[var(--text-muted)] mt-1 font-semibold">Total Borrows</p>
        </div>
        <div class="mockup-card p-5 text-center">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 text-lg flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <p class="text-2xl font-heading font-extrabold text-blue-600">{{ $totalReturns }}</p>
            <p class="text-[11px] text-[var(--text-muted)] mt-1 font-semibold">Total Returns</p>
        </div>
        <div class="mockup-card p-5 text-center">
            <div class="w-10 h-10 rounded-xl bg-[var(--purple-soft)] text-[var(--purple-primary)] text-lg flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <p class="text-2xl font-heading font-extrabold text-[var(--purple-primary)]">{{ $totalGranted }}</p>
            <p class="text-[11px] text-[var(--text-muted)] mt-1 font-semibold">QR Access Granted</p>
        </div>
        <div class="mockup-card p-5 text-center">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 text-lg flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-ban"></i>
            </div>
            <p class="text-2xl font-heading font-extrabold text-rose-600">{{ $totalDenied }}</p>
            <p class="text-[11px] text-[var(--text-muted)] mt-1 font-semibold">QR Access Denied</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Daily Borrows Chart (Line) -->
        <div class="mockup-card p-6">
            <h3 class="mockup-card-title text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-[var(--purple-primary)]"></i>
                Daily Key Borrows (Last 30 Days)
            </h3>
            <canvas id="dailyBorrowsChart" height="200"></canvas>
        </div>

        <!-- Key Status Pie Chart -->
        <div class="mockup-card p-6">
            <h3 class="mockup-card-title text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-[var(--purple-primary)]"></i>
                Key Status Overview
            </h3>
            <canvas id="statusPieChart" height="200"></canvas>
        </div>
    </div>

    <!-- Top Borrowers Table -->
    <div class="mockup-card p-6">
        <h3 class="mockup-card-title text-sm mb-4 flex items-center gap-2">
            <i class="fa-solid fa-ranking-star text-amber-500"></i>
            Top 10 Borrowers
        </h3>
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-[var(--border-subtle)]">
                    <th class="pb-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">#</th>
                    <th class="pb-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">User</th>
                    <th class="pb-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Total Borrows</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border-subtle)]">
                @forelse($topBorrowers as $i => $borrower)
                    <tr class="hover:bg-[var(--purple-soft)] transition-colors">
                        <td class="py-2.5 text-[var(--text-muted)] font-mono text-xs font-semibold">{{ $i + 1 }}</td>
                        <td class="py-2.5 font-bold text-[var(--text-heading)]">{{ $borrower->user->name ?? 'Unknown' }}</td>
                        <td class="py-2.5">
                            <span class="font-extrabold text-[var(--purple-primary)]">{{ $borrower->total }}</span>
                            <span class="text-[var(--text-muted)] text-xs ml-1">borrows</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-8 text-center text-[var(--text-muted)] text-sm">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
// Daily Borrows Chart
const dailyLabels = {!! json_encode($dailyLabels) !!};
const dailyData = {!! json_encode($dailyData) !!};

const dailyCanvas = document.getElementById('dailyBorrowsChart');
const dCtx = dailyCanvas.getContext('2d');
const areaGrad = dCtx.createLinearGradient(0, 0, 0, 200);
areaGrad.addColorStop(0, 'rgba(109,40,217,0.18)');
areaGrad.addColorStop(1, 'rgba(109,40,217,0)');

new Chart(dailyCanvas, {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Borrows',
            data: dailyData,
            borderColor: '#6d28d9',
            backgroundColor: areaGrad,
            borderWidth: 2.5,
            tension: 0.45,
            pointRadius: 4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#6d28d9',
            pointBorderWidth: 2,
            pointHoverRadius: 6,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#12141c',
                titleFont: { weight: '700', size: 12 },
                bodyFont: { size: 12 },
                padding: 10,
                cornerRadius: 8,
                displayColors: false,
            },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { grid: { color: '#f1f0f5' }, ticks: { font: { size: 10 } }, border: { display: false } }
        }
    }
});

// Status Pie Chart
new Chart(document.getElementById('statusPieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Borrowed', 'Missing'],
        datasets: [{
            data: [{{ $availableCount }}, {{ $borrowedCount }}, {{ $missingCount }}],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 11 }, padding: 16 }
            }
        }
    }
});
</script>
@endsection