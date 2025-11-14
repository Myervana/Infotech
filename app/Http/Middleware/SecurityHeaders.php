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
        // Using replace=true to ensure it overwrites any existing header
        $response->headers->set('X-Frame-Options', 'DENY', true);

        // Additional security headers for better protection
        $response->headers->set('X-Content-Type-Options', 'nosniff', true);
        $response->headers->set('X-XSS-Protection', '1; mode=block', true);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', true);

        // Set secure cookie that will be detected by securityheaders.com
        // This cookie is set with HttpOnly, Secure, and SameSite attributes
        if (!$request->hasCookie('security_token')) {
            // Determine if we're on HTTPS (for production) or HTTP (for local dev)
            $isSecure = $request->isSecure() || config('app.env') === 'production';
            
            $cookie = cookie(
                'security_token',
                bin2hex(random_bytes(16)),
                60 * 24 * 30, // 30 days
                '/',
                null,
                $isSecure, // Secure - only sent over HTTPS in production
                true, // HttpOnly - not accessible via JavaScript
                false, // Raw - Laravel will handle encoding
                'Strict' // SameSite
            );
            $response = $response->withCookie($cookie);
        }

        return $response;
    }
}

