@extends('layouts.app')

@section('title', 'Add Key Slot')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('keys.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--purple-primary)] hover:underline transition-all">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Back to Key Slot Management
        </a>
        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 uppercase tracking-wider">
            + New Slot
        </span>
    </div>

    <!-- Main Create Card -->
    <div class="mockup-card p-6 md:p-8 space-y-6">

        <!-- Top Card Header -->
        <div class="flex items-center gap-3 pb-5 border-b border-[var(--border-subtle)]">
            <div class="w-12 h-12 rounded-2xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-xl font-extrabold shadow-sm">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <h2 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Register New Physical Key Slot</h2>
                <p class="text-xs text-[var(--text-muted)] mt-0.5">Assign a hardware slot number, key identifier, and room location to the AUTOBOX system.</p>
            </div>
        </div>

        <!-- Form Body -->
        <form method="POST" action="{{ route('keys.store') }}" class="space-y-6">
            @csrf

            <!-- Physical Slot Number -->
            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                    Physical Slot Number
                </label>
                <div class="relative max-w-xs">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-bold">#</span>
                    <input type="number" name="slot_number" value="{{ old('slot_number') }}" required min="1" placeholder="e.g. 01"
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] pl-8 pr-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                </div>
                @error('slot_number') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            <!-- Key Identifier Name & Room Name -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Key Identifier Name
                    </label>
                    <input type="text" name="key_name" value="{{ old('key_name') }}" required placeholder="e.g. Lab 301 Key"
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('key_name') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Assigned Room / Facility Name
                    </label>
                    <input type="text" name="room_name" value="{{ old('room_name') }}" required placeholder="e.g. Computer Laboratory 301"
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('room_name') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Initial Operational Status Selector (NO EMOJIS) -->
            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-2 uppercase tracking-widest">
                    Initial Operational Status
                </label>
                @php $initStatus = old('status', 'available'); @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ status: '{{ $initStatus }}' }">

                    <!-- Available Option -->
                    <label class="relative flex flex-col p-3.5 rounded-xl border-2 cursor-pointer transition-all shadow-sm"
                        :class="status === 'available' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-[var(--border-subtle)] bg-[var(--app-bg)] hover:border-slate-300'">
                        <input type="radio" name="status" value="available" x-model="status" class="sr-only">
                        <div class="flex items-center justify-between">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center bg-emerald-100 text-emerald-600 text-xs">
                                <i class="fa-solid fa-lock-open"></i>
                            </span>
                            <span class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center" :class="status === 'available' ? 'border-emerald-600 bg-emerald-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="status === 'available'"></span>
                            </span>
                        </div>
                        <span class="font-heading font-extrabold text-xs text-[var(--text-heading)] mt-2">Available</span>
                        <span class="text-[10px] text-[var(--text-muted)] font-medium">Ready in Lock Box</span>
                    </label>

                    <!-- Missing Option -->
                    <label class="relative flex flex-col p-3.5 rounded-xl border-2 cursor-pointer transition-all shadow-sm"
                        :class="status === 'missing' ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-950/30' : 'border-[var(--border-subtle)] bg-[var(--app-bg)] hover:border-slate-300'">
                        <input type="radio" name="status" value="missing" x-model="status" class="sr-only">
                        <div class="flex items-center justify-between">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center bg-rose-100 text-rose-600 text-xs">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </span>
                            <span class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center" :class="status === 'missing' ? 'border-rose-600 bg-rose-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="status === 'missing'"></span>
                            </span>
                        </div>
                        <span class="font-heading font-extrabold text-xs text-[var(--text-heading)] mt-2">Flagged Missing</span>
                        <span class="text-[10px] text-[var(--text-muted)] font-medium">System Alert Active</span>
                    </label>
                </div>
                @error('status') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            <!-- Form Actions -->
            <div class="pt-4 flex items-center justify-between border-t border-[var(--border-subtle)]">
                <a href="{{ route('keys.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-extrabold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                    <i class="fa-solid fa-floppy-disk text-xs"></i> Save Key Slot
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
