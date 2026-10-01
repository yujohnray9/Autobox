@extends('layouts.app')

@section('title', 'Access Schedules')

@php
    // Map of day keys (lowercase) to full display names for Monday–Sunday
    $days = [
        'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
        'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
    ];
    // Keep previously selected days if validation fails and the form reloads
    $oldDays    = array_values(array_intersect((array) old('days', []), array_keys($days)));
    $oldDaysJs  = $oldDays ? "['" . implode("','", $oldDays) . "']" : '[]';
@endphp

@section('content')
{{-- For managing active day tab, room selection, form visibility, and modal data --}}
<div class="space-y-6" x-data="{
    showForm: {{ session('conflict_error') ? 'true' : 'false' }},
    // Determines today's day of week in lowercase ('sunday', 'monday', etc.)
    todayKey: ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'][new Date().getDay()],
    // Holds the currently clicked/active day tab (defaults to monday, then switches to today in init())
    activeDay: 'monday',
    // Holds the currently clicked/active room card (defaults to the first room key)
    activeRoom: {{ $keys->first()->id ?? 1 }},
    formKeyId: '{{ old('key_id', '') }}',
    formDays: {!! $oldDaysJs !!},
    init() { 
        this.activeDay = this.todayKey; // Automatically select today's tab on page load
        if (!this.formDays.length) { this.formDays = [this.todayKey]; } 
    },
    toggleDay(d) { this.formDays = this.formDays.includes(d) ? this.formDays.filter(x => x !== d) : [...this.formDays, d]; },
    openFormFor(d, kId = null) { 
        this.formDays = [d]; 
        if (kId) { this.formKeyId = kId; }
        this.showForm = true; 
        window.scrollTo({ top: 0, behavior: 'smooth' }); 
    },
    deleteModalOpen: false, deleteScheduleId: null, deleteUserName: '', deleteUserRole: '',
    deleteKeyName: '', deleteSlotNum: '', deleteDayTime: ''
}">

    {{-- Displays error or success alerts passed from the Controller via session() --}}
    {{-- 1. Schedule Conflict Error Alert: Shown when a user already has a schedule or another user is booked in the same slot --}}
    @if(session('conflict_error'))
    <div class="flex items-start gap-3 px-5 py-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-800 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
        <div>
            <p class="font-extrabold text-sm">Schedule Conflict Detected</p>
            <p class="text-xs font-medium mt-0.5 leading-relaxed">{{ session('conflict_error') }}</p>
        </div>
    </div>
    @endif

    {{-- 2. Success Alert: Shown after successfully creating or removing a schedule --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 shadow-sm">
        <i class="fa-solid fa-circle-check text-emerald-500"></i>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Header Section: Title, subtitle, and the "Add Schedule" form toggle button -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-[var(--purple-primary)] text-lg"></i> Access Schedules
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Pick a day to see who borrows which key, and when.</p>
        </div>
        {{-- Toggles the slide-down schedule creation form --}}
        <button type="button" @click="if (!showForm) { formDays = [activeDay]; } showForm = !showForm"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
            <i class="fa-solid fa-plus text-xs"></i> Add Schedule
        </button>
    </div>

    <!-- Add Schedule Form (Collapsible card controlled by Alpine's showForm) -->
    <div x-show="showForm" x-transition style="display: none;">
        <form method="POST" action="{{ route('schedules.store') }}" class="mockup-card p-6 space-y-5">
            {{-- CSRF Token protection against cross-site request forgery --}}
            @csrf

            {{-- Alpine generates a hidden input for every day the user clicked (e.g. days[] = monday) --}}
            <template x-for="d in formDays" :key="d"><input type="hidden" name="days[]" :value="d"></template>

            <div class="flex items-center justify-between pb-3 border-b border-[var(--border-subtle)]">
                <h3 class="font-heading font-extrabold text-sm uppercase tracking-wider text-[var(--text-heading)]">Assign Access Schedule</h3>
                {{-- Enable / Disable checkbox for new schedule rule --}}
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-slate-300 text-[var(--purple-primary)]">
                    <span class="text-xs font-bold text-[var(--text-body)]">Enable</span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- User Selection: Loop through eligible faculty/staff members --}}
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Faculty / Staff <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->employee_id ?? 'No ID' }}) &mdash; {{ ucfirst($user->role) }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Form validation error message for user_id --}}
                    @error('user_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Key Selection: Loop through available physical key slots --}}
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Key Slot / Room <span class="text-rose-500">*</span></label>
                    <select name="key_id" x-model="formKeyId" required class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30">
                        <option value="">-- Select Key Slot --</option>
                        @foreach($keys as $key)
                            <option value="{{ $key->id }}" {{ old('key_id') == $key->id ? 'selected' : '' }}>
                                Slot #{{ $key->slot_number }} — {{ $key->key_name }} ({{ $key->room_name }})
                            </option>
                        @endforeach
                    </select>
                    {{-- Form validation error message for key_id --}}
                    @error('key_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Day Selection Pills (interactive buttons to choose repeat days) -->
            <div>
                <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-widest">Repeat on which days? <span class="text-rose-500">*</span></label>
                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Tap one or more days. The schedule repeats every week on the days you pick.</p>
                    </div>
                    {{-- Quick selection presets: Weekdays, All 7 Days, or Clear --}}
                    <div class="flex gap-1.5 text-[10px]">
                        <button type="button" @click="formDays = ['monday','tuesday','wednesday','thursday','friday']" class="px-2.5 py-1 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] font-bold">Weekdays</button>
                        <button type="button" @click="formDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']" class="px-2.5 py-1 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] font-bold">All 7</button>
                        <button type="button" @click="formDays = []" class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-600 font-bold">Clear</button>
                    </div>
                </div>

                {{-- 7-column grid with a pill button for each day of the week --}}
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2.5">
                    @foreach($days as $val => $full)
                        <button type="button" @click="toggleDay('{{ $val }}')"
                            :class="formDays.includes('{{ $val }}') ? 'day-pill-active' : 'day-pill-inactive'"
                            class="day-pill-btn flex items-center justify-center rounded-2xl border transition-all select-none">
                            <span class="text-xs sm:text-sm font-extrabold uppercase">{{ strtoupper(substr($full, 0, 3)) }}</span>
                        </button>
                    @endforeach
                </div>
                {{-- Live reactive text showing all chosen days --}}
                <p class="mt-2 text-[11px] text-[var(--text-muted)]" x-text="formDays.length ? 'Will repeat every: ' + formDays.map(d => d.charAt(0).toUpperCase() + d.slice(1)).join(', ') : 'No days selected yet'"></p>
                @error('days') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Start Time and End Time Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Start Time *</label>
                    <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}" required class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold">
                    @error('start_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">End Time *</label>
                    <input type="time" name="end_time" value="{{ old('end_time', '09:00') }}" required class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold">
                    @error('end_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Form Action Buttons -->
            <div class="flex items-center justify-between gap-3 pt-3 border-t border-[var(--border-subtle)]">
                <button type="button" @click="showForm = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] shadow-lg">
                    <i class="fa-solid fa-calendar-check text-xs"></i> Save Schedule
                </button>
            </div>
        </form>
    </div>

    <!-- Day tabs (Monday – Sunday buttons) -->
    <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 -mb-3">
        <i class="fa-solid fa-eye text-[var(--purple-primary)] text-xs"></i>
        <p class="text-[11px] font-extrabold uppercase tracking-widest text-[var(--text-heading)]">View schedules by day</p>
        <p class="text-[11px] text-[var(--text-muted)]">&mdash; tap a day to see who borrows which key, and when.</p>
    </div>
    <div class="mockup-card p-2 flex gap-2 overflow-x-auto">
        {{-- Loop through each day of the week to create the tab buttons --}}
        @foreach($days as $key => $full)
            @php 
                // Count how many schedules exist for this specific day
                $count = ($schedulesByDay[$key] ?? collect())->count(); 
            @endphp
            {{-- Clicking this button updates activeDay to this day key (e.g. 'monday') --}}
            <button type="button" @click="activeDay = '{{ $key }}'"
                :class="activeDay === '{{ $key }}' ? 'day-pill-active' : 'day-pill-inactive'"
                class="relative flex-1 min-w-[76px] flex flex-col items-center justify-center py-3 rounded-2xl border transition-all select-none">
                {{-- Show short 3-letter name (e.g., "Monday" -> "MON") --}}
                <span class="text-sm font-extrabold uppercase">{{ substr($full, 0, 3) }}</span>
                <span class="text-[10px] font-semibold opacity-80 mt-0.5">{{ $count }} {{ \Illuminate\Support\Str::plural('schedule', $count) }}</span>
                {{-- Green dot indicator if this tab is today's day --}}
                <span x-show="todayKey === '{{ $key }}'" style="display: none;" class="absolute top-1.5 right-2 w-1.5 h-1.5 rounded-full bg-emerald-400" title="Today"></span>
            </button>
        @endforeach
    </div>

    <!-- Day panels: Room 1–3 cards and schedule list for each day -->
    @foreach($days as $key => $full)
        @php
            // 1. Get all schedule records for this day (fallback to empty collection if none exist)
            $daySchedules = $schedulesByDay[$key] ?? collect();

            // 2. Group schedules by time window (e.g. "08:00|09:00")
            $slots = $daySchedules->groupBy(fn ($s) => $s->start_time . '|' . $s->end_time);
        @endphp

        {{-- Only display the panel corresponding to the currently clicked activeDay tab --}}
        <div x-show="activeDay === '{{ $key }}'" x-transition.opacity style="display: none;" class="mockup-card overflow-hidden">
            <div class="flex items-center justify-between flex-wrap gap-3 px-5 py-4 border-b border-[var(--border-subtle)]">
                <div>
                    {{-- Full day name (e.g. "Wednesday") --}}
                    <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">{{ $full }}</h3>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">{{ $slots->count() }} time {{ \Illuminate\Support\Str::plural('slot', $slots->count()) }} &bull; {{ $daySchedules->count() }} {{ \Illuminate\Support\Str::plural('borrower', $daySchedules->count()) }}</p>
                </div>
                <button type="button" @click="openFormFor('{{ $key }}', activeRoom)" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all shadow-sm">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add for {{ $full }}
                </button>
            </div>

            <div class="p-5">
                <!-- Room Selection Cards (Room 1, Room 2, Room 3) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-6">
                    @foreach($keys as $k)
                        @php
                            $roomScheds = $daySchedules->where('key_id', $k->id);
                            $roomCount = $roomScheds->count();
                            $roomLabel = $k->room_name ?: $k->key_name ?: ('Room ' . $k->slot_number);
                        @endphp
                        <button type="button"
                                @click="activeRoom = {{ $k->id }}"
                                :class="activeRoom == {{ $k->id }} 
                                    ? 'bg-[var(--purple-primary)] text-white shadow-md shadow-[var(--purple-primary)]/25 border-[var(--purple-primary)] ring-2 ring-[var(--purple-primary)]/20' 
                                    : 'bg-white text-[var(--text-heading)] border-[var(--border-subtle)] hover:border-[var(--purple-primary)]/40 hover:bg-[var(--purple-soft)]/30'"
                                class="flex items-center gap-3.5 p-4 rounded-2xl border transition-all text-left cursor-pointer select-none">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors"
                                 :class="activeRoom == {{ $k->id }} ? 'bg-white/20 text-white' : 'bg-[var(--purple-soft)] text-[var(--purple-primary)]'">
                                <i class="fa-solid fa-door-closed text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-heading font-extrabold text-sm sm:text-base leading-tight truncate"
                                   :class="activeRoom == {{ $k->id }} ? 'text-white' : 'text-[var(--text-heading)]'">
                                    {{ $roomLabel }}
                                </p>
                                <p class="text-xs font-semibold mt-0.5"
                                   :class="activeRoom == {{ $k->id }} ? 'text-white/85' : 'text-[var(--text-muted)]'">
                                    {{ $roomCount }} {{ \Illuminate\Support\Str::plural('schedule', $roomCount) }}
                                </p>
                            </div>
                        </button>
                    @endforeach
                </div>

                <!-- Selected Room Schedules Section -->
                @foreach($keys as $k)
                    @php
                        $roomScheds = $daySchedules->where('key_id', $k->id)->sortBy('start_time');
                        $roomCount = $roomScheds->count();
                        $roomLabel = $k->room_name ?: $k->key_name ?: ('Room ' . $k->slot_number);
                    @endphp
                    <div x-show="activeRoom == {{ $k->id }}" x-transition.opacity style="display: none;" class="space-y-4">
                        <!-- Room Header Bar -->
                        <div class="flex items-center justify-between pb-3 border-b border-[var(--border-subtle)]">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-door-closed text-[var(--purple-primary)] text-sm"></i>
                                <h4 class="font-heading font-extrabold text-sm sm:text-base text-[var(--text-heading)]">{{ $roomLabel }} Schedules</h4>
                            </div>
                            <span class="text-xs font-bold text-[var(--text-muted)]">{{ $roomCount }} {{ \Illuminate\Support\Str::plural('schedule', $roomCount) }}</span>
                        </div>

                        <!-- Room Schedule List -->
                        <div class="space-y-2.5">
                            @forelse($roomScheds as $schedule)
                                <div class="flex items-center justify-between gap-4 p-3.5 sm:p-4 rounded-2xl border border-[var(--border-subtle)] bg-white hover:border-[var(--purple-primary)]/40 hover:bg-[var(--purple-soft)]/20 transition-all">
                                    <!-- Time Column (Military Time, e.g. 1200 to 1300) -->
                                    <div class="w-24 sm:w-28 flex-shrink-0 text-left">
                                        <p class="font-mono font-extrabold text-xs sm:text-sm text-[var(--text-heading)] leading-tight">
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('Hi') }}
                                        </p>
                                        <p class="font-mono text-[11px] font-semibold text-[var(--text-muted)] mt-0.5">
                                            to {{ \Carbon\Carbon::parse($schedule->end_time)->format('Hi') }}
                                        </p>
                                    </div>

                                    <!-- User Avatar & Information -->
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($schedule->user->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-xs sm:text-sm text-[var(--text-heading)] truncate">
                                                {{ $schedule->user->name ?? 'Unknown User' }}
                                            </p>
                                            <p class="text-[11px] font-medium text-[var(--text-muted)] truncate mt-0.5">
                                                {{ ucfirst($schedule->user->role ?? 'user') }} &bull; 
                                                Slot #{{ $schedule->key->slot_number ?? '?' }} &bull; 
                                                {{ $schedule->key->key_name ?? '' }}
                                                @if(!empty($schedule->key->room_name)) ({{ $schedule->key->room_name }}) @endif
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @unless($schedule->is_active)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600">Inactive</span>
                                        @endunless

                                        @if($schedule->user)
                                            <a href="{{ route('users.qr', $schedule->user) }}"
                                               title="View & Print QR Badge for {{ $schedule->user->name }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all shadow-sm">
                                                <i class="fa-solid fa-qrcode text-[11px]"></i> <span>Print QR</span>
                                            </a>
                                        @endif

                                        <button type="button"
                                            @click="deleteModalOpen = true;
                                                    deleteScheduleId = '{{ $schedule->id }}';
                                                    deleteUserName = '{{ addslashes($schedule->user->name ?? 'Unknown User') }}';
                                                    deleteUserRole = '{{ ucfirst($schedule->user->role ?? 'User') }}';
                                                    deleteKeyName = '{{ addslashes($schedule->key->key_name ?? 'Key') }}';
                                                    deleteSlotNum = '{{ $schedule->key->slot_number ?? '?' }}';
                                                    deleteDayTime = '{{ $full }} · {{ \Carbon\Carbon::parse($schedule->start_time)->format('Hi') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('Hi') }}'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-rose-600 hover:text-white transition-all">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i> <span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-[var(--text-muted)] bg-slate-50/60 rounded-2xl border border-dashed border-[var(--border-subtle)]">
                                    <i class="fa-solid fa-calendar-xmark text-3xl block mb-2 opacity-30"></i>
                                    <p class="font-heading font-bold text-sm">No schedules for {{ $roomLabel }} on {{ $full }}.</p>
                                    <p class="text-xs mt-1">Click <strong>Add for {{ $full }}</strong> above to assign access to this room.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Remove Confirmation Modal (Shown when deleteModalOpen is true) -->
    <div x-show="deleteModalOpen" x-transition.opacity @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        {{-- Clicking outside the modal content dismisses it --}}
        <div @click.away="deleteModalOpen = false" class="w-full max-w-md bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 space-y-5 text-center">
            <!-- Modal Warning Icon -->
            <div class="w-16 h-16 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-2xl flex items-center justify-center mx-auto ring-4 ring-rose-100">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <div>
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Remove Access Schedule?</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5">This user will no longer be authorized to borrow this key during this time window.</p>
            </div>
            <!-- Target Schedule Details Preview -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-[var(--text-heading)]" x-text="deleteUserName"></span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-purple-100 text-purple-700" x-text="deleteUserRole"></span>
                </div>
                <div class="pt-2 border-t border-[var(--border-subtle)] flex justify-between">
                    <span class="text-[var(--text-muted)] font-medium">Key Slot:</span>
                    <span class="font-bold text-[var(--text-heading)]">#<span x-text="deleteSlotNum"></span> <span x-text="deleteKeyName"></span></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-muted)] font-medium">Window:</span>
                    <span class="font-mono font-bold text-rose-600" x-text="deleteDayTime"></span>
                </div>
            </div>
            <!-- Action Buttons: Cancel vs Confirm Delete Form -->
            <div class="flex items-center gap-3">
                <button type="button" @click="deleteModalOpen = false" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200">Cancel</button>
                <form :action="'{{ url('/schedules') }}/' + deleteScheduleId" method="POST" class="flex-1">
                    {{-- CSRF protection and HTTP DELETE method spoofing for Laravel --}}
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-extrabold text-white bg-rose-600 hover:bg-rose-700 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i> Yes, Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Custom CSS styling for the day pills and interaction states --}}
<style>
/* Ensures a consistent touch/click height for all day buttons */
.day-pill-btn { min-height: 52px; }

/* Active Day State: Vibrant purple-indigo gradient with subtle glow shadow */
.day-pill-active {
    background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
    border-color: #a78bfa !important; color: #fff !important;
    box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3) !important;
}

/* Inactive Day State: Clean white background with soft border and subtle hover state */
.day-pill-inactive { background: #fff !important; border-color: #e6e5f0 !important; color: #847d9c !important; }
.day-pill-inactive:hover { border-color: #6451a3 !important; color: #6451a3 !important; background: #f2eefb !important; }
</style>
@endsection