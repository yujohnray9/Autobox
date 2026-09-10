<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardwareApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected string $hardwareKey = 'autobox-sec-hw-token-ccsict-2026';

    public function test_hardware_request_succeeds_with_valid_hmac_signature(): void
    {
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', "{$timestamp}:", $this->hardwareKey);

        $response = $this->getJson('/api/keys', [
            'X-AUTOBOX-TIMESTAMP' => $timestamp,
            'X-AUTOBOX-SIGNATURE' => $signature,
        ]);

        $response->assertStatus(200);
    }

    public function test_hardware_request_fails_with_tampered_payload(): void
    {
        $timestamp = (string) time();
        $payload = ['qr_token' => 'original_token'];
        $rawBody = json_encode($payload);

        // Sign original payload
        $signature = hash_hmac('sha256', "{$timestamp}:{$rawBody}", $this->hardwareKey);

        // Tamper with payload in transit
        $tamperedPayload = ['qr_token' => 'tampered_token'];

        $response = $this->postJson('/api/authenticate-qr', $tamperedPayload, [
            'X-AUTOBOX-TIMESTAMP' => $timestamp,
            'X-AUTOBOX-SIGNATURE' => $signature,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'INVALID_SIGNATURE',
        ]);
    }

    public function test_hardware_request_fails_with_expired_timestamp(): void
    {
        // 10 minutes in the past (beyond 300s window)
        $expiredTimestamp = (string) (time() - 600);
        $signature = hash_hmac('sha256', "{$expiredTimestamp}:", $this->hardwareKey);

        $response = $this->getJson('/api/keys', [
            'X-AUTOBOX-TIMESTAMP' => $expiredTimestamp,
            'X-AUTOBOX-SIGNATURE' => $signature,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'TIMESTAMP_EXPIRED',
        ]);
    }

    public function test_hardware_request_succeeds_with_legacy_raw_api_key(): void
    {
        $response = $this->getJson('/api/keys', [
            'X-AUTOBOX-API-KEY' => $this->hardwareKey,
        ]);

        $response->assertStatus(200);
    }

    public function test_hardware_request_fails_without_authentication(): void
    {
        $response = $this->getJson('/api/keys');

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'UNAUTHORIZED',
        ]);
    }
}
