@extends('layouts.app')

@section('title', 'Edit User & Schedule')

@section('content')
<div class="max-w-2xl space-y-6" x-data="{ showDeleteModal: false }">
    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--purple-primary)] transition-colors">
            <i class="fa-solid fa-chevron-left text-[9px]"></i> Back to Users
        </a>

        <div class="flex items-center gap-2">
            <button type="button" @click="showDeleteModal = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/40 hover:bg-rose-600 hover:text-white hover:border-transparent transition-all shadow-sm">
                <i class="fa-solid fa-trash-can text-[10px]"></i> Delete User
            </button>
            <a href="{{ route('users.qr', $user) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow">
                <i class="fa-solid fa-qrcode"></i> View QR Badge
            </a>
        </div>
    </div>

    <div class="mockup-card p-6 md:p-8 space-y-6">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-[var(--purple-primary)]"></i>
                Edit User Profile
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Update credentials and role for {{ $user->name }}.</p>
        </div>

        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Employee / Student ID <span class="text-rose-500">*</span></label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" required
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('employee_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Role <span class="text-rose-500">*</span></label>
                    <select name="role" id="roleEditSelect"
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        <option value="faculty" {{ old('role', $user->role) === 'faculty' ? 'selected' : '' }}>Faculty</option>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Department / Office</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}"
                        class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    @error('department') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Admin Password Edit Fields (Only shown when Role is Admin) -->
                <div id="adminPasswordEditFields" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-2xl bg-purple-950/20 border border-purple-500/30" style="display: none;">
                    <div class="md:col-span-2 flex items-center gap-2 pb-1 border-b border-purple-500/20">
                        <i class="fa-solid fa-shield-halved text-xs text-[var(--purple-primary)]"></i>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-purple-300">Admin Login Credentials</span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                            New Password <span class="text-[var(--text-muted)] font-normal normal-case">(leave blank to keep current)</span>
                        </label>
                        <input type="password" name="password" placeholder="••••••••"
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                        @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Confirm New Password</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••"
                            class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    </div>
                </div>
            </div>

            <!-- Existing Schedules Overview -->
            <div class="pt-4 border-t border-[var(--border-subtle)] space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-[var(--text-heading)] flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-days text-[var(--purple-primary)]"></i>
                        Active Schedules ({{ $user->schedules->count() }})
                    </h3>
                    <a href="{{ route('schedules.index') }}" class="text-[11px] font-bold text-[var(--purple-primary)] hover:underline">
                        Manage All Schedules <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                </div>

                @if($user->schedules->count() > 0)
                    <div class="space-y-2">
                        @foreach($user->schedules as $sched)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)] text-xs">
                                <div>
                                    <span class="font-bold text-[var(--text-heading)]">
                                        Slot #{{ $sched->key->slot_number ?? '?' }} — {{ $sched->key->key_name ?? 'Key' }}
                                    </span>
                                    <span class="text-[var(--text-muted)] text-[11px] block">
                                        {{ ucfirst($sched->day_of_week) }} &bull; {{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($sched->end_time)->format('h:i A') }}
                                    </span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sched->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-500/10 text-slate-400' }}">
                                    {{ $sched->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-[var(--text-muted)] italic">No schedule rules assigned yet.</p>
                @endif
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-[var(--border-subtle)]">
                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                        <i class="fa-solid fa-floppy-disk text-xs"></i> Update Profile
                    </button>
                    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700 transition-colors">
                        Cancel
                    </a>
                </div>
                <button type="button" @click="showDeleteModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-rose-400 hover:text-white hover:bg-rose-600 transition-colors">
                    <i class="fa-solid fa-trash-can text-xs"></i> Delete User
                </button>
            </div>
        </form>
    </div>

    <!-- ═══════════════════════════════════
         PREMIUM CONFIRMATION DELETE USER MODAL
         ═══════════════════════════════════ -->
    <div x-show="showDeleteModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showDeleteModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md"
         style="display: none;">

        <div @click.away="showDeleteModal = false"
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
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <!-- Header Content -->
            <div>
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Delete {{ $user->name }}?</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed font-medium">
                    Are you sure you want to permanently delete this user account? All access rules, schedule assignments, and QR token badges will be permanently erased.
                </p>
            </div>

            <!-- User Detail Preview Card -->
            <div class="p-3.5 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] flex items-center gap-3 text-left">
                <div class="w-11 h-11 rounded-xl bg-purple-600/20 border border-purple-500/30 text-[var(--purple-primary)] font-extrabold text-base flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-sm text-[var(--text-heading)] truncate">{{ $user->name }}</p>
                    <p class="text-xs text-[var(--text-muted)] truncate">{{ $user->email }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-purple-500/15 text-[var(--purple-primary)]">{{ ucfirst($user->role) }}</span>
                        <span class="text-[11px] font-mono text-[var(--text-muted)]">ID: <span class="font-bold text-[var(--text-heading)]">{{ $user->employee_id ?? 'N/A' }}</span></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-1">
                <button type="button" @click="showDeleteModal = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700/50 hover:text-white transition-all">
                    Cancel
                </button>

                <form action="{{ route('users.destroy', $user) }}" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-rose-600 hover:bg-rose-500 active:scale-[0.98] transition-all shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('roleEditSelect');
        const adminPasswordContainer = document.getElementById('adminPasswordEditFields');

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
    });
</script>
@endsection

