@extends('layouts.app')

@section('title', 'User QR Code & Access Badge')

@section('content')
<div class="max-w-xl mx-auto space-y-6" x-data="{ showRegenModal: false }">
    <!-- Top Action Nav -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--purple-primary)] hover:underline transition-all">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Back to Users Management
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow">
                <i class="fa-solid fa-print text-[11px]"></i> Print Badge
            </button>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
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
            <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-extrabold uppercase tracking-wider">
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
        <div class="bg-white border border-[var(--border-subtle)] rounded-2xl p-4 text-left space-y-3 shadow-sm">
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
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
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
                                    <span class="font-mono text-emerald-700 font-bold">{{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }}</span>
                                    <span>-</span>
                                    <span class="font-mono text-rose-700 font-bold">{{ \Carbon\Carbon::parse($sched->end_time)->format('h:i A') }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ $sched->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sched->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
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
            <button type="button" @click="showRegenModal = true"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                <i class="fa-solid fa-rotate text-xs"></i> Regenerate QR Token
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════
         PREMIUM CONFIRMATION REGENERATE QR MODAL
         ═══════════════════════════════════ -->
    <div x-show="showRegenModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showRegenModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="showRegenModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
             class="w-full max-w-md bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 sm:p-7 space-y-5 text-center relative overflow-hidden">
            
            <!-- Glowing Purple/Amber Ambient Light Accent -->
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-44 h-24 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Caution Icon -->
            <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 text-2xl flex items-center justify-center mx-auto shadow-inner ring-4 ring-amber-100">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>

            <!-- Header Content -->
            <div>
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Regenerate QR Token?</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed font-medium">
                    This will immediately revoke the current QR badge for <strong class="text-[var(--text-heading)]">{{ $user->name }}</strong>. Any previously saved or printed copies will no longer unlock the terminal.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-1">
                <button type="button" @click="showRegenModal = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                    Cancel
                </button>

                <form method="POST" action="{{ route('users.regenerate-qr', $user) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] active:scale-[0.98] transition-all shadow-lg shadow-purple-600/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate text-xs"></i> Yes, Regenerate
                    </button>
                </form>
            </div>
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

