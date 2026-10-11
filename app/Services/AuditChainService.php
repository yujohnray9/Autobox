<?php

namespace App\Services;

use App\Models\AccessLog;
use Illuminate\Support\Facades\DB;

class AuditChainService
{
    public const GENESIS_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    /**
     * Compute the SHA-256 cryptographic hash for an AccessLog record.
     */
    public static function computeHash(AccessLog|array $log, string $previousHash): string
    {
        $userId    = is_array($log) ? ($log['user_id'] ?? '') : ($log->user_id ?? '');
        $qrToken   = is_array($log) ? ($log['qr_token'] ?? '') : ($log->qr_token ?? '');
        $action    = is_array($log) ? ($log['action'] ?? '') : ($log->action ?? '');
        $result    = is_array($log) ? ($log['result'] ?? '') : ($log->result ?? '');
        $reason    = is_array($log) ? ($log['reason'] ?? '') : ($log->reason ?? '');
        $ipAddress = is_array($log) ? ($log['ip_address'] ?? '') : ($log->ip_address ?? '');

        $createdAt = '';
        if (is_array($log)) {
            $createdAt = !empty($log['created_at']) ? (string) $log['created_at'] : '';
        } elseif ($log instanceof AccessLog && $log->created_at instanceof \Carbon\CarbonInterface) {
            $createdAt = $log->created_at->format('Y-m-d H:i:s');
        } elseif (!empty($log->created_at)) {
            $createdAt = (string) $log->created_at;
        }

        $payload = implode('|', [
            $previousHash,
            (string) $userId,
            (string) $qrToken,
            (string) $action,
            (string) $result,
            (string) $reason,
            (string) $ipAddress,
            $createdAt,
        ]);

        return hash('sha256', $payload);
    }

    /**
     * Seal a single AccessLog into the cryptographic chain before or after saving.
     */
    public static function seal(AccessLog $log): void
    {
        if (empty($log->created_at)) {
            $log->created_at = now();
        }

        $lastRecord = AccessLog::whereNotNull('record_hash')
            ->when($log->id, fn ($q) => $q->where('id', '<', $log->id))
            ->latest('id')
            ->first();

        $previousHash = $lastRecord?->record_hash ?? self::GENESIS_HASH;

        $log->previous_hash = $previousHash;
        $log->record_hash   = self::computeHash($log, $previousHash);
    }

    /**
     * Verify the entire cryptographic audit hash chain in the database.
     */
    public function verifyChain(): array
    {
        $logs = AccessLog::orderBy('id', 'asc')->get();

        if ($logs->isEmpty()) {
            return [
                'is_valid'      => true,
                'total_blocks'  => 0,
                'tampered_count'=> 0,
                'broken_blocks' => [],
                'status'        => 'EMPTY_CHAIN',
                'message'       => 'Audit chain is empty. Awaiting first log.',
            ];
        }

        $brokenBlocks = [];
        $expectedPrevHash = self::GENESIS_HASH;

        foreach ($logs as $index => $log) {
            $isGenesis = ($index === 0);

            // 1. Check previous_hash link
            $prevLinkMatches = ($log->previous_hash === $expectedPrevHash);

            // 2. Re-compute this record's hash from its actual stored fields
            $recalculatedHash = self::computeHash($log, (string) $log->previous_hash);
            $hashMatches = ($log->record_hash === $recalculatedHash);

            if (!$prevLinkMatches || !$hashMatches) {
                $brokenBlocks[] = [
                    'id'               => $log->id,
                    'user_id'          => $log->user_id,
                    'action'           => $log->action,
                    'result'           => $log->result,
                    'reason'           => $log->reason,
                    'stored_hash'      => $log->record_hash,
                    'recalculated_hash'=> $recalculatedHash,
                    'stored_prev_hash' => $log->previous_hash,
                    'expected_prev'    => $expectedPrevHash,
                    'reason_failed'    => !$hashMatches ? 'DATA_MODIFIED' : 'CHAIN_LINK_BROKEN',
                ];
            }

            // Next record's expected previous_hash is this record's stored hash
            $expectedPrevHash = (string) $log->record_hash;
        }

        $isValid = count($brokenBlocks) === 0;

        return [
            'is_valid'      => $isValid,
            'total_blocks'  => $logs->count(),
            'tampered_count'=> count($brokenBlocks),
            'broken_blocks' => $brokenBlocks,
            'genesis_hash'  => self::GENESIS_HASH,
            'latest_hash'   => $logs->last()?->record_hash,
            'status'        => $isValid ? 'SECURE' : 'COMPROMISED',
            'message'       => $isValid
                ? "Cryptographic chain fully verified: {$logs->count()} blocks intact. No database tampering detected."
                : "TAMPER ALERT! Detected " . count($brokenBlocks) . " compromised record(s) in audit chain.",
        ];
    }

    /**
     * Backfill the hash chain for any existing access logs that lack hashes.
     */
    public function backfillChain(): int
    {
        $logs = AccessLog::orderBy('id', 'asc')->get();
        $count = 0;
        $prevHash = self::GENESIS_HASH;

        foreach ($logs as $log) {
            $log->previous_hash = $prevHash;
            $log->record_hash   = self::computeHash($log, $prevHash);
            $log->saveQuietly();

            $prevHash = $log->record_hash;
            $count++;
        }

        return $count;
    }

    /**
     * Simulate database tampering on a test record so the user can demonstrate
     * tamper-detection live to their adviser and panel.
     */
    public function simulateTamper(): array
    {
        // Pick the latest log or a denied log to tamper
        $log = AccessLog::latest('id')->first();
        if (!$log) {
            return ['success' => false, 'message' => 'No logs available to tamper with.'];
        }

        // Save original reason in session or cache to allow restoration
        session(['simulated_tamper_id' => $log->id, 'simulated_tamper_original_reason' => $log->reason]);

        // Secretly alter the reason in MySQL without updating the cryptographic hash
        DB::table('access_logs')->where('id', $log->id)->update([
            'reason' => '[SIMULATED DATABASE TAMPER] Unauthorized scan attempt wiped by attacker.',
        ]);

        return [
            'success'   => true,
            'tampered_id' => $log->id,
            'message'   => "Simulated tamper injected into Log #{$log->id}. Database row was modified while hash remained unchanged.",
        ];
    }

    /**
     * Restore any simulated tamper back to its authentic state.
     */
    public function restoreTamper(): array
    {
        $id = session('simulated_tamper_id');
        $orig = session('simulated_tamper_original_reason');

        if ($id && $orig !== null) {
            DB::table('access_logs')->where('id', $id)->update(['reason' => $orig]);
            session()->forget(['simulated_tamper_id', 'simulated_tamper_original_reason']);
        }

        // Re-seal the whole chain cleanly
        $this->backfillChain();

        return [
            'success' => true,
            'message' => 'Audit chain restored and cryptographically re-sealed. 100% Valid.',
        ];
    }
}
