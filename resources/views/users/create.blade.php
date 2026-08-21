@extends('layouts.app')

@section('title', 'Add User & Assign Schedule')

@section('content')
<div class="max-w-3xl">
    <!-- Back link -->
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--purple-primary)] transition-colors mb-4">
        <i class="fa-solid fa-chevron-left text-[9px]"></i> Back to Users
    </a>

    @if(session('conflict_error'))
    <div class="mb-5 flex items-start gap-3 px-5 py-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-amber-400 mt-0.5 flex-shrink-0 text-base"></i>
        <div>
            <p class="font-extrabold text-sm text-amber-200">Schedule Conflict Warning</p>
            <p class="text-xs font-medium mt-0.5 leading-relaxed text-amber-300/90">{{ session('conflict_error') }}</p>
        </div>
    </div>
    @endif

    <div class="mockup-card p-6 md:p-8 space-y-6">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-[var(--purple-primary)]"></i>
                Register New User & QR Access
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-1">Register a faculty, staff, or admin user and configure their authorized key access schedule.</p>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: User Profile Info -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-[var(--border-subtle)]">
                    <span class="w-6 h-6 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] flex items-center justify-center text-xs font-bold">1</span>
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-[var(--text-heading)]">User Profile Details</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all"
                            placeholder="e.g. Juan Dela Cruz">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Email Address <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all"
                            placeholder="user@autobox.edu.ph">
                        @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Employee / ID Number</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}"
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all"
                            placeholder="e.g. EMP-2024-005 (Leave blank to auto-generate)">
                        @error('employee_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Role <span class="text-rose-500">*</span></label>
                        <select name="role" id="roleSelect"
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                            <option value="faculty" {{ old('role', 'faculty') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Department / Specialization</label>
                        <input type="text" name="department" value="{{ old('department') }}"
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all"
                            placeholder="e.g. Computer Science Department / Networking Lab">
                        @error('department') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Admin Password Fields (Only shown when Role is Admin) -->
                    <div id="adminPasswordFields" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-2xl bg-purple-950/20 border border-purple-500/30" style="display: none;">
                        <div class="md:col-span-2 flex items-center gap-2 pb-1 border-b border-purple-500/20">
                            <i class="fa-solid fa-shield-halved text-xs text-[var(--purple-primary)]"></i>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-purple-300">Admin Login Credentials</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                                Admin Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="passwordInput" placeholder="••••••••"
                                    class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] pl-3.5 pr-10 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                                <button type="button" onclick="togglePasswordVisibility('passwordInput', 'eyeIconPassword')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[var(--text-muted)] hover:text-white transition-colors cursor-pointer" title="Toggle password visibility">
                                    <i id="eyeIconPassword" class="fa-regular fa-eye text-sm"></i>
                                </button>
                            </div>
                            @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                                Confirm Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="passwordConfirmInput" placeholder="••••••••"
                                    class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] pl-3.5 pr-10 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                                <button type="button" onclick="togglePasswordVisibility('passwordConfirmInput', 'eyeIconPasswordConfirm')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[var(--text-muted)] hover:text-white transition-colors cursor-pointer" title="Toggle password visibility">
                                    <i id="eyeIconPasswordConfirm" class="fa-regular fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Key Access Schedule -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between pb-2 border-b border-[var(--border-subtle)]">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">2</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-widest text-[var(--text-heading)]">Assign Access Schedule & Key Slot</h3>
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="assign_schedule" id="assignScheduleToggle" value="1" {{ old('assign_schedule', '1') == '1' ? 'checked' : '' }} class="rounded border-[var(--border-subtle)] text-[var(--purple-primary)] focus:ring-[var(--purple-primary)]/30">
                        <span class="text-xs font-bold text-[var(--text-body)]">Enable Key Schedule</span>
                    </label>
                </div>

                <div id="scheduleFieldsContainer" class="space-y-4 bg-[var(--app-bg)]/60 border border-[var(--border-subtle)] rounded-2xl p-5">
                    <!-- Key Slot Selector -->
                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                            Authorized Key Slot / Room <span class="text-rose-500">*</span>
                        </label>
                        <select name="key_id" id="keySelect"
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
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
                        <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
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
                                <button type="button" onclick="selectDayPreset('clear')" class="px-2.5 py-1 rounded-lg bg-[var(--border-subtle)] text-[var(--text-muted)] hover:text-white font-bold transition-all">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Interactive Day Pills Grid -->
                        <div class="grid grid-cols-7 gap-1.5 sm:gap-2.5" id="daysGrid">
                            @php
                                $daysConfig = [
                                    'monday'    => ['short' => 'Mon', 'letter' => 'M'],
                                    'tuesday'   => ['short' => 'Tue', 'letter' => 'T'],
                                    'wednesday' => ['short' => 'Wed', 'letter' => 'W'],
                                    'thursday'  => ['short' => 'Thu', 'letter' => 'Th'],
                                    'friday'    => ['short' => 'Fri', 'letter' => 'F'],
                                    'saturday'  => ['short' => 'Sat', 'letter' => 'Sa'],
                                    'sunday'    => ['short' => 'Sun', 'letter' => 'Su'],
                                ];
                                $selectedDays = (array) old('days', ['monday', 'wednesday', 'friday']);
                            @endphp

                            @foreach($daysConfig as $val => $info)
                                @php $isSelected = in_array($val, $selectedDays); @endphp
                                <button type="button"
                                        onclick="toggleDaySelection('{{ $val }}')"
                                        id="day-btn-{{ $val }}"
                                        class="day-pill-btn relative flex flex-col items-center justify-center py-3 px-1 rounded-2xl border transition-all select-none cursor-pointer {{ $isSelected ? 'day-pill-active' : 'day-pill-inactive' }}">
                                    <span class="text-xs sm:text-sm font-extrabold uppercase tracking-tight">{{ $info['short'] }}</span>
                                    <span class="text-[9px] font-semibold opacity-75 capitalize hidden sm:inline mt-0.5">{{ substr($val, 0, 3) }}</span>
                                    <div class="day-check-indicator w-2 h-2 rounded-full mt-1.5 transition-all {{ $isSelected ? 'bg-white shadow' : 'bg-transparent' }}"></div>
                                    <input type="checkbox" name="days[]" value="{{ $val }}" id="day-input-{{ $val }}" {{ $isSelected ? 'checked' : '' }} class="sr-only">
                                </button>
                            @endforeach
                        </div>

                        <!-- Active Days Summary Indicator -->
                        <div class="mt-2 flex items-center justify-between text-[11px] text-[var(--text-muted)]">
                            <span id="selectedDaysSummary">Selected: <strong class="text-[var(--text-heading)] font-bold">Mon, Wed, Fri</strong> (3 days)</span>
                            <span class="text-[10px] opacity-70 italic">Click day to toggle</span>
                        </div>
                        @error('days') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Time Window -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                                <i class="fa-regular fa-clock text-emerald-400 mr-1"></i> Start Time <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}"
                                class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                            @error('start_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                                <i class="fa-regular fa-clock text-rose-400 mr-1"></i> End Time <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="end_time" value="{{ old('end_time', '17:00') }}"
                                class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                            @error('end_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-[var(--text-muted)] pt-1">
                        <i class="fa-solid fa-circle-info text-[var(--purple-primary)] text-xs"></i>
                        <span>The hardware QR scanner will authorize key unlock during this scheduled time window.</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 flex items-center justify-between gap-3 border-t border-[var(--border-subtle)]">
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow-lg hover:shadow-violet-600/30">
                    <i class="fa-solid fa-qrcode text-xs"></i> Create User & Generate QR
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.day-pill-btn {
    min-height: 58px;
}
.day-pill-active {
    background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
    border-color: #a78bfa !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px 0 rgba(124, 58, 237, 0.4) !important;
    transform: translateY(-1px);
}
.day-pill-inactive {
    background: var(--app-bg, #130f24) !important;
    border-color: var(--border-subtle, #292244) !important;
    color: var(--text-muted, #847bb0) !important;
}
.day-pill-inactive:hover {
    border-color: #8b5cf6 !important;
    color: #ffffff !important;
    background: rgba(124, 58, 237, 0.1) !important;
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
                summary.innerHTML = '<span class="text-amber-400 font-bold"><i class="fa-solid fa-triangle-exclamation"></i> No days selected</span>';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('assignScheduleToggle');
        const container = document.getElementById('scheduleFieldsContainer');

        function updateScheduleVisibility() {
            if (toggle && container) {
                if (toggle.checked) {
                    container.classList.remove('opacity-40', 'pointer-events-none');
                } else {
                    container.classList.add('opacity-40', 'pointer-events-none');
                }
            }
        }

        const roleSelect = document.getElementById('roleSelect');
        const adminPasswordContainer = document.getElementById('adminPasswordFields');

        function updateRolePasswordVisibility() {
            if (roleSelect && adminPasswordContainer) {
                if (roleSelect.value === 'admin') {
                    adminPasswordContainer.style.display = 'grid';
                } else {
                    adminPasswordContainer.style.display = 'none';
                }
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', updateRolePasswordVisibility);
            updateRolePasswordVisibility();
        }

        updateDaysSummary();
    });

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection

