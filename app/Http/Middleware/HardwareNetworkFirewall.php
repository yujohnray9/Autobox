<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HardwareNetworkFirewall
{
    /**
     * Handle an incoming hardware request and enforce IP whitelisting.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIpsConfig = config('services.autobox.allowed_hardware_ips') 
            ?: env('AUTOBOX_ALLOWED_HARDWARE_IPS', '');

        if (empty(trim((string) $allowedIpsConfig)) || trim((string) $allowedIpsConfig) === '*') {
            return $next($request);
        }

        $allowedList = array_filter(array_map('trim', explode(',', (string) $allowedIpsConfig)));
        $clientIp = (string) $request->ip();

        if (in_array($clientIp, ['127.0.0.1', '::1'], true)) {
            return $next($request);
        }

        $isAllowed = false;
        foreach ($allowedList as $allowed) {
            if ($this->ipMatches($clientIp, $allowed)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            Log::warning('[FIREWALL BLOCKED] Unauthorized device attempted hardware API access', [
                'blocked_ip'  => $clientIp,
                'endpoint'    => $request->path(),
                'method'      => $request->method(),
                'user_agent'  => $request->userAgent(),
                'allowed_ips' => $allowedList,
            ]);

            return response()->json([
                'success' => false,
                'status'  => 'FIREWALL_BLOCKED',
                'message' => 'Access Denied: Your device IP is not permitted to communicate with Autobox hardware.',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if a client IP matches an allowed pattern (exact, wildcard, or CIDR).
     */
    protected function ipMatches(string $clientIp, string $pattern): bool
    {
        // 1. Exact match
        if ($clientIp === $pattern) {
            return true;
        }

        // 2. Wildcard pattern match (e.g. 192.168.11.*)
        if (str_contains($pattern, '*') && fnmatch($pattern, $clientIp)) {
            return true;
        }

        // 3. CIDR subnet match (e.g. 192.168.11.0/24)
        if (str_contains($pattern, '/')) {
            [$subnet, $bits] = explode('/', $pattern, 2);
            if (filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipLong = ip2long($clientIp);
                $subnetLong = ip2long($subnet);
                $mask = -1 << (32 - (int) $bits);
                return ($ipLong & $mask) === ($subnetLong & $mask);
            }
        }

        return false;
    }
}
