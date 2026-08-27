<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\User;
use App\Models\Transaction;
use App\Mail\KeyUnreturnedUserNotice;
use App\Mail\KeyUnreturnedAdminAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class KeyController extends Controller
{
    public function index()
    {
        $keys = Key::orderBy('slot_number')->paginate(15);
        $users = User::where('is_active', true)->get();
        return view('keys.index', compact('keys', 'users'));
    }

    public function create()
    {
        return view('keys.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key_name'    => 'required|string|max:255',
            'room_name'   => 'required|in:Room 1,Room 2,Room 3',
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
            'room_name'   => 'required|in:Room 1,Room 2,Room 3',
            'slot_number' => 'required|integer|min:1|max:50|unique:keys,slot_number,' . $key->id,
            'description' => 'nullable|string',
            'status'      => 'required|in:available,borrowed,missing',
        ]);

        $key->update($validated);

        return redirect()->route('keys.index')->with('success', 'Key slot details updated successfully.');
    }

    public function destroy(Key $key)
    {
        $slotNum = $key->slot_number;
        $key->delete();
        return redirect()->route('keys.index')->with('success', "Key slot #{$slotNum} deleted successfully.");
    }

    public function updateStatus(Request $request, Key $key)
    {
        $request->validate([
            'status'  => 'required|in:available,borrowed,missing',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $newStatus = $request->status;
        $key->update(['status' => $newStatus]);

        if ($newStatus === 'borrowed') {
            $userId = $request->input('user_id', auth()->id());
            Transaction::create([
                'user_id'     => $userId,
                'key_id'      => $key->id,
                'action'      => 'borrow',
                'status'      => 'success',
                'borrowed_at' => now(),
            ]);
            $msg = "Key slot #{$key->slot_number} successfully borrowed.";
        } elseif ($newStatus === 'available') {
            $activeTx = Transaction::where('key_id', $key->id)
                ->where('action', 'borrow')
                ->whereNull('returned_at')
                ->latest()
                ->first();

            if ($activeTx) {
                $activeTx->update([
                    'returned_at' => now(),
                ]);
            }

            Transaction::create([
                'user_id'     => auth()->id(),
                'key_id'      => $key->id,
                'action'      => 'return',
                'status'      => 'success',
                'returned_at' => now(),
            ]);
            $msg = "Key slot #{$key->slot_number} marked as returned and available.";
        } else {
            // Flagged missing: find borrower with active unreturned transaction
            $activeTx = Transaction::where('key_id', $key->id)
                ->where('action', 'borrow')
                ->whereNull('returned_at')
                ->latest()
                ->with('user')
                ->first();

            $borrower = $activeTx?->user;

            if ($borrower && !empty($borrower->email) && filter_var($borrower->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($borrower->email)->send(new KeyUnreturnedUserNotice($borrower, $key, $activeTx));
                } catch (\Throwable $e) {
                    Log::error("[UNRETURNED KEY] Failed to send email to borrower: " . $e->getMessage());
                }
            }

            try {
                $admins = User::where('role', 'admin')->whereNotNull('email')->get();
                foreach ($admins as $admin) {
                    if (filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($admin->email)->send(new KeyUnreturnedAdminAlert($admin, $borrower, $key, $activeTx));
                    }
                }
            } catch (\Throwable $e) {
                Log::error("[UNRETURNED KEY] Failed to send email to admins: " . $e->getMessage());
            }

            $msg = "Key slot #{$key->slot_number} flagged as missing, and email alerts have been sent.";
        }

        return redirect()->back()->with('success', $msg);
    }
}
