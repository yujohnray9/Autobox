@extends('layouts.app')

@section('title', 'Users & QR Code Management')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-heading font-bold text-slate-900">Authorized Personnel</h2>
            <p class="text-xs text-slate-500 mt-0.5">Faculty, staff, and administrators registered for AUTOBOX access</p>
        </div>
        <a href="{{ route('users.create') }}" class="px-4 py-2.5 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200 hover:opacity-95 transition-all flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Add New User
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Name & Email</th>
                        <th class="p-4">Employee ID</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Department</th>
                        <th class="p-4">QR Token</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $user->name }}</span>
                                <span class="text-slate-400 text-xs">{{ $user->email }}</span>
                            </td>
                            <td class="p-4 font-mono font-semibold text-violet-700">
                                {{ $user->employee_id ?? 'N/A' }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                    @if($user->role === 'admin') bg-violet-100 text-violet-800
                                    @elseif($user->role === 'faculty') bg-blue-100 text-blue-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-600 font-medium">
                                {{ $user->department ?? 'General' }}
                            </td>
                            <td class="p-4 font-mono text-[11px] text-slate-500 truncate max-w-[120px]">
                                {{ $user->qr_token ?? 'Not generated' }}
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase
                                    @if($user->is_active) bg-emerald-100 text-emerald-800 @else bg-rose-100 text-rose-800 @endif">
                                    {{ $user->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <a href="{{ route('users.qr', $user->id) }}" class="px-2.5 py-1.5 rounded-lg bg-violet-50 text-violet-700 hover:bg-violet-100 font-semibold text-xs transition-colors inline-flex items-center gap-1">
                                    <i class="fa-solid fa-qrcode"></i> View QR
                                </a>
                                <a href="{{ route('users.edit', $user->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-xs transition-colors inline-flex items-center gap-1">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
