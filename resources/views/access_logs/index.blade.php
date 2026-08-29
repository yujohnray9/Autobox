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
                        @php
                            $cleanReason = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}]/u', '', $log->reason);
                            $cleanReason = trim($cleanReason);
                            $isSecurityAlert = $log->action === 'security_alert' || str_contains(strtoupper($log->reason), 'SECURITY ALERT');
                        @endphp
                        <tr class="hover:bg-[var(--purple-soft)] transition-colors {{ $isSecurityAlert ? 'bg-rose-50/70' : '' }}">
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-[var(--text-heading)] text-sm flex items-center gap-1.5">
                                    @if($isSecurityAlert)
                                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-xs"></i>
                                    @endif
                                    {{ $log->user->name ?? 'Unknown User' }}
                                </p>
                                <p class="text-xs text-[var(--text-muted)]">{{ $log->user->employee_id ?? 'Unregistered' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs font-mono text-[var(--text-muted)] truncate max-w-[130px]">{{ $log->qr_token }}</td>
                            <td class="px-5 py-3.5 text-sm">
                                @if($isSecurityAlert)
                                    <span class="inline-flex items-center gap-1.5 text-rose-700 font-bold bg-rose-100 px-2.5 py-1 rounded-lg border border-rose-200 text-xs">
                                        <svg class="w-3.5 h-3.5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        {{ $cleanReason }}
                                    </span>
                                @else
                                    <span class="text-[var(--text-body)]">{{ $cleanReason }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($log->result === 'granted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Granted
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                        <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Denied
                                    </span>
                                @endif
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
