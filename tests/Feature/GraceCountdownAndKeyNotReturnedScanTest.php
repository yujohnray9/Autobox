<?php

namespace Tests\Feature;

use App\Models\Key;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraceCountdownAndKeyNotReturnedScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_in_10_min_grace_period_is_not_marked_missing(): void
    {
        $today = strtolower(now()->format('l'));

        $user1 = User::create([
            'name'      => 'User 1',
            'email'     => 'user1@autobox.test',
            'password'  => bcrypt('password'),
            'role'      => 'faculty',
            'qr_token'  => 'QR_USER_1',
            'is_active' => true,
        ]);

        $key = Key::create([
            'key_name'    => 'Science Lab 101',
            'room_name'   => 'Room 101',
            'slot_number' => 1,
            'status'      => 'borrowed',
            'is_active'   => true,
        ]);

        Schedule::create([
            'user_id'     => $user1->id,
            'key_id'      => $key->id,
            'day_of_week' => $today,
            'start_time'  => '08:00:00',
            'end_time'    => '09:00:00',
            'is_active'   => true,
        ]);

        Transaction::create([
            'user_id'     => $user1->id,
            'key_id'      => $key->id,
            'action'      => 'borrow',
            'status'      => 'success',
            'borrowed_at' => Carbon::today()->setTime(8, 0, 0),
        ]);

        // Freeze time at 09:05:00 (5 minutes after schedule end, inside 10-min countdown)
        Carbon::setTestNow(Carbon::today()->setTime(9, 5, 0));

        $info = $key->getScheduleStatusInfo();
        $this->assertNotNull($info);
        $this->assertTrue($info['in_grace']);
        $this->assertEquals(300, $info['seconds_left']);
        $this->assertEquals('09:00 AM', $info['schedule_end']);

        $this->artisan('autobox:check-unreturned')
            ->assertSuccessful();

        $key->refresh();
        $this->assertEquals('borrowed', $key->status, 'Key must NOT be marked missing within the 10-minute grace period');
    }

    public function test_key_past_10_min_grace_period_is_marked_missing(): void
    {
        $today = strtolower(now()->format('l'));

        $user1 = User::create([
            'name'      => 'User 1',
            'email'     => 'user1@autobox.test',
            'password'  => bcrypt('password'),
            'role'      => 'faculty',
            'qr_token'  => 'QR_USER_1',
            'is_active' => true,
        ]);

        $key = Key::create([
            'key_name'    => 'Science Lab 101',
            'room_name'   => 'Room 101',
            'slot_number' => 1,
            'status'      => 'borrowed',
            'is_active'   => true,
        ]);

        Schedule::create([
            'user_id'     => $user1->id,
            'key_id'      => $key->id,
            'day_of_week' => $today,
            'start_time'  => '08:00:00',
            'end_time'    => '09:00:00',
            'is_active'   => true,
        ]);

        Transaction::create([
            'user_id'     => $user1->id,
            'key_id'      => $key->id,
            'action'      => 'borrow',
            'status'      => 'success',
            'borrowed_at' => Carbon::today()->setTime(8, 0, 0),
        ]);

        // Freeze time at 09:11:00 (11 minutes after schedule end, 10-min grace has expired)
        Carbon::setTestNow(Carbon::today()->setTime(9, 11, 0));

        $info = $key->getScheduleStatusInfo();
        $this->assertNotNull($info);
        $this->assertFalse($info['in_grace']);
        $this->assertEquals('overdue', $info['state']);

        $this->artisan('autobox:check-unreturned')
            ->assertSuccessful();

        $key->refresh();
        $this->assertEquals('missing', $key->status, 'Key must be marked missing once 10-minute grace period has passed');
    }

    protected function authHeaders(array $payload = []): array
    {
        $hardwareKey = 'autobox-sec-hw-token-ccsict-2026';
        $timestamp = (string) time();
        $rawBody = !empty($payload) ? json_encode($payload) : '';
        $signature = hash_hmac('sha256', "{$timestamp}:{$rawBody}", $hardwareKey);

        return [
            'X-AUTOBOX-TIMESTAMP' => $timestamp,
            'X-AUTOBOX-SIGNATURE' => $signature,
        ];
    }

    public function test_manual_web_borrow_without_schedule_marks_missing_after_10_minutes(): void
    {
        $user = User::create([
            'name'      => 'Web Borrower',
            'email'     => 'borrower@autobox.test',
            'password'  => bcrypt('password'),
            'role'      => 'faculty',
            'qr_token'  => 'QR_MANUAL_1',
            'is_active' => true,
        ]);

        $key = Key::create([
            'key_name'    => 'Demo Room 101',
            'room_name'   => 'Room 1',
            'slot_number' => 3,
            'status'      => 'borrowed',
            'is_active'   => true,
        ]);

        // Borrowed at 10:00:00 without any schedule
        $borrowTime = Carbon::today()->setTime(10, 0, 0);
        Transaction::create([
            'user_id'     => $user->id,
            'key_id'      => $key->id,
            'action'      => 'borrow',
            'status'      => 'success',
            'borrowed_at' => $borrowTime,
        ]);

        // At 10:05:00 (5 minutes after borrow): should be in grace period
        Carbon::setTestNow($borrowTime->copy()->addMinutes(5));
        $info = $key->getScheduleStatusInfo();
        $this->assertNotNull($info);
        $this->assertTrue($info['in_grace']);
        $this->assertEquals(300, $info['seconds_left']);

        $this->artisan('autobox:check-unreturned')->assertSuccessful();
        $key->refresh();
        $this->assertEquals('borrowed', $key->status, 'Key must remain borrowed at 5 minutes');

        // At 10:11:00 (11 minutes after borrow): 10-minute return window expired
        Carbon::setTestNow($borrowTime->copy()->addMinutes(11));
        $infoExpired = $key->getScheduleStatusInfo();
        $this->assertNotNull($infoExpired);
        $this->assertFalse($infoExpired['in_grace']);
        $this->assertEquals('overdue', $infoExpired['state']);

        $this->artisan('autobox:check-unreturned')->assertSuccessful();
        $key->refresh();
        $this->assertEquals('missing', $key->status, 'Key must be marked missing after 10 minutes');
    }

    public function test_next_user_gets_key_has_not_return_yet_when_scanning_unreturned_key(): void
    {
        $today = strtolower(now()->format('l'));

        $user1 = User::create([
            'name'      => 'User 1 (8-9 AM)',
            'email'     => 'user1@autobox.test',
            'password'  => bcrypt('password'),
            'role'      => 'faculty',
            'qr_token'  => 'QR_USER_1',
            'is_active' => true,
        ]);

        $user2 = User::create([
            'name'      => 'User 2 (9-10 AM)',
            'email'     => 'user2@autobox.test',
            'password'  => bcrypt('password'),
            'role'      => 'faculty',
            'qr_token'  => 'QR_USER_2',
            'is_active' => true,
        ]);

        $key = Key::create([
            'key_name'    => 'Room 202 Key',
            'room_name'   => 'Room 202',
            'slot_number' => 2,
            'status'      => 'borrowed',
            'is_active'   => true,
        ]);

        Schedule::create([
            'user_id'     => $user1->id,
            'key_id'      => $key->id,
            'day_of_week' => $today,
            'start_time'  => '08:00:00',
            'end_time'    => '09:00:00',
            'is_active'   => true,
        ]);

        Schedule::create([
            'user_id'     => $user2->id,
            'key_id'      => $key->id,
            'day_of_week' => $today,
            'start_time'  => '09:00:00',
            'end_time'    => '10:00:00',
            'is_active'   => true,
        ]);

        Transaction::create([
            'user_id'     => $user1->id,
            'key_id'      => $key->id,
            'action'      => 'borrow',
            'status'      => 'success',
            'borrowed_at' => Carbon::today()->setTime(8, 0, 0),
        ]);

        // It is now 09:05:00. User 2 scans their QR code.
        Carbon::setTestNow(Carbon::today()->setTime(9, 5, 0));

        $payload2 = ['qr_token' => 'QR_USER_2'];
        $response = $this->postJson('/api/authenticate-qr', $payload2, $this->authHeaders($payload2));

        $response->assertStatus(409)
            ->assertJson([
                'success'    => false,
                'status'     => 'DENIED',
                'error_code' => 'KEY_NOT_RETURNED',
                'message'    => 'Key has not Return Yet',
                'lcd_line1'  => 'Key has not',
                'lcd_line2'  => 'Return Yet',
            ]);

        // Now User 1 returns the key
        $payload1 = ['qr_token' => 'QR_USER_1'];
        $returnResponse = $this->postJson('/api/authenticate-qr', $payload1, $this->authHeaders($payload1));

        $returnResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 'GRANTED',
                'action'  => 'RETURN',
            ]);

        $key->refresh();
        $this->assertEquals('available', $key->status);

        // Now User 2 scans again: Key is available, access granted
        $borrowResponse = $this->postJson('/api/authenticate-qr', $payload2, $this->authHeaders($payload2));

        $borrowResponse->assertStatus(200)
            ->assertJson([
                'success'     => true,
                'status'      => 'GRANTED',
                'action'      => 'BORROW',
                'slot_number' => 2,
            ]);

        $key->refresh();
        $this->assertEquals('borrowed', $key->status);
    }
}
