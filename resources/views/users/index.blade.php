@extends('layouts.app')

@section('title', 'Users & QR Management')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="mockup-card-title text-xl flex items-center gap-2">
                <i class="fa-solid fa-users text-[var(--purple-primary)] text-lg"></i>
                Users & QR Management
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Manage registered users, access schedules, and generated QR badges.</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-[var(--purple-primary)] hover:bg-[var(--purple-dark)] transition-colors shadow-md">
            <i class="fa-solid fa-user-plus text-xs"></i> Add New User & Schedule
        </a>
    </div>

    <!-- Users Table -->
    <div class="mockup-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-[var(--border-subtle)]">
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Employee ID</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Role</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Assigned Schedule</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider">QR Code</th>
                        <th class="px-5 py-3 text-[10px] font-extrabold text-[var(--text-muted)] uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                    @forelse($users as $user)
                        <tr class="hover:bg-[var(--purple-soft)] transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[var(--purple-soft)] text-[var(--purple-primary)] font-extrabold text-sm flex items-center justify-center ring-2 ring-[var(--purple-primary)]/20">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[var(--text-heading)] text-sm">{{ $user->name }}</p>
                                        <p class="text-xs text-[var(--text-muted)]">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-sm font-mono font-semibold text-[var(--purple-primary)]">{{ $user->employee_id ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                    {{ $user->role === 'admin' ? 'bg-[var(--purple-primary)] text-white' : 'bg-[var(--border-subtle)] text-[var(--text-body)]' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($user->schedules && $user->schedules->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($user->schedules->take(2) as $sched)
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-[var(--purple-soft)] text-[var(--purple-primary)] text-[11px] font-bold">
                                                <i class="fa-solid fa-key text-[9px]"></i>
                                                <span>Slot #{{ $sched->key->slot_number ?? '?' }} ({{ $sched->key->key_name ?? '' }})</span>
                                                <span class="text-[9px] font-mono opacity-80 uppercase">[{{ substr($sched->day_of_week, 0, 3) }}]</span>
                                            </div>
                                        @endforeach
                                        @if($user->schedules->count() > 2)
                                            <p class="text-[10px] text-[var(--text-muted)] font-semibold">+{{ $user->schedules->count() - 2 }} more rules</p>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-[var(--text-muted)] font-medium italic">No schedule</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($user->qr_token)
                                    <span class="text-xs text-emerald-400 font-bold flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-check"></i> Active Token
                                    </span>
                                @else
                                    <span class="text-xs text-[var(--text-muted)] font-semibold">Not generated</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.qr', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                                        <i class="fa-solid fa-qrcode"></i> Badge
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-[var(--border-subtle)] text-[var(--text-body)] hover:bg-[var(--purple-primary)] hover:text-white transition-all">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] font-bold bg-[var(--border-subtle)] text-[var(--text-body)] hover:bg-rose-100 hover:text-rose-700 transition-all">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-[var(--text-muted)] text-sm">
                                <i class="fa-solid fa-users text-4xl block mb-3 opacity-20"></i>
                                No users registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-[var(--border-subtle)]">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

