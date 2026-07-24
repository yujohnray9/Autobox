@extends('layouts.app')

@section('title', 'User QR Code')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center space-y-6">
        
        <div>
            <span class="px-3 py-1 rounded-full bg-violet-50 text-violet-700 text-xs font-bold uppercase tracking-wider">AUTOBOX QR Access Badge</span>
            <h2 class="text-2xl font-heading font-extrabold text-slate-900 mt-2">{{ $user->name }}</h2>
            <p class="text-xs text-slate-500 font-medium">{{ $user->role }} — {{ $user->department ?? 'CCSICT' }}</p>
            <p class="text-xs font-mono font-bold text-violet-600 mt-1">ID: {{ $user->employee_id }}</p>
        </div>

        <!-- Printable QR Code Card -->
        <div id="printableQr" class="p-6 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 inline-block shadow-inner">
            <div class="bg-white p-4 rounded-xl shadow-md inline-block border border-slate-100">
                {!! QrCode::size(200)->gradient(109, 40, 217, 37, 99, 235, 'vertical')->generate($user->qr_token) !!}
            </div>
            <p class="text-[11px] font-mono text-slate-400 mt-3 break-all max-w-[220px] mx-auto">{{ $user->qr_token }}</p>
        </div>

        <div class="space-y-3 pt-2">
            <button onclick="window.print()" class="w-full py-2.5 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200 flex items-center justify-center gap-2">
                <i class="fa-solid fa-print"></i> Print Access Badge
            </button>

            <form action="{{ route('users.regenerate-qr', $user->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Regenerate QR code? The previous QR will become invalid.');" class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-xs transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rotate"></i> Regenerate QR Token
                </button>
            </form>

            <a href="{{ route('users.index') }}" class="inline-block text-xs font-semibold text-slate-500 hover:text-slate-700">← Back to Users List</a>
        </div>

    </div>
</div>
@endsection
