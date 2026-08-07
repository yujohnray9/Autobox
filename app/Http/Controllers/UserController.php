<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'role'        => 'required|in:admin,faculty,staff',
            'department'  => 'nullable|string|max:255',
            'employee_id' => 'required|string|unique:users,employee_id',
        ]);

        $validated['password'] = Hash::make(Str::random(16));
        $validated['qr_token'] = 'AUTOBOX-QR-' . strtoupper(Str::random(10));
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully with generated QR code.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role'        => 'required|in:admin,faculty,staff',
            'department'  => 'nullable|string|max:255',
            'employee_id' => 'required|string|unique:users,employee_id,' . $user->id,
            'is_active'   => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function qr(User $user)
    {
        if (!$user->qr_token) {
            $user->generateQrToken();
        }
        return view('users.qr', compact('user'));
    }

    public function regenerateQr(User $user)
    {
        $user->generateQrToken();
        return redirect()->back()->with('success', 'QR Code regenerated successfully.');
    }
}
