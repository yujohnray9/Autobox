@extends('layouts.app')

@section('title', 'Key Slots Management')

@section('content')
<div class="space-y-6" x-data="{
    deleteModalOpen: false, targetKeyId: null, targetKeyName: '', targetSlotNum: '',
    borrowModalOpen: false, borrowKeyId: null, borrowKeyName: '', borrowSlotNum: ''
}">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-key text-[(--purple-primary)] text-lg"></i>
                Key Slot Management
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Manage physical key slots, assign manual borrows, or restore returned keys.</p>
        </div>
         <a href="{{ route('keys.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
            <i class="fa-solid fa-plus text-xs"></i> Add New Key Slot
        </a>
    </div>

    <!-- Key Slots Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($keys as $key)
            @php
                $borrower = $key->currentBorrower();
                $badgeClass = match($key->status) { 'available' => 'slot-badge-available', 'borrowed' => 'slot-badge-borrowed', default => 'slot-badge-missing' };
            @endphp

            <div class="redesigned-slot-card flex flex-col justify-between">
                <div>
                    <!-- Top Bar: Slot Badge + Top Action Buttons (Edit & Delete Modal Trigger) -->
                    <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-[var(--border-subtle)]">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $badgeClass }}">
                            Slot #{{ $key->slot_number }}
                        </span>

                        <!-- Top Right Edit & Delete Modal Trigger -->
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('keys.edit', $key) }}"
                               title="Edit Key Slot"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all shadow-sm">
                                <i class="fa-solid fa-pen-to-square text-[9px]"></i> Edit
                            </a>
                            <button type="button"
                                    @click="deleteModalOpen = true; targetKeyId = '{{ $key->id }}'; targetKeyName = '{{ addslashes($key->key_name) }}'; targetSlotNum = '{{ $key->slot_number }}'"
                                    title="Delete Key Slot"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-rose-100 text-rose-700 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                <i class="fa-solid fa-trash-can text-[9px]"></i> Delete
                            </button>
                        </div>
                    </div>

                    <!-- Key Details -->
                    <div class="mt-3">
                        <h3 class="font-heading font-extrabold text-sm text-[var(--text-heading)] truncate" title="{{ $key->key_name }}">{{ $key->key_name }}</h3>
                        <p class="text-xs font-semibold text-[var(--purple-primary)] flex items-center gap-1 mt-0.5">
                            <i class="fa-solid fa-door-open text-[10px]"></i> {{ $key->room_name }}
                        </p>
                    </div>
                </div>

                <!-- Clean Status Indicator Footer with Borrow & Return Quick Actions -->
                <div class="mt-3 pt-2.5 border-t border-[var(--border-subtle)] text-xs flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        @if($key->status === 'borrowed' && $borrower)
                            <p class="text-[11px] font-bold text-[var(--text-heading)] truncate max-w-[110px]" title="{{ $borrower->user->name ?? 'User' }}">{{ $borrower->user->name ?? 'User' }}</p>
                            <p class="text-[9px] text-[var(--text-muted)]"><i class="fa-regular fa-clock"></i> {{ $borrower->created_at->diffForHumans() }}</p>
                        @elseif($key->status === 'available')
                            <span class="text-emerald-600 font-bold text-[11px] flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Ready in Lock Box
                            </span>
                        @else
                            <span class="text-rose-600 font-bold text-[11px] flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span> Flagged Missing
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons: Borrow / Return / Mark Available -->
                    <div class="flex-shrink-0">
                        @if($key->status === 'available')
                            <!-- Borrow Action Button -->
                            <button type="button"
                                    @click="borrowModalOpen = true; borrowKeyId = '{{ $key->id }}'; borrowKeyName = '{{ addslashes($key->key_name) }}'; borrowSlotNum = '{{ $key->slot_number }}'"
                                    title="Borrow this key slot"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center gap-1">
                                <i class="fa-solid fa-key text-[9px]"></i> Borrow
                            </button>
                        @elseif($key->status === 'borrowed')
                            <!-- Return Action Button -->
                            <form method="POST" action="{{ route('keys.update-status', $key) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="available">
                                <button type="submit" title="Return key to lock box" class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-100 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-rotate-left text-[9px]"></i> Return Key
                                </button>
                            </form>
                        @else
                            <!-- Restore Action for Missing Keys -->
                            <form method="POST" action="{{ route('keys.update-status', $key) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="available">
                                <button type="submit" title="Mark key as found and available" class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-100 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Mark Found
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full mockup-card text-center py-16 text-[var(--text-muted)]">
                <i class="fa-solid fa-key text-5xl mb-4 block opacity-20"></i>
                <p class="font-heading font-bold text-base">No key slots configured yet.</p>
                <p class="text-sm mt-1">Add your first key slot to get started.</p>
            </div>
        @endforelse
    </div>

    <!-- ═══════════════════════════════════
         PREMIUM MANUAL BORROW MODAL
         ═══════════════════════════════════ -->
    <div x-show="borrowModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="borrowModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden p-6 sm:p-7 space-y-5 text-left z-50">

            <div class="flex items-center gap-3 pb-3 border-b border-[var(--border-subtle)]">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 text-xl flex items-center justify-center font-extrabold shadow-inner">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-lg text-slate-900">Borrow Key Slot #<span x-text="borrowSlotNum"></span></h3>
                    <p class="text-xs text-slate-500" x-text="borrowKeyName"></p>
                </div>
            </div>

            <form :action="'{{ url('/keys') }}/' + borrowKeyId + '/status'" method="POST" class="space-y-4">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="borrowed">

                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Assign Borrower (Faculty / Staff)
                    </label>
                    <select name="user_id" required class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="">-- Select Borrower --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->employee_id ?? 'No ID' }}) &mdash; {{ ucfirst($u->role) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-[var(--border-subtle)]">
                    <button type="button" @click="borrowModalOpen = false"
                            class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>

                    <button type="submit"
                            class="flex-1 py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-key text-xs"></i> Confirm Borrow
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══════════════════════════════════
         PREMIUM CONFIRMATION DELETE MODAL
         ═══════════════════════════════════ -->
    <div x-show="deleteModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="deleteModalOpen = false"
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
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Delete Key Slot #<span x-text="targetSlotNum"></span>?</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed font-medium">
                    Are you sure you want to delete <strong class="text-[var(--text-heading)]" x-text="targetKeyName"></strong>?
                    This action will permanently remove the physical slot configuration and all related access schedules.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="button" @click="deleteModalOpen = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                    Cancel
                </button>

                <form :action="'{{ url('/keys') }}/' + targetKeyId" method="POST" class="flex-1">
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
