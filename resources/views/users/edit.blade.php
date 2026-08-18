@extends('layouts.app')

@section('title', 'Edit User & Schedule')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--purple-primary)] transition-colors">
            <i class="fa-solid fa-chevron-left text-[9px]"></i> Back to Users
        </a>

        <a href="{{ route('users.qr', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-all shadow">
            <i class="fa-solid fa-qrcode"></i> View QR Badge
        </a>
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
                    <select name="role"
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

                <div>
                    <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">
                        New Password <span class="text-[var(--text-muted)] font-normal normal-case">(leave blank to keep)</span>
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

            <div class="pt-4 flex gap-3 border-t border-[var(--border-subtle)]">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                    <i class="fa-solid fa-floppy-disk text-xs"></i> Update Profile
                </button>
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

