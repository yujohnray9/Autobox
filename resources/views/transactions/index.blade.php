@extends('layouts.app')

@section('title', 'Key Transaction Audit Trail')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-heading font-bold text-slate-900">Transaction History</h2>
            <p class="text-xs text-slate-500 mt-0.5">Complete log of all borrow and return transactions</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('transactions.export') }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs shadow-sm hover:bg-slate-50 flex items-center gap-2">
                <i class="fa-solid fa-file-csv text-emerald-600"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <form method="GET" action="{{ route('transactions.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user, key, room..." class="text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500 w-full sm:w-64">

            <select name="action" class="text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                <option value="">All Actions</option>
                <option value="borrow" {{ request('action') === 'borrow' ? 'selected' : '' }}>Borrow</option>
                <option value="return" {{ request('action') === 'return' ? 'selected' : '' }}>Return</option>
            </select>

            <select name="status" class="text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                <option value="">All Statuses</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                <option value="denied" {{ request('status') === 'denied' ? 'selected' : '' }}>Denied</option>
                <option value="missing" {{ request('status') === 'missing' ? 'selected' : '' }}>Missing</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl gradient-violet-blue text-white font-semibold text-xs">Filter</button>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Transaction ID</th>
                        <th class="p-4">User</th>
                        <th class="p-4">Key / Room</th>
                        <th class="p-4">Action</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Borrowed At</th>
                        <th class="p-4">Returned At</th>
                        <th class="p-4">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-4 font-mono font-bold text-slate-400">#{{ $t->id }}</td>
                            <td class="p-4">
                                <span class="font-bold text-slate-900 block text-sm">{{ $t->user->name ?? 'N/A' }}</span>
                                <span class="text-slate-400 text-xs">{{ $t->user->employee_id ?? '' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-slate-800">{{ $t->key->key_name ?? 'N/A' }}</span>
                                <span class="block text-violet-600 font-medium text-xs">{{ $t->key->room_name ?? '' }} (Slot #{{ $t->key->slot_number ?? '' }})</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                    @if($t->action === 'borrow') bg-amber-100 text-amber-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $t->action }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                    @if($t->status === 'success') bg-emerald-100 text-emerald-800
                                    @elseif($t->status === 'denied') bg-rose-100 text-rose-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $t->status }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-slate-600">
                                {{ $t->borrowed_at ? $t->borrowed_at->format('M d, Y h:i A') : 'N/A' }}
                            </td>
                            <td class="p-4 font-mono text-slate-600">
                                {{ $t->returned_at ? $t->returned_at->format('M d, Y h:i A') : '—' }}
                            </td>
                            <td class="p-4 text-slate-500 italic max-w-xs truncate">
                                {{ $t->notes ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">No transactions matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection
