<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\IpTracking;

class LogTraffic
{
    protected function path(): string { return storage_path('app/traffic.log'); }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        try {
            // Log to file (legacy)
            $row = [
                'ip' => $request->ip(),
                'ua' => substr((string)$request->userAgent(), 0, 200),
                'path' => $request->path(),
                'type' => 'visit',
                'time' => now()->toIso8601String(),
            ];
            @file_put_contents($this->path(), json_encode($row) . "\n", FILE_APPEND);
            
            // Log to database for better tracking (don't let this block requests if it fails)
            try {
                IpTracking::logEvent($request->ip(), 'visit', null, false, $request->userAgent());
            } catch (\Exception $e) {
                // Silently fail - don't log to avoid spam
            }
        } catch (\Throwable $e) {}
        return $response;
    }
}


