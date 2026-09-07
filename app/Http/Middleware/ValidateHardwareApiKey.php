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

        // Extract API key from custom header or Authorization Bearer header
        $providedKey = $request->header('X-AUTOBOX-API-KEY');

        if (!$providedKey) {
            $authHeader = $request->header('Authorization', '');
            if (str_starts_with($authHeader, 'Bearer ')) {
                $providedKey = substr($authHeader, 7);
            }
        }

        // Timing-safe comparison to prevent timing-based attacks
        if (!$providedKey || !hash_equals((string) $expectedKey, (string) $providedKey)) {
            Log::warning('[SECURITY ALERT] Unauthorized hardware API access attempt', [
                'ip'         => $request->ip(),
                'url'        => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'has_key'    => !empty($providedKey),
            ]);

            return response()->json([
                'success' => false,
                'status'  => 'UNAUTHORIZED',
                'message' => 'Unauthorized hardware access. Valid API key required.',
            ], 401);
        }

        return $next($request);
    }
}
