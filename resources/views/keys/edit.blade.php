@extends('layouts.app')

@section('title', 'Edit Key Slot')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-heading font-bold text-slate-900">Edit Key Slot</h2>
            <p class="text-xs text-slate-500 mt-0.5">Modify key details or slot number assignment</p>
        </div>

        <form action="{{ route('keys.update', $key->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Key Name</label>
                    <input type="text" name="key_name" value="{{ old('key_name', $key->key_name) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Room Name</label>
                    <input type="text" name="room_name" value="{{ old('room_name', $key->room_name) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Physical Slot #</label>
                    <input type="number" name="slot_number" value="{{ old('slot_number', $key->slot_number) }}" min="1" max="50" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status</label>
                    <select name="status" required class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                        <option value="available" {{ $key->status === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="borrowed" {{ $key->status === 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                        <option value="missing" {{ $key->status === 'missing' ? 'selected' : '' }}>Missing</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (Optional)</label>
                <textarea name="description" rows="3" class="w-full text-xs rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">{{ old('description', $key->description) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <form action="{{ route('keys.destroy', $key->id) }}" method="POST" onsubmit="return confirm('Delete this key slot?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Delete Key</button>
                </form>

                <div class="flex items-center gap-3">
                    <a href="{{ route('keys.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-5 py-2 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
