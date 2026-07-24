@extends('layouts.app')

@section('title', 'Assign New Schedule')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-heading font-bold text-slate-900">Add Schedule Rule</h2>
            <p class="text-xs text-slate-500 mt-0.5">Assign a faculty or staff member access to a key for a specific day and time window</p>
        </div>

        <form action="{{ route('schedules.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">User (Faculty / Staff)</label>
                <select name="user_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->employee_id }}) — {{ $user->department }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Key / Room</label>
                <select name="key_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                    <option value="">-- Select Key Slot --</option>
                    @foreach($keys as $key)
                        <option value="{{ $key->id }}">Slot #{{ $key->slot_number }} — {{ $key->key_name }} ({{ $key->room_name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Day of Week</label>
                    <select name="day_of_week" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Start Time</label>
                    <input type="time" name="start_time" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">End Time</label>
                    <input type="time" name="end_time" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('schedules.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200">Save Schedule</button>
            </div>
        </form>
    </div>
</div>
@endsection
