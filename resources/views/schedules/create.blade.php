@extends('layouts.app')

@section('title', 'Assign New Schedule')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="card card-body space-y-6">
        <div class="form-header pb-4">
            <h2 class="form-title">Add Schedule Rule</h2>
            <p class="form-subtitle">Assign a faculty or staff member access to a key for a specific day and time window</p>
        </div>

        <form action="{{ route('schedules.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="form-label">User (Faculty / Staff)</label>
                <select name="user_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->employee_id }}) — {{ $user->department }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Key / Room</label>
                <select name="key_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                    <option value="">-- Select Key Slot --</option>
                    @foreach($keys as $key)
                        <option value="{{ $key->id }}">Slot #{{ $key->slot_number }} — {{ $key->key_name }} ({{ $key->room_name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Day of Week</label>
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
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>
            </div>

            <div class="form-footer flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('schedules.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Schedule</button>
            </div>
        </form>
    </div>
</div>
@endsection
