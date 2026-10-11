<?php

namespace App\Services;

use App\Events\AccessLogged;
use App\Events\KeyStatusUpdated;
use App\Models\AccessLog;
use App\Models\Key;
use App\Models\OfflineRecord;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Handles events recorded by the Raspberry Pi while it had no connection to the server.
 *
 * Flow (automatic):
 *  1. Lock box comes back online and uploads its queue -> stage()        (saved to offline_records)
 *  2. Server immediately applies them                    -> applyPending() (written to transactions / access_logs)
 *  3. Admin reviews what happened in the "Offline Records" tab (history). Anything that failed stays
 *     "pending" and can be retried with the Retry Sync button.
 */
class OfflineSyncService
{
    /**
     * Save uploaded offline events into the staging table (no changes to live data yet).
     *
     * @return array{staged:int, duplicates:int, ids:int[]}
     */
    public function stage(array $logs, ?string $ip = null): array
    {
        $staged = 0;
        $duplicates = 0;
        $ids = [];

        foreach ($logs as $item) {
            if (!is_array($item)) {
                continue;
            }

            $type = $item['type'] ?? null;
            if (!in_array($type, ['transaction', 'access_log'], true)) {
                continue;
            }

            // Older Pi builds did not send a uid — derive a stable one from the content.
            $uid = (string) ($item['uid'] ?? hash('sha256', json_encode($item)));

            if (OfflineRecord::where('uid', $uid)->exists()) {
                $duplicates++;
                continue;
            }

            try {
                $occurredAt = isset($item['timestamp']) ? Carbon::parse($item['timestamp']) : now();
            } catch (\Throwable $e) {
                $occurredAt = now();
            }

            $userId = isset($item['user_id']) && User::whereKey($item['user_id'])->exists()
                ? (int) $item['user_id']
                : null;

            if ($ip && !isset($item['ip_address'])) {
                $item['ip_address'] = $ip;
            }

            $ids[] = OfflineRecord::create([
                'uid'         => $uid,
                'type'        => $type,
                'user_id'     => $userId,
                'slot_number' => isset($item['slot_number']) ? (int) $item['slot_number'] : null,
                'action'      => $item['action'] ?? null,
                'result'      => $item['result'] ?? ($type === 'transaction' ? 'granted' : null),
                'reason'      => Str::limit((string) ($item['reason'] ?? $item['notes'] ?? ''), 250, ''),
                'payload'     => $item,
                'occurred_at' => $occurredAt,
                'status'      => 'pending',
            ])->id;
            $staged++;
        }

        return ['staged' => $staged, 'duplicates' => $duplicates, 'ids' => $ids];
    }

    /**
     * Apply pending offline records to the live database in chronological order.
     *
     * @return array{synced:int, skipped:int}
     */
    public function applyPending(?User $admin = null, ?array $ids = null): array
    {
        $query = OfflineRecord::pending()->orderBy('occurred_at')->orderBy('id');
        if ($ids) {
            $query->whereIn('id', $ids);
        }

        $synced = 0;
        $skipped = 0;
        $touchedKeys = [];

        // These are past events - don't fire live security pop-ups while importing them
        AccessLog::$muteAlerts = true;

        try {
        foreach ($query->get() as $record) {
            try {
                DB::transaction(function () use ($record, $admin, &$synced, &$skipped, &$touchedKeys) {
                    [$ok, $note, $keyId] = $this->applyRecord($record);

                    $record->update([
                        'status'    => $ok ? 'synced' : 'skipped',
                        'sync_note' => $note,
                        'synced_at' => now(),
                        'synced_by' => $admin?->id,
                    ]);

                    $ok ? $synced++ : $skipped++;
                    if ($keyId) {
                        $touchedKeys[$keyId] = true;
                    }
                });
            } catch (\Throwable $e) {
                Log::error("[OFFLINE SYNC] Failed to apply record #{$record->id}: " . $e->getMessage());
                $record->update(['status' => 'skipped', 'sync_note' => 'Error: ' . Str::limit($e->getMessage(), 200)]);
                $skipped++;
            }
        }
        } finally {
            AccessLog::$muteAlerts = false;
        }

        // Notify live dashboards once per affected key
        foreach (array_keys($touchedKeys) as $keyId) {
            $key = Key::find($keyId);
            if ($key) {
                $this->safeBroadcast(fn () => KeyStatusUpdated::dispatch(
                    $key->id, $key->slot_number, $key->status, $key->key_name, $key->room_name, null
                ));
            }
        }

        return ['synced' => $synced, 'skipped' => $skipped];
    }

    /**
     * @return array{0:bool,1:string,2:?int}  [success, note, affected key id]
     */
    protected function applyRecord(OfflineRecord $record): array
    {
        $item = $record->payload ?? [];
        $at   = $record->occurred_at ?? now();

        if ($record->type === 'access_log') {
            $log = new AccessLog([
                'user_id'    => $record->user_id,
                'qr_token'   => $item['qr_token'] ?? 'OFFLINE_SCAN',
                'action'     => $record->action ?? 'scan',
                'result'     => in_array($record->result, ['granted', 'denied'], true) ? $record->result : 'granted',
                'reason'     => Str::limit(($record->reason ?: 'Offline scan event') . ' [Offline]', 250, ''),
                'ip_address' => $item['ip_address'] ?? null,
            ]);
            $log->created_at = $at;
            $log->updated_at = $at;
            $log->save();

            return [true, 'Access log recorded', null];
        }

        // ── Transactions (borrow / return) ──────────────────
        $key = null;
        if (!empty($item['key_id'])) {
            $key = Key::find($item['key_id']);
        }
        if (!$key && $record->slot_number) {
            $key = Key::where('slot_number', $record->slot_number)->first();
        }
        if (!$key) {
            return [false, 'Key slot no longer exists', null];
        }

        $user = $record->user_id ? User::find($record->user_id) : null;
        if (!$user) {
            return [false, 'User no longer exists', $key->id];
        }

        $notes = $item['notes'] ?? 'Recorded via Offline Mode';

        if ($record->action === 'borrow') {
            if ($user->role === 'admin') {
                return [false, 'Admin borrows are not tracked', $key->id];
            }

            $tx = new Transaction([
                'user_id'     => $user->id,
                'key_id'      => $key->id,
                'action'      => 'borrow',
                'status'      => 'success',
                'notes'       => $notes,
                'borrowed_at' => $at,
            ]);
            $tx->created_at = $at;
            $tx->updated_at = $at;
            $tx->save();

            $hasNewerOnlineActivity = $this->hasNewerActivity($key, $at);
            if (!$hasNewerOnlineActivity) {
                $key->update(['status' => 'borrowed']);
            }

            return [true, $hasNewerOnlineActivity ? 'Borrow recorded (newer online activity kept current status)' : 'Borrow recorded', $key->id];
        }

        if ($record->action === 'return') {
            $openBorrow = Transaction::where('user_id', $user->id)
                ->where('key_id', $key->id)
                ->where('action', 'borrow')
                ->whereNull('returned_at')
                ->whereRaw('COALESCE(borrowed_at, created_at) <= ?', [$at->toDateTimeString()])
                ->latest()
                ->first();

            $openBorrow?->update(['returned_at' => $at]);

            $tx = new Transaction([
                'user_id'     => $user->id,
                'key_id'      => $key->id,
                'action'      => 'return',
                'status'      => 'success',
                'notes'       => $notes,
                'returned_at' => $at,
            ]);
            $tx->created_at = $at;
            $tx->updated_at = $at;
            $tx->save();

            $hasNewerOnlineActivity = $this->hasNewerActivity($key, $at);
            if (!$hasNewerOnlineActivity) {
                $key->update(['status' => 'available']);
            }

            return [true, $openBorrow ? 'Return recorded' : 'Return recorded (no matching open borrow found)', $key->id];
        }

        return [false, 'Unknown transaction action', $key->id];
    }

    /**
     * Did something happen to this key online AFTER the offline event? If so, the live status wins.
     * Uses the real event time (returned_at / borrowed_at), not the row insert time.
     */
    protected function hasNewerActivity(Key $key, Carbon $at): bool
    {
        return Transaction::where('key_id', $key->id)
            ->whereRaw('COALESCE(returned_at, borrowed_at, created_at) > ?', [$at->toDateTimeString()])
            ->exists();
    }

    /**
     * Apply still-pending offline transactions on top of the live key map so the Pi's
     * offline cache stays consistent with what physically happened before the admin syncs.
     */
    public function overlayPendingOnKeyMap(array $keyMap): array
    {
        $pending = OfflineRecord::pending()
            ->where('type', 'transaction')
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        foreach ($pending as $record) {
            $slot = (int) $record->slot_number;
            if (!isset($keyMap[$slot])) {
                continue;
            }
            if ($record->action === 'borrow') {
                $keyMap[$slot]['status'] = 'borrowed';
                $keyMap[$slot]['borrowed_by_user_id'] = $record->user_id;
            } elseif ($record->action === 'return') {
                $keyMap[$slot]['status'] = 'available';
                $keyMap[$slot]['borrowed_by_user_id'] = null;
            }
        }

        return $keyMap;
    }

    protected function safeBroadcast(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning('[BROADCAST] Event dispatch skipped: ' . $e->getMessage());
        }
    }
}
