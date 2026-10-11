<?php

namespace Tests\Feature;

use App\Models\AccessLog;
use App\Models\User;
use App\Services\AuditChainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditChainIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_access_logs_are_automatically_sealed_with_sha256_hash(): void
    {
        $log1 = AccessLog::create([
            'qr_token' => 'token-123',
            'action'   => 'scan',
            'result'   => 'granted',
            'reason'   => 'Valid schedule',
        ]);

        $this->assertNotNull($log1->record_hash);
        $this->assertEquals(AuditChainService::GENESIS_HASH, $log1->previous_hash);

        $log2 = AccessLog::create([
            'qr_token' => 'token-456',
            'action'   => 'scan',
            'result'   => 'denied',
            'reason'   => 'Unregistered QR',
        ]);

        $this->assertNotNull($log2->record_hash);
        $this->assertEquals($log1->record_hash, $log2->previous_hash);

        $service = new AuditChainService();
        $status = $service->verifyChain();

        $this->assertTrue($status['is_valid']);
        $this->assertEquals(2, $status['total_blocks']);
        $this->assertEquals(0, $status['tampered_count']);
    }

    public function test_tampered_database_row_is_detected_by_cryptographic_verification(): void
    {
        AccessLog::create([
            'qr_token' => 'token-1',
            'action'   => 'scan',
            'result'   => 'denied',
            'reason'   => 'Tamper test',
        ]);

        $service = new AuditChainService();
        $this->assertTrue($service->verifyChain()['is_valid']);

        // Simulate database attack
        $service->simulateTamper();

        $tamperedStatus = $service->verifyChain();
        $this->assertFalse($tamperedStatus['is_valid']);
        $this->assertEquals(1, $tamperedStatus['tampered_count']);

        // Restore
        $service->restoreTamper();
        $this->assertTrue($service->verifyChain()['is_valid']);
    }

    public function test_admin_can_access_integrity_verification_endpoint(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('access-logs.integrity'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'is_valid',
            'total_blocks',
            'tampered_count',
            'status',
            'message',
        ]);
    }
}
