@extends('layouts.app')

@section('title', 'Add Key Slot')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-heading font-bold text-slate-900">New Key Slot</h2>
            <p class="text-xs text-slate-500 mt-0.5">Assign a key slot number to a room or facility</p>
        </div>

        <form action="{{ route('keys.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Key Name</label>
                    <input type="text" name="key_name" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="Lab 1 Key">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Room Name</label>
                    <input type="text" name="room_name" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="ComLab 101">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Physical Slot #</label>
                    <input type="number" name="slot_number" min="1" max="50" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="1">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Initial Status</label>
                    <select name="status" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="missing">Missing</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (Optional)</label>
                <textarea name="description" rows="3" class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500" placeholder="Notes about room location or equipment..."></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('keys.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200">Save Key Slot</button>
            </div>
        </form>
    </div>
</div>
@endsection
