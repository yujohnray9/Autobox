@extends('layouts.app')

@section('title', 'QR Audit Logs')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-qrcode text-[var(--purple-primary)] text-lg"></i>
                QR Audit Logs
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">All QR scan attempts recorded by the AUTOBOX terminal with cryptographic proof.</p>
        </div>
    </div>

    <!-- Cryptographic Data Integrity & Tamper-Proof Audit Card -->
    <div class="mockup-card p-5 {{ ($chainStatus['is_valid'] ?? true) ? 'bg-gradient-to-br from-white via-white to-violet-50/40 border-[var(--border-subtle)]' : 'bg-rose-50/90 border-rose-300 ring-2 ring-rose-200' }}" id="chainCard">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0 {{ ($chainStatus['is_valid'] ?? true) ? 'bg-emerald-100 text-emerald-700 shadow-sm border border-emerald-200' : 'bg-rose-600 text-white shadow-lg animate-pulse' }}">
                    <i class="fa-solid {{ ($chainStatus['is_valid'] ?? true) ? 'fa-shield-halved' : 'fa-triangle-exclamation' }} text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-extrabold text-[var(--text-heading)]">
                            Cryptographic Audit Chain: 
                            <span class="{{ ($chainStatus['is_valid'] ?? true) ? 'text-emerald-700' : 'text-rose-700' }}" id="chainStatusText">
                                {{ ($chainStatus['is_valid'] ?? true) ? '100% Tamper-Proof (SECURE)' : 'TAMPERING DETECTED!' }}
                            </span>
                        </h3>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ ($chainStatus['is_valid'] ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-200 text-rose-900' }}">
                            SHA-256 Chained
                        </span>
                    </div>
                    <p class="text-xs text-[var(--text-muted)] mt-1 max-w-3xl" id="chainMessageText">
                        {{ $chainStatus['message'] ?? 'Every scan and entry is mathematically chained to the previous block using SHA-256 hashing. Direct SQL edits or record tampering will immediately break the cryptographic seal.' }}
                    </p>

                    <!-- Chain Details / Stats -->
                    <div class="flex items-center gap-4 mt-3 flex-wrap text-[11px] font-mono">
                        <span class="text-slate-600 font-bold">Total Blocks: <strong class="text-slate-900" id="totalBlocksCount">{{ $chainStatus['total_blocks'] ?? 0 }}</strong></span>
                        <span class="text-slate-600 font-bold">Genesis Hash: <code class="text-[10px] text-slate-500 bg-slate-100 px-1 py-0.5 rounded">0000...0000</code></span>
                        <span class="text-slate-600 font-bold truncate max-w-[260px]" title="Latest Block Hash: {{ $chainStatus['latest_hash'] ?? 'N/A' }}">Latest Seal: <code class="text-[10px] text-violet-700 bg-violet-50 px-1 py-0.5 rounded" id="latestSealHash">{{ substr($chainStatus['latest_hash'] ?? 'N/A', 0, 16) }}...</code></span>
                        <span class="text-slate-600 font-bold">Compromised: <strong class="{{ ($chainStatus['tampered_count'] ?? 0) > 0 ? 'text-rose-600 font-black' : 'text-emerald-600' }}" id="tamperedCount">{{ $chainStatus['tampered_count'] ?? 0 }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Interactive Action & Demonstration Buttons -->
            <div class="flex items-center gap-2 flex-wrap flex-shrink-0 self-start md:self-center">
                <!-- Live Verify Button -->
                <button type="button" onclick="verifyIntegrityLive()" id="verifyBtn"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:opacity-95 shadow-sm flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate text-[11px]" id="verifyBtnIcon"></i>
                    <span>Verify Integrity</span>
                </button>

                <!-- Simulate Tamper Demo -->
                <form method="POST" action="{{ route('access-logs.simulate-tamper') }}" class="inline">
                    @csrf
                    <button type="submit" title="Demonstrate to your adviser/panel: Simulates what happens if a database row is secretly modified in MySQL"
                            class="px-3 py-2 rounded-xl text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 flex items-center gap-1.5 transition-colors cursor-pointer"
                            onclick="return confirm('Simulate database tampering for defense presentation? This will alter a record in MySQL without re-signing the cryptographic hash so you can observe the tamper detection alert.')">
                        <i class="fa-solid fa-bug text-[11px]"></i>
                        <span>Simulate Tamper</span>
                    </button>
                </form>

                <!-- Scan Absent Schedules Action -->
                <form method="POST" action="{{ route('access-logs.check-unattended') }}" class="inline">
                    @csrf
                    <button type="submit" title="Scan class schedules for absent users who did not scan or retrieve their scheduled key"
                            class="px-3 py-2 rounded-xl text-xs font-bold text-amber-900 bg-amber-200 hover:bg-amber-300 border border-amber-300 flex items-center gap-1.5 transition-colors cursor-pointer shadow-sm">
                        <i class="fa-solid fa-user-xmark text-[11px]"></i>
                        <span>Scan Absent Schedules</span>
                    </button>
                </form>

                <!-- Reseal / Restore Chain -->
                <form method="POST" action="{{ route('access-logs.restore-tamper') }}" class="inline">
                    @csrf
                    <button type="submit" title="Reseal all database rows and restore 100% cryptographic integrity"
                            class="px-3 py-2 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 flex items-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-solid fa-screwdriver-wrench text-[11px]"></i>
                        <span>Reseal Chain</span>
                    </button>
                </form>
            </div>
        </div>
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
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Cryptographic Proof (SHA-256)</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Date & Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                    @forelse($logs as $log)
                        @php
                            $cleanReason = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}]/u', '', $log->reason);
                            $cleanReason = trim($cleanReason);
                            $isAbsent = $log->action === 'absent' || str_contains(strtoupper($log->reason), 'ABSENT') || str_contains(strtoupper($log->reason), 'NOT ATTEND');
                            $isSecurityAlert = !$isAbsent && ($log->action === 'security_alert' || str_contains(strtoupper($log->reason), 'SECURITY ALERT') || str_contains(strtoupper($log->reason), 'TAMPER'));
                        @endphp
                        <tr class="transition-colors {{ $isAbsent ? 'bg-amber-50/75 hover:bg-amber-100/50' : ($isSecurityAlert ? 'bg-rose-50/70 hover:bg-rose-100/50' : 'hover:bg-[var(--purple-soft)]') }}">
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-[var(--text-heading)] text-sm flex items-center gap-1.5 flex-wrap">
                                    @if($isAbsent)
                                        <i class="fa-solid fa-user-xmark text-amber-600 text-xs"></i>
                                    @elseif($isSecurityAlert)
                                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-xs"></i>
                                    @endif
                                    {{ $log->user->name ?? 'Unknown User' }}
                                    @if($isAbsent)
                                        <span class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase tracking-wider bg-amber-200 text-amber-900 border border-amber-300">Absent</span>
                                    @endif
                                </p>
                                <p class="text-xs text-[var(--text-muted)]">{{ $log->user->employee_id ?? 'Unregistered' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs font-mono text-[var(--text-muted)] truncate max-w-[130px]">{{ $log->qr_token }}</td>
                            <td class="px-5 py-3.5 text-sm">
                                @if($isAbsent)
                                    <span class="inline-flex items-start gap-1.5 text-amber-900 font-semibold bg-amber-100/90 px-2.5 py-1.5 rounded-lg border border-amber-300 text-xs leading-relaxed">
                                        <i class="fa-solid fa-calendar-xmark text-amber-700 text-xs mt-0.5 flex-shrink-0"></i>
                                        <span>{{ $cleanReason }}</span>
                                    </span>
                                @elseif($isSecurityAlert)
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
                                @if($isAbsent)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                        <i class="fa-solid fa-xmark text-amber-700"></i>
                                        Absent / No-Show
                                    </span>
                                @elseif($log->result === 'granted')
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
                            <td class="px-5 py-3.5">
                                @if(!empty($log->record_hash))
                                    <div class="flex items-center gap-1.5" title="Block Hash: {{ $log->record_hash }}&#10;Previous Hash: {{ $log->previous_hash ?? 'Genesis' }}">
                                        <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-100 text-slate-700 border border-slate-200 flex items-center gap-1 cursor-help hover:bg-violet-50 hover:text-violet-700 hover:border-violet-300 transition-colors">
                                            <i class="fa-solid fa-link text-[8px] text-violet-500"></i>
                                            {{ substr($log->record_hash, 0, 7) }}...{{ substr($log->record_hash, -4) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-400">Legacy</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm font-mono text-[var(--text-muted)]">{{ $log->created_at->format('M d, Y · h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-[var(--text-muted)] text-sm">
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

<script>
    async function verifyIntegrityLive() {
        const btn = document.getElementById('verifyBtn');
        const icon = document.getElementById('verifyBtnIcon');
        if (btn) btn.disabled = true;
        if (icon) icon.classList.add('fa-spin');

        try {
            const res = await fetch("{{ route('access-logs.integrity') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            const card = document.getElementById('chainCard');
            const statusText = document.getElementById('chainStatusText');
            const messageText = document.getElementById('chainMessageText');
            const totalCount = document.getElementById('totalBlocksCount');
            const tamperedCount = document.getElementById('tamperedCount');
            const latestSeal = document.getElementById('latestSealHash');

            if (totalCount) totalCount.textContent = data.total_blocks;
            if (tamperedCount) tamperedCount.textContent = data.tampered_count;
            if (latestSeal && data.latest_hash) latestSeal.textContent = data.latest_hash.substring(0, 16) + '...';

            if (data.is_valid) {
                if (statusText) {
                    statusText.textContent = '100% Tamper-Proof (SECURE)';
                    statusText.className = 'text-emerald-700';
                }
                if (messageText) messageText.textContent = data.message;
                if (card) {
                    card.className = 'mockup-card p-5 bg-gradient-to-br from-white via-white to-violet-50/40 border-[var(--border-subtle)]';
                }
                if (typeof showToast === 'function') {
                    showToast(`<b>Audit Chain Verified!</b><br>${data.total_blocks} blocks verified with SHA-256. Zero tampering.`, 'success', 5000);
                }
            } else {
                if (statusText) {
                    statusText.textContent = 'TAMPERING DETECTED!';
                    statusText.className = 'text-rose-700 font-black';
                }
                if (messageText) {
                    const blockId = data.broken_blocks[0]?.id || '?';
                    messageText.textContent = `${data.message} Compromise detected at Record #${blockId}.`;
                }
                if (card) {
                    card.className = 'mockup-card p-5 bg-rose-50/90 border-rose-400 ring-2 ring-rose-200';
                }
                if (typeof showToast === 'function') {
                    showToast(`<b>SECURITY TAMPER ALERT!</b><br>${data.tampered_count} record(s) failed hash verification!`, 'error', 8000);
                }
            }
        } catch (e) {
            console.error('Integrity check failed', e);
        } finally {
            if (btn) btn.disabled = false;
            if (icon) icon.classList.remove('fa-spin');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.Echo) {
            window.Echo.channel('autobox-hardware')
                .listen('.AccessLogged', (e) => {
                    console.log('[Pusher Echo] New Access Logged — refreshing audit logs...', e);
                    setTimeout(() => window.location.reload(), 800);
                });
        }
    });
</script>
@endsection
