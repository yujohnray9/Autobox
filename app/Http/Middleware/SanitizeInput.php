<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Fields that should never be altered (e.g. passwords).
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    /**
     * Handle an incoming request and sanitize inputs against XSS and injection attacks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($value) && !in_array($key, $this->except, true)) {
                // 1. Remove null-byte characters (null-byte poisoning)
                $value = str_replace(chr(0), '', $value);

                // 2. Strip dangerous script tags and malicious event handlers
                $value = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);
                $value = preg_replace('#javascript:#is', '', $value);

                // 3. Strip raw HTML tags to prevent stored XSS attacks
                $value = strip_tags($value);

                // 4. Normalize and trim whitespace
                $value = trim($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
