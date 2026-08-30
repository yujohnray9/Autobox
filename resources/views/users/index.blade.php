@extends('layouts.app')

@section('title', 'Users & QR Management')

@section('content')
<div class="space-y-6" x-data="{
    deleteModalOpen: false,
    targetUserId: null,
    targetUserName: '',
    targetUserEmail: '',
    targetUserRole: '',
    targetUserInitials: '',
    targetEmployeeId: ''
}">

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
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-[var(--purple-primary)] text-xs font-bold border border-purple-200">
                                        <i class="fa-solid fa-shield-halved text-[11px]"></i>
                                        <span>Full Access (24/7)</span>
                                    </span>
                                @elseif($user->schedules && $user->schedules->count() > 0)
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
                                    <span class="text-xs text-emerald-600 font-bold flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-check"></i> Active Token
                                    </span>
                                @else
                                    <span class="text-xs text-[var(--text-muted)] font-semibold">Not generated</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.qr', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-qrcode"></i> Badge
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-[var(--purple-primary)] hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button"
                                        @click="deleteModalOpen = true; 
                                                targetUserId = '{{ $user->id }}'; 
                                                targetUserName = '{{ addslashes($user->name) }}'; 
                                                targetUserEmail = '{{ addslashes($user->email) }}'; 
                                                targetUserRole = '{{ ucfirst($user->role) }}'; 
                                                targetUserInitials = '{{ strtoupper(substr($user->name, 0, 1)) }}';
                                                targetEmployeeId = '{{ $user->employee_id ?? 'N/A' }}'"
                                        title="Delete User"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
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

    <!-- ═══════════════════════════════════
         PREMIUM CONFIRMATION DELETE USER MODAL
         ═══════════════════════════════════ -->
    <div x-show="deleteModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="deleteModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
             class="w-full max-w-md bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 sm:p-7 space-y-5 text-center relative overflow-hidden">
            
            <!-- Glowing Red Ambient Light Accent -->
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-44 h-24 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Danger Warning Icon -->
            <div class="w-16 h-16 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-2xl flex items-center justify-center mx-auto shadow-inner ring-4 ring-rose-100">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <!-- Header Content -->
            <div>
                <h3 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Delete User Account?</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed font-medium">
                    Are you sure you want to permanently remove this user? All assigned access schedules and QR token badges will be deleted.
                </p>
            </div>

            <!-- User Detail Preview Card -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3 text-left">
                <div class="w-11 h-11 rounded-xl bg-purple-100 border border-purple-200 text-purple-700 font-extrabold text-base flex items-center justify-center flex-shrink-0" x-text="targetUserInitials"></div>
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-sm text-[var(--text-heading)] truncate" x-text="targetUserName"></p>
                    <p class="text-xs text-[var(--text-muted)] truncate" x-text="targetUserEmail"></p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-purple-100 text-purple-700" x-text="targetUserRole"></span>
                        <span class="text-[11px] font-mono text-[var(--text-muted)]">ID: <span class="font-bold text-[var(--text-heading)]" x-text="targetEmployeeId"></span></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-1">
                <button type="button" @click="deleteModalOpen = false"
                        class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                    Cancel
                </button>

                <form :action="'{{ url('/users') }}/' + targetUserId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-extrabold text-white bg-rose-600 hover:bg-rose-700 active:scale-[0.98] transition-all shadow-lg shadow-rose-600/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

