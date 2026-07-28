@extends('layouts.app')

@section('title', 'Access Schedules')

@section('content')
<div class="space-y-6" x-data="{ showForm: false, deleteId: null, deleteName: '' }">

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

                <!-- User -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Assigned User
                    </label>
                    <select name="user_id" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="">-- Select Faculty / Staff --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->employee_id ?? 'No ID' }}) — {{ ucfirst($u->role) }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Key Slot -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Key Slot
                    </label>
                    <select name="key_id" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="">-- Select Key Slot --</option>
                        @foreach($keys as $k)
                            <option value="{{ $k->id }}" {{ old('key_id') == $k->id ? 'selected' : '' }}>
                                Slot #{{ $k->slot_number }} — {{ $k->key_name }} ({{ $k->room_name }})
                            </option>
                        @endforeach
                    </select>
                    @error('key_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Day of Week -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Day of Week
                    </label>
                    <select name="day_of_week" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm font-semibold text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="">-- Select Day --</option>
                        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <option value="{{ $day }}" {{ old('day_of_week') === $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                    @error('day_of_week') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Is Active toggle -->
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                            <div class="w-10 h-5 bg-[var(--border-subtle)] peer-checked:bg-[var(--purple-primary)] rounded-full transition-all"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-all peer-checked:translate-x-5"></div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[var(--text-heading)]">Active Schedule</p>
                            <p class="text-[10px] text-[var(--text-muted)]">Enable this schedule rule</p>
                        </div>
                    </label>
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        Start Time
                    </label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('start_time') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- End Time -->
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        End Time
                    </label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('end_time') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Buttons -->
                <div class="sm:col-span-2 flex gap-3 pt-3 border-t border-[var(--border-subtle)]">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                        <i class="fa-solid fa-floppy-disk text-xs"></i> Save Schedule
                    </button>
                    <button type="button" @click="showForm = false"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Cancel
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
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Key Slot</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Day</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Time Window</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                    @forelse($schedules as $schedule)
                        <tr class="hover:bg-[var(--purple-soft)] transition-colors group">
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
                                <form method="POST" action="{{ route('schedules.destroy', $schedule) }}"
                                      onsubmit="return confirm('Remove this schedule for {{ addslashes($schedule->user->name ?? 'this user') }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-[var(--border-subtle)] text-[var(--text-body)] hover:bg-rose-100 hover:text-rose-700 dark:hover:bg-rose-950/60 dark:hover:text-rose-300 transition-all">
                                        <i class="fa-solid fa-trash text-[10px]"></i> Remove
                                    </button>
                                </form>
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
</div>
@endsection
