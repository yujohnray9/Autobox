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
            // Holds the currently clicked/active room tab (defaults to the first room key)
            activeRoom: {{ $keys->first()->id ?? 1 }},
            formKeyId: '{{ old('key_id', '') }}',
            formDays: {!! $oldDaysJs !!},
            init() { 
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
            deleteKeyName: '', deleteSlotNum: '', deleteDayTime: '',
            // Schedule details modal (opened by clicking a schedule box)
            detailOpen: false, detail: {}
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
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">Pick a room to see its weekly schedule — who borrows the key, and when.</p>
                </div>
                {{-- Toggles the slide-down schedule creation form --}}
                <button type="button" @click="if (!showForm) { formDays = [todayKey]; formKeyId = activeRoom; } showForm = !showForm"
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

                    <!-- Start Time and End Time Inputs (7:00 AM – 5:00 PM only) -->
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                                    Start Time (7:00 AM – 5:00 PM) <span class="text-rose-500">*</span>
                                </label>
                                <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}" min="07:00" max="17:00" required class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold">
                                @error('start_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                                    End Time (7:00 AM – 5:00 PM) <span class="text-rose-500">*</span>
                                </label>
                                <input type="time" name="end_time" value="{{ old('end_time', '09:00') }}" min="07:00" max="17:00" required class="w-full rounded-xl border border-[var(--border-subtle)] bg-white px-3.5 py-2.5 text-sm font-semibold">
                                @error('end_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="text-[11px] text-[var(--text-muted)] mt-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-[var(--purple-primary)] text-xs"></i>
                            <span>Allowed schedule window is strictly between <strong>7:00 AM</strong> and <strong>5:00 PM</strong>.</span>
                        </p>
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

            <!-- Room tabs (Room 1 – Room 3 buttons) -->
            <div class="mockup-card p-4 space-y-3">
                <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 px-1">
                    <i class="fa-solid fa-door-open text-[var(--purple-primary)] text-xs"></i>
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-[var(--text-heading)]">Select a room</p>
                    <p class="text-[11px] text-[var(--text-muted)]">&mdash; tap a room to see its weekly schedule.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach($keys as $k)
                        @php
                            // Count all schedules (across every day) for this room
                            $roomTotal = $schedulesByDay->flatten()->where('key_id', $k->id)->count();
                            $roomLabel = $k->room_name ?: $k->key_name ?: ('Room ' . $k->slot_number);
                        @endphp
                        {{-- Clicking this button updates activeRoom to this key id --}}
                        <button type="button" @click="activeRoom = {{ $k->id }}"
                            :class="activeRoom == {{ $k->id }} ? 'day-pill-active' : 'day-pill-inactive'"
                            class="flex items-center gap-3.5 p-4 rounded-2xl border transition-all text-left select-none">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors"
                                :class="activeRoom == {{ $k->id }} ? 'bg-white/20 text-white' : 'bg-[var(--purple-soft)] text-[var(--purple-primary)]'">
                                <i class="fa-solid fa-door-closed text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-heading font-extrabold text-sm sm:text-base leading-tight truncate">{{ $roomLabel }}</p>
                                <p class="text-[11px] font-semibold opacity-80 mt-0.5">Slot #{{ $k->slot_number }} &bull; {{ $roomTotal }} {{ \Illuminate\Support\Str::plural('schedule', $roomTotal) }}/week</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Room panels: weekly timetable (Monday – Sunday columns) for each room -->
            @foreach($keys as $k)
                @php
                    $roomLabel = $k->room_name ?: $k->key_name ?: ('Room ' . $k->slot_number);
                    $roomTotal = $schedulesByDay->flatten()->where('key_id', $k->id)->count();
                @endphp

                {{-- Only display the panel for the currently selected room --}}
                <div x-show="activeRoom == {{ $k->id }}" x-transition.opacity style="display: none;" class="mockup-card overflow-hidden">
                    <div class="flex items-center justify-between flex-wrap gap-3 px-5 py-4 border-b border-[var(--border-subtle)]">
                        <div>
                            <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)] flex items-center gap-2">
                                <i class="fa-solid fa-door-closed text-[var(--purple-primary)] text-base"></i> {{ $roomLabel }}
                            </h3>
                            <p class="text-xs text-[var(--text-muted)] mt-0.5">Weekly schedule &bull; {{ $roomTotal }} {{ \Illuminate\Support\Str::plural('schedule', $roomTotal) }}</p>
                        </div>
                        <button type="button" @click="openFormFor(todayKey, {{ $k->id }})" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all shadow-sm">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add for {{ $roomLabel }}
                        </button>
                    </div>

                    <!-- Weekly grid: one column per day, schedules stacked layer by layer (sorted by start time) -->
                    <div class="p-4 overflow-x-auto">
                        <div class="week-board grid grid-cols-7 min-w-[980px] rounded-2xl border border-[var(--border-subtle)] overflow-hidden bg-white">
                            @foreach($days as $dayKey => $full)
                                @php
                                    $cellScheds = ($schedulesByDay[$dayKey] ?? collect())
                                        ->where('key_id', $k->id)
                                        ->sortBy('start_time');
                                @endphp
                                <div class="week-col flex flex-col min-h-[420px]"
                                    :class="todayKey === '{{ $dayKey }}' ? 'week-col-today' : ''">
                                    <!-- Day column header -->
                                    <div class="week-col-head flex items-center justify-between px-3 py-2.5">
                                        <div>
                                            <p class="font-heading font-extrabold text-sm text-[var(--text-heading)] flex items-center gap-1.5">
                                                {{ $full }}
                                                <span x-show="todayKey === '{{ $dayKey }}'" style="display: none;" class="w-1.5 h-1.5 rounded-full bg-emerald-400" title="Today"></span>
                                            </p>
                                            <p class="text-[10px] font-semibold text-[var(--text-muted)]">{{ $cellScheds->count() }} {{ \Illuminate\Support\Str::plural('schedule', $cellScheds->count()) }}</p>
                                        </div>
                                        <button type="button" @click="openFormFor('{{ $dayKey }}', {{ $k->id }})" title="Add schedule on {{ $full }}"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-[var(--purple-primary)] bg-[var(--purple-soft)] hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    </div>

                                    <!-- Stacked schedule boxes -->
                                    <div class="flex-1 p-2 space-y-2">
                                        @forelse($cellScheds as $schedule)
                                            @php
                                                $timeRange = \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($schedule->end_time)->format('g:i A');
                                                $detailData = [
                                                    'id'         => $schedule->id,
                                                    'name'       => $schedule->user->name ?? 'Unknown User',
                                                    'employeeId' => $schedule->user->employee_id ?? 'No ID',
                                                    'role'       => ucfirst($schedule->user->role ?? 'user'),
                                                    'department' => $schedule->user->department ?? '—',
                                                    'email'      => $schedule->user->email ?? '—',
                                                    'room'       => $roomLabel,
                                                    'keyName'    => $schedule->key->key_name ?? 'Key',
                                                    'slot'       => $schedule->key->slot_number ?? '?',
                                                    'day'        => $full,
                                                    'time'       => $timeRange,
                                                    'active'     => (bool) $schedule->is_active,
                                                    'qrUrl'      => $schedule->user ? route('users.qr', $schedule->user) : null,
                                                ];
                                            @endphp
                                            <div @click="detail = @js($detailData); detailOpen = true" title="Click to view full details"
                                                class="sched-box group relative p-3 rounded-xl border text-center transition-all cursor-pointer {{ $schedule->is_active ? 'sched-box-active' : 'sched-box-inactive' }}">
                                                <p class="font-mono font-extrabold text-[11px] tracking-wide text-[var(--text-heading)] truncate">
                                                    {{ $schedule->user->employee_id ?? 'NO ID' }}
                                                </p>
                                                <p class="font-bold text-xs uppercase text-[var(--purple-primary)] truncate mt-0.5" title="{{ $schedule->user->name ?? 'Unknown User' }}">
                                                    {{ $schedule->user->name ?? 'Unknown User' }}
                                                </p>
                                                <p class="font-mono text-[11px] font-semibold text-[var(--text-heading)] mt-1">
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                                </p>
                                                <p class="text-[10px] text-[var(--text-muted)] mt-0.5 truncate">
                                                    {{ ucfirst($schedule->user->role ?? 'user') }} &bull; {{ $roomLabel }}
                                                </p>
                                                @unless($schedule->is_active)
                                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-slate-200 text-slate-600">Inactive</span>
                                                @endunless

                                                <!-- Hover actions -->
                                                <div class="flex items-center justify-center gap-1.5 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    @if($schedule->user)
                                                        <a href="{{ route('users.qr', $schedule->user) }}" @click.stop title="Print QR Badge for {{ $schedule->user->name }}"
                                                        class="w-7 h-7 rounded-lg flex items-center justify-center bg-white border border-[var(--border-subtle)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                                                            <i class="fa-solid fa-qrcode text-[11px]"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" title="Remove schedule"
                                                        @click.stop="deleteModalOpen = true;
                                                                deleteScheduleId = '{{ $schedule->id }}';
                                                                deleteUserName = '{{ addslashes($schedule->user->name ?? 'Unknown User') }}';
                                                                deleteUserRole = '{{ ucfirst($schedule->user->role ?? 'User') }}';
                                                                deleteKeyName = '{{ addslashes($schedule->key->key_name ?? 'Key') }}';
                                                                deleteSlotNum = '{{ $schedule->key->slot_number ?? '?' }}';
                                                                deleteDayTime = '{{ $full }} · {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}'"
                                                        class="w-7 h-7 rounded-lg flex items-center justify-center bg-white border border-[var(--border-subtle)] text-slate-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="h-full min-h-[120px] flex flex-col items-center justify-center text-center text-[var(--text-muted)] opacity-60">
                                                <i class="fa-regular fa-calendar text-lg mb-1"></i>
                                                <p class="text-[10px] font-semibold">No schedule</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Schedule Details Modal (opens when a schedule box is clicked) -->
            <div x-show="detailOpen" x-transition.opacity @keydown.escape.window="detailOpen = false"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
                <div @click.away="detailOpen = false" class="sd-modal">
                    <!-- Header with avatar + full name -->
                    <div class="sd-head">
                        <button type="button" @click="detailOpen = false" class="sd-close" title="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <div class="sd-head-row">
                            <div class="sd-avatar" x-text="(detail.name || '?').charAt(0).toUpperCase()"></div>
                            <div class="sd-head-info">
                                <h3 class="sd-name" x-text="detail.name"></h3>
                                <p class="sd-empid" x-text="detail.employeeId"></p>
                                <div class="sd-tags">
                                    <span class="sd-tag" x-text="detail.role"></span>
                                    <span class="sd-tag sd-tag-status">
                                        <span class="sd-dot" :class="detail.active ? 'sd-dot-active' : 'sd-dot-inactive'"></span>
                                        <span x-text="detail.active ? 'Active' : 'Inactive'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail rows -->
                    <div class="sd-body">
                        <div class="sd-pair">
                            <div class="sd-chip">
                                <p class="sd-chip-label"><i class="fa-regular fa-calendar"></i> Day</p>
                                <p class="sd-chip-value" x-text="detail.day"></p>
                            </div>
                            <div class="sd-chip">
                                <p class="sd-chip-label"><i class="fa-regular fa-clock"></i> Time</p>
                                <p class="sd-chip-value" x-text="detail.time"></p>
                            </div>
                        </div>
                        <div class="sd-list">
                            <div class="sd-row"><span>Room</span><strong x-text="detail.room"></strong></div>
                            <div class="sd-row"><span>Key Slot</span><strong x-text="'#' + detail.slot + ' — ' + detail.keyName"></strong></div>
                            <div class="sd-row"><span>Department</span><strong x-text="detail.department"></strong></div>
                            <div class="sd-row"><span>Email</span><strong class="sd-break" x-text="detail.email"></strong></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="sd-actions">
                        <a x-show="detail.qrUrl" :href="detail.qrUrl" class="sd-btn sd-btn-qr">
                            <i class="fa-solid fa-qrcode"></i> Print QR
                        </a>
                        <button type="button"
                            @click="detailOpen = false;
                                    deleteScheduleId = detail.id; deleteUserName = detail.name; deleteUserRole = detail.role;
                                    deleteKeyName = detail.keyName; deleteSlotNum = detail.slot;
                                    deleteDayTime = detail.day + ' · ' + detail.time;
                                    $nextTick(() => deleteModalOpen = true)"
                            class="sd-btn sd-btn-remove">
                            <i class="fa-solid fa-trash-can"></i> Remove
                        </button>
                    </div>
                </div>
            </div>

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

        /* Weekly timetable: one single box, columns separated by thin dividers */
        .week-col { border-left: 1px solid #ece9f5; background: #fff; }
        .week-col:first-child { border-left: none; }
        .week-col-head { border-bottom: 1px solid #ece9f5; background: #faf9fd; }
        /* Today's column: soft purple tint instead of a heavy border */
        .week-col-today { background: #f7f3ff; }
        .week-col-today .week-col-head { background: #efe8fd; box-shadow: inset 0 3px 0 #7c3aed; }

        /* Weekly timetable schedule boxes (stacked inside each day column) */
        .sched-box-active { background: linear-gradient(180deg, #fbf8ff 0%, #f3edff 100%); border-color: #e4d9fb; }
        .sched-box-active:hover { border-color: #a78bfa; box-shadow: 0 6px 16px rgba(124, 58, 237, 0.15); transform: translateY(-1px); }
        .sched-box-inactive { background: #f8fafc; border-color: #e2e8f0; border-style: dashed; opacity: .75; }
        .sched-box-inactive:hover { opacity: 1; }

        /* Schedule details modal (self-contained so it doesn't depend on compiled Tailwind utilities) */
        .sd-modal { width: 100%; max-width: 440px; background: #fff; border-radius: 24px; overflow: hidden; box-shadow: 0 25px 60px rgba(30, 20, 60, .3); }
        .sd-head { position: relative; padding: 24px 56px 22px 24px; color: #fff; background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); }
        .sd-close { position: absolute; top: 14px; right: 14px; width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,.18); color: #fff; transition: background .2s; }
        .sd-close:hover { background: rgba(255,255,255,.32); }
        .sd-head-row { display: flex; align-items: center; gap: 16px; }
        .sd-avatar { width: 56px; height: 56px; flex-shrink: 0; border-radius: 16px; background: rgba(255,255,255,.22); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; }
        .sd-head-info { min-width: 0; }
        .sd-name { font-weight: 800; font-size: 18px; line-height: 1.25; word-break: break-word; }
        .sd-empid { font-family: ui-monospace, monospace; font-size: 12px; opacity: .9; margin-top: 2px; }
        .sd-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .sd-tag { padding: 3px 9px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; background: rgba(255,255,255,.22); }
        .sd-tag-status { display: inline-flex; align-items: center; gap: 6px; }
        .sd-dot { width: 7px; height: 7px; border-radius: 9999px; flex-shrink: 0; }
        .sd-dot-active { background: #34d399; box-shadow: 0 0 0 0 rgba(52, 211, 153, .7); animation: sd-blink 1.4s ease-in-out infinite; }
        .sd-dot-inactive { background: #cbd5e1; }
        @keyframes sd-blink {
            0%   { opacity: 1; box-shadow: 0 0 0 0 rgba(52, 211, 153, .7); }
            50%  { opacity: .35; box-shadow: 0 0 0 4px rgba(52, 211, 153, 0); }
            100% { opacity: 1; box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
        }
        .sd-body { padding: 22px 24px 8px; display: flex; flex-direction: column; gap: 14px; }
        .sd-pair { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .sd-chip { padding: 12px 14px; border-radius: 16px; background: #f3effc; }
        .sd-chip-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #847d9c; }
        .sd-chip-value { font-weight: 700; font-size: 14px; color: #1e1b2e; margin-top: 3px; }
        .sd-list { border: 1px solid #ece9f5; border-radius: 16px; overflow: hidden; }
        .sd-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 11px 16px; font-size: 13px; }
        .sd-row + .sd-row { border-top: 1px solid #f1eff7; }
        .sd-row span { color: #847d9c; font-weight: 500; }
        .sd-row strong { color: #1e1b2e; font-weight: 700; text-align: right; }
        .sd-break { word-break: break-all; }
        .sd-actions { display: flex; gap: 12px; padding: 18px 24px 28px; }
        .sd-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 0; border-radius: 12px; font-size: 14px; font-weight: 700; transition: all .2s; }
        .sd-btn-qr { background: #f3effc; color: #6451a3; }
        .sd-btn-qr:hover { background: #6451a3; color: #fff; }
        .sd-btn-remove { background: #f1f5f9; color: #334155; }
        .sd-btn-remove:hover { background: #e11d48; color: #fff; }
        </style>
        @endsection