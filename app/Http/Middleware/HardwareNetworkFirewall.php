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

        // Resolve real client IP even behind Hostinger reverse proxy or Cloudflare
        $clientIp = $request->header('cf-connecting-ip')
            ?: ($request->header('x-real-ip')
            ?: ($request->header('x-forwarded-for') ? trim(explode(',', $request->header('x-forwarded-for'))[0]) : (string) $request->ip()));

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
                'success'     => false,
                'status'      => 'FIREWALL_BLOCKED',
                'message'     => 'Access Denied: Your device IP is not permitted to communicate with Autobox hardware.',
                'detected_ip' => $clientIp,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if a client IP matches an allowed pattern (exact, wildcard, or CIDR).
     */
    protected function ipMatches(string $clientIp, string $pattern): bool
    {
        $clientIp = strtolower(trim($clientIp));
        $pattern  = strtolower(trim($pattern));

        // 1. Exact match (IPv4 or IPv6)
        if ($clientIp === $pattern) {
            return true;
        }

        // 2. Wildcard pattern match (e.g. 192.168.11.* or 2001:fd8:2aac:51c4:*)
        if (str_contains($pattern, '*') && fnmatch($pattern, $clientIp, FNM_CASEFOLD)) {
            return true;
        }

        // 3. Prefix matching for IPv6 (e.g. 2001:fd8:2aac:51c4)
        if (str_contains($clientIp, ':') && !str_contains($pattern, '/') && str_starts_with($clientIp, rtrim($pattern, ':*'))) {
            return true;
        }

        // 4. CIDR subnet match (IPv4 and IPv6)
        if (str_contains($pattern, '/')) {
            [$subnet, $bits] = explode('/', $pattern, 2);
            $bits = (int) $bits;

            // IPv4 CIDR
            if (filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipLong = ip2long($clientIp);
                $subnetLong = ip2long($subnet);
                $mask = -1 << (32 - $bits);
                return ($ipLong & $mask) === ($subnetLong & $mask);
            }

            // IPv6 CIDR
            if (filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) &&
                filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $ipBin = inet_pton($clientIp);
                $subnetBin = inet_pton($subnet);
                if ($ipBin !== false && $subnetBin !== false) {
                    $bytes = (int) ($bits / 8);
                    $remBits = $bits % 8;
                    if (substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
                        return false;
                    }
                    if ($remBits > 0) {
                        $mask = chr(0xFF << (8 - $remBits));
                        return (ord($ipBin[$bytes]) & ord($mask)) === (ord($subnetBin[$bytes]) & ord($mask));
                    }
                    return true;
                }
            }
        }

        return false;
    }
}
