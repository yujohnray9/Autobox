@extends('layouts.app')

@section('title', 'Assign Access Schedule & Key Slot')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <!-- Back link -->
    <a href="{{ route('schedules.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--purple-primary)] transition-colors">
        <i class="fa-solid fa-chevron-left text-[9px]"></i> Back to Schedules
    </a>

    @if(session('conflict_error'))
    <div class="flex items-start gap-3 px-5 py-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-800 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0 text-base"></i>
        <div>
            <p class="font-extrabold text-sm text-amber-900">Schedule Conflict Detected</p>
            <p class="text-xs font-medium mt-0.5 leading-relaxed text-amber-800">{{ session('conflict_error') }}</p>
        </div>
    </div>
    @endif

    <div class="mockup-card p-6 md:p-8 space-y-6">
        <div class="flex items-center justify-between pb-2 border-b border-[var(--border-subtle)]">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-extrabold shadow-sm">
                    2
                </span>
                <h2 class="font-heading font-extrabold text-sm sm:text-base uppercase tracking-wider text-[var(--text-heading)]">
                    Assign Access Schedule & Key Slot
                </h2>
            </div>
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="is_active" id="assignScheduleToggle" value="1" checked class="w-4 h-4 rounded border-slate-300 text-[var(--purple-primary)] focus:ring-[var(--purple-primary)]/30">
                <span class="text-xs font-bold text-[var(--text-body)]">Enable Key Schedule</span>
            </label>
        </div>

        <form method="POST" action="{{ route('schedules.store') }}" class="space-y-6">
            @csrf

            <!-- User Selector -->
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

            <div id="scheduleFieldsContainer" class="space-y-5 bg-[var(--app-bg)]/60 border border-[var(--border-subtle)] rounded-2xl p-5 sm:p-6">
                <!-- Key Slot Selector -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Authorized Key Slot / Room <span class="text-rose-500">*</span>
                    </label>
                    <select name="key_id" id="keySelect" required
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
                            <button type="button" onclick="selectDayPreset('weekdays')" class="px-2.5 py-1 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white font-bold transition-all">
                                Weekdays (M-F)
                            </button>
                            <button type="button" onclick="selectDayPreset('all')" class="px-2.5 py-1 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white font-bold transition-all">
                                All 7 Days
                            </button>
                            <button type="button" onclick="selectDayPreset('clear')" class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-600 hover:bg-slate-300 font-bold transition-all">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Interactive Day Pills Grid -->
                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2.5" id="daysGrid">
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
                                    onclick="toggleDaySelection('{{ $val }}')"
                                    id="day-btn-{{ $val }}"
                                    class="day-pill-btn relative flex flex-col items-center justify-center py-3.5 px-1 rounded-2xl border transition-all select-none cursor-pointer {{ $isSelected ? 'day-pill-active' : 'day-pill-inactive' }}">
                                <span class="text-xs sm:text-sm font-extrabold uppercase tracking-tight">{{ $info['short'] }}</span>
                                <span class="text-[9px] font-semibold opacity-75 capitalize hidden sm:inline mt-0.5">{{ $info['sub'] }}</span>
                                <div class="day-check-indicator w-2 h-2 rounded-full mt-1.5 transition-all {{ $isSelected ? 'bg-white shadow' : 'bg-transparent' }}"></div>
                                <input type="checkbox" name="days[]" value="{{ $val }}" id="day-input-{{ $val }}" {{ $isSelected ? 'checked' : '' }} class="sr-only">
                            </button>
                        @endforeach
                    </div>

                    <!-- Active Days Summary Indicator -->
                    <div class="mt-2.5 flex items-center justify-between text-[11px] text-[var(--text-muted)]">
                        <span id="selectedDaysSummary">Selected: <strong class="text-[var(--text-heading)] font-bold">Mon, Wed, Fri</strong> (3 days)</span>
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
                        <div class="relative">
                            <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}" required
                                class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        </div>
                        @error('start_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest flex items-center gap-1">
                            <i class="fa-regular fa-clock text-rose-500 text-xs"></i> END TIME <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="time" name="end_time" value="{{ old('end_time', '17:00') }}" required
                                class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        </div>
                        @error('end_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2 text-[11px] text-[var(--text-muted)] pt-1">
                    <i class="fa-solid fa-circle-info text-[var(--purple-primary)] text-xs"></i>
                    <span>The hardware QR scanner will authorize key unlock during this scheduled time window.</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-2 flex items-center justify-between gap-3 border-t border-[var(--border-subtle)]">
                <a href="{{ route('schedules.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow-lg hover:shadow-purple-600/30">
                    <i class="fa-solid fa-calendar-check text-xs"></i> Save Access Schedule
                </button>
            </div>
        </form>
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
    function toggleDaySelection(dayVal) {
        const input = document.getElementById('day-input-' + dayVal);
        if (!input) return;

        input.checked = !input.checked;
        updateDayPillVisual(dayVal);
        updateDaysSummary();
    }

    function updateDayPillVisual(dayVal) {
        const btn = document.getElementById('day-btn-' + dayVal);
        const input = document.getElementById('day-input-' + dayVal);
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

    function selectDayPreset(type) {
        const allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        allDays.forEach(day => {
            const input = document.getElementById('day-input-' + day);
            if (!input) return;

            if (type === 'all') {
                input.checked = true;
            } else if (type === 'weekdays') {
                input.checked = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].includes(day);
            } else if (type === 'clear') {
                input.checked = false;
            }
            updateDayPillVisual(day);
        });
        updateDaysSummary();
    }

    function updateDaysSummary() {
        const allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const names = { monday: 'Mon', tuesday: 'Tue', wednesday: 'Wed', thursday: 'Thu', friday: 'Fri', saturday: 'Sat', sunday: 'Sun' };
        const checked = allDays.filter(d => {
            const input = document.getElementById('day-input-' + d);
            return input && input.checked;
        }).map(d => names[d]);

        const summary = document.getElementById('selectedDaysSummary');
        if (summary) {
            if (checked.length > 0) {
                summary.innerHTML = 'Selected: <strong class="text-[var(--text-heading)] font-bold">' + checked.join(', ') + '</strong> (' + checked.length + ' ' + (checked.length === 1 ? 'day' : 'days') + ')';
            } else {
                summary.innerHTML = '<span class="text-amber-500 font-bold"><i class="fa-solid fa-triangle-exclamation"></i> No days selected</span>';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('assignScheduleToggle');
        const container = document.getElementById('scheduleFieldsContainer');

        if (toggle && container) {
            toggle.addEventListener('change', function () {
                if (toggle.checked) {
                    container.classList.remove('opacity-40', 'pointer-events-none');
                } else {
                    container.classList.add('opacity-40', 'pointer-events-none');
                }
            });
        }

        updateDaysSummary();
    });
</script>
@endsection
