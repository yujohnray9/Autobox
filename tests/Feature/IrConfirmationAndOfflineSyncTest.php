<?php

namespace Tests\Feature;

use App\Models\AccessLog;
use App\Models\Key;
use App\Models\OfflineRecord;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IrConfirmationAndOfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function authHeaders(array $payload = []): array
    {
        $timestamp = (string) time();
        $rawBody = !empty($payload) ? json_encode($payload) : '';

        return [
            'X-AUTOBOX-TIMESTAMP' => $timestamp,
            'X-AUTOBOX-SIGNATURE' => hash_hmac('sha256', "{$timestamp}:{$rawBody}", 'autobox-sec-hw-token-ccsict-2026'),
        ];
    }

    protected function makeBorrowedKey(): array
    {
        $user = User::create([
            'name' => 'Prof. Borrower', 'email' => 'borrower@autobox.test', 'password' => bcrypt('x'),
            'role' => 'faculty', 'qr_token' => 'QR_BORROWER', 'is_active' => true,
        ]);
        $key = Key::create(['key_name' => 'Lab Key', 'room_name' => 'Room 1', 'slot_number' => 1, 'status' => 'borrowed']);
        Transaction::create([
            'user_id' => $user->id, 'key_id' => $key->id, 'action' => 'borrow',
            'status' => 'success', 'borrowed_at' => now()->subMinutes(30),
        ]);

        return [$user, $key];
    }

    protected function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@autobox.test', 'password' => bcrypt('x'),
            'role' => 'admin', 'is_active' => true,
        ]);
    }

    public function test_return_scan_without_ir_detection_keeps_key_borrowed(): void
    {
        [, $key] = $this->makeBorrowedKey();

        $scan = ['qr_token' => 'QR_BORROWER'];
        $res = $this->postJson('/api/authenticate-qr', $scan, $this->authHeaders($scan))
            ->assertOk()->assertJson(['action' => 'RETURN']);

        $confirm = ['pending_token' => $res->json('pending_token'), 'detected' => false];
        $this->postJson('/api/confirm-action', $confirm, $this->authHeaders($confirm))
            ->assertOk()->assertJson(['committed' => false]);

        $this->assertEquals('borrowed', $key->fresh()->status);
        $this->assertDatabaseHas('transactions', ['key_id' => $key->id, 'action' => 'borrow', 'returned_at' => null]);
        $this->assertDatabaseMissing('transactions', ['key_id' => $key->id, 'action' => 'return']);
        $this->assertDatabaseHas('access_logs', ['action' => 'return', 'result' => 'denied']);
    }

    public function test_pending_token_cannot_be_confirmed_twice(): void
    {
        $this->makeBorrowedKey();

        $scan = ['qr_token' => 'QR_BORROWER'];
        $token = $this->postJson('/api/authenticate-qr', $scan, $this->authHeaders($scan))->json('pending_token');

        $confirm = ['pending_token' => $token, 'detected' => true];
        $this->postJson('/api/confirm-action', $confirm, $this->authHeaders($confirm))->assertOk();
        $this->postJson('/api/confirm-action', $confirm, $this->authHeaders($confirm))->assertStatus(410);

        $this->assertEquals(1, Transaction::where('action', 'return')->count());
    }

    public function test_offline_upload_is_applied_automatically(): void
    {
        [$user, $key] = $this->makeBorrowedKey();
        $admin = $this->admin();

        $logs = ['logs' => [
            ['uid' => 'u-1', 'type' => 'transaction', 'user_id' => $user->id, 'key_id' => $key->id, 'slot_number' => 1,
             'action' => 'return', 'notes' => 'Returned via Offline QR Scan (IR confirmed)', 'timestamp' => now()->subMinutes(5)->toDateTimeString()],
            ['uid' => 'u-2', 'type' => 'access_log', 'user_id' => null, 'qr_token' => 'BAD', 'action' => 'scan',
             'result' => 'denied', 'reason' => 'Invalid or inactive QR code (Offline)', 'timestamp' => now()->subMinutes(4)->toDateTimeString()],
        ]];

        $this->postJson('/api/sync-offline-logs', $logs, $this->authHeaders($logs))
            ->assertOk()->assertJson(['staged' => 2, 'duplicates' => 0, 'applied' => 2]);

        // Applied right away - no admin click needed
        $this->assertEquals('available', $key->fresh()->status);
        $this->assertEquals(0, OfflineRecord::pending()->count());
        $this->assertDatabaseHas('transactions', ['key_id' => $key->id, 'action' => 'return']);
        $this->assertNotNull(Transaction::where('key_id', $key->id)->where('action', 'borrow')->first()->returned_at);

        // Retried upload must not duplicate
        $this->postJson('/api/sync-offline-logs', $logs, $this->authHeaders($logs))
            ->assertOk()->assertJson(['staged' => 0, 'duplicates' => 2]);
        $this->assertEquals(1, Transaction::where('action', 'return')->count());

        $log = AccessLog::where('qr_token', 'BAD')->first();
        $this->assertNotNull($log);
        $this->assertEquals(now()->subMinutes(4)->format('Y-m-d H:i'), $log->created_at->format('Y-m-d H:i'), 'Offline log keeps its original time');

        // Retry Sync endpoint still works (nothing left to do)
        $this->actingAs($admin)->getJson(route('offline.pending'))->assertOk()->assertJson(['count' => 0]);
        $this->actingAs($admin)->postJson(route('offline.sync'))->assertOk();
    }

    public function test_offline_borrow_uploaded_before_scan_lets_return_work(): void
    {
        // Prof borrows offline at 8:00, internet returns, he scans to return later
        $user = User::create([
            'name' => 'Prof. Juan', 'email' => 'juan@autobox.test', 'password' => bcrypt('x'),
            'role' => 'faculty', 'qr_token' => 'QR_JUAN', 'is_active' => true,
        ]);
        $key = Key::create(['key_name' => 'Room 1 Key', 'room_name' => 'Room 1', 'slot_number' => 1, 'status' => 'available']);

        $logs = ['logs' => [
            ['uid' => 'b-1', 'type' => 'transaction', 'user_id' => $user->id, 'key_id' => $key->id, 'slot_number' => 1,
             'action' => 'borrow', 'notes' => 'Borrowed via Offline QR Scan (IR confirmed)', 'timestamp' => now()->subMinutes(90)->toDateTimeString()],
        ]];
        $this->postJson('/api/sync-offline-logs', $logs, $this->authHeaders($logs))->assertOk();

        // No active schedule covers "now", so the overdue offline borrow is flagged missing immediately
        $this->assertEquals('missing', $key->fresh()->status);

        // The scan is now recognised as a RETURN, not a new borrow
        $scan = ['qr_token' => 'QR_JUAN'];
        $this->postJson('/api/authenticate-qr', $scan, $this->authHeaders($scan))
            ->assertOk()->assertJson(['action' => 'RETURN']);
    }

    public function test_unknown_qr_shows_in_alert_feed_and_dashboard(): void
    {
        $admin = $this->admin();

        $scan = ['qr_token' => 'NOT_A_REAL_QR'];
        $this->postJson('/api/authenticate-qr', $scan, $this->authHeaders($scan))->assertStatus(401);

        $this->actingAs($admin)->getJson(route('alerts.feed'))
            ->assertOk()
            ->assertJsonPath('today_count', 1)
            ->assertJsonPath('alerts.0.who', 'Unknown QR Code')
            ->assertJsonPath('alerts.0.category', 'Unregistered QR');

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unauthorized Access Attempts')
            ->assertSee('manila-clock', false);

        $this->actingAs($admin)->get(route('offline.index'))
            ->assertOk()
            ->assertSee('What Happened While Offline');
    }
}
