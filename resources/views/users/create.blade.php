@extends('layouts.app')

@section('title', 'Add New Authorized User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-heading font-bold text-slate-900">User Registration</h2>
            <p class="text-xs text-slate-500 mt-0.5">A unique QR token will be generated automatically upon registration</p>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="Prof. Juan Dela Cruz">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="juan@autobox.edu.ph">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Employee ID</label>
                    <input type="text" name="employee_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="EMP-1004">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role</label>
                    <select name="role" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                        <option value="faculty">Faculty</option>
                        <option value="staff">Staff / Custodian</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Department</label>
                    <input type="text" name="department" class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="Information Technology">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
                    <input type="password" name="password" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="••••••••">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200">Register & Generate QR</button>
            </div>
        </form>
    </div>
</div>
@endsection
