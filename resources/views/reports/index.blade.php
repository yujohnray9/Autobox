@extends('layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-xl font-heading font-bold text-slate-900">Analytics & Reports</h2>
        <p class="text-xs text-slate-500 mt-0.5">Key usage statistics, access trends, and system performance insights</p>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Available Slots</p>
            <h3 class="text-3xl font-heading font-extrabold text-emerald-600 mt-1">{{ $statusCounts['available'] ?? 0 }}</h3>
            <p class="text-xs text-emerald-600 font-semibold mt-1">Ready for access</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Borrowed Slots</p>
            <h3 class="text-3xl font-heading font-extrabold text-amber-500 mt-1">{{ $statusCounts['borrowed'] ?? 0 }}</h3>
            <p class="text-xs text-amber-600 font-semibold mt-1">Currently in use</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Missing / Alert</p>
            <h3 class="text-3xl font-heading font-extrabold text-rose-600 mt-1">{{ $statusCounts['missing'] ?? 0 }}</h3>
            <p class="text-xs text-rose-600 font-semibold mt-1">Requires attention</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Daily Borrows Chart (Line) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-base font-heading font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-blue-600"></i>
                Daily Borrow Activity (Last 7 Days)
            </h2>
            <canvas id="dailyBorrowsChart" height="200"></canvas>
        </div>

        <!-- Key Status Pie Chart -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-base font-heading font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-violet-600"></i>
                Current Key Status Distribution
            </h2>
            <canvas id="statusPieChart" height="200"></canvas>
        </div>

    </div>

    <!-- Most Borrowed Keys Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-base font-heading font-bold text-slate-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-fire text-orange-500"></i>
            Most Frequently Borrowed Keys
        </h2>

        @if($popularKeys->isEmpty())
            <p class="text-xs text-slate-400 text-center py-6">No transaction data yet.</p>
        @else
            <div class="space-y-3">
                @foreach($popularKeys as $i => $pk)
                    <div class="flex items-center gap-4">
                        <span class="w-7 h-7 rounded-lg bg-violet-50 text-violet-700 font-extrabold text-xs flex items-center justify-center border border-violet-100">{{ $i + 1 }}</span>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-900">{{ $pk->key->key_name ?? 'Unknown' }}
                                <span class="ml-2 text-xs font-medium text-violet-600">{{ $pk->key->room_name ?? '' }}</span>
                            </p>
                            <div class="mt-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full gradient-violet-blue"
                                     style="width: {{ min(100, ($pk->total / max($popularKeys->max('total'), 1)) * 100) }}%"></div>
                            </div>
                        </div>
                        <span class="text-xs font-extrabold text-slate-600">{{ $pk->total }} borrows</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
// Daily Borrows Chart
const dailyLabels = @json($dailyBorrows->keys());
const dailyData   = @json($dailyBorrows->values());

new Chart(document.getElementById('dailyBorrowsChart'), {
    type: 'line',
    data: {
        labels: dailyLabels.length ? dailyLabels : ['No data'],
        datasets: [{
            label: 'Borrows',
            data: dailyData.length ? dailyData : [0],
            borderColor: '#6d28d9',
            backgroundColor: 'rgba(109, 40, 217, 0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6d28d9',
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// Status Pie Chart
const statusLabels = ['Available', 'Borrowed', 'Missing'];
const statusData   = [
    {{ $statusCounts['available'] ?? 0 }},
    {{ $statusCounts['borrowed'] ?? 0 }},
    {{ $statusCounts['missing'] ?? 0 }},
];

new Chart(document.getElementById('statusPieChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusData,
            backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
            borderWidth: 3,
            borderColor: '#ffffff',
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
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
