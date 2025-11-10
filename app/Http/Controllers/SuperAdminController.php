<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;

class SuperAdminController extends Controller
{
    protected function cache()
    {
        // Force file cache store to avoid DB cache table dependency
        return Cache::store('file');
    }
    protected function storePath(): string { return storage_path('app/super_admin.json'); }
    protected function readStore(): array {
        $path = $this->storePath();
        if (!file_exists($path)) return [];
        $json = @file_get_contents($path);
        $data = json_decode($json ?: '[]', true);
        return is_array($data) ? $data : [];
    }
    protected function writeStore(array $data): void {
        $path = $this->storePath();
        $dir = dirname($path);
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    // GET /super-admin/status
    public function status()
    {
        $data = $this->readStore();
        return response()->json([
            'registered' => isset($data['email']) && isset($data['password_hash']),
            'email' => $data['email'] ?? null,
        ]);
    }

    // POST /super-admin/register (only if none exists)
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        $data = $this->readStore();
        if (isset($data['email'])) {
            return response()->json(['message' => 'Super Admin already registered'], 409);
        }
        $email = strtolower($request->input('email'));
        $hash = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $data = [ 'email' => $email, 'password_hash' => $hash, 'created_at' => now()->toISOString() ];
        $this->writeStore($data);
        return response()->json(['success' => true]);
    }
    // POST /super-admin/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Use registered store (single account)
        $stored = $this->readStore();
        if (!isset($stored['email'], $stored['password_hash'])) {
            return response()->json(['message' => 'Super Admin not registered'], 404);
        }
        if (strcasecmp($request->input('email'), $stored['email']) !== 0) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check lockout (by email)
        $lockKey = 'sa:lock:' . strtolower($stored['email']);
        if ($this->cache()->has($lockKey) && $this->cache()->get($lockKey) > now()) {
            return response()->json([
                'message' => 'Locked',
            ], 429);
        }

        $password = (string)$request->input('password');
        $ok = false;
        try {
            // Prefer password_verify to support argon2id/bcrypt
            $ok = password_verify($password, (string)$stored['password_hash']);
        } catch (\Throwable $e) {
            Log::warning('SuperAdmin password verify error: ' . $e->getMessage());
            $ok = false;
        }

        if (!$ok) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create OTP session
        $token = Str::random(48);
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'sa:otp:' . $token;
        $this->cache()->put($cacheKey, [
            'email' => $stored['email'],
            'otp' => $otp,
            'attempts' => 2, // two retries allowed
        ], now()->addMinutes(10));

        // Send OTP email
        try {
            Mail::raw("Your Super Admin OTP is: {$otp}. It expires in 10 minutes.", function ($m) use ($stored) {
                $m->to($stored['email'])->subject('Super Admin OTP');
            });
        } catch (\Throwable $e) {
            Log::error('Failed sending Super Admin OTP: ' . $e->getMessage());
        }

        // Log login attempt
        try {
            @file_put_contents(storage_path('app/traffic.log'), json_encode([
                'ip' => request()->ip(),
                'type' => 'login_attempt',
                'success' => true,
                'time' => now()->toIso8601String(),
            ]) . "\n", FILE_APPEND);
        } catch (\Throwable $e) {}

        // Do not expose OTP in responses
        return response()->json(['token' => $token]);
    }

    // POST /super-admin/resend-otp
    public function resendOtp(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');
        $cacheKey = 'sa:otp:' . $token;
        $data = $this->cache()->get($cacheKey);
        if (!$data) {
            return response()->json(['message' => 'Session expired'], 410);
        }

        // regenerate OTP
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $data['otp'] = $otp;
        $this->cache()->put($cacheKey, $data, now()->addMinutes(10));

        try {
            Mail::raw("Your Super Admin OTP is: {$otp}. It expires in 10 minutes.", function ($m) use ($data) {
                $m->to($data['email'])->subject('Super Admin OTP');
            });
        } catch (\Throwable $e) {
            Log::error('Failed resending Super Admin OTP: ' . $e->getMessage());
        }

        // Do not expose OTP in responses
        return response()->json([]);
    }

    // POST /super-admin/verify-otp
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $token = $request->input('token');
        $cacheKey = 'sa:otp:' . $token;
        $data = $this->cache()->get($cacheKey);
        if (!$data) {
            return response()->json(['message' => 'Session expired'], 410);
        }

        // Check lock first
        $lockKey = 'sa:lock:' . strtolower($data['email']);
        if ($this->cache()->has($lockKey) && $this->cache()->get($lockKey) > now()) {
            return response()->json(['message' => 'Locked'], 429);
        }

        if (hash_equals($data['otp'], (string)$request->input('otp'))) {
            // success
            $this->cache()->forget($cacheKey);
            // Mark session as super admin
            session(['is_super_admin' => true, 'super_admin_email' => $data['email']]);
            return response()->json(['success' => true]);
        }

        // Decrement attempts
        $data['attempts'] = max(0, (int)($data['attempts'] ?? 0) - 1);
        if ($data['attempts'] <= 0) {
            // Lock for 24 hours
            $this->cache()->put($lockKey, now()->addHours(24), now()->addHours(24));
            $this->cache()->forget($cacheKey);
            return response()->json([
                'message' => 'Too many invalid attempts',
                'remaining_attempts' => 0,
            ], 429);
        }

        $this->cache()->put($cacheKey, $data, now()->addMinutes(10));
        return response()->json([
            'message' => 'Invalid OTP',
            'remaining_attempts' => $data['attempts'],
        ], 422);
    }
}


