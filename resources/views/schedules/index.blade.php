@extends('layouts.app')

@section('title', 'Access Schedules')

@section('content')
<div class="space-y-6" x-data="{ 
    showForm: {{ session('conflict_error') ? 'true' : 'false' }}, 
    deleteModalOpen: false, 
    deleteScheduleId: null, 
    deleteUserName: '', 
    deleteUserRole: '', 
    deleteKeyName: '', 
    deleteSlotNum: '', 
    deleteDayTime: '' 
}">

    @if(session('conflict_error'))
    <div class="flex items-start gap-3 px-5 py-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-800 dark:bg-amber-950/40 dark:border-amber-700/50 dark:text-amber-300 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="font-extrabold text-sm">Schedule Conflict Detected</p>
            <p class="text-xs font-medium mt-0.5 leading-relaxed">{{ session('conflict_error') }}</p>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-700/50 dark:text-emerald-300 shadow-sm">
        <i class="fa-solid fa-circle-check text-emerald-500 flex-shrink-0"></i>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-[var(--purple-primary)] text-lg"></i>
                Access Schedules
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Define time-based windows where faculty/staff may borrow keys.</p>
        </div>
        <button type="button" @click="showForm = !showForm"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
            <i class="fa-solid fa-plus text-xs"></i> Add Schedule
        </button>
    </div>

    <!-- Add Schedule Form (Animated) -->
    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-3"
         style="display: none;">
        <div class="mockup-card">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[var(--border-subtle)]">
                <div class="w-9 h-9 rounded-xl bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center">
                    <i class="fa-solid fa-calendar-plus text-sm"></i>
                </div>
                <div>
                    <h3 class="mockup-card-title text-base">New Access Schedule</h3>
                    <p class="text-[11px] text-[var(--text-muted)]">Assign a borrowing window for a user and key slot</p>
                </div>
            </div>

            <form method="POST" action="{{ route('schedules.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf

                <!-- User Selection -->
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        User (Faculty / Staff) <span class="text-rose-500">*</span>
                    </label>
                    <select name="user_id" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="">— Select Authorized User —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->employee_id ?? 'No ID' }}) &mdash; {{ ucfirst($user->role) }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Key Slot Selection -->
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Key Slot / Room <span class="text-rose-500">*</span>
                    </label>
                    <select name="key_id" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="">— Select Key Slot —</option>
                        @foreach($keys as $key)
                            <option value="{{ $key->id }}" {{ old('key_id') == $key->id ? 'selected' : '' }}>
                                Slot #{{ $key->slot_number }} &mdash; {{ $key->key_name }} ({{ $key->room_name }})
                            </option>
                        @endforeach
                    </select>
                    @error('key_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Day of Week -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Day of the Week <span class="text-rose-500">*</span>
                    </label>
                    <select name="day_of_week" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <option value="{{ $day }}" {{ old('day_of_week') == $day ? 'selected' : '' }}>
                                {{ ucfirst($day) }}
                            </option>
                        @endforeach
                    </select>
                    @error('day_of_week') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3 pt-5">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                        class="w-4 h-4 rounded text-[var(--purple-primary)] border-[var(--border-subtle)] focus:ring-[var(--purple-primary)]">
                    <label for="is_active" class="text-xs font-bold text-[var(--text-heading)] cursor-pointer">
                        Enable this schedule rule immediately
                    </label>
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Start Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('start_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- End Time -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        End Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="end_time" value="{{ old('end_time', '17:00') }}" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('end_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="sm:col-span-2 flex items-center justify-end gap-3 pt-3 border-t border-[var(--border-subtle)]">
                    <button type="button" @click="showForm = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                        <i class="fa-solid fa-floppy-disk text-xs"></i> Save Access Rule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="mockup-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-[var(--border-subtle)]">
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Authorized User</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Key Slot / Room</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Day</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Time Window</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                    @forelse($schedules as $schedule)
                        <tr class="hover:bg-[var(--purple-soft)] transition-colors">
                            <!-- User -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($schedule->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[var(--text-heading)] text-xs leading-tight">{{ $schedule->user->name ?? 'Unknown User' }}</p>
                                        <p class="text-[10px] text-[var(--text-muted)]">{{ $schedule->user->employee_id ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Key Slot -->
                            <td class="px-5 py-3.5">
                                <div>
                                    <p class="font-bold text-[var(--text-heading)] text-xs">{{ $schedule->key->key_name ?? 'Unknown Key' }}</p>
                                    <p class="text-[10px] text-[var(--text-muted)]">
                                        <i class="fa-solid fa-door-open text-[9px]"></i>
                                        Slot #{{ $schedule->key->slot_number ?? '?' }} &bull; {{ $schedule->key->room_name ?? '' }}
                                    </p>
                                </div>
                            </td>
                            <!-- Day -->
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-[var(--purple-soft)] text-[var(--purple-primary)]">
                                    {{ ucfirst($schedule->day_of_week) }}
                                </span>
                            </td>
                            <!-- Time Window -->
                            <td class="px-5 py-3.5 font-mono text-sm font-semibold text-[var(--text-body)]">
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                </span>
                                <span class="text-[var(--text-muted)] mx-1">—</span>
                                <span class="text-rose-500 dark:text-rose-400 font-bold">
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </span>
                            </td>
                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                @if($schedule->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-[var(--border-subtle)] text-[var(--text-muted)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right">
                                <button type="button"
                                    @click="deleteModalOpen = true; 
                                            deleteScheduleId = '{{ $schedule->id }}'; 
                                            deleteUserName = '{{ addslashes($schedule->user->name ?? 'Unknown User') }}'; 
                                            deleteUserRole = '{{ ucfirst($schedule->user->role ?? 'User') }}';
                                            deleteKeyName = '{{ addslashes($schedule->key->key_name ?? 'Key') }}'; 
                                            deleteSlotNum = '{{ $schedule->key->slot_number ?? '?' }}'; 
                                            deleteDayTime = '{{ ucfirst($schedule->day_of_week) }} · {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-[var(--border-subtle)] text-[var(--text-body)] hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i> Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-[var(--text-muted)] text-sm">
                                <i class="fa-solid fa-calendar-xmark text-5xl block mb-4 opacity-20"></i>
                                <p class="font-heading font-bold text-base">No access schedules yet.</p>
                                <p class="text-xs mt-1">Click <strong>Add Schedule</strong> to define a time-based borrowing rule.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
            <div class="px-5 py-4 border-t border-[var(--border-subtle)]">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>

    <!-- ═══════════════════════════════════
         PREMIUM CONFIRMATION REMOVE SCHEDULE MODAL
         ═══════════════════════════════════ -->
    <div x-show="deleteModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md"
         style="display: none;">

        <div @click.away="deleteModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
             class="w-full max-w-md bg-[var(--surface-white)] border border-[var(--border-subtle)] rounded-3xl shadow-2xl p-6 sm:p-7 space-y-5 text-center relative overflow-hidden">
            
            <!-- Glowing Red Ambient Light Accent -->
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-44 h-24 bg-rose-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Danger Warning Icon -->
            <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-2xl flex items-center justify-center mx-auto shadow-inner ring-4 ring-rose-500/10">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>

            <!-- Header Content -->
            <div>
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Remove Access Schedule?</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed font-medium">
                    This user will no longer be authorized to borrow this key during this specified time window.
                </p>
            </div>

            <!-- Schedule Detail Preview Card -->
            <div class="p-4 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] text-left space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-user text-[var(--purple-primary)] text-xs"></i>
                        <span class="font-bold text-sm text-[var(--text-heading)]" x-text="deleteUserName"></span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-purple-500/15 text-[var(--purple-primary)]" x-text="deleteUserRole"></span>
                </div>

                <div class="pt-2 border-t border-[var(--border-subtle)] flex items-center justify-between text-xs">
                    <span class="text-[var(--text-muted)] font-medium">Key Slot:</span>
                    <span class="font-bold text-[var(--text-heading)] flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded bg-[var(--purple-soft)] text-[var(--purple-primary)] font-mono text-[10px]">#<span x-text="deleteSlotNum"></span></span>
                        <span x-text="deleteKeyName"></span>
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="text-[var(--text-muted)] font-medium">Window:</span>
                    <span class="font-mono font-bold text-rose-400 text-[11px]" x-text="deleteDayTime"></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-1">
                <button type="button" @click="deleteModalOpen = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700/50 hover:text-white transition-all">
                    Cancel
                </button>

                <form :action="'{{ url('/schedules') }}/' + deleteScheduleId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-rose-600 hover:bg-rose-500 active:scale-[0.98] transition-all shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i> Yes, Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
