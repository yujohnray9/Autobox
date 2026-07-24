@extends('layouts.app')

@section('title', 'Schedule Assignments')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-heading font-bold text-slate-900">User Schedule Assignments</h2>
            <p class="text-xs text-slate-500 mt-0.5">Assign faculty/staff time slots for accessing specific room keys</p>
        </div>
        <a href="{{ route('schedules.create') }}" class="px-4 py-2.5 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200 hover:opacity-95 transition-all flex items-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i> Add Schedule
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Faculty / Staff</th>
                        <th class="p-4">Assigned Key & Room</th>
                        <th class="p-4">Day of Week</th>
                        <th class="p-4">Time Window</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $sched)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $sched->user->name ?? 'N/A' }}</span>
                                <span class="text-slate-400 text-xs">{{ $sched->user->employee_id ?? '' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-slate-800">{{ $sched->key->key_name ?? 'N/A' }}</span>
                                <span class="block text-violet-600 font-medium text-xs">{{ $sched->key->room_name ?? '' }} (Slot #{{ $sched->key->slot_number ?? '' }})</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full bg-violet-50 text-violet-700 font-extrabold uppercase text-[10px]">
                                    {{ $sched->day_of_week }}
                                </span>
                            </td>
                            <td class="p-4 font-mono font-semibold text-slate-700">
                                {{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('h:i A') }}
                            </td>
                            <td class="p-4 text-right">
                                <form action="{{ route('schedules.destroy', $sched->id) }}" method="POST" onsubmit="return confirm('Remove schedule?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold text-xs transition-colors">
                                        <i class="fa-solid fa-trash"></i> Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">No active schedule assignments.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $schedules->links() }}
        </div>
    </div>

</div>
@endsection
