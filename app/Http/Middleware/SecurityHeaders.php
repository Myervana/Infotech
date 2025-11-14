<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // X-Frame-Options: Prevents clickjacking attacks
        // DENY: Page cannot be displayed in a frame at all
        // SAMEORIGIN: Page can only be displayed in a frame on the same origin
        $response->headers->set('X-Frame-Options', 'DENY', true);

        // Additional security headers for better protection
        $response->headers->set('X-Content-Type-Options', 'nosniff', true);
        $response->headers->set('X-XSS-Protection', '1; mode=block', true);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', true);

        return $response;
    }
}

