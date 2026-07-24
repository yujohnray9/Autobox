@extends('layouts.app')

@section('title', 'Real-Time Monitoring Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Keys -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Key Slots</p>
                <h3 class="text-3xl font-heading font-extrabold text-slate-900 mt-1">{{ $totalKeys }}</h3>
                <p class="text-xs text-slate-500 mt-1">Configured in system</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl font-bold border border-violet-100">
                <i class="fa-solid fa-key"></i>
            </div>
        </div>

        <!-- Available Keys -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Available Keys</p>
                <h3 class="text-3xl font-heading font-extrabold text-emerald-600 mt-1">{{ $availableKeys }}</h3>
                <p class="text-xs text-emerald-600 font-semibold mt-1">Ready for access</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold border border-emerald-100">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <!-- Borrowed Keys -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Currently Borrowed</p>
                <h3 class="text-3xl font-heading font-extrabold text-amber-500 mt-1">{{ $borrowedKeys }}</h3>
                <p class="text-xs text-amber-600 font-semibold mt-1">In active use</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold border border-amber-100">
                <i class="fa-solid fa-user-clock"></i>
            </div>
        </div>

        <!-- Missing / Alert Keys -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Missing / Alerts</p>
                <h3 class="text-3xl font-heading font-extrabold text-rose-600 mt-1">{{ $missingKeys }}</h3>
                <p class="text-xs text-rose-600 font-semibold mt-1">Requires attention</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold border border-rose-100">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <!-- Key Slots Real-Time Grid -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-heading font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-violet-600"></i>
                    Physical Key Box Slots (Real-Time Status)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Live status monitor for AUTOBOX hardware key slots</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Available</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Borrowed</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Missing</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($keys as $key)
                @php
                    $borrower = $key->currentBorrower();
                @endphp
                <div class="p-4 rounded-xl border transition-all duration-200 relative overflow-hidden
                    @if($key->status === 'available') bg-emerald-50/40 border-emerald-200 hover:border-emerald-400
                    @elseif($key->status === 'borrowed') bg-amber-50/40 border-amber-200 hover:border-amber-400
                    @else bg-rose-50/40 border-rose-200 hover:border-rose-400 @endif">
                    
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md
                                @if($key->status === 'available') bg-emerald-100 text-emerald-800
                                @elseif($key->status === 'borrowed') bg-amber-100 text-amber-800
                                @else bg-rose-100 text-rose-800 @endif">
                                Slot #{{ $key->slot_number }}
                            </span>
                            <h3 class="font-heading font-bold text-base text-slate-900 mt-2">{{ $key->key_name }}</h3>
                            <p class="text-xs font-semibold text-violet-700 flex items-center gap-1 mt-0.5">
                                <i class="fa-solid fa-door-open text-[10px]"></i> {{ $key->room_name }}
                            </p>
                        </div>

                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold
                            @if($key->status === 'available') bg-emerald-500 text-white
                            @elseif($key->status === 'borrowed') bg-amber-500 text-white
                            @else bg-rose-500 text-white @endif shadow-sm">
                            <i class="fa-solid @if($key->status === 'available') fa-lock-open @elseif($key->status === 'borrowed') fa-key @else fa-ban @endif"></i>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200/60 text-xs">
                        @if($key->status === 'borrowed' && $borrower)
                            <p class="text-slate-600 font-medium truncate"><span class="text-slate-400">Borrower:</span> {{ $borrower->user->name ?? 'User' }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5"><i class="fa-regular fa-clock"></i> {{ $borrower->created_at->diffForHumans() }}</p>
                        @elseif($key->status === 'available')
                            <p class="text-emerald-700 font-medium">Ready in Lock Box</p>
                        @else
                            <p class="text-rose-700 font-medium">Flagged Missing</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Transactions & Scan Audit Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Live Transactions Log -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-heading font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                    Recent Key Transactions
                </h2>
                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800">View All →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="pb-3">User</th>
                            <th class="pb-3">Key / Room</th>
                            <th class="pb-3">Action</th>
                            <th class="pb-3">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentTransactions as $t)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 font-semibold text-slate-800">
                                    {{ $t->user->name ?? 'System' }}
                                    <span class="block text-[10px] text-slate-400 font-normal">{{ $t->user->employee_id ?? '' }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="font-medium text-slate-700">{{ $t->key->key_name ?? 'N/A' }}</span>
                                    <span class="block text-[10px] text-violet-600 font-semibold">{{ $t->key->room_name ?? '' }} (Slot #{{ $t->key->slot_number ?? '' }})</span>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase
                                        @if($t->action === 'borrow') bg-amber-100 text-amber-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ $t->action }}
                                    </span>
                                </td>
                                <td class="py-3 text-slate-500">
                                    {{ $t->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent QR Scan Logs -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-heading font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-qrcode text-violet-600"></i>
                    Recent QR Scans
                </h2>
                <a href="{{ route('access-logs.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800">View All →</a>
            </div>

            <div class="space-y-3">
                @forelse($recentLogs as $log)
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <div class="truncate">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $log->user->name ?? 'Unknown QR Code' }}</p>
                            <p class="text-[10px] text-slate-500 truncate">{{ $log->reason }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase flex-shrink-0
                            @if($log->result === 'granted') bg-emerald-100 text-emerald-800 @else bg-rose-100 text-rose-800 @endif">
                            {{ $log->result }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No scan logs recorded.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
