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
        // This is required by securityheaders.com
        $response->headers->set('X-Frame-Options', 'DENY', true);

        // X-Content-Type-Options: Prevents MIME type sniffing
        // Required by securityheaders.com
        $response->headers->set('X-Content-Type-Options', 'nosniff', true);

        // Referrer-Policy: Controls referrer information
        // Required by securityheaders.com
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', true);

        // Content-Security-Policy: Prevents XSS and other injection attacks
        // Required by securityheaders.com
        // Updated to allow all external resources used in the application
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
               "https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com " .
               "https://maps.googleapis.com https://nominatim.openstreetmap.org " .
               "https://server.arcgisonline.com https://www.google.com; " .
               "style-src 'self' 'unsafe-inline' " .
               "https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com " .
               "https://fonts.googleapis.com; " .
               "img-src 'self' data: https: blob:; " .
               "font-src 'self' data: " .
               "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com; " .
               "connect-src 'self' " .
               "https://nominatim.openstreetmap.org https://maps.googleapis.com " .
               "https://www.google.com https://server.arcgisonline.com " .
               "https://*.tile.openstreetmap.org https://*.basemaps.cartocdn.com; " .
               "frame-src 'self' https://www.google.com; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";
        $response->headers->set('Content-Security-Policy', $csp, true);

        // Permissions-Policy: Controls browser features
        // Required by securityheaders.com
        $permissionsPolicy = "geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()";
        $response->headers->set('Permissions-Policy', $permissionsPolicy, true);

        // Strict-Transport-Security (HSTS): Forces HTTPS connections
        // Only set on HTTPS connections (required by securityheaders.com)
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload', true);
        }

        // X-XSS-Protection: Legacy XSS protection (deprecated but still checked)
        $response->headers->set('X-XSS-Protection', '1; mode=block', true);

        // Remove server information disclosure
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}

