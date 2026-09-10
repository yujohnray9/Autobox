<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateHardwareApiKey
{
    /**
     * Handle an incoming hardware request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.autobox.hardware_key') ?: env('AUTOBOX_HARDWARE_KEY', 'autobox-sec-hw-token-ccsict-2026');

        $signature = $request->header('X-AUTOBOX-SIGNATURE');
        $timestamp = $request->header('X-AUTOBOX-TIMESTAMP');

        // 1. Primary: HMAC-SHA256 Signature Verification with Anti-Replay Protection
        if ($signature && $timestamp) {
            $timeDiff = abs(time() - (int) $timestamp);

            // Replay protection window: 300 seconds (5 minutes) clock drift tolerance
            if ($timeDiff > 300) {
                Log::warning('[SECURITY ALERT] Hardware HMAC request timestamp expired (Replay blocked)', [
                    'ip'           => $request->ip(),
                    'timestamp'    => $timestamp,
                    'server_time'  => time(),
                    'diff_seconds' => $timeDiff,
                ]);

                return response()->json([
                    'success' => false,
                    'status'  => 'TIMESTAMP_EXPIRED',
                    'message' => 'Request timestamp expired or system clock is out of sync. Replay protection active.',
                ], 401);
            }

            // Expected signature: HMAC-SHA256(timestamp:body, secret_key)
            $rawBody = (string) $request->getContent();
            if (($request->isMethod('GET') || $request->isMethod('HEAD')) && $rawBody === '[]') {
                $rawBody = '';
            }

            $dataToSign = "{$timestamp}:{$rawBody}";
            $expectedSignature = hash_hmac('sha256', $dataToSign, (string) $expectedKey);

            if (hash_equals($expectedSignature, (string) $signature)) {
                return $next($request);
            }

            Log::warning('[SECURITY ALERT] Hardware HMAC signature mismatch (Data integrity failure)', [
                'ip'        => $request->ip(),
                'url'       => $request->fullUrl(),
                'timestamp' => $timestamp,
            ]);

            return response()->json([
                'success' => false,
                'status'  => 'INVALID_SIGNATURE',
                'message' => 'Hardware cryptographic signature mismatch. Payload may have been tampered with.',
            ], 401);
        }

        // 2. Fallback: Direct API Key check (Backward compatibility for local testing)
        $providedKey = $request->header('X-AUTOBOX-API-KEY');
        if (!$providedKey) {
            $authHeader = $request->header('Authorization', '');
            if (str_starts_with($authHeader, 'Bearer ')) {
                $providedKey = substr($authHeader, 7);
            }
        }

        if ($providedKey && hash_equals((string) $expectedKey, (string) $providedKey)) {
            return $next($request);
        }

        Log::warning('[SECURITY ALERT] Unauthorized hardware API access attempt', [
            'ip'         => $request->ip(),
            'url'        => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'has_key'    => !empty($providedKey),
        ]);

        return response()->json([
            'success' => false,
            'status'  => 'UNAUTHORIZED',
            'message' => 'Unauthorized hardware access. Valid HMAC signature or API key required.',
        ], 401);
    }
}
