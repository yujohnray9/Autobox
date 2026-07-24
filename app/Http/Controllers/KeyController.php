<?php

namespace App\Http\Controllers;

use App\Models\Key;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    public function index()
    {
        $keys = Key::orderBy('slot_number')->paginate(15);
        return view('keys.index', compact('keys'));
    }

    public function create()
    {
        return view('keys.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key_name'    => 'required|string|max:255',
            'room_name'   => 'required|string|max:255',
            'slot_number' => 'required|integer|unique:keys,slot_number|min:1|max:50',
            'description' => 'nullable|string',
            'status'      => 'required|in:available,borrowed,missing',
        ]);

        Key::create($validated);

        return redirect()->route('keys.index')->with('success', 'Key slot added successfully.');
    }

    public function edit(Key $key)
    {
        return view('keys.edit', compact('key'));
    }

    public function update(Request $request, Key $key)
    {
        $validated = $request->validate([
            'key_name'    => 'required|string|max:255',
            'room_name'   => 'required|string|max:255',
            'slot_number' => 'required|integer|min:1|max:50|unique:keys,slot_number,' . $key->id,
            'description' => 'nullable|string',
            'status'      => 'required|in:available,borrowed,missing',
        ]);

        $key->update($validated);

        return redirect()->route('keys.index')->with('success', 'Key updated successfully.');
    }

    public function destroy(Key $key)
    {
        $key->delete();
        return redirect()->route('keys.index')->with('success', 'Key deleted successfully.');
    }

    public function updateStatus(Request $request, Key $key)
    {
        $request->validate([
            'status' => 'required|in:available,borrowed,missing',
        ]);

        $key->update(['status' => $request->status]);

        return redirect()->back()->with('success', "Key status updated to {$request->status}.");
    }
}
