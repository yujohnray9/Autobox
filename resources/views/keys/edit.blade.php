@extends('layouts.app')

@section('title', 'Edit Key Slot')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{ showDeleteModal: false }">

    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('keys.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--purple-primary)] hover:underline transition-all">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Back to Key Slot Management
        </a>
        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] uppercase tracking-wider">
            Editing Slot #{{ $key->slot_number }}
        </span>
    </div>

    <!-- Main Edit Card -->
    <div class="mockup-card p-6 md:p-8 space-y-6">

        <!-- Top Card Header with Title and Prominent Delete Modal Trigger -->
        <div class="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-[var(--border-subtle)]">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-xl font-extrabold shadow-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h2 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Edit Key Slot Details</h2>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">Modify information, slot assignment, or operational status for this key.</p>
                </div>
            </div>

            <!-- Delete Trigger (Opens Modal) -->
            <button type="button" @click="showDeleteModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-extrabold text-rose-700 bg-rose-100 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                <i class="fa-solid fa-trash-can text-xs"></i> Delete Key Slot
            </button>
        </div>

        <!-- Form Body -->
        <form method="POST" action="{{ route('keys.update', $key) }}" class="space-y-6">
            @csrf @method('PUT')

            <!-- Slot Number Input -->
            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                    Physical Slot Number
                </label>
                <div class="relative max-w-xs">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-bold">#</span>
                    <input type="number" name="slot_number" value="{{ old('slot_number', $key->slot_number) }}" required min="1"
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] pl-8 pr-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                </div>
                @error('slot_number') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            <!-- Key Identifier Name & Room Selection (Only Room 1, Room 2, Room 3) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Key Identifier Name
                    </label>
                    <input type="text" name="key_name" value="{{ old('key_name', $key->key_name) }}" required placeholder="e.g. Lab 1 Key"
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('key_name') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Assigned Room
                    </label>
                    <select name="room_name" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="Room 1" {{ old('room_name', $key->room_name) === 'Room 1' ? 'selected' : '' }}>Room 1</option>
                        <option value="Room 2" {{ old('room_name', $key->room_name) === 'Room 2' ? 'selected' : '' }}>Room 2</option>
                        <option value="Room 3" {{ old('room_name', $key->room_name) === 'Room 3' ? 'selected' : '' }}>Room 3</option>
                    </select>
                    @error('room_name') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Operational Status Selector -->
            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-2 uppercase tracking-widest">
                    Operational Status
                </label>
                @php $selectedStatus = old('status', $key->status); @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" x-data="{ status: '{{ $selectedStatus }}' }">

                    <!-- Available Option -->
                    <label class="relative flex flex-col p-3.5 rounded-xl border-2 cursor-pointer transition-all shadow-sm"
                        :class="status === 'available' ? 'border-emerald-500 bg-emerald-50' : 'border-[var(--border-subtle)] bg-[var(--app-bg)] hover:border-slate-300'">
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

                    <!-- Borrowed Option -->
                    <label class="relative flex flex-col p-3.5 rounded-xl border-2 cursor-pointer transition-all shadow-sm"
                        :class="status === 'borrowed' ? 'border-amber-500 bg-amber-50' : 'border-[var(--border-subtle)] bg-[var(--app-bg)] hover:border-slate-300'">
                        <input type="radio" name="status" value="borrowed" x-model="status" class="sr-only">
                        <div class="flex items-center justify-between">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center bg-amber-100 text-amber-600 text-xs">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <span class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center" :class="status === 'borrowed' ? 'border-amber-600 bg-amber-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="status === 'borrowed'"></span>
                            </span>
                        </div>
                        <span class="font-heading font-extrabold text-xs text-[var(--text-heading)] mt-2">Borrowed</span>
                        <span class="text-[10px] text-[var(--text-muted)] font-medium">Currently checked out</span>
                    </label>

                    <!-- Missing Option -->
                    <label class="relative flex flex-col p-3.5 rounded-xl border-2 cursor-pointer transition-all shadow-sm"
                        :class="status === 'missing' ? 'border-rose-500 bg-rose-50' : 'border-[var(--border-subtle)] bg-[var(--app-bg)] hover:border-slate-300'">
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
                <a href="{{ route('keys.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-extrabold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                    <i class="fa-solid fa-floppy-disk text-xs"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- ═══════════════════════════════════
         PREMIUM CONFIRMATION DELETE MODAL
         ═══════════════════════════════════ -->
    <div x-show="showDeleteModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showDeleteModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="showDeleteModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
             class="w-full max-w-md bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 sm:p-7 space-y-5 text-center relative overflow-hidden">

            <!-- Glowing Red Ambient Light Accent -->
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-44 h-24 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="w-16 h-16 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-2xl flex items-center justify-center mx-auto shadow-inner ring-4 ring-rose-100">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <div>
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Delete Key Slot #{{ $key->slot_number }}?</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed font-medium">
                    Are you sure you want to delete <strong class="text-[var(--text-heading)]">{{ $key->key_name }}</strong> ({{ $key->room_name }})?
                    This will permanently remove the key slot from AUTOBOX and cancel any associated borrowing schedules.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="button" @click="showDeleteModal = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                    Cancel
                </button>

                <form method="POST" action="{{ route('keys.destroy', $key) }}" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-rose-600 hover:bg-rose-700 active:scale-[0.98] transition-all shadow-lg shadow-rose-600/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i> Yes, Delete Slot
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
