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


    <!-- =========================================================
         PRINTABLE QR BADGE
         ========================================================= -->
    <div class="mockup-card p-6 md:p-8 text-center space-y-6 print-badge-card" id="printableQr">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-[var(--border-subtle)] pb-4">
            <div class="flex items-center gap-2.5 text-left">
                <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-white shadow-sm flex items-center justify-center p-0.5 flex-shrink-0">
                    <img src="{{ asset('build/assets/logo.jpg') }}" alt="CCSICT Logo" class="w-full h-full object-contain rounded-full">
                </div>

                <div>
                    <h3 class="font-heading font-extrabold text-sm text-[var(--text-heading)] leading-none">
                        AUTOBOX ACCESS BADGE
                    </h3>

                    <p class="text-[10px] text-[var(--text-muted)] font-bold tracking-wider uppercase mt-0.5">
                        CCSIСT Key Locker System
                    </p>
                </div>
            </div>

            <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-extrabold uppercase tracking-wider">
                Authorized
            </span>
        </div>


        <!-- =====================================================
             PRINT-ONLY INFORMATION
             Name + Department/Role/ID + QR Code + Authorized Schedule
             ===================================================== -->
        <div class="print-only-badge space-y-3">

            <!-- User Name & Details -->
            <div class="mb-2">
                <h2 class="font-heading font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ $user->name }}
                </h2>

                <p class="text-xs text-slate-600 font-semibold mt-1 flex items-center justify-center gap-1.5 flex-wrap">
                    @if($user->employee_id)
                        <span class="font-mono font-bold text-slate-800">ID: {{ $user->employee_id }}</span>
                        <span>&bull;</span>
                    @endif

                    <span class="capitalize font-bold text-purple-800">{{ $user->role }}</span>

                    @if($user->department)
                        <span>&bull;</span>
                        <span class="text-slate-700">{{ $user->department }}</span>
                    @endif
                </p>
            </div>

            <!-- QR Code -->
            @php
                $token = $user->qr_token ?? $user->generateQrToken();
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($token);
            @endphp

            <div class="p-3 rounded-2xl bg-white border-2 border-slate-300 inline-block shadow-sm">
                <img src="{{ $qrUrl }}"
                     alt="QR Access Code for {{ $user->name }}"
                     class="w-48 h-48 mx-auto rounded-lg">
            </div>

            <!-- Authorized Schedule & Key Access Details (PRINT VIEW) -->
            <div class="mt-3 pt-3 border-t-2 border-slate-200 text-left space-y-2">

                <div class="flex items-center justify-between pb-1.5 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid {{ $user->role === 'admin' ? 'fa-shield-halved text-purple-700' : 'fa-calendar-check text-purple-700' }} text-xs"></i>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-900">
                            {{ $user->role === 'admin' ? 'Access Authorization' : 'Assigned Key & Access Schedule' }}
                        </span>
                    </div>

                    @if($user->role !== 'admin' && $user->schedules->count() > 0)
                        <span class="text-[10px] font-bold text-slate-700 bg-slate-100 border border-slate-300 px-2 py-0.5 rounded-full">
                            {{ $user->schedules->count() }} {{ Str::plural('rule', $user->schedules->count()) }}
                        </span>
                    @endif
                </div>

                @if($user->role === 'admin')
                    <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 flex items-center gap-3 text-left">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0 text-sm font-bold">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-extrabold text-purple-900 uppercase tracking-wider">
                                Unrestricted 24/7 System Access
                            </h4>
                            <p class="text-[11px] text-purple-800 mt-0.5 font-medium">
                                Master administrator authorization for all key slots at any time.
                            </p>
                        </div>
                    </div>
                @elseif($user->schedules->count() > 0)
                       @include('users.partials.schedule-groups', ['user' => $user])
                @else
                    <div class="py-2.5 text-center text-xs text-slate-600 border border-dashed border-slate-300 rounded-xl bg-slate-50">
                        <p class="font-semibold">
                            No scheduled access rules currently assigned.
                        </p>
                    </div>
                @endif

                <div class="pt-2 border-t border-slate-200 text-center space-y-0.5">
                    <p class="text-[10px] text-slate-500 font-medium leading-relaxed">
                        Scan this QR code at the physical terminal to unlock your assigned key slot during authorized schedule hours.
                    </p>
                    <p class="text-[9px] text-slate-400 font-medium">
                        Printed on {{ now()->format('M d, Y · h:i A') }}
                    </p>
                </div>

            </div>

        </div>


        <!-- =====================================================
             SCREEN-ONLY INFORMATION
             ===================================================== -->

        <!-- User Avatar & Profile Info -->
        <div class="screen-only">
            <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-500 text-white text-2xl font-extrabold flex items-center justify-center mx-auto mb-3 shadow-md ring-4 ring-violet-500/20">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>

            <h2 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">
                {{ $user->name }}
            </h2>

            <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">
                ID:
                <span class="font-mono font-bold text-[var(--text-heading)]">
                    {{ $user->employee_id ?? 'N/A' }}
                </span>

                &middot;

                <span class="capitalize font-bold text-[var(--purple-primary)]">
                    {{ $user->role }}
                </span>

                @if($user->department)
                    &middot;
                    <span class="text-[var(--text-body)]">
                        {{ $user->department }}
                    </span>
                @endif
            </p>
        </div>


        <!-- Screen QR -->
        <div class="screen-only space-y-3">

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-inner inline-block relative group">
                <img src="{{ $qrUrl }}"
                     alt="QR Access Code for {{ $user->name }}"
                     class="w-48 h-48 mx-auto rounded-lg transition-transform group-hover:scale-105">
            </div>

            <!-- Display Token -->
            <div class="bg-[var(--app-bg)] px-3 py-2 rounded-xl max-w-sm mx-auto border border-[var(--border-subtle)]">
                <p class="text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider mb-0.5">
                    Physical Scanner Token
                </p>

                <p class="text-[11px] font-mono font-bold text-[var(--text-heading)] break-all">
                    {{ $token }}
                </p>
            </div>

        </div>


        <!-- Authorized Schedule & Key Access Details -->
        <div class="screen-only bg-white border border-[var(--border-subtle)] rounded-2xl p-4 text-left space-y-3 shadow-sm">

            <div class="flex items-center justify-between pb-2 border-b border-[var(--border-subtle)]">

                <div class="flex items-center gap-2">
                    <i class="fa-solid {{ $user->role === 'admin' ? 'fa-shield-halved' : 'fa-calendar-check' }} text-[var(--purple-primary)] text-xs"></i>

                    <span class="text-xs font-extrabold uppercase tracking-wider text-[var(--text-heading)]">
                        {{ $user->role === 'admin' ? 'Access Authorization' : 'Assigned Key & Access Schedule' }}
                    </span>
                </div>

                @if($user->role !== 'admin')
                    <span class="text-[10px] font-bold text-[var(--text-muted)]">
                        {{ $user->schedules->count() }}
                        {{ Str::plural('rule', $user->schedules->count()) }}
                    </span>
                @endif

            </div>


            @if($user->role === 'admin')

                <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-200 flex items-center gap-3 text-left">

                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-[var(--purple-primary)] flex items-center justify-center flex-shrink-0 text-base shadow-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h4 class="text-xs font-extrabold text-purple-900 uppercase tracking-wider">
                            Unrestricted 24/7 System Access
                        </h4>

                        <p class="text-[11px] text-purple-700 mt-0.5">
                            Master administrator authorization for all key slots at any time.
                        </p>
                    </div>

                </div>

            @elseif($user->schedules->count() > 0)

                   @include('users.partials.schedule-groups', ['user' => $user])

            @else

                <div class="py-2 text-center text-xs text-[var(--text-muted)]">

                    <p class="font-semibold">
                        No scheduled access rules currently assigned.
                    </p>

                    <a href="{{ route('schedules.index') }}"
                       class="text-[var(--purple-primary)] hover:underline font-bold text-[11px] mt-1 inline-block no-print">
                        + Assign Schedule Now
                    </a>

                </div>

            @endif

        </div>


        <!-- Screen Instruction -->
        <p class="screen-only text-[11px] text-[var(--text-muted)] leading-relaxed max-w-xs mx-auto font-medium">
            Scan this QR code at the physical terminal to unlock your assigned key slot during authorized schedule hours.
        </p>


        <!-- Action Buttons -->
        <div class="pt-2 border-t border-[var(--border-subtle)] space-y-3 no-print">

            <button type="button"
                    @click="showRegenModal = true"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">

                <i class="fa-solid fa-rotate text-xs"></i>
                Regenerate QR Token

            </button>

        </div>

    </div>


    <!-- =========================================================
         REGENERATE QR MODAL
         ========================================================= -->

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
             class="w-full max-w-md bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 sm:p-7 space-y-5 text-center relative overflow-hidden">

            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-44 h-24 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 text-2xl flex items-center justify-center mx-auto shadow-inner ring-4 ring-amber-100">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>

            <div>
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">
                    Regenerate QR Token?
                </h3>

                <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed font-medium">
                    This will immediately revoke the current QR badge for
                    <strong class="text-[var(--text-heading)]">{{ $user->name }}</strong>.
                    Any previously saved or printed copies will no longer unlock the terminal.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-1">

                <button type="button"
                        @click="showRegenModal = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                    Cancel
                </button>

                <form method="POST"
                      action="{{ route('users.regenerate-qr', $user) }}"
                      class="flex-1">

                    @csrf

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] active:scale-[0.98] transition-all shadow-lg shadow-purple-600/20 flex items-center justify-center gap-2">

                        <i class="fa-solid fa-rotate text-xs"></i>
                        Yes, Regenerate

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     PRINT STYLES
     ========================================================= -->

<style>

.print-only-badge {
    display: none;
}

@media print {

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide everything by default */
    body * {
        visibility: hidden !important;
    }

    /* Show only the badge */
    #printableQr,
    #printableQr * {
        visibility: visible !important;
    }

    /* Hide normal screen content */
    .screen-only,
    .no-print {
        display: none !important;
    }

    /* Show print-only content */
    .print-only-badge {
        display: block !important;
    }

    /* Remove page background */
    body {
        background: white !important;
        color: black !important;
        margin: 0 !important;
    }

    /* Badge itself */
    .print-badge-card {
        position: absolute !important;
        left: 50% !important;
        top: 20px !important;
        transform: translateX(-50%) !important;

        width: 480px !important;
        max-width: 480px !important;

        margin: 0 !important;
        padding: 24px 28px !important;

        border: 2px solid #0f172a !important;
        border-radius: 20px !important;

        box-shadow: none !important;
        background: white !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    /* Make QR clear and balanced when printed */
    .print-only-badge img {
        width: 190px !important;
        height: 190px !important;
        display: block !important;
        margin: 0 auto !important;
    }

    /* Remove unnecessary page margins */
    @page {
        margin: 10mm;
        size: auto;
    }
}

</style>

@endsection