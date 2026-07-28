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
        <a href="{{ route('transactions.export') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-md">
            <i class="fa-solid fa-file-csv text-xs"></i> Export CSV
        </a>
    </div>

    <!-- Transactions Table -->
    <div class="mockup-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-[var(--border-subtle)]">
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Key / Room</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Action</th>
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
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                    {{ $t->action === 'borrow'
                                        ? 'bg-amber-100 text-amber-800'
                                        : 'bg-blue-100 text-blue-800' }}">
                                    {{ $t->action }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm font-mono text-[var(--text-muted)]">
                                {{ $t->created_at->format('M d, Y · h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-[var(--text-muted)] text-sm">
                                <i class="fa-solid fa-right-left text-4xl block mb-3 opacity-20"></i>
                                No transactions recorded yet.
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
@endsection
