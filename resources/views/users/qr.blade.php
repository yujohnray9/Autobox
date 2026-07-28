@extends('layouts.app')

@section('title', 'User QR Code')

@section('content')
<div class="max-w-md mx-auto space-y-6">
    <!-- Back Link -->
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--purple-primary)] hover:underline transition-all">
        <i class="fa-solid fa-arrow-left text-[11px]"></i> Back to Users Management
    </a>

    <!-- Printable QR Badge Card -->
    <div class="mockup-card p-8 text-center space-y-6" id="printableQr">

        <!-- Header Badge Title -->
        <div class="inline-block px-3 py-1 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] text-[10px] font-extrabold uppercase tracking-widest">
            AUTOBOX Access Badge
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
            </p>
        </div>

        <!-- Dynamic Real QR Code Image -->
        @php
            $token = $user->qr_token ?? $user->generateQrToken();
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($token);
        @endphp

        <div class="space-y-3">
            <div class="p-4 rounded-2xl bg-white border border-slate-200 dark:border-purple-900/40 shadow-inner inline-block relative group">
                <img src="{{ $qrUrl }}"
                     alt="QR Access Code for {{ $user->name }}"
                     class="w-48 h-48 mx-auto rounded-lg transition-transform group-hover:scale-105">
            </div>

            <!-- Display Token -->
            <div class="bg-[var(--app-bg)] px-3 py-2 rounded-xl max-w-xs mx-auto border border-[var(--border-subtle)]">
                <p class="text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider mb-0.5">Token Data</p>
                <p class="text-[11px] font-mono font-bold text-[var(--text-heading)] break-all">{{ $token }}</p>
            </div>
        </div>

        <p class="text-[11px] text-[var(--text-muted)] leading-relaxed max-w-xs mx-auto font-medium">
            Present this QR code to the AUTOBOX physical scanner terminal to authenticate and unlock key slots.
        </p>

        <!-- Action Buttons -->
        <div class="pt-2 border-t border-[var(--border-subtle)]">
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
@endsection
