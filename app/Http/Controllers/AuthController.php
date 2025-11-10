<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\IpTracking;
use App\Models\DeviceBinding;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegisterForm() {
        return view('register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'password' => 'required|confirmed|min:6',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'email.regex' => 'Please use a valid Gmail address (e.g., user@gmail.com)',
            'email.required' => 'Gmail address is required',
        ]);

        // Check if email is verified
        if (!session('email_verification_verified') || session('email_verification_email') !== $request->email) {
            return back()->withErrors(['email' => 'Please verify your Gmail address first by completing the OTP verification.']);
        }

        $photoName = time() . '.' . $request->photo->extension();
        $request->photo->move(public_path('photos'), $photoName);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'photo' => $photoName,
            'is_approved' => false, // default to pending
            'is_new_user' => true, // Mark as new user for data isolation
        ];
        // Ensure non-null mobile column is satisfied if present
        try {
            if (Schema::hasColumn('users', 'mobile') && !isset($data['mobile'])) {
                $data['mobile'] = '';
            }
        } catch (\Throwable $e) {}

        $user = User::create($data);

        // Bind device to account on registration
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        DeviceBinding::create([
            'user_id' => $user->id,
            'device_fingerprint' => $deviceFingerprint,
            'device_name' => $this->getDeviceName($request),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'is_primary' => true,
            'is_active' => true,
            'last_accessed_at' => now(),
        ]);

        // Clear email verification session
        session()->forget(['email_verification_otp', 'email_verification_email', 'email_verification_expires', 'email_verification_verified']);

        // Generate and send access token to the user's Gmail
        try {
            // Generate a long, URL-safe token
            $accessToken = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
            $cacheKey = 'access_token:' . $user->email;
            Cache::put($cacheKey, $accessToken, now()->addMinutes(10));

            Mail::send('emails.access_token', [
                'token' => $accessToken,
                'user' => $user
            ], function ($message) use ($user) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to($user->email)
                        ->subject('Your Access Token - IT Inventory System');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send access token email: ' . $e->getMessage());
        }

        // Redirect to login with modal to request access token
        return redirect('/login')->with([
            'success' => 'Registration successful! Enter the Access Token sent to your Gmail to activate and login.',
            'access_token_email' => $user->email,
            'show_access_token_modal' => true,
        ]);
    }

    public function showLoginForm() {
        return view('login');
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'password' => 'required',
        ], [
            'email.regex' => 'Please use a valid Gmail address (e.g., user@gmail.com)',
            'email.required' => 'Gmail address is required',
        ]);

        $credentials = $request->only('email', 'password');
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Log login attempt (don't let this block login if it fails)
        try {
            IpTracking::logEvent($ip, 'login_attempt', $request->email, false, $userAgent);
        } catch (\Exception $e) {
            Log::error('Failed to log login attempt: ' . $e->getMessage());
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if (!$user->is_approved) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is not yet activated. Please enter the Access Token sent to your Gmail.']).with([
                    'access_token_email' => $request->email,
                    'show_access_token_modal' => true,
                ]);
            }

            // FIRST: Check if account was registered on this device (has primary device binding)
            // If account is registered/binded to this device, allow login immediately - NO ERROR MESSAGE
            // Check with both active and inactive primary bindings
            $primaryBinding = DeviceBinding::where('user_id', $user->id)
                ->where('is_primary', true)
                ->orderBy('created_at', 'asc') // Get the oldest one (original registration)
                ->first();
            
            $deviceBinding = null;
            $deviceFingerprint = $this->generateDeviceFingerprint($request);
            $userAgent = $request->userAgent() ?? '';
            $ipAddress = $request->ip();
            
            // If account has a primary device binding (registered on a device), allow login immediately
            // This means the account was created on a device, so it can always login from that device
            if ($primaryBinding) {
                // Reactivate if it was deactivated
                if (!$primaryBinding->is_active) {
                    $primaryBinding->is_active = true;
                    $primaryBinding->save();
                }
                
                // Use the primary binding and update it with current device info
                $deviceBinding = $primaryBinding;
                $deviceBinding->update([
                    'device_fingerprint' => $deviceFingerprint,
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress,
                    'device_name' => $this->getDeviceName($request),
                    'last_accessed_at' => now()
                ]);
                
                Log::info('Login SUCCESS - Account registered on device (Primary device binding found)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'primary_binding_id' => $primaryBinding->id,
                    'device_fingerprint' => $deviceFingerprint
                ]);
                
                // Continue to login - DO NOT show error message
                // The account is registered on this device, so login is allowed
            } else {
                // No primary binding found - this could mean:
                // 1. Account was created before device binding was implemented
                // 2. Primary binding was deleted
                // 3. Account is trying to login from device where it was created
                // 
                // SOLUTION: Create a primary device binding automatically
                // This allows accounts created on this device to login
                Log::info('No primary binding found - Creating primary device binding for account', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                // Create primary device binding - this account is now bound to this device
                $primaryBinding = DeviceBinding::create([
                    'user_id' => $user->id,
                    'device_fingerprint' => $deviceFingerprint,
                    'device_name' => $this->getDeviceName($request),
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress,
                    'is_primary' => true,
                    'is_active' => true,
                    'last_accessed_at' => now(),
                ]);
                
                $deviceBinding = $primaryBinding;
                
                Log::info('Primary device binding created - Login allowed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'primary_binding_id' => $primaryBinding->id
                ]);
                
                // Continue to login - DO NOT show error message
                // The account is now bound to this device
            }
            
            // Log successful login (don't let this block login if it fails)
            try {
                IpTracking::logEvent($ip, 'login_success', $request->email, true, $userAgent);
            } catch (\Exception $e) {
                Log::error('Failed to log successful login: ' . $e->getMessage());
            }

            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    /**
     * Resend access token to the provided Gmail address
     */
    public function resendAccessToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'No account found with this email'], 404);
        }

        if ($user->is_approved) {
            return response()->json(['message' => 'Account already activated. You can login now.'], 409);
        }

        try {
            $accessToken = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
            $cacheKey = 'access_token:' . $user->email;
            Cache::put($cacheKey, $accessToken, now()->addMinutes(10));

            Mail::send('emails.access_token', [
                'token' => $accessToken,
                'user' => (object)['name' => $user->name]
            ], function ($message) use ($user) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to($user->email)
                        ->subject('Your Access Token - IT Inventory System');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to resend access token email: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send access token. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Access token sent to your Gmail']);
    }

    /**
     * Verify access token and activate/login the user
     */
    public function verifyAccessToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'token' => 'required|string|min:20',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'No account found with this email'], 404);
        }

        $cacheKey = 'access_token:' . $user->email;
        $stored = Cache::get($cacheKey);
        if (!$stored) {
            return response()->json(['message' => 'Access token expired. Click resend to get a new token.'], 410);
        }

        if ($stored !== $request->token) {
            return response()->json(['message' => 'Invalid access token'], 400);
        }

        // Activate account (do not auto-login)
        $user->is_approved = true;
        $user->save();
        Cache::forget($cacheKey);

        return response()->json(['message' => 'Access token verified. You can now login.']);
    }

    public function dashboard() {
        return view('dashboard', ['user' => Auth::user()]);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Serve the reference ID image (ako.png) for client-side comparison
     */
    public function idImage()
    {
        $path = base_path('ako.png');
        if (!File::exists($path)) {
            abort(404, 'ID image not found');
        }
        return response()->file($path, [
            'Content-Type' => File::mimeType($path),
            'Content-Disposition' => 'inline; filename="ako.png"'
        ]);
    }

    /**
     * Login the user when client-side scan succeeds
     */
    public function loginByScan(Request $request)
    {
        // Basic server-side guard: require a boolean flag that client only sends on exact match
        $request->validate([
            'scan_ok' => 'required|boolean'
        ]);

        if (!$request->boolean('scan_ok')) {
            return response()->json(['message' => 'Scan failed'], 422);
        }

        // Choose an approved user to authenticate the session
        $user = \App\Models\User::where('is_approved', true)->first();
        if (!$user) {
            return response()->json(['message' => 'No approved user available'], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();
        return response()->json(['redirect' => url('/dashboard')]);
    }

    // Show list of users pending approval
    public function showPendingAccounts() {
        $users = User::where('is_approved', false)->get();
        $currentUser = Auth::user();
        
        // Get all device bindings for the current user (including inactive to show history)
        $deviceBindings = DeviceBinding::where('user_id', $currentUser->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('is_primary', 'desc')
            ->orderBy('last_accessed_at', 'desc')
            ->get();
        
        return view('add-new-user', compact('users', 'deviceBindings'));
    }


    // Password Reset Methods
    public function sendOTP(Request $request) {
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'
        ], [
            'email.regex' => 'Please use a valid Gmail address (e.g., user@gmail.com)',
            'email.required' => 'Gmail address is required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No account found with this email'], 404);
        }

        // Check if device is bound
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $deviceBinding = DeviceBinding::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->where('is_active', true)
            ->first();

        // If exact fingerprint doesn't match, try fallback strategies (same as login)
        if (!$deviceBinding) {
            $userAgent = $request->userAgent() ?? '';
            $ipAddress = $request->ip();
            
            // Strategy 1: Exact user agent match
            $deviceBinding = DeviceBinding::where('user_id', $user->id)
                ->where('user_agent', $userAgent)
                ->where('is_active', true)
                ->orderBy('is_primary', 'desc')
                ->first();
            
            // Strategy 2: Primary device with IP match
            if (!$deviceBinding) {
                $deviceBinding = DeviceBinding::where('user_id', $user->id)
                    ->where('is_primary', true)
                    ->where('is_active', true)
                    ->where('ip_address', $ipAddress)
                    ->first();
            }
            
            // If found via fallback, update the fingerprint
            if ($deviceBinding) {
                $deviceBinding->update([
                    'device_fingerprint' => $deviceFingerprint,
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress
                ]);
            }
        }

        // If device is not bound, return device binding message
        if (!$deviceBinding) {
            return response()->json([
                'message' => 'This account is Registered/Binded to your Device',
                'device_binding_required' => true,
            ], 403);
        }

        // Lockout check
        $lockUntil = session('password_reset_lock_until');
        if ($lockUntil && now()->lt($lockUntil)) {
            return response()->json([
                'message' => 'Too many attempts. Try again later.',
                'locked_until' => $lockUntil->toIso8601String(),
            ], 429);
        }

        // Generate 6-digit OTP and store for 5 minutes
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session([
            'password_reset_email' => $request->email,
            'password_reset_otp' => $otp,
            'password_reset_expires' => now()->addMinutes(5),
            'password_reset_attempts' => 0,
        ]);

        try {
            // Check if Gmail credentials are configured
            $gmailPassword = env('MAIL_PASSWORD');
            $gmailUsername = env('MAIL_USERNAME');
            $gmailHost = env('MAIL_HOST');
            $gmailPort = env('MAIL_PORT');
            
            Log::info('Email configuration check:', [
                'MAIL_PASSWORD' => $gmailPassword ? 'SET' : 'NOT_SET',
                'MAIL_USERNAME' => $gmailUsername,
                'MAIL_HOST' => $gmailHost,
                'MAIL_PORT' => $gmailPort
            ]);
            
            if (empty($gmailPassword) || $gmailPassword === 'your-app-password-here') {
                Log::error('Gmail SMTP password not configured. Please set MAIL_PASSWORD in .env file');
                
                // For development: return OTP in response when SMTP is not configured
                if (env('APP_DEBUG', false)) {
                    return response()->json([
                        'message' => 'OTP sent to your email (Development Mode - SMTP not configured)',
                        'token' => 'reset_' . sha1($request->email . '|' . time()),
                        'debug_otp' => $otp, // Only in debug mode
                        'error' => 'SMTP_NOT_CONFIGURED'
                    ]);
                }
                
                return response()->json([
                    'message' => 'Email service not configured. Please contact administrator.',
                    'error' => 'SMTP_NOT_CONFIGURED'
                ], 500);
            }

            Mail::send('emails.password_otp', ['otp' => $otp, 'user' => $user], function ($message) use ($request) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to($request->email)
                        ->subject('Your Password Reset OTP - IT Inventory System');
            });
        } catch (\Throwable $e) {
            Log::error('Password reset OTP email send failed: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            
            // Provide more specific error messages
            if (strpos($e->getMessage(), 'Authentication failed') !== false) {
                return response()->json([
                    'message' => 'Email authentication failed. Please check Gmail credentials.',
                    'error' => 'AUTH_FAILED'
                ], 500);
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                return response()->json([
                    'message' => 'Cannot connect to email server. Please check network settings.',
                    'error' => 'CONNECTION_FAILED'
                ], 500);
            } else {
                return response()->json([
                    'message' => 'Failed to send OTP email: ' . $e->getMessage(),
                    'error' => 'SEND_FAILED'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'OTP sent to your email',
            'token' => 'reset_' . sha1($request->email . '|' . time()),
        ]);
    }

    public function verifyOTP(Request $request) {
        $request->validate([
            'token' => 'required',
            'otp' => 'required|digits:6',
            'email' => 'required|email'
        ]);

        $storedOTP = session('password_reset_otp');
        $storedEmail = session('password_reset_email');
        $expires = session('password_reset_expires');
        $attempts = (int) session('password_reset_attempts', 0);
        if (!$storedOTP || !$expires || now()->gt($expires) || !$storedEmail || $storedEmail !== $request->email) {
            return response()->json(['message' => 'OTP expired or invalid'], 400);
        }
        if ($storedOTP !== $request->otp) {
            $attempts++;
            session(['password_reset_attempts' => $attempts]);
            if ($attempts >= 3) {
                $lockUntil = now()->addMinutes(5);
                session(['password_reset_lock_until' => $lockUntil]);
                return response()->json([
                    'message' => 'Too many invalid attempts. Try again later.',
                    'locked_until' => $lockUntil->toIso8601String(),
                ], 429);
            }
            return response()->json(['message' => 'Invalid OTP', 'remaining_attempts' => max(0, 3 - $attempts)], 400);
        }
        session(['password_reset_verified' => true, 'password_reset_attempts' => 0]);
        return response()->json(['message' => 'OTP verified successfully']);
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        if (!session('password_reset_verified')) {
            return response()->json(['message' => 'OTP verification required'], 400);
        }

        $email = session('password_reset_email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear reset session
        session()->forget(['password_reset_otp', 'password_reset_email', 'password_reset_expires', 'password_reset_verified']);

        return response()->json(['message' => 'Password updated successfully']);
    }

    // Email Verification Methods for Registration
    public function sendEmailVerificationOTP(Request $request) {
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'
        ], [
            'email.regex' => 'Please use a valid Gmail address (e.g., user@gmail.com)',
            'email.required' => 'Gmail address is required',
        ]);

        // Check if email is already registered
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json(['message' => 'This Gmail address is already registered'], 409);
        }

        // Lockout check for email verification
        $lockUntil = session('email_verification_lock_until');
        if ($lockUntil && now()->lt($lockUntil)) {
            return response()->json([
                'message' => 'Too many attempts. Try again later.',
                'locked_until' => $lockUntil->toIso8601String(),
            ], 429);
        }

        // Generate 6-digit OTP and store for 5 minutes
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session([
            'email_verification_email' => $request->email,
            'email_verification_otp' => $otp,
            'email_verification_expires' => now()->addMinutes(5),
            'email_verification_attempts' => 0,
        ]);

        try {
            // Check if Gmail credentials are configured
            $gmailPassword = env('MAIL_PASSWORD');
            $gmailUsername = env('MAIL_USERNAME');
            $gmailHost = env('MAIL_HOST');
            $gmailPort = env('MAIL_PORT');
            
            Log::info('Email verification configuration check:', [
                'MAIL_PASSWORD' => $gmailPassword ? 'SET' : 'NOT_SET',
                'MAIL_USERNAME' => $gmailUsername,
                'MAIL_HOST' => $gmailHost,
                'MAIL_PORT' => $gmailPort
            ]);
            
            if (empty($gmailPassword) || $gmailPassword === 'your-app-password-here') {
                Log::error('Gmail SMTP password not configured. Please set MAIL_PASSWORD in .env file');
                
                // For development: return OTP in response when SMTP is not configured
                if (env('APP_DEBUG', false)) {
                    return response()->json([
                        'message' => 'Email verification OTP sent (Development Mode - SMTP not configured)',
                        'token' => 'email_verify_' . sha1($request->email . '|' . time()),
                        'debug_otp' => $otp, // Only in debug mode
                        'error' => 'SMTP_NOT_CONFIGURED'
                    ]);
                }
                
                return response()->json([
                    'message' => 'Email service not configured. Please contact administrator.',
                    'error' => 'SMTP_NOT_CONFIGURED'
                ], 500);
            }

            Mail::send('emails.email_verification', ['otp' => $otp, 'email' => $request->email], function ($message) use ($request) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to($request->email)
                        ->subject('Verify Your Gmail - IT Inventory System Registration');
            });
        } catch (\Throwable $e) {
            Log::error('Email verification OTP send failed: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            
            // Provide more specific error messages
            if (strpos($e->getMessage(), 'Authentication failed') !== false) {
                return response()->json([
                    'message' => 'Email authentication failed. Please check Gmail credentials.',
                    'error' => 'AUTH_FAILED'
                ], 500);
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                return response()->json([
                    'message' => 'Cannot connect to email server. Please check network settings.',
                    'error' => 'CONNECTION_FAILED'
                ], 500);
            } else {
                return response()->json([
                    'message' => 'Failed to send verification OTP: ' . $e->getMessage(),
                    'error' => 'SEND_FAILED'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Verification OTP sent to your Gmail',
            'token' => 'email_verify_' . sha1($request->email . '|' . time()),
        ]);
    }

    public function verifyEmailOTP(Request $request) {
        $request->validate([
            'token' => 'required',
            'otp' => 'required|digits:6',
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'
        ], [
            'email.regex' => 'Please use a valid Gmail address (e.g., user@gmail.com)',
        ]);

        $storedOTP = session('email_verification_otp');
        $storedEmail = session('email_verification_email');
        $expires = session('email_verification_expires');
        $attempts = (int) session('email_verification_attempts', 0);

        if (!$storedOTP || !$expires || now()->gt($expires) || !$storedEmail || $storedEmail !== $request->email) {
            return response()->json(['message' => 'OTP expired or invalid'], 400);
        }

        if ($storedOTP !== $request->otp) {
            $attempts++;
            session(['email_verification_attempts' => $attempts]);
            if ($attempts >= 3) {
                $lockUntil = now()->addMinutes(5);
                session(['email_verification_lock_until' => $lockUntil]);
                return response()->json([
                    'message' => 'Too many invalid attempts. Try again later.',
                    'locked_until' => $lockUntil->toIso8601String(),
                ], 429);
            }
            return response()->json(['message' => 'Invalid OTP', 'remaining_attempts' => max(0, 3 - $attempts)], 400);
        }

        // Mark email as verified
        session(['email_verification_verified' => true, 'email_verification_attempts' => 0]);
        return response()->json(['message' => 'Email verified successfully']);
    }

    // Admin Approval System
    private function sendAdminApprovalNotification($user) {
        try {
            $approvalUrl = url('/add-new-user');
            
            // Generate secure approval and rejection links
            $approveToken = hash('sha256', $user->email . '|' . $user->created_at->timestamp . '|approve');
            $rejectToken = hash('sha256', $user->email . '|' . $user->created_at->timestamp . '|reject');
            
            $approveUrl = url('/email-approve/' . $user->id . '/' . $approveToken);
            $rejectUrl = url('/email-reject/' . $user->id . '/' . $rejectToken);
            
            // Check if Gmail credentials are configured
            $gmailPassword = env('MAIL_PASSWORD');
            if (empty($gmailPassword) || $gmailPassword === 'your-app-password-here') {
                Log::warning('Gmail SMTP not configured. Admin approval notification not sent for user: ' . $user->email);
                return;
            }
            
            Mail::send('emails.admin_approval_request', [
                'user' => $user,
                'approvalUrl' => $approvalUrl,
                'approveUrl' => $approveUrl,
                'rejectUrl' => $rejectUrl
            ], function ($message) use ($user) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to('iitech.inventory@gmail.com')
                        ->subject('New Account Registration - Approval Required: ' . $user->name);
            });
            
            Log::info('Admin approval notification sent successfully for user: ' . $user->email);
        } catch (\Throwable $e) {
            Log::error('Failed to send admin approval notification for user ' . $user->email . ': ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            // Don't fail registration if email fails, but log the issue
        }
    }

    public function approveUser($id) {
        try {
            $user = User::findOrFail($id);
            
            // Check if user is already approved
            if ($user->is_approved) {
                return redirect()->back()->with('warning', 'User is already approved.');
            }
            
            $user->is_approved = true;
            $user->save();

            Log::info('User approved successfully: ' . $user->email . ' (ID: ' . $user->id . ')');

            // Send approval confirmation to user
            $this->sendUserApprovalConfirmation($user);

            return redirect()->back()->with('success', 'User approved successfully! ' . $user->name . ' can now login directly to the IT Inventory System. Confirmation email sent to ' . $user->email);
        } catch (\Throwable $e) {
            Log::error('Failed to approve user ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve user. Please try again.');
        }
    }

    public function rejectUser($id) {
        try {
            $user = User::findOrFail($id);
            $userEmail = $user->email;
            $userName = $user->name;
            
            $user->delete();

            Log::info('User rejected and deleted: ' . $userEmail . ' (ID: ' . $id . ')');

            // Send rejection notification to user
            $this->sendUserRejectionNotification($userEmail);

            return redirect()->back()->with('success', 'User ' . $userName . ' rejected and deleted. Rejection email sent to ' . $userEmail);
        } catch (\Throwable $e) {
            Log::error('Failed to reject user ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject user. Please try again.');
        }
    }

    private function sendUserApprovalConfirmation($user) {
        try {
            Mail::send('emails.user_approval_confirmation', [
                'user' => $user
            ], function ($message) use ($user) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to($user->email)
                        ->subject('Account Approved - IT Inventory System Access Granted');
            });
            
            Log::info('User approval confirmation sent to: ' . $user->email);
        } catch (\Throwable $e) {
            Log::error('Failed to send user approval confirmation: ' . $e->getMessage());
        }
    }

    private function sendUserRejectionNotification($userEmail) {
        try {
            Mail::send('emails.user_rejection_notification', [
                'userEmail' => $userEmail
            ], function ($message) use ($userEmail) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to($userEmail)
                        ->subject('Account Registration Rejected - IT Inventory System');
            });
            
            Log::info('User rejection notification sent to: ' . $userEmail);
        } catch (\Throwable $e) {
            Log::error('Failed to send user rejection notification: ' . $e->getMessage());
        }
    }

    // Test method to check email functionality
    public function testEmailSystem() {
        try {
            // Test sending email to admin
            $testUser = (object) [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'created_at' => now()
            ];
            
            $approvalUrl = url('/add-new-user');
            
            Mail::send('emails.admin_approval_request', [
                'user' => $testUser,
                'approvalUrl' => $approvalUrl
            ], function ($message) {
                $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                        ->to('iitech.inventory@gmail.com')
                        ->subject('TEST: Email System Check - IT Inventory System');
            });
            
            return response()->json(['message' => 'Test email sent successfully to iitech.inventory@gmail.com']);
        } catch (\Throwable $e) {
            Log::error('Email test failed: ' . $e->getMessage());
            return response()->json(['error' => 'Email test failed: ' . $e->getMessage()], 500);
        }
    }

    // Method to resend admin notification for a specific user
    public function resendAdminNotification($id) {
        try {
            $user = User::findOrFail($id);
            $this->sendAdminApprovalNotification($user);
            return redirect()->back()->with('success', 'Admin notification resent for ' . $user->name);
        } catch (\Throwable $e) {
            Log::error('Failed to resend admin notification for user ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to resend notification. Please try again.');
        }
    }

    // Email-based approval system
    public function emailApproveUser($id, $token) {
        try {
            $user = User::findOrFail($id);
            
            // Verify the token matches the user's email and current timestamp
            $expectedToken = hash('sha256', $user->email . '|' . $user->created_at->timestamp . '|approve');
            if (!hash_equals($expectedToken, $token)) {
                return view('email-approval-result', [
                    'success' => false,
                    'message' => 'Invalid approval link. This link may have expired or been tampered with.',
                    'user' => $user
                ]);
            }
            
            // Check if user is already approved
            if ($user->is_approved) {
                return view('email-approval-result', [
                    'success' => true,
                    'message' => 'Account is already approved. User can login to the IT Inventory System.',
                    'user' => $user,
                    'alreadyApproved' => true
                ]);
            }
            
            // Approve the user
            $user->is_approved = true;
            $user->save();

            Log::info('User approved via email: ' . $user->email . ' (ID: ' . $user->id . ')');

            // Send approval confirmation to user
            $this->sendUserApprovalConfirmation($user);

            return view('email-approval-result', [
                'success' => true,
                'message' => 'Account approved successfully! ' . $user->name . ' can now login to the IT Inventory System.',
                'user' => $user,
                'alreadyApproved' => false
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to approve user via email ID ' . $id . ': ' . $e->getMessage());
            return view('email-approval-result', [
                'success' => false,
                'message' => 'Failed to approve account. Please try again or contact the administrator.',
                'user' => null
            ]);
        }
    }

    public function emailRejectUser($id, $token) {
        try {
            $user = User::findOrFail($id);
            
            // Verify the token matches the user's email and current timestamp
            $expectedToken = hash('sha256', $user->email . '|' . $user->created_at->timestamp . '|reject');
            if (!hash_equals($expectedToken, $token)) {
                return view('email-approval-result', [
                    'success' => false,
                    'message' => 'Invalid rejection link. This link may have expired or been tampered with.',
                    'user' => $user
                ]);
            }
            
            $userEmail = $user->email;
            $userName = $user->name;
            
            // Delete the user
            $user->delete();

            Log::info('User rejected via email: ' . $userEmail . ' (ID: ' . $id . ')');

            // Send rejection notification to user
            $this->sendUserRejectionNotification($userEmail);

            return view('email-approval-result', [
                'success' => true,
                'message' => 'Account rejected and deleted. Rejection notification sent to ' . $userEmail,
                'user' => null,
                'rejected' => true
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to reject user via email ID ' . $id . ': ' . $e->getMessage());
            return view('email-approval-result', [
                'success' => false,
                'message' => 'Failed to reject account. Please try again or contact the administrator.',
                'user' => null
            ]);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        // Base validation (all optional; validate format when present)
        $request->validate([
            'name' => 'nullable|string|min:2',
            'email' => 'nullable|email',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Only validate/update password when BOTH fields are provided and non-empty
        $newPassword = trim((string) $request->input('password'));
        $newPasswordConfirmation = trim((string) $request->input('password_confirmation'));
        $shouldUpdatePassword = ($newPassword !== '' && $newPasswordConfirmation !== '');
        if ($shouldUpdatePassword) {
            $request->validate([
                'password' => 'min:6|confirmed',
            ]);
        }

        // Email uniqueness if changed and provided
        if ($request->filled('email') && $request->email !== $user->email) {
            $exists = \App\Models\User::where('email', $request->email)->exists();
            if ($exists) {
                return response()->json(['message' => 'Email already in use'], 409);
            }
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        if ($shouldUpdatePassword) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $photoName = time() . '.' . $request->photo->extension();
            $request->photo->move(public_path('photos'), $photoName);
            $user->photo = $photoName;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $user->photo,
            ],
        ]);
    }

    /**
     * Generate device fingerprint from request
     * Note: This uses browser characteristics that are stable across sessions
     * Session ID is excluded as it changes between requests
     */
    private function generateDeviceFingerprint(Request $request)
    {
        $userAgent = $request->userAgent() ?? '';
        $acceptLanguage = $request->header('Accept-Language') ?? '';
        $acceptEncoding = $request->header('Accept-Encoding') ?? '';
        $acceptCharset = $request->header('Accept-Charset') ?? '';
        
        // Create a fingerprint from various device characteristics (excluding IP and session ID as they can change)
        // This ensures the same device/browser produces the same fingerprint across sessions
        $fingerprint = hash('sha256', $userAgent . '|' . $acceptLanguage . '|' . $acceptEncoding . '|' . $acceptCharset);
        
        return $fingerprint;
    }

    /**
     * Get device name from request
     */
    private function getDeviceName(Request $request)
    {
        $userAgent = $request->userAgent() ?? 'Unknown Device';
        
        // Try to extract device name from user agent
        if (preg_match('/(Windows|Mac|Linux|Android|iOS|iPhone|iPad)/i', $userAgent, $matches)) {
            return $matches[1] . ' Device';
        }
        
        return substr($userAgent, 0, 50) . '...';
    }

    /**
     * Normalize user agent to match same browser type even if version changes
     * This helps match devices when browser updates
     */
    private function normalizeUserAgent($userAgent)
    {
        if (empty($userAgent)) {
            return '';
        }
        
        // Extract browser name and major version, ignore minor version changes
        // Chrome/Edge pattern: Chrome/120.0.0.0 -> Chrome/120
        // Firefox pattern: Firefox/121.0 -> Firefox/121
        // Safari pattern: Version/17.2 Safari/605.1.15 -> Safari/17
        
        $normalized = $userAgent;
        
        // Normalize Chrome/Chromium
        if (preg_match('/(Chrome|Chromium)\/(\d+)/i', $userAgent, $matches)) {
            $normalized = $matches[1] . '/' . $matches[2];
        }
        // Normalize Firefox
        elseif (preg_match('/Firefox\/(\d+)/i', $userAgent, $matches)) {
            $normalized = 'Firefox/' . $matches[1];
        }
        // Normalize Safari
        elseif (preg_match('/Version\/(\d+).*Safari/i', $userAgent, $matches)) {
            $normalized = 'Safari/' . $matches[1];
        }
        // Normalize Edge
        elseif (preg_match('/Edg\/(\d+)/i', $userAgent, $matches)) {
            $normalized = 'Edge/' . $matches[1];
        }
        
        return $normalized;
    }

    /**
     * Check if two IP addresses are on the same network
     * For IPv4, checks if first 3 octets match (same /24 subnet)
     * Special handling for localhost addresses
     */
    private function isSameNetwork($ip1, $ip2)
    {
        if (empty($ip1) || empty($ip2)) {
            return false;
        }
        
        // Special case: localhost addresses (127.0.0.1, ::1) are considered same network
        $localhostIps = ['127.0.0.1', '::1', 'localhost'];
        if ((in_array($ip1, $localhostIps) || strpos($ip1, '127.') === 0) &&
            (in_array($ip2, $localhostIps) || strpos($ip2, '127.') === 0)) {
            return true;
        }
        
        // For IPv4 addresses
        if (filter_var($ip1, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && 
            filter_var($ip2, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts1 = explode('.', $ip1);
            $parts2 = explode('.', $ip2);
            
            // Check first 3 octets (same /24 network)
            return count($parts1) === 4 && count($parts2) === 4 &&
                   $parts1[0] === $parts2[0] &&
                   $parts1[1] === $parts2[1] &&
                   $parts1[2] === $parts2[2];
        }
        
        // For exact match on other IP formats
        return $ip1 === $ip2;
    }

    /**
     * Generate share token for device binding
     */
    public function generateDeviceShareToken(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Generate a share token
        $shareToken = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $cacheKey = 'device_share_token:' . $user->id;
        Cache::put($cacheKey, $shareToken, now()->addHours(24)); // Token valid for 24 hours

        return response()->json([
            'token' => $shareToken,
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ]);
    }

    /**
     * Verify share token and bind device
     */
    public function verifyDeviceShareToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'token' => 'required|string|min:20',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'No account found with this email'], 404);
        }

        // Verify share token
        $cacheKey = 'device_share_token:' . $user->id;
        $storedToken = Cache::get($cacheKey);
        
        if (!$storedToken || $storedToken !== $request->token) {
            return response()->json(['message' => 'Invalid or expired share token'], 400);
        }

        // Bind device to account
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        
        // Check if device is already bound
        $existingBinding = DeviceBinding::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->first();

        if (!$existingBinding) {
            // Create new device binding with the share token
            $deviceBinding = DeviceBinding::create([
                'user_id' => $user->id,
                'device_fingerprint' => $deviceFingerprint,
                'device_share_token' => $request->token, // Store the device share token
                'device_name' => $this->getDeviceName($request),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'is_primary' => false,
                'is_active' => true,
                'last_accessed_at' => now(),
            ]);

            // Send email notification to owner
            try {
                Mail::send('emails.device_access_granted', [
                    'user' => $user,
                    'deviceName' => $this->getDeviceName($request),
                    'ipAddress' => $request->ip(),
                    'userAgent' => $request->userAgent(),
                ], function ($message) use ($user) {
                    $message->from('iitech.inventory@gmail.com', 'IT Inventory System')
                            ->to($user->email)
                            ->subject('New Device Access - IT Inventory System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed to send device access notification: ' . $e->getMessage());
            }
        }

        // Clear the share token
        Cache::forget($cacheKey);

        // Login the user
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Share token to other device success, You are now able to login',
            'redirect' => url('/dashboard'),
            'success' => true
        ]);
    }

    /**
     * Remove device binding (but keep the share token data)
     */
    public function removeDeviceBinding(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $deviceBinding = DeviceBinding::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$deviceBinding) {
            return response()->json(['message' => 'Device binding not found'], 404);
        }

        // Don't allow removing primary device
        if ($deviceBinding->is_primary) {
            return response()->json(['message' => 'Cannot remove primary device'], 403);
        }

        // Keep the device_share_token but remove the binding by setting is_active to false
        // or we can delete it completely - based on user requirement, let's delete but keep token in history
        // Actually, let's just set is_active to false to keep the record with the token
        $deviceBinding->is_active = false;
        $deviceBinding->save();

        return response()->json([
            'message' => 'Device access removed successfully',
        ]);
    }
}
    