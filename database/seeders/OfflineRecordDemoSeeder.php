<?php

namespace Database\Seeders;

use App\Models\Key;
use App\Models\OfflineRecord;
use App\Models\User;
use App\Services\OfflineSyncService;
use Illuminate\Database\Seeder;

/**
 * Sample "the lock box was offline this morning" data for the Offline Records tab.
 *
 *   php artisan db:seed --class=OfflineRecordDemoSeeder
 *
 * Re-running is safe (records use fixed "demo-" ids, so duplicates are ignored).
 * Like a real device upload (auto-sync), the records are applied right away, so this
 * creates real (backdated) transactions and access logs for today.
 * Remove unsynced demo rows with:
 *   php artisan tinker --execute="App\Models\OfflineRecord::where('uid','like','demo-%')->where('status','pending')->delete();"
 */
class OfflineRecordDemoSeeder extends Seeder
{
    public function run(OfflineSyncService $sync): void
    {
        $faculty = User::where('role', '!=', 'admin')->where('is_active', true)->orderBy('id')->take(3)->get();
        $keys    = Key::orderBy('slot_number')->take(3)->get();

        if ($faculty->count() < 2 || $keys->count() < 2) {
            $this->command?->warn('Need at least 2 non-admin users and 2 key slots to create demo offline data.');
            return;
        }

        [$u1, $u2] = [$faculty[0], $faculty[1]];
        $u3 = $faculty[2] ?? $faculty[0];
        [$k1, $k2] = [$keys[0], $keys[1]];

        // Pretend the internet went down today from 7:30 to 9:15 AM
        $t = fn (string $time) => today()->setTimeFromTimeString($time)->toDateTimeString();
        $ip = '192.168.11.50';

        $logs = [
            // 07:42 — someone scans an unregistered QR code
            ['uid' => 'demo-01', 'type' => 'access_log', 'user_id' => null, 'qr_token' => 'UNKNOWN-QR-8F2A', 'action' => 'scan',
             'result' => 'denied', 'reason' => 'Invalid or inactive QR code (Offline)', 'timestamp' => $t('07:42:10'), 'ip_address' => $ip],

            // 07:55 — User 1 borrows Slot 1 (IR confirmed key taken)
            ['uid' => 'demo-02', 'type' => 'transaction', 'user_id' => $u1->id, 'key_id' => $k1->id, 'slot_number' => $k1->slot_number,
             'action' => 'borrow', 'notes' => 'Borrowed via Offline QR Scan (IR confirmed)', 'timestamp' => $t('07:55:03')],
            ['uid' => 'demo-03', 'type' => 'access_log', 'user_id' => $u1->id, 'qr_token' => $u1->qr_token, 'action' => 'borrow',
             'result' => 'granted', 'reason' => "Key taken from Slot #{$k1->slot_number} (IR confirmed)", 'slot_number' => $k1->slot_number,
             'timestamp' => $t('07:55:03'), 'ip_address' => $ip],

            // 08:10 — User 2 scans outside their schedule
            ['uid' => 'demo-04', 'type' => 'access_log', 'user_id' => $u2->id, 'qr_token' => $u2->qr_token, 'action' => 'borrow',
             'result' => 'denied', 'reason' => 'Access Denied: Outside schedule (' . strtolower(today()->format('l')) . ') (Offline)',
             'timestamp' => $t('08:10:47'), 'ip_address' => $ip],

            // 08:20 — User 3 borrows Slot 2
            ['uid' => 'demo-05', 'type' => 'transaction', 'user_id' => $u3->id, 'key_id' => $k2->id, 'slot_number' => $k2->slot_number,
             'action' => 'borrow', 'notes' => 'Borrowed via Offline QR Scan (IR confirmed)', 'timestamp' => $t('08:20:15')],
            ['uid' => 'demo-06', 'type' => 'access_log', 'user_id' => $u3->id, 'qr_token' => $u3->qr_token, 'action' => 'borrow',
             'result' => 'granted', 'reason' => "Key taken from Slot #{$k2->slot_number} (IR confirmed)", 'slot_number' => $k2->slot_number,
             'timestamp' => $t('08:20:15'), 'ip_address' => $ip],

            // 08:58 — User 1 scans to return but walks away; IR never sees the key -> NOT returned
            ['uid' => 'demo-07', 'type' => 'access_log', 'user_id' => $u1->id, 'qr_token' => $u1->qr_token, 'action' => 'return',
             'result' => 'denied', 'reason' => "Return NOT completed: IR sensor did not detect the key in Slot #{$k1->slot_number}",
             'slot_number' => $k1->slot_number, 'timestamp' => $t('08:58:30'), 'ip_address' => $ip],

            // 09:02 — User 1 tries again and properly returns Slot 1
            ['uid' => 'demo-08', 'type' => 'transaction', 'user_id' => $u1->id, 'key_id' => $k1->id, 'slot_number' => $k1->slot_number,
             'action' => 'return', 'notes' => 'Returned via Offline QR Scan (IR confirmed)', 'timestamp' => $t('09:02:11')],
            ['uid' => 'demo-09', 'type' => 'access_log', 'user_id' => $u1->id, 'qr_token' => $u1->qr_token, 'action' => 'return',
             'result' => 'granted', 'reason' => "Key returned to Slot #{$k1->slot_number} (IR confirmed)", 'slot_number' => $k1->slot_number,
             'timestamp' => $t('09:02:11'), 'ip_address' => $ip],

            // 09:10 — User 3 returns Slot 2
            ['uid' => 'demo-10', 'type' => 'transaction', 'user_id' => $u3->id, 'key_id' => $k2->id, 'slot_number' => $k2->slot_number,
             'action' => 'return', 'notes' => 'Returned via Offline QR Scan (IR confirmed)', 'timestamp' => $t('09:10:40')],
            ['uid' => 'demo-11', 'type' => 'access_log', 'user_id' => $u3->id, 'qr_token' => $u3->qr_token, 'action' => 'return',
             'result' => 'granted', 'reason' => "Key returned to Slot #{$k2->slot_number} (IR confirmed)", 'slot_number' => $k2->slot_number,
             'timestamp' => $t('09:10:40'), 'ip_address' => $ip],
        ];

        $result = $sync->stage($logs);

        // Auto-sync (Option B): apply everything waiting, exactly like the device upload does
        $applied = $sync->applyPending(null);

        $this->command?->info("Demo offline data: {$result['staged']} new record(s) staged, {$result['duplicates']} already existed.");
        $this->command?->info("Auto-synced: {$applied['synced']} applied, {$applied['skipped']} skipped.");
        $this->command?->info('Pending offline records now: ' . OfflineRecord::pending()->count());
    }
}
