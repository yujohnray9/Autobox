@extends('layouts.app')

@section('title', 'QR Audit Logs')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div>
        <h2 class="mockup-card-title text-xl flex items-center gap-2">
            <i class="fa-solid fa-qrcode text-[var(--purple-primary)] text-lg"></i>
            QR Audit Logs
        </h2>
        <p class="text-xs text-[var(--text-muted)] mt-0.5">All QR scan attempts recorded by the AUTOBOX terminal.</p>
    </div>

    <!-- Table -->
    <div class="mockup-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-[var(--border-subtle)]">
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">QR Code</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Reason</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Result</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Date & Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-[var(--purple-soft)] transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-[var(--text-heading)] text-sm">{{ $log->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-[var(--text-muted)]">{{ $log->user->employee_id ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs font-mono text-[var(--text-muted)] truncate max-w-[130px]">{{ $log->qr_token }}</td>
                            <td class="px-5 py-3.5 text-sm text-[var(--text-body)]">{{ $log->reason }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                    {{ $log->result === 'granted'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-rose-100 text-rose-700' }}">
                                    {{ $log->result }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm font-mono text-[var(--text-muted)]">{{ $log->created_at->format('M d, Y · h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-[var(--text-muted)] text-sm">
                                <i class="fa-solid fa-qrcode text-4xl block mb-3 opacity-20"></i>
                                No QR scan logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-5 py-4 border-t border-[var(--border-subtle)]">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
