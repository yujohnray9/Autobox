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
    <div class="flex items-start gap-3 px-5 py-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-800 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="font-extrabold text-sm">Schedule Conflict Detected</p>
            <p class="text-xs font-medium mt-0.5 leading-relaxed">{{ session('conflict_error') }}</p>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 shadow-sm">
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
        <div class="mockup-card p-6 sm:p-7 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--border-subtle)]">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-extrabold shadow-sm">
                        2
                    </span>
                    <h3 class="font-heading font-extrabold text-sm sm:text-base uppercase tracking-wider text-[var(--text-heading)]">
                        Assign Access Schedule & Key Slot
                    </h3>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" id="assignScheduleToggleIndex" value="1" checked class="w-4 h-4 rounded border-slate-300 text-[var(--purple-primary)] focus:ring-[var(--purple-primary)]/30">
                    <span class="text-xs font-bold text-[var(--text-body)]">Enable Key Schedule</span>
                </label>
            </div>

            <form method="POST" action="{{ route('schedules.store') }}" class="space-y-5">
                @csrf

                <!-- User Selection -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Authorized User (Faculty / Staff) <span class="text-rose-500">*</span>
                    </label>
                    <select name="user_id" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->employee_id ?? 'No ID' }}) &mdash; {{ ucfirst($user->role) }}
                                @if($user->department) &bull; {{ $user->department }} @endif
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="scheduleFieldsContainerIndex" class="space-y-4 bg-[var(--app-bg)]/60 border border-[var(--border-subtle)] rounded-2xl p-5">
                    <!-- Key Slot Selector -->
                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                            Authorized Key Slot / Room <span class="text-rose-500">*</span>
                        </label>
                        <select name="key_id" required
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                            <option value="">-- Select Key Slot --</option>
                            @foreach($keys as $key)
                                <option value="{{ $key->id }}" {{ old('key_id') == $key->id ? 'selected' : '' }}>
                                    Slot #{{ $key->slot_number }} — {{ $key->key_name }} ({{ $key->room_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('key_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Days of Week Selector (Multi-Day Select) -->
                    <div>
                        <div class="flex items-center justify-between mb-2.5 flex-wrap gap-2">
                            <label class="block text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest">
                                Authorized Days of Week <span class="text-rose-500">*</span>
                            </label>
                            <!-- Quick Preset Badges -->
                            <div class="flex items-center gap-1.5 text-[10px]">
                                <button type="button" onclick="selectDayPresetIndex('weekdays')" class="px-2.5 py-1 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white font-bold transition-all">
                                    Weekdays (M-F)
                                </button>
                                <button type="button" onclick="selectDayPresetIndex('all')" class="px-2.5 py-1 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white font-bold transition-all">
                                    All 7 Days
                                </button>
                                <button type="button" onclick="selectDayPresetIndex('clear')" class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-600 hover:bg-slate-300 font-bold transition-all">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Interactive Day Pills Grid -->
                        <div class="grid grid-cols-7 gap-1.5 sm:gap-2.5">
                            @php
                                $daysConfig = [
                                    'monday'    => ['short' => 'MON', 'sub' => 'Mon'],
                                    'tuesday'   => ['short' => 'TUE', 'sub' => 'Tue'],
                                    'wednesday' => ['short' => 'WED', 'sub' => 'Wed'],
                                    'thursday'  => ['short' => 'THU', 'sub' => 'Thu'],
                                    'friday'    => ['short' => 'FRI', 'sub' => 'Fri'],
                                    'saturday'  => ['short' => 'SAT', 'sub' => 'Sat'],
                                    'sunday'    => ['short' => 'SUN', 'sub' => 'Sun'],
                                ];
                                $selectedDays = (array) old('days', ['monday', 'wednesday', 'friday']);
                            @endphp

                            @foreach($daysConfig as $val => $info)
                                @php $isSelected = in_array($val, $selectedDays); @endphp
                                <button type="button"
                                        onclick="toggleDaySelectionIndex('{{ $val }}')"
                                        id="index-day-btn-{{ $val }}"
                                        class="day-pill-btn relative flex flex-col items-center justify-center py-3.5 px-1 rounded-2xl border transition-all select-none cursor-pointer {{ $isSelected ? 'day-pill-active' : 'day-pill-inactive' }}">
                                    <span class="text-xs sm:text-sm font-extrabold uppercase tracking-tight">{{ $info['short'] }}</span>
                                    <span class="text-[9px] font-semibold opacity-75 capitalize hidden sm:inline mt-0.5">{{ $info['sub'] }}</span>
                                    <div class="day-check-indicator w-2 h-2 rounded-full mt-1.5 transition-all {{ $isSelected ? 'bg-white shadow' : 'bg-transparent' }}"></div>
                                    <input type="checkbox" name="days[]" value="{{ $val }}" id="index-day-input-{{ $val }}" {{ $isSelected ? 'checked' : '' }} class="sr-only">
                                </button>
                            @endforeach
                        </div>

                        <!-- Active Days Summary Indicator -->
                        <div class="mt-2.5 flex items-center justify-between text-[11px] text-[var(--text-muted)]">
                            <span id="indexSelectedDaysSummary">Selected: <strong class="text-[var(--text-heading)] font-bold">Mon, Wed, Fri</strong> (3 days)</span>
                            <span class="text-[10px] opacity-70 italic">Click day to toggle</span>
                        </div>
                        @error('days') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Time Window -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest flex items-center gap-1">
                                <i class="fa-regular fa-clock text-emerald-500 text-xs"></i> START TIME <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}" required
                                class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                            @error('start_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest flex items-center gap-1">
                                <i class="fa-regular fa-clock text-rose-500 text-xs"></i> END TIME <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="end_time" value="{{ old('end_time', '17:00') }}" required
                                class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                            @error('end_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-[var(--text-muted)] pt-1">
                        <i class="fa-solid fa-circle-info text-[var(--purple-primary)] text-xs"></i>
                        <span>The hardware QR scanner will authorize key unlock during this scheduled time window.</span>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between gap-3 pt-2 border-t border-[var(--border-subtle)]">
                    <button type="button" @click="showForm = false"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow-lg hover:shadow-purple-600/30">
                        <i class="fa-solid fa-calendar-check text-xs"></i> Save Access Schedule
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
                                <span class="text-emerald-600 font-bold">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                </span>
                                <span class="text-[var(--text-muted)] mx-1">—</span>
                                <span class="text-rose-600 font-bold">
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </span>
                            </td>
                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                @if($schedule->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600">
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
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
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

            <!-- Danger Warning Icon -->
            <div class="w-16 h-16 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-2xl flex items-center justify-center mx-auto shadow-inner ring-4 ring-rose-100">
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
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-user text-[var(--purple-primary)] text-xs"></i>
                        <span class="font-bold text-sm text-[var(--text-heading)]" x-text="deleteUserName"></span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-purple-100 text-purple-700" x-text="deleteUserRole"></span>
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
                    <span class="font-mono font-bold text-rose-600 text-[11px]" x-text="deleteDayTime"></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-1">
                <button type="button" @click="deleteModalOpen = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                    Cancel
                </button>

                <form :action="'{{ url('/schedules') }}/' + deleteScheduleId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-rose-600 hover:bg-rose-700 active:scale-[0.98] transition-all shadow-lg shadow-rose-600/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i> Yes, Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.day-pill-btn {
    min-height: 62px;
}
.day-pill-active {
    background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
    border-color: #a78bfa !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px 0 rgba(124, 58, 237, 0.3) !important;
    transform: translateY(-1px);
}
.day-pill-inactive {
    background: #ffffff !important;
    border-color: #e6e5f0 !important;
    color: #847d9c !important;
}
.day-pill-inactive:hover {
    border-color: #6451a3 !important;
    color: #6451a3 !important;
    background: #f2eefb !important;
}
</style>

<script>
    function toggleDaySelectionIndex(dayVal) {
        const input = document.getElementById('index-day-input-' + dayVal);
        if (!input) return;

        input.checked = !input.checked;
        updateDayPillVisualIndex(dayVal);
        updateDaysSummaryIndex();
    }

    function updateDayPillVisualIndex(dayVal) {
        const btn = document.getElementById('index-day-btn-' + dayVal);
        const input = document.getElementById('index-day-input-' + dayVal);
        const dot = btn ? btn.querySelector('.day-check-indicator') : null;
        if (!btn || !input) return;

        if (input.checked) {
            btn.classList.remove('day-pill-inactive');
            btn.classList.add('day-pill-active');
            if (dot) {
                dot.classList.remove('bg-transparent');
                dot.classList.add('bg-white', 'shadow');
            }
        } else {
            btn.classList.remove('day-pill-active');
            btn.classList.add('day-pill-inactive');
            if (dot) {
                dot.classList.remove('bg-white', 'shadow');
                dot.classList.add('bg-transparent');
            }
        }
    }

    function selectDayPresetIndex(type) {
        const allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        allDays.forEach(day => {
            const input = document.getElementById('index-day-input-' + day);
            if (!input) return;

            if (type === 'all') {
                input.checked = true;
            } else if (type === 'weekdays') {
                input.checked = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].includes(day);
            } else if (type === 'clear') {
                input.checked = false;
            }
            updateDayPillVisualIndex(day);
        });
        updateDaysSummaryIndex();
    }

    function updateDaysSummaryIndex() {
        const allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const names = { monday: 'Mon', tuesday: 'Tue', wednesday: 'Wed', thursday: 'Thu', friday: 'Fri', saturday: 'Sat', sunday: 'Sun' };
        const checked = allDays.filter(d => {
            const input = document.getElementById('index-day-input-' + d);
            return input && input.checked;
        }).map(d => names[d]);

        const summary = document.getElementById('indexSelectedDaysSummary');
        if (summary) {
            if (checked.length > 0) {
                summary.innerHTML = 'Selected: <strong class="text-[var(--text-heading)] font-bold">' + checked.join(', ') + '</strong> (' + checked.length + ' ' + (checked.length === 1 ? 'day' : 'days') + ')';
            } else {
                summary.innerHTML = '<span class="text-amber-500 font-bold"><i class="fa-solid fa-triangle-exclamation"></i> No days selected</span>';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('assignScheduleToggleIndex');
        const container = document.getElementById('scheduleFieldsContainerIndex');

        if (toggle && container) {
            toggle.addEventListener('change', function () {
                if (toggle.checked) {
                    container.classList.remove('opacity-40', 'pointer-events-none');
                } else {
                    container.classList.add('opacity-40', 'pointer-events-none');
                }
            });
        }

        updateDaysSummaryIndex();
    });
</script>
@endsection
