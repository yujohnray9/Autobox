@extends('layouts.app')

@section('title', 'Physical Key Slots')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-heading font-bold text-slate-900">Key Slot Configuration</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage physical key slots, rooms, and manual status overrides</p>
        </div>
        <a href="{{ route('keys.create') }}" class="px-4 py-2.5 rounded-xl gradient-violet-blue text-white font-semibold text-xs shadow-md shadow-violet-200 hover:opacity-95 transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Key Slot
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Slot #</th>
                        <th class="p-4">Key Name</th>
                        <th class="p-4">Room Name</th>
                        <th class="p-4">Description</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($keys as $key)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-4">
                                <span class="w-8 h-8 rounded-lg bg-violet-100 text-violet-800 font-extrabold flex items-center justify-center font-mono text-sm">
                                    {{ $key->slot_number }}
                                </span>
                            </td>
                            <td class="p-4 font-bold text-slate-900 text-sm">
                                {{ $key->key_name }}
                            </td>
                            <td class="p-4 font-semibold text-blue-700">
                                {{ $key->room_name }}
                            </td>
                            <td class="p-4 text-slate-500 max-w-xs truncate">
                                {{ $key->description ?? 'N/A' }}
                            </td>
                            <td class="p-4">
                                <form action="{{ route('keys.update-status', $key->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="text-[11px] font-extrabold uppercase rounded-full px-2.5 py-1 border-0 cursor-pointer focus:ring-0
                                        @if($key->status === 'available') bg-emerald-100 text-emerald-800
                                        @elseif($key->status === 'borrowed') bg-amber-100 text-amber-800
                                        @else bg-rose-100 text-rose-800 @endif">
                                        <option value="available" {{ $key->status === 'available' ? 'selected' : '' }}>AVAILABLE</option>
                                        <option value="borrowed" {{ $key->status === 'borrowed' ? 'selected' : '' }}>BORROWED</option>
                                        <option value="missing" {{ $key->status === 'missing' ? 'selected' : '' }}>MISSING</option>
                                    </select>
                                </form>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <a href="{{ route('keys.edit', $key->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-xs transition-colors inline-flex items-center gap-1">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No key slots configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $keys->links() }}
        </div>
    </div>

</div>
@endsection
