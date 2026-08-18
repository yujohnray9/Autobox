@extends('layouts.app')

@section('title', 'User QR Code & Access Badge')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Top Action Nav -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--purple-primary)] hover:underline transition-all">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Back to Users Management
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow">
                <i class="fa-solid fa-print text-[11px]"></i> Print Badge
            </button>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700 transition-all">
                <i class="fa-solid fa-user-plus text-[11px]"></i> New User
            </a>
        </div>
    </div>

    <!-- Printable QR Badge Card -->
    <div class="mockup-card p-6 md:p-8 text-center space-y-6 print-badge-card" id="printableQr">

        <!-- Header Badge Title -->
        <div class="flex items-center justify-between border-b border-[var(--border-subtle)] pb-4">
            <div class="flex items-center gap-2 text-left">
                <div class="w-8 h-8 rounded-xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center font-extrabold text-sm shadow-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-sm text-[var(--text-heading)] leading-none">AUTOBOX ACCESS BADGE</h3>
                    <p class="text-[10px] text-[var(--text-muted)] font-bold tracking-wider uppercase mt-0.5">CCSICT Key Locker System</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider">
                Authorized
            </span>
        </div>

        <!-- User Avatar & Profile info -->
        <div>
            <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-500 text-white text-2xl font-extrabold flex items-center justify-center mx-auto mb-3 shadow-md ring-4 ring-violet-500/20">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">{{ $user->name }}</h2>
            <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">
                ID: <span class="font-mono font-bold text-[var(--text-heading)]">{{ $user->employee_id ?? 'N/A' }}</span>
                &middot;
                <span class="capitalize font-bold text-[var(--purple-primary)]">{{ $user->role }}</span>
                @if($user->department)
                    &middot; <span class="text-[var(--text-body)]">{{ $user->department }}</span>
                @endif
            </p>
        </div>

        <!-- Dynamic Real QR Code Image -->
        @php
            $token = $user->qr_token ?? $user->generateQrToken();
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=" . urlencode($token);
        @endphp

        <div class="space-y-3">
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-inner inline-block relative group">
                <img src="{{ $qrUrl }}"
                     alt="QR Access Code for {{ $user->name }}"
                     class="w-48 h-48 mx-auto rounded-lg transition-transform group-hover:scale-105">
            </div>

            <!-- Display Token -->
            <div class="bg-[var(--app-bg)] px-3 py-2 rounded-xl max-w-sm mx-auto border border-[var(--border-subtle)]">
                <p class="text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider mb-0.5">Physical Scanner Token</p>
                <p class="text-[11px] font-mono font-bold text-[var(--text-heading)] break-all">{{ $token }}</p>
            </div>
        </div>

        <!-- Authorized Schedule & Key Access Details -->
        <div class="bg-[var(--app-bg)]/80 border border-[var(--border-subtle)] rounded-2xl p-4 text-left space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-[var(--border-subtle)]">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-[var(--purple-primary)] text-xs"></i>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-[var(--text-heading)]">Assigned Key & Access Schedule</span>
                </div>
                <span class="text-[10px] font-bold text-[var(--text-muted)]">
                    {{ $user->schedules->count() }} {{ Str::plural('rule', $user->schedules->count()) }}
                </span>
            </div>

            @if($user->schedules->count() > 0)
                <div class="space-y-2">
                    @foreach($user->schedules as $sched)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[var(--card-bg)] border border-[var(--border-subtle)] text-xs">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded bg-[var(--purple-soft)] text-[var(--purple-primary)] font-mono font-extrabold text-[10px]">
                                        Slot #{{ $sched->key->slot_number ?? '?' }}
                                    </span>
                                    <span class="font-bold text-[var(--text-heading)]">
                                        {{ $sched->key->key_name ?? 'Key Slot' }}
                                    </span>
                                    <span class="text-[var(--text-muted)] text-[11px]">
                                        ({{ $sched->key->room_name ?? 'Room' }})
                                    </span>
                                </div>
                                <div class="text-[11px] text-[var(--text-muted)] flex items-center gap-1.5">
                                    <span class="capitalize font-semibold text-[var(--text-body)]">{{ $sched->day_of_week }}</span>
                                    <span>&bull;</span>
                                    <span class="font-mono text-emerald-400 font-bold">{{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }}</span>
                                    <span>-</span>
                                    <span class="font-mono text-rose-400 font-bold">{{ \Carbon\Carbon::parse($sched->end_time)->format('h:i A') }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ $sched->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-500/10 text-slate-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sched->is_active ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                                {{ $sched->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-2 text-center text-xs text-[var(--text-muted)]">
                    <p class="font-semibold">No scheduled access rules currently assigned.</p>
                    <a href="{{ route('schedules.index') }}" class="text-[var(--purple-primary)] hover:underline font-bold text-[11px] mt-1 inline-block no-print">
                        + Assign Schedule Now
                    </a>
                </div>
            @endif
        </div>

        <p class="text-[11px] text-[var(--text-muted)] leading-relaxed max-w-xs mx-auto font-medium">
            Scan this QR code at the physical terminal to unlock your assigned key slot during authorized schedule hours.
        </p>

        <!-- Action Buttons (Hidden on Print) -->
        <div class="pt-2 border-t border-[var(--border-subtle)] space-y-3 no-print">
            <form method="POST" action="{{ route('users.regenerate-qr', $user) }}">
                @csrf
                <button type="submit" onclick="return confirm('Regenerate QR token? The old QR code will no longer work.')"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                    <i class="fa-solid fa-rotate text-xs"></i> Regenerate QR Token
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, header, aside, .sidebar, #toastContainer {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .print-badge-card {
        border: 2px solid #333 !important;
        box-shadow: none !important;
        background: white !important;
        color: black !important;
        max-width: 450px !important;
        margin: 0 auto !important;
    }
}
</style>
@endsection

