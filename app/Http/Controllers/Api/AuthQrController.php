<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Key;
use App\Models\Transaction;
use App\Models\AccessLog;
use App\Models\Schedule;
use App\Events\SliderStateChanged;
use App\Events\KeyStatusUpdated;
use App\Events\AccessLogged;
use Illuminate\Http\Request;

class AuthQrController extends Controller
{
    /**
     * Authenticate QR code scanned by hardware scanner
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'slot_number' => 'nullable|integer',
        ]);

        $qrToken = $request->qr_token;
        $ip = $request->ip();

        // 1. Find User by QR token
        $user = User::where('qr_token', $qrToken)->where('is_active', true)->first();

        if (!$user) {
            AccessLog::create([
                'user_id'    => null,
                'qr_token'   => $qrToken,
                'action'     => 'scan',
                'result'     => 'denied',
                'reason'     => 'Invalid or inactive QR code',
                'ip_address' => $ip,
            ]);

            return response()->json([
                'success' => false,
                'status'  => 'DENIED',
                'message' => 'Invalid or inactive QR Code',
            ], 401);
        }

        // 2. Check if user has an active schedule today for a key
        $today = strtolower(now()->format('l')); // e.g. 'monday'
        $currentTime = now()->format('H:i:s');

        $schedule = Schedule::where('user_id', $user->id)
            ->where('day_of_week', $today)
            ->where('is_active', true)
            ->with('key')
            ->first();

        // Find assigned key or default key
        $key = null;
        if ($request->filled('slot_number')) {
            $key = Key::where('slot_number', $request->slot_number)->first();
        } elseif ($schedule) {
            $key = $schedule->key;
        } else {
            // Find first available key or borrowed key for returning
            $borrowedKey = Transaction::where('user_id', $user->id)
                ->where('action', 'borrow')
                ->whereNull('returned_at')
                ->latest()
                ->first();

            if ($borrowedKey) {
                $key = $borrowedKey->key;
            } else {
                $key = Key::where('status', 'available')->first();
            }
        }

        if (!$key) {
            AccessLog::create([
                'user_id'    => $user->id,
                'qr_token'   => $qrToken,
                'action'     => 'scan',
                'result'     => 'denied',
                'reason'     => 'No keys available or assigned',
                'ip_address' => $ip,
            ]);

            return response()->json([
                'success' => false,
                'status'  => 'DENIED',
                'message' => 'No key available for access',
            ], 404);
        }

        // 3. Determine action: Borrow or Return?
        $existingBorrow = Transaction::where('user_id', $user->id)
            ->where('key_id', $key->id)
            ->where('action', 'borrow')
            ->whereNull('returned_at')
            ->latest()
            ->first();

        if ($existingBorrow) {
            // ACTION: RETURN KEY
            $existingBorrow->update([
                'returned_at' => now(),
            ]);

            // Create return transaction
            Transaction::create([
                'user_id'     => $user->id,
                'key_id'      => $key->id,
                'action'      => 'return',
                'status'      => 'success',
                'notes'       => 'Returned via QR Scan',
                'returned_at' => now(),
            ]);

            $key->update(['status' => 'available']);

            AccessLog::create([
                'user_id'    => $user->id,
                'qr_token'   => $qrToken,
                'action'     => 'return',
                'result'     => 'granted',
                'reason'     => "Key returned to Slot #{$key->slot_number}",
                'ip_address' => $ip,
            ]);

            // Dispatch WebSocket Events
            KeyStatusUpdated::dispatch($key->id, $key->slot_number, 'available', $key->key_name, $key->room_name, null);
            AccessLogged::dispatch($user->name, 'return', 'granted', "Returned to Slot #{$key->slot_number}", $key->key_name, $key->room_name);

            return response()->json([
                'success'     => true,
                'status'      => 'GRANTED',
                'action'      => 'RETURN',
                'slot_number' => $key->slot_number,
                'key_name'    => $key->key_name,
                'user_name'   => $user->name,
                'message'     => "Access Granted: Return Key to Slot #{$key->slot_number}",
            ]);
        } else {
            // ACTION: BORROW KEY
            if ($key->status === 'borrowed') {
                AccessLog::create([
                    'user_id'    => $user->id,
                    'qr_token'   => $qrToken,
                    'action'     => 'borrow',
                    'result'     => 'denied',
                    'reason'     => "Key Slot #{$key->slot_number} is already borrowed",
                    'ip_address' => $ip,
                ]);

                AccessLogged::dispatch($user->name, 'borrow', 'denied', "Slot #{$key->slot_number} already borrowed", $key->key_name, $key->room_name);

                return response()->json([
                    'success' => false,
                    'status'  => 'DENIED',
                    'message' => "Key is currently borrowed by another user",
                ], 409);
            }

            Transaction::create([
                'user_id'     => $user->id,
                'key_id'      => $key->id,
                'action'      => 'borrow',
                'status'      => 'success',
                'notes'       => 'Borrowed via QR Scan',
                'borrowed_at' => now(),
            ]);

            $key->update(['status' => 'borrowed']);

            AccessLog::create([
                'user_id'    => $user->id,
                'qr_token'   => $qrToken,
                'action'     => 'borrow',
                'result'     => 'granted',
                'reason'     => "Key Slot #{$key->slot_number} unlocked for borrowing",
                'ip_address' => $ip,
            ]);

            // Dispatch WebSocket Events
            KeyStatusUpdated::dispatch($key->id, $key->slot_number, 'borrowed', $key->key_name, $key->room_name, $user->name);
            AccessLogged::dispatch($user->name, 'borrow', 'granted', "Borrowed Slot #{$key->slot_number}", $key->key_name, $key->room_name);

            return response()->json([
                'success'     => true,
                'status'      => 'GRANTED',
                'action'      => 'BORROW',
                'slot_number' => $key->slot_number,
                'key_name'    => $key->key_name,
                'user_name'   => $user->name,
                'message'     => "Access Granted: Unlock Slot #{$key->slot_number}",
            ]);
        }
    }

    /**
     * Get real-time status of all key slots
     */
    public function getKeyStatuses()
    {
        $keys = Key::orderBy('slot_number')->get(['id', 'key_name', 'room_name', 'slot_number', 'status']);
        return response()->json([
            'success' => true,
            'keys'    => $keys,
        ]);
    }

    /**
     * Hardware reports key missing
     */
    public function reportMissing(Request $request)
    {
        $request->validate([
            'slot_number' => 'required|integer',
        ]);

        $key = Key::where('slot_number', $request->slot_number)->first();
        if ($key) {
            $key->update(['status' => 'missing']);
            KeyStatusUpdated::dispatch($key->id, $key->slot_number, 'missing', $key->key_name, $key->room_name, null);
            AccessLogged::dispatch('Hardware Alert', 'missing', 'denied', "Key missing from Slot #{$key->slot_number}", $key->key_name, $key->room_name);
            return response()->json(['success' => true, 'message' => "Slot #{$key->slot_number} marked as missing"]);
        }

        return response()->json(['success' => false, 'message' => 'Slot not found'], 404);
    }

    /**
     * Hardware reports slider door movement (opened/closed via Ultrasonic DC Motor)
     */
    public function reportSliderEvent(Request $request)
    {
        $request->validate([
            'state'  => 'required|string|in:opened,closed',
            'reason' => 'nullable|string',
        ]);

        $action = $request->state === 'opened' ? 'slider_open' : 'slider_close';
        $reason = $request->reason ?? ("DC Motor Slider " . ucfirst($request->state));

        AccessLog::create([
            'user_id'    => null,
            'qr_token'   => 'HARDWARE_SLIDER',
            'action'     => $action,
            'result'     => 'granted',
            'reason'     => $reason,
            'ip_address' => $request->ip(),
        ]);

        // Dispatch WebSocket Event for Live Frontend Update
        SliderStateChanged::dispatch($request->state, $reason);
        AccessLogged::dispatch('System / Ultrasonic', $action, 'granted', $reason);

        return response()->json([
            'success' => true,
            'message' => "Slider event recorded: {$request->state}",
        ]);
    }
}
