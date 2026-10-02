@extends('layouts.app')

@section('title', 'Transaction Logs')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-right-left text-[var(--purple-primary)] text-lg"></i>
                Transaction Logs
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Complete history of all key borrow and return activities.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('transactions.export', request()->query()) }}"
               title="Export filtered transactions to CSV"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-md">
                <i class="fa-solid fa-file-csv text-xs"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Search & Filters Bar -->
    <div class="mockup-card p-4 space-y-3">
        <form method="GET" action="{{ route('transactions.index') }}" class="flex items-center gap-2.5 flex-wrap">
            <!-- Keyword / General Search Input -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search user, ID, room, notes, or date..."
                       class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-[var(--border-subtle)] bg-white text-xs font-semibold text-[var(--text-heading)] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 transition-all">
            </div>

            <!-- Room / Key Filter Dropdown -->
            <div class="min-w-[160px] flex-shrink-0">
                <select name="key_id"
                        class="w-full px-3 py-2.5 rounded-xl border border-[var(--border-subtle)] bg-white text-xs font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 transition-all">
                    <option value="">All Rooms / Keys</option>
                    @foreach($keys as $k)
                        <option value="{{ $k->id }}" {{ (string) request('key_id') === (string) $k->id ? 'selected' : '' }}>
                            Slot #{{ $k->slot_number }} — {{ $k->room_name ?: $k->key_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Picker Filter -->
            <div class="min-w-[145px] flex-shrink-0 relative">
                <input type="date" name="date" value="{{ request('date') }}"
                       title="Filter transactions by date"
                       class="w-full px-3 py-2 rounded-xl border border-[var(--border-subtle)] bg-white text-xs font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 transition-all">
            </div>

            <!-- Action Filter Dropdown -->
            <div class="min-w-[125px] flex-shrink-0">
                <select name="action"
                        class="w-full px-3 py-2.5 rounded-xl border border-[var(--border-subtle)] bg-white text-xs font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 transition-all">
                    <option value="">All Actions</option>
                    <option value="borrow" {{ request('action') === 'borrow' ? 'selected' : '' }}>Borrow</option>
                    <option value="return" {{ request('action') === 'return' ? 'selected' : '' }}>Return</option>
                </select>
            </div>

            <!-- Submit Search Button -->
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] active:scale-[0.98] transition-all shadow-sm flex-shrink-0">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                <span>Search</span>
            </button>

            <!-- Reset Button -->
            @if(request()->hasAny(['search', 'key_id', 'date', 'action', 'status']))
                <a href="{{ route('transactions.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors flex-shrink-0"
                   title="Reset all search filters">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>Reset</span>
                </a>
            @endif
        </form>

        <!-- Active Filter Indicator Badges -->
        @if(request()->hasAny(['search', 'key_id', 'date', 'action']))
            <div class="flex items-center justify-between text-xs text-[var(--text-muted)] bg-purple-50/70 border border-purple-100 rounded-xl px-3.5 py-2 flex-wrap gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-extrabold text-[var(--purple-primary)] text-[11px] uppercase tracking-wider flex items-center gap-1">
                        <i class="fa-solid fa-filter text-[10px]"></i> Active Filters:
                    </span>
                    @if(request('search'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white border border-purple-200 text-purple-900 font-semibold text-[11px]">
                            Keyword: <strong>"{{ request('search') }}"</strong>
                        </span>
                    @endif
                    @if(request('key_id'))
                        @php $selectedKey = $keys->firstWhere('id', request('key_id')); @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white border border-purple-200 text-purple-900 font-semibold text-[11px]">
                            Room: <strong>{{ $selectedKey->room_name ?: $selectedKey->key_name }}</strong>
                        </span>
                    @endif
                    @if(request('date'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white border border-purple-200 text-purple-900 font-semibold text-[11px]">
                            Date: <strong>{{ \Carbon\Carbon::parse(request('date'))->format('M d, Y') }}</strong>
                        </span>
                    @endif
                    @if(request('action'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white border border-purple-200 text-purple-900 font-semibold text-[11px] uppercase">
                            Action: <strong>{{ request('action') }}</strong>
                        </span>
                    @endif
                </div>
                <a href="{{ route('transactions.index') }}" class="text-[var(--purple-primary)] hover:underline font-extrabold text-[11px]">
                    Clear all filters
                </a>
            </div>
        @endif
    </div>

    <!-- Transactions Table -->
    <div class="mockup-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-[var(--border-subtle)]">
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Key / Room</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Action & Status</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Date & Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-[var(--purple-soft)] transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] font-extrabold text-xs flex items-center justify-center ring-2 ring-[var(--purple-primary)]/20">
                                        {{ strtoupper(substr($t->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[var(--text-heading)] text-sm">{{ $t->user->name ?? 'System' }}</p>
                                        <p class="text-xs text-[var(--text-muted)]">{{ $t->user->employee_id ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-[var(--text-heading)] text-sm">{{ $t->key->key_name ?? 'N/A' }}</p>
                                <p class="text-xs font-semibold text-[var(--purple-primary)]">{{ $t->key->room_name ?? '' }} · Slot #{{ $t->key->slot_number ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                        {{ $t->action === 'borrow'
                                            ? 'bg-amber-100 text-amber-800 border border-amber-200'
                                            : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ $t->action }}
                                    </span>
                                    @if($t->status)
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase
                                            {{ strtolower($t->status) === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $t->status }}
                                        </span>
                                    @endif
                                </div>
                                @if(!empty($t->notes))
                                    <p class="text-[11px] text-[var(--text-muted)] mt-1 italic truncate max-w-xs">{{ $t->notes }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-mono font-semibold text-[var(--text-heading)]">
                                    {{ $t->created_at->format('M d, Y · h:i A') }}
                                </p>
                                @if($t->borrowed_at && $t->action === 'return' && $t->returned_at)
                                    <p class="text-[10px] font-mono text-[var(--text-muted)] mt-0.5">
                                        Borrowed: {{ $t->borrowed_at->format('M d, h:i A') }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-[var(--text-muted)] text-sm">
                                <i class="fa-solid fa-magnifying-glass text-4xl block mb-3 opacity-20"></i>
                                @if(request()->hasAny(['search', 'key_id', 'date', 'action']))
                                    <p class="font-bold text-[var(--text-heading)]">No transactions found matching your search filters.</p>
                                    <p class="text-xs text-[var(--text-muted)] mt-1">Try adjusting your keyword, selected room, date, or action filter.</p>
                                    <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3.5 py-1.5 rounded-xl text-xs font-bold text-[var(--purple-primary)] bg-[var(--purple-soft)] hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                                        <i class="fa-solid fa-rotate-left text-xs"></i> Reset Filters
                                    </a>
                                @else
                                    <p class="font-bold">No transactions recorded yet.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="px-5 py-4 border-t border-[var(--border-subtle)]">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.Echo) {
            window.Echo.channel('autobox-hardware')
                .listen('.KeyStatusUpdated', (e) => {
                    console.log('[Pusher Echo] Key Status Updated — refreshing transactions...', e);
                    setTimeout(() => window.location.reload(), 800);
                })
                .listen('.AccessLogged', (e) => {
                    if (e.action === 'borrow' || e.action === 'return') {
                        console.log('[Pusher Echo] Transaction Access Logged — refreshing transactions...', e);
                        setTimeout(() => window.location.reload(), 800);
                    }
                });
        }
    });
</script>
@endsection
