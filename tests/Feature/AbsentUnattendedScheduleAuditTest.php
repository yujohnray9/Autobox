<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Key;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\AccessLog;
use App\Services\AuditChainService;
use App\Console\Commands\CheckUnattendedSchedules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsentUnattendedScheduleAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_unattended_schedule_is_detected_and_logged_as_absent()
    {
        $user = User::factory()->create([
            'name'     => 'Prof. Alan Turing',
            'role'     => 'faculty',
            'qr_token' => 'qr-turing-test-token',
        ]);

        $key = Key::create([
            'key_name'    => 'Key Room 101',
            'room_name'   => 'Room 101',
            'slot_number' => 10,
            'status'      => 'available',
        ]);

        $today = strtolower(now()->format('l'));

        $schedule = Schedule::create([
            'user_id'     => $user->id,
            'key_id'      => $key->id,
            'day_of_week' => $today,
            'start_time'  => '08:00',
            'end_time'    => '10:00',
            'is_active'   => true,
        ]);

        // Run scan with force = true
        $loggedCount = CheckUnattendedSchedules::scanUnattendedSchedules(true);

        $this->assertEquals(1, $loggedCount);

        // Verify AccessLog record
        $log = AccessLog::where('user_id', $user->id)
            ->where('action', 'absent')
            ->first();

        $this->assertNotNull($log, 'An absent access log must be created.');
        $this->assertEquals('denied', $log->result);
        $this->assertStringContainsString('Absent / Did Not Attend Class', $log->reason);
        $this->assertStringContainsString('Room 101', $log->reason);
        $this->assertStringContainsString(now()->format('M d, Y'), $log->reason);
        $this->assertNotEmpty($log->record_hash, 'Log must be cryptographically sealed.');

        // Verify cryptographic hash chain
        $auditChain = new AuditChainService();
        $verification = $auditChain->verifyChain();
        $this->assertTrue($verification['is_valid'], 'Cryptographic chain must remain valid.');
    }

    public function test_attended_schedule_with_borrow_does_not_create_absent_log()
    {
        $user = User::factory()->create([
            'name'     => 'Prof. Ada Lovelace',
            'role'     => 'faculty',
            'qr_token' => 'qr-lovelace-test-token',
        ]);

        $key = Key::create([
            'key_name'    => 'Key Room 102',
            'room_name'   => 'Room 102',
            'slot_number' => 11,
            'status'      => 'borrowed',
        ]);

        $today = strtolower(now()->format('l'));

        Schedule::create([
            'user_id'     => $user->id,
            'key_id'      => $key->id,
            'day_of_week' => $today,
            'start_time'  => '08:00',
            'end_time'    => '10:00',
            'is_active'   => true,
        ]);

        // User actually borrowed the key
        Transaction::create([
            'user_id'     => $user->id,
            'key_id'      => $key->id,
            'action'      => 'borrow',
            'status'      => 'success',
            'borrowed_at' => now(),
        ]);

        $loggedCount = CheckUnattendedSchedules::scanUnattendedSchedules(true);

        $this->assertEquals(0, $loggedCount);
        $this->assertDatabaseMissing('access_logs', [
            'user_id' => $user->id,
            'action'  => 'absent',
        ]);
    }

    public function test_deduplication_prevents_duplicate_absent_logs()
    {
        $user = User::factory()->create([
            'name'     => 'Prof. John von Neumann',
            'role'     => 'faculty',
            'qr_token' => 'qr-neumann-test-token',
        ]);

        $key = Key::create([
            'key_name'    => 'Key Room 103',
            'room_name'   => 'Room 103',
            'slot_number' => 12,
            'status'      => 'available',
        ]);

        $today = strtolower(now()->format('l'));

        Schedule::create([
            'user_id'     => $user->id,
            'key_id'      => $key->id,
            'day_of_week' => $today,
            'start_time'  => '09:00',
            'end_time'    => '11:00',
            'is_active'   => true,
        ]);

        // First scan logs the absent user
        $firstCount = CheckUnattendedSchedules::scanUnattendedSchedules(true);
        $this->assertEquals(1, $firstCount);

        // Second scan skips already logged schedule
        $secondCount = CheckUnattendedSchedules::scanUnattendedSchedules(true);
        $this->assertEquals(0, $secondCount);

        $this->assertEquals(1, AccessLog::where('user_id', $user->id)->where('action', 'absent')->count());
    }

    public function test_web_endpoint_triggers_unattended_check_and_redirects()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->post(route('access-logs.check-unattended'), [
                'force' => true,
            ]);

        $response->assertRedirect(route('access-logs.index'));
        $response->assertSessionHas('success');
    }
}
