@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-heading font-bold text-slate-900">Edit User Details</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update user information and access status</p>
        </div>

        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Employee ID</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role</label>
                    <select name="role" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                        <option value="faculty" {{ $user->role === 'faculty' ? 'selected' : '' }}>Faculty</option>
                        <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff / Custodian</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}" class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">New Password (Optional)</label>
                    <input type="password" name="password" class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="Leave blank to keep current">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Account Active (Permit Key Box Access)</label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Delete User</button>
                </form>

                <div class="flex items-center gap-3">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-5 py-2 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
