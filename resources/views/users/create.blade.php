@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="max-w-lg">
    <!-- Back link -->
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--purple-primary)] transition-colors mb-4">
        <i class="fa-solid fa-chevron-left text-[9px]"></i> Back to Users
    </a>

    <div class="mockup-card p-6 space-y-5">
        <div>
            <h2 class="mockup-card-title text-lg">Add New User</h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Register a new user with QR access to the AUTOBOX system.</p>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all"
                    placeholder="e.g. Juan Dela Cruz">
                @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all"
                    placeholder="user@ccsict.edu">
                @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Employee / Student ID</label>
                <input type="text" name="employee_id" value="{{ old('employee_id') }}" required
                    class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all"
                    placeholder="e.g. EMP-2024-001">
                @error('employee_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[10px] font-extrabold text-[var(--text-muted)] mb-1.5 uppercase tracking-widest">Role</label>
                <select name="role"
                    class="w-full rounded-xl border border-[var(--border-subtle)] bg-[var(--app-bg)] px-3.5 py-2.5 text-sm text-[var(--text-heading)] focus:outline-none focus:ring-2 focus:ring-[var(--purple-primary)]/30 focus:border-[var(--purple-primary)] transition-all">
                    <option value="faculty" {{ old('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-3 flex gap-3 border-t border-[var(--border-subtle)]">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
                    <i class="fa-solid fa-user-plus text-xs"></i> Create User
                </button>
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-body)] bg-[var(--border-subtle)] hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
