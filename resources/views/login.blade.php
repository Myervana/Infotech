<?php
if (!headers_sent()) {
    // Send HSTS only over HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login / Register</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
    <meta name="password-hash" content="argon2id">
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		// Flags from server to trigger Access Token modal after signup or blocked login
		window.ACCESS_TOKEN_MODAL = {!! session('show_access_token_modal') ? 'true' : 'false' !!};
		window.ACCESS_TOKEN_EMAIL = {!! json_encode(session('access_token_email')) !!};
		// Device binding flags
		window.DEVICE_BINDING_REQUIRED = {!! session('device_binding_required') ? 'true' : 'false' !!};
		window.DEVICE_BINDING_EMAIL = {!! json_encode(session('device_binding_email')) !!};
	</script>
    <script>
        // Ensure any SweetAlert will close all app modals first and grab focus
        document.addEventListener('DOMContentLoaded', function(){
            if (window.Swal && !window.__swalPatched) {
                const originalFire = Swal.fire.bind(Swal);
                function closeAllCustomModals(){
                    // Do NOT close superAdminModal to keep 2FA flow persistent
                    ['passwordResetModal','emailVerificationModal','superAdminRegisterModal','termsModal']
                        .forEach(id=>{ const el=document.getElementById(id); if(el){ el.style.display='none'; }});
                }
                Swal.fire = function(opts){
                    closeAllCustomModals();
                    return originalFire(opts);
                };
                window.__swalPatched = true;
            }
            // Queue SweetAlerts until OTP modals are closed
            function isVisible(id){ const el=document.getElementById(id); if(!el) return false; return el.style.display && el.style.display !== 'none'; }
            function isAnyOtpOpen(){ return isVisible('passwordResetModal') || isVisible('emailVerificationModal'); }
            window.queueSwal = function(opts){
                if (!window.Swal) return;
                if (!isAnyOtpOpen()) { return Swal.fire(opts); }
                const started = Date.now();
                const poll = setInterval(function(){
                    if (!isAnyOtpOpen()) {
                        clearInterval(poll);
                        Swal.fire(opts);
                    }
                    if (Date.now() - started > 30000) { clearInterval(poll); }
                }, 150);
            };
        });
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg,rgb(170, 39, 39),rgba(255, 255, 255, 0.9));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }

        .container {
            position: relative;
            width: 100%;
            max-width: 1040px;
            height: 620px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        @keyframes show {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: linear-gradient(45deg,rgb(170, 39, 39),rgb(2, 3, 3));
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: white;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        .form-content {
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
            position: relative;
        }

        /* Logo Background */
        .form-content::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;          /* LOGO SIZE: Change this to adjust logo width */
            height: 500px;         /* LOGO SIZE: Change this to adjust logo height */
            background-image: url('{{ asset("images/logo.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.4;          /* TRANSPARENCY: Change this value (0.1 = very transparent, 1.0 = fully visible) */
            z-index: 1;
            pointer-events: none;
        }

        .form-content > * {
            position: relative;
            z-index: 2;
        }

        h1 {
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            font-size: 32px;
        }

        .overlay h1 {
            color: white;
            font-size: 28px;
            margin-bottom: 15px;
        }

        p {
            font-size: 16px;
            font-weight: bold;
            line-height: 24px;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            color: #666;
        }

       .overlay p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 30px;
    font-family: 'Poppins', sans-serif;
    font-weight: 700; /* bold */
}


        .form-group {
            margin-bottom: 15px;
            width: 100%;
        }

        input {
            background: #f6f6f6;
            border: none;
            padding: 15px 20px;
            margin: 8px 0;
            width: 100%;
            border-radius: 25px;
            font-size: 16px; /* ensure readable */
            line-height: 1.2; /* ensure text fits */
            color: #222; /* strong contrast */
            outline: none;
            transition: all 0.3s ease;
        }

        input:focus {
            background: #e8e8e8;
            transform: scale(1.02);
        }

        input[type="file"] {
            background: white;
            border: 2px dashed #4ecdc4;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
        }

        input[type="file"]:hover {
            border-color: #45b7d1;
            background: #f0fdff;
        }

        button, .btn-like {
            border-radius: 25px;
            border: 1px solid transparent;
            background: linear-gradient(45deg,rgb(170, 39, 39),rgb(2, 3, 3));
            color: white;
            font-size: 14px;
            font-weight: bold;
            padding: 15px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 15px;
        }

        button:hover, .btn-like:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        button:active, .btn-like:active {
            transform: scale(0.95);
        }

        button.ghost {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 12px 40px;
        }

        button.ghost:hover {
            background: white;
            color: #4ecdc4;
        }

        .file-input-wrapper {
            position: relative;
            width: 100%;
            margin: 8px 0;
        }

        .file-input-label {
            display: block;
            padding: 15px 20px;
            background: #f6f6f6;
            border-radius: 25px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 14px;
            color: #666;
        }

        .file-input-label:hover {
            background: #e8e8e8;
            transform: scale(1.02);
        }

        .file-input-label::before {
            content: "📁 ";
            margin-right: 8px;
        }

        .hidden-file-input {
            position: absolute;
            left: -9999px;
            opacity: 0;
        }

        @media (max-width: 768px) {
            .container {
                max-width: 400px;
                height: auto;
                min-height: 600px;
            }

            .form-container,
            .overlay-container {
                position: relative;
                width: 100%;
                height: auto;
            }

            .overlay {
                display: none;
            }

            .form-content {
                padding: 40px 30px;
            }

            .container.right-panel-active .sign-up-container,
            .container.right-panel-active .sign-in-container {
                transform: none;
            }

            .mobile-toggle {
                display: block;
                text-align: center;
                padding: 20px;
                background: #f8f9fa;
            }

            .mobile-toggle button {
                margin: 0 10px;
                padding: 10px 20px;
                font-size: 12px;
            }

            .form-content::before {
                width: 150px;      /* MOBILE LOGO SIZE: Adjust logo size for mobile */
                height: 150px;     /* MOBILE LOGO SIZE: Adjust logo size for mobile */
            }
        }

        .mobile-toggle {
            display: none;
        }

        /* Access Token Modal */
        #accessTokenModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .access-modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }
        .access-header { text-align:center; margin-bottom: 10px; }
        .access-header h3 { color:#333; margin-bottom:6px; font-size:22px; }
        .access-header p { color:#666; font-size:14px; }
        .access-email { font-weight:bold; color:#111; }
        .access-resend { text-align:center; margin-top:10px; }
        .access-resend button { background:none; border:none; color:#007bff; text-decoration:underline; cursor:pointer; }
        .access-error { color:#dc3545; text-align:center; margin-top:8px; min-height:20px; }
        .access-actions { display:flex; justify-content:center; gap:12px; margin-top:8px; }

        /* Password Reset Modal */
        #passwordResetModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        /* Login OTP Modal (2FA) */
        #loginOTPModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 10040;
            align-items: center;
            justify-content: center;
        }
        
        .login-otp-modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }

        .reset-modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }

        .reset-step {
            display: none;
        }

        .reset-step.active {
            display: block;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .reset-header h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .reset-header p {
            color: #666;
            font-size: 14px;
        }

        .otp-input {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
            flex-wrap: nowrap; /* ensure 6 boxes stay in one row */
            width: 100%;
        }

        .otp-input input {
            width: 54px;
            height: 54px;
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            border: 3px solid #007bff;
            border-radius: 12px;
            background: white;
            color: #000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            line-height: 1;
            padding: 0;
            margin: 0 5px;
        }

        .otp-input input:focus {
            border-color: #0056b3;
            background: #f8f9fa;
            box-shadow: 0 0 0 4px rgba(0,123,255,0.3);
            outline: none;
            transform: scale(1.05);
        }

        .otp-input input:not(:placeholder-shown) {
            background: #e3f2fd;
            border-color: #1976d2;
            color: #000;
            font-weight: 900;
        }

        .otp-input input::placeholder {
            color: #ccc;
            font-size: 20px;
        }

        .otp-timer {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin: 10px 0;
        }

        .otp-timer.warning {
            color: #dc3545;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .modal-close:hover {
            color: #333;
        }

        .resend-btn {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }

        .resend-btn:disabled {
            color: #999;
            cursor: not-allowed;
        }
        
        /* Keep 6 boxes in a single row on smaller screens */
        @media (max-width: 420px) {
            .otp-input { gap: 6px; }
            .otp-input input {
                width: 40px;
                height: 48px;
                font-size: 20px;
                margin: 0;
            }
        }
    </style>
    <style>
        /* Validation & strength styles */
        .input-invalid {
            border: 2px solid #dc3545 !important;
            box-shadow: 0 0 0 4px rgba(220,53,69,0.2) !important;
            background-color: #fff5f5 !important;
        }
        .input-valid {
            border: 2px solid #28a745 !important;
            box-shadow: 0 0 0 4px rgba(40,167,69,0.2) !important;
            background-color: #f8fff9 !important;
        }
        .strength-weak { border-color: #dc3545 !important; box-shadow: 0 0 0 4px rgba(220,53,69,0.2) !important; }
        .strength-strong { border-color: #ffc107 !important; box-shadow: 0 0 0 4px rgba(255,193,7,0.25) !important; background-color: #fffdf5 !important; }
        .strength-super { border-color: #28a745 !important; box-shadow: 0 0 0 4px rgba(40,167,69,0.2) !important; background-color: #f8fff9 !important; }
        .file-input-label.invalid { border: 2px solid #dc3545; box-shadow: 0 0 0 4px rgba(220,53,69,0.2); color: #dc3545; }
        input[type="file"].invalid { border-color: #dc3545; box-shadow: 0 0 0 4px rgba(220,53,69,0.2); }
    </style>
    <script>
        function showForm(formType) {
            const container = document.getElementById('container');
            if (formType === 'registerForm') {
                container.classList.add('right-panel-active');
            } else {
                container.classList.remove('right-panel-active');
            }
        }

        // Initialize with login form
        window.onload = function() {
            showForm('loginForm');
        };

        // Secure file input handler (reject PHP and non-images)
        function handleFileSelect(input) {
            const label = input.nextElementSibling;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileName = (file.name || '').toLowerCase();
                const ext = fileName.split('.').pop();
                const forbidden = ['php','php3','php4','php5','php7','pht','phtml','phar'];
                const isImage = (file.type || '').startsWith('image/');
                const isTooLarge = file.size > 2 * 1024 * 1024; // 2MB
                if (!isImage || forbidden.includes(ext) || isTooLarge) {
                    Swal.fire({
                        icon: 'error',
                        title: isTooLarge ? 'File Too Large' : 'Invalid File Type',
                        text: isTooLarge ? 'Maximum size is 2 MB.' : 'Only image files are allowed.',
                        confirmButtonColor: '#dc3545',
                        zIndex: 10001
                    });
                    // mark invalid and block terms & sign up
                    input.classList.add('invalid');
                    if (label && label.classList.contains('file-input-label')) { label.classList.add('invalid'); }
                    blockRegistrationControls(true);
                    return; // keep file value so they can see state; they must change to clear
                }
                // valid file
                input.classList.remove('invalid');
                if (label && label.classList.contains('file-input-label')) { label.classList.remove('invalid'); }
                unblockRegistrationControlsIfEligible();
                const safeName = file.name.replace(/[<>"'&]/g, '');
                if (label && label.classList.contains('file-input-label')) {
                    label.textContent = '📁 ' + safeName;
                }
            }
        }
        function blockRegistrationControls(block){
            const signUpBtn = document.getElementById('signUpBtn');
            const termsCheckbox = document.getElementById('termsCheckbox');
            if (signUpBtn) signUpBtn.disabled = !!block;
            if (termsCheckbox) termsCheckbox.disabled = !!block;
            const hint = document.getElementById('termsHint');
            if (hint) hint.style.display = block ? 'block' : 'none';
        }
        function unblockRegistrationControlsIfEligible(){
            const photo = document.getElementById('photo');
            const okPhoto = photo && !photo.classList.contains('invalid');
            const emailOk = window.isEmailVerified === true; // set elsewhere
            const terms = document.getElementById('termsCheckbox');
            const signUpBtn = document.getElementById('signUpBtn');
            if (signUpBtn && terms) {
                signUpBtn.disabled = !(okPhoto && terms.checked && emailOk);
            }
        }

        // Client-side lockout (10 minutes) to mitigate brute-force; backend enforcement recommended
        (function(){
            const LOCK_KEY = 'auth_lockout_until';
            const ATTEMPTS_KEY = 'auth_attempts';
            const WINDOW_KEY = 'auth_window_start';
            const MAX_ATTEMPTS = 5;
            const WINDOW_MS = 10 * 60 * 1000; // 10 minutes
            const LOCKOUT_MS = 10 * 60 * 1000; // 10 minutes

            function now(){ return Date.now(); }
            function getNum(key){ return parseInt(localStorage.getItem(key) || '0', 10); }
            function setNum(key, val){ localStorage.setItem(key, String(val)); }
            function getTs(key){ const v = parseInt(localStorage.getItem(key) || '0', 10); return isNaN(v)?0:v; }
            function setTs(key, val){ localStorage.setItem(key, String(val)); }

            function isLocked(){ return getTs(LOCK_KEY) > now(); }

            function disableAllButtons(){
                document.querySelectorAll('button').forEach(b=>{ b.disabled = true; b.dataset._disabled='1'; });
                // Show lockout message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Account Locked',
                    text: 'Too many login attempts. Your account has been temporarily locked.',
                    confirmButtonColor: '#dc3545',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    zIndex: 10001,
                    didOpen: () => {
                        // Start the countdown timer
                        startLockoutTimer();
                    }
                });
            }

            function enableAllButtons(){
                document.querySelectorAll('button').forEach(b=>{ if(b.dataset._disabled==='1'){ b.disabled = false; delete b.dataset._disabled; } });
                // Close any open SweetAlert
                Swal.close();
            }

            let timerInterval = null;
            function startTimer(){
                function tick(){
                    const rem = Math.max(0, getTs(LOCK_KEY) - now());
                    const m = String(Math.floor(rem/60000)).padStart(2,'0');
                    const s = String(Math.floor((rem%60000)/1000)).padStart(2,'0');
                    
                    // Update SweetAlert content with countdown
                    if(Swal.isVisible()){
                        Swal.update({
                            html: `
                                <div style="text-align: center;">
                                    <h3 style="color: #dc3545; margin-bottom: 15px;">Account Locked</h3>
                                    <p style="color: #666; margin-bottom: 10px;">Too many login attempts. Your account has been temporarily locked.</p>
                                    <p style="color: #dc3545; font-weight: bold; font-size: 18px;">Try again in: <span id="lockout-timer">${m}:${s}</span></p>
                                </div>
                            `
                        });
                    }
                    
                    if(rem<=0){
                        clearInterval(timerInterval); timerInterval=null;
                        localStorage.removeItem(LOCK_KEY);
                        localStorage.removeItem(ATTEMPTS_KEY);
                        localStorage.removeItem(WINDOW_KEY);
                        enableAllButtons();
                    }
                }
                if(timerInterval) clearInterval(timerInterval);
                timerInterval = setInterval(tick, 500);
                tick();
            }

            function startLockoutTimer(){
                startTimer();
            }

            function ensureWindow(){
                const start = getTs(WINDOW_KEY);
                const n = now();
                if(!start || (n - start) > WINDOW_MS){ setTs(WINDOW_KEY, n); setNum(ATTEMPTS_KEY, 0); }
            }

            function recordAttempt(){
                ensureWindow();
                const attempts = getNum(ATTEMPTS_KEY) + 1;
                setNum(ATTEMPTS_KEY, attempts);
                if(attempts >= MAX_ATTEMPTS){ setTs(LOCK_KEY, now() + LOCKOUT_MS); }
            }

            function guardSubmit(e){
                if(isLocked()){
                    e.preventDefault(); e.stopPropagation();
                    disableAllButtons(); startTimer(); return false;
                }
                recordAttempt();
                return true;
            }

            function init(){
                const loginForm = document.querySelector('form[action="/login"]');
                if(loginForm){
                    // reCAPTCHA v3 verification before submitting to backend
                    let recaptchaPassed = false;
                    loginForm.addEventListener('submit', async function(e){
                        e.preventDefault(); e.stopPropagation();
                        
                        if(isLocked()){
                            disableAllButtons(); startTimer(); return false;
                        }
                        recordAttempt();
                        
                        if (!recaptchaPassed) {
                            try {
                                const token = await executeRecaptcha();
                                recaptchaPassed = true;
                                
                                // Get form data
                                const email = loginForm.querySelector('input[name="email"]').value;
                                const password = loginForm.querySelector('input[name="password"]').value;
                                
                                if(!email || !password) {
                                (window.queueSwal || Swal.fire)({
                                        icon: 'warning',
                                        title: 'Missing Fields',
                                        text: 'Enter email and password',
                                        confirmButtonColor: '#ffc107',
                                        zIndex: 10001
                            });
                            return false;
                                }
                                
                                // Disable submit button
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                if(submitBtn) {
                    submitBtn.disabled = true;
                                    submitBtn.textContent = 'Logging in...';
                                }
                                
                                // Immediately show OTP modal for faster UX
                                window.loginAttemptsRemaining = 3;
                                if(window.openLoginOTPModal) {
                                    window.openLoginOTPModal(email);
                                } else {
                                    console.error('openLoginOTPModal function not found');
                                }
                                
                                // Show subtle info
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Check Your Email',
                                    text: 'We are sending your OTP...',
                                    timer: 1200,
                                    showConfirmButton: false,
                                    zIndex: 10001
                                });
                                
                                // Send request in background
                    const formData = new FormData();
                    formData.append('email', email);
                    formData.append('password', password);
                    formData.append('_token', getCSRFToken());
                        formData.append('recaptcha_ok', '1');
                                formData.append('recaptcha_token', token);
                    
                    const response = await fetch('/login', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    let data = {};
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json().catch(() => ({}));
                    } else {
                        const text = await response.text().catch(() => '');
                        try {
                            data = JSON.parse(text);
                        } catch(e) {
                            console.error('Failed to parse response:', text);
                        }
                    }
                    
                    console.log('Login response:', response.status, data);
                    
                    if(response.ok && data.requires_otp){
                        // 2FA required - show modal with OTP input
                        window.loginOtpToken = data.token || '';
                        window.loginOtpEmail = email;
                        console.log('2FA required, opening modal. Token:', window.loginOtpToken);
                        
                        // Ensure modal is visible and properly configured
                        const m = document.getElementById('loginOTPModal');
                        if(m) {
                            // Update email in modal
                            const emailSpan = document.getElementById('loginOtpEmail');
                            if(emailSpan) {
                                emailSpan.textContent = email;
                            }
                            
                            // Clear any previous errors
                            const errorEl = document.getElementById('loginOtpError');
                            if(errorEl) {
                                errorEl.textContent = '';
                            }
                            
                            // Show modal
                            m.style.display = 'flex';
                            m.style.setProperty('display', 'flex', 'important');
                            m.style.setProperty('visibility', 'visible', 'important');
                            m.style.setProperty('opacity', '1', 'important');
                            m.style.setProperty('z-index', '10040', 'important');
                            
                            // Focus first OTP input
                            const firstInput = m.querySelector('.login-otp-digit');
                            if(firstInput) {
                                setTimeout(() => firstInput.focus(), 100);
                            }
                            
                            // Start timer if function exists
                            if(window.startLoginOtpTimer) {
                                window.startLoginOtpTimer();
                            }
                        } else {
                            console.error('loginOTPModal element not found!');
                            // Fallback: show error if modal doesn't exist
                            (window.queueSwal || Swal.fire)({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to display OTP modal. Please refresh the page.',
                                confirmButtonColor: '#dc3545',
                                zIndex: 10001
                            });
                        }
                        
                        // Re-enable submit button in case user needs to try again
                        if(submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Log In';
                        }
                    } else if(response.ok && data.redirect){
                        // Direct redirect (no 2FA required) - close modal first
                        if(window.closeLoginOTPModal) window.closeLoginOTPModal(true);
                        window.location.href = data.redirect;
                    } else {
                        // Login failed - close modal and show error
                        if(window.closeLoginOTPModal) window.closeLoginOTPModal(true);
                        (window.queueSwal || Swal.fire)({
                            icon: 'error',
                            title: 'Login Failed',
                            text: data.message || 'Invalid credentials',
                            confirmButtonColor: '#dc3545',
                            zIndex: 10001
                        });
                        if(submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Log In';
                        }
                    }
                } catch(error) {
                    console.error('Login error:', error);
                    if(window.closeLoginOTPModal) window.closeLoginOTPModal(true);
                    (window.queueSwal || Swal.fire)({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#dc3545',
                        zIndex: 10001
                    });
                                const submitBtn = loginForm.querySelector('button[type="submit"]');
                    if(submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Log In';
                    }
                }
                            return false;
                        }
                        return false;
                    }, {capture:true});
                }
                if(isLocked()){ disableAllButtons(); startTimer(); }
            }
            document.addEventListener('DOMContentLoaded', init);
        })();
    </script>
    <script>
        // Access Token modal logic
        (function(){
            let atTimer = null;
            function clearAtTimer(){ if(atTimer){ clearInterval(atTimer); atTimer=null; } }
            function startAtTimer(){
                let t = 600; const timerEl = document.getElementById('accessOtpTimer'); const countEl = document.getElementById('accessTimerCount'); const btn = document.getElementById('accessResendBtn');
                clearAtTimer(); if(btn) btn.disabled = true; if(timerEl) timerEl.classList.remove('warning');
                atTimer = setInterval(()=>{
                    t--; if(countEl) countEl.textContent = t;
                    if(t<=10 && timerEl) timerEl.classList.add('warning');
                    if(t<=0){ clearAtTimer(); if(btn) btn.disabled=false; if(timerEl){ timerEl.textContent = 'Token expired. Click resend to get a new code.'; timerEl.classList.remove('warning'); } }
                },1000);
            }
            function openAccessModal(email){
                const m = document.getElementById('accessTokenModal'); if(!m) return;
                document.getElementById('accessEmail').textContent = email || '';
                m.style.display = 'flex';
                document.querySelectorAll('#accessOtpInputs .access-otp-digit').forEach(i=> i.value='');
                document.querySelector('#accessOtpInputs .access-otp-digit')?.focus();
                startAtTimer();
            }
            window.resendAccessToken = async function(){
                const email = document.getElementById('accessEmail')?.textContent||'';
                try{
                    const res = await fetchWithCSRFRetry('/access-token/resend', { method:'POST', body: JSON.stringify({ email }) });
                    const data = await res.json().catch(()=>({}));
                    if(res.ok){ startAtTimer(); document.getElementById('accessError').textContent=''; }
                    else { document.getElementById('accessError').textContent = data.message || 'Failed to send token'; }
                }catch(e){ handleCSRFError(e, 'resend access token'); }
            };
            window.verifyAccessToken = async function(){
                const email = document.getElementById('accessEmail')?.textContent||'';
                const code = (document.getElementById('accessTokenInput')?.value||'').trim();
                if(code.length<20){ document.getElementById('accessError').textContent = 'Paste the full token string'; return; }
                try{
                    const res = await fetchWithCSRFRetry('/access-token/verify', { method:'POST', body: JSON.stringify({ email, token: code }) });
                    const data = await res.json().catch(()=>({}));
                    if(res.ok){
                        // Success: show congrats, then close modal and stay on login form
                        (window.queueSwal || Swal.fire)({
                            icon: 'success',
                            title: 'Congratulations!',
                            text: 'You are now able to login.',
                            confirmButtonColor: '#28a745',
                            zIndex: 10001,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(()=>{
                            const m = document.getElementById('accessTokenModal'); if(m) m.style.display='none';
                            // Optionally prefill email field on login form
                            const emailInput = document.querySelector('form[action="/login"] input[name="email"]');
                            if(emailInput && email){ emailInput.value = email; emailInput.focus(); }
                        });
                        return;
                    }
                    document.getElementById('accessError').textContent = data.message || 'Invalid token';
                }catch(e){ handleCSRFError(e, 'verify access token'); }
            };
            document.addEventListener('DOMContentLoaded', function(){
                if(window.ACCESS_TOKEN_MODAL){
                    openAccessModal(window.ACCESS_TOKEN_EMAIL||'');
                    document.getElementById('accessTokenInput')?.focus();
                }
                
                // Handle device binding required
                if(window.DEVICE_BINDING_REQUIRED){
                    const email = window.DEVICE_BINDING_EMAIL || '';
                    showDeviceBindingMessage(email);
                }
            });
            
            // Function to show device binding message
            function showDeviceBindingMessage(email){
                Swal.fire({
                    title: 'Device Binding Required',
                    html: `
                        <p style="margin-bottom: 15px; font-size: 16px; font-weight: 600;">This account is binded to your device</p>
                        <p style="margin-bottom: 20px; color: #6c757d; font-size: 14px;">Please ask the owner for a share device token</p>
                    `,
                    icon: 'warning',
                    showCancelButton: false,
                    showDenyButton: false,
                    confirmButtonText: 'Share Device Token',
                    confirmButtonColor: '#007bff',
                    width: '500px',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    buttonsStyling: true,
                    didOpen: () => {
                        setTimeout(() => {
                            const confirmBtn = document.querySelector('.swal2-confirm');
                            if (confirmBtn) {
                                confirmBtn.style.minWidth = '200px';
                                confirmBtn.style.padding = '12px 24px';
                                confirmBtn.style.fontSize = '14px';
                            }
                        }, 100);
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show device share token input modal
                        showDeviceShareTokenModal(email);
                    }
                });
            }
            
            // Function to show device share token pasting modal
            function showDeviceShareTokenModal(email){
                Swal.fire({
                    title: 'Enter Share Device Token',
                    html: `
                        <p style="margin-bottom: 15px; color: #6c757d; font-size: 14px;">Paste the share device token provided by the account owner</p>
                        <input type="text" id="deviceShareTokenInput" class="swal2-input" placeholder="Paste share device token here" style="width: 100%; padding: 12px; border: 2px solid #007bff; border-radius: 8px; font-family: monospace; font-size: 14px;">
                        <input type="hidden" id="deviceShareTokenEmail" value="${email}">
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Verify Token',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    preConfirm: () => {
                        const token = document.getElementById('deviceShareTokenInput').value.trim();
                        const email = document.getElementById('deviceShareTokenEmail').value;
                        
                        if (!token) {
                            Swal.showValidationMessage('Please enter the share device token');
                            return false;
                        }
                        
                        return fetch('/device-share-token/verify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCSRFToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ email: email, token: token })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw new Error(data.message || 'Token verification failed');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Share token to other device success, You are now able to login',
                                confirmButtonText: 'Continue',
                                confirmButtonColor: '#28a745',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                // Redirect to dashboard after success message
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    // Reload the page to show login form again
                                    window.location.reload();
                                }
                            });
                            return true;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message || 'Failed to verify token. Please try again.');
                            return false;
                        });
                    }
                });
            }
        })();
    </script>
    <script>
        // Login OTP Modal Logic (2FA) - 1 minute timer
        (function(){
            let loginOtpTimer = null;
            window.loginOtpToken = '';
            window.loginOtpEmail = '';
            window.loginAttemptsRemaining = 3;
            
            function clearLoginOtpTimer(){ if(loginOtpTimer){ clearInterval(loginOtpTimer); loginOtpTimer=null; } }
            window.startLoginOtpTimer = function(){
                let t = 60; // 1 minute (60 seconds)
                const timerEl = document.getElementById('loginOtpTimer');
                const countEl = document.getElementById('loginTimerCount');
                const btn = document.getElementById('loginResendBtn');
                clearLoginOtpTimer();
                if(btn) btn.disabled = true;
                if(timerEl) timerEl.classList.remove('warning');
                loginOtpTimer = setInterval(()=>{
                    t--;
                    if(countEl) countEl.textContent = t;
                    if(t<=10 && timerEl) timerEl.classList.add('warning');
                    if(t<=0){
                        clearLoginOtpTimer();
                        if(btn) btn.disabled = false;
                        if(timerEl){
                            timerEl.textContent = 'OTP expired. Click resend to get a new code.';
                            timerEl.classList.remove('warning');
                        }
                    }
                },1000);
            }

            async function postLoginJSON(url, payload){
                const res = await fetchWithCSRFRetry(url, { method:'POST', body: JSON.stringify(payload) });
                let data={}; try{ data=await res.json(); }catch(e){}
                return { ok: res.ok, status: res.status, data };
            }

            window.openLoginOTPModal = function(email){
                const m = document.getElementById('loginOTPModal');
                if(!m) {
                    console.error('loginOTPModal element not found!');
                    return;
                }
                console.log('Opening login OTP modal for:', email);
                m.style.display = 'flex';
                m.style.setProperty('display', 'flex', 'important');
                m.style.setProperty('visibility', 'visible', 'important');
                m.style.setProperty('opacity', '1', 'important');
                m.style.setProperty('z-index', '10040', 'important');
                if(email) {
                    const emailSpan = document.getElementById('loginOtpEmail');
                    if(emailSpan) {
                        emailSpan.textContent = email;
                    }
                    window.loginOtpEmail = email;
                }
                // Clear OTP inputs and errors
                document.querySelectorAll('#loginOTPModal .login-otp-digit').forEach(i=>i.value='');
                const errorEl = document.getElementById('loginOtpError');
                if(errorEl) {
                    errorEl.textContent = '';
                }
                const first = document.querySelector('#loginOTPModal .login-otp-digit');
                if(first) {
                    setTimeout(() => first.focus(), 100);
                }
                if(window.startLoginOtpTimer) {
                    window.startLoginOtpTimer();
                }
            };
            
            window.closeLoginOTPModal = function(force = false){
                if(!force) return; // Prevent accidental closing
                const m = document.getElementById('loginOTPModal');
                if(!m) return;
                    m.style.display = 'none';
                clearLoginOtpTimer();
                window.loginOtpToken = '';
                window.loginOtpEmail = '';
                document.querySelectorAll('#loginOTPModal .login-otp-digit').forEach(i=>i.value='');
            };
            
            window.resendLoginOTP = async function(){
                const email = window.loginOtpEmail || document.getElementById('loginOtpEmail')?.textContent||'';
                const r = await postLoginJSON('/login/resend-otp', { token: window.loginOtpToken, email });
                if(r.ok){
                    window.loginOtpToken = r.data.token || window.loginOtpToken;
                        startLoginOtpTimer();
                        document.getElementById('loginOtpError').textContent = '';
                        (window.queueSwal || Swal.fire)({
                            icon: 'success',
                            title: 'OTP Resent',
                            text: 'A new OTP has been sent to your email.',
                            confirmButtonColor: '#28a745',
                            timer: 2000,
                            zIndex: 10001
                        });
                    } else {
                    document.getElementById('loginOtpError').textContent = r.data?.message || 'Failed to resend OTP';
                }
            };
            
            window.verifyLoginOTP = async function(){
                const code = Array.from(document.querySelectorAll('#loginOTPModal .login-otp-digit')).map(i=>i.value).join('');
                if(code.length!==6){
                    (window.queueSwal || Swal.fire)({
                        icon: 'warning',
                        title: 'Incomplete OTP',
                        text: 'Enter 6-digit OTP',
                        confirmButtonColor: '#ffc107',
                        zIndex: 10001
                    });
                    return;
                }
                const r = await postLoginJSON('/login/verify-otp', { token: window.loginOtpToken, otp: code, email: window.loginOtpEmail });
                if(r.ok){
                    // Close modal and redirect to dashboard
                    if(window.closeLoginOTPModal) window.closeLoginOTPModal(true);
                    window.location.href = r.data?.redirect || '/dashboard';
                    } else {
                    window.loginAttemptsRemaining = (r.data && r.data.remaining_attempts!==undefined) ? r.data.remaining_attempts : (window.loginAttemptsRemaining-1);
                    if(window.loginAttemptsRemaining <= 0){
                        if(window.closeLoginOTPModal) window.closeLoginOTPModal(true);
                        (window.queueSwal || Swal.fire)({
                            icon: 'error',
                            title: 'Locked',
                            text: 'Too many invalid OTP attempts. Please try again later.',
                            confirmButtonColor: '#dc3545',
                            zIndex: 10001
                        });
                        return;
                    }
                    document.getElementById('loginOtpError').textContent = r.data?.message || `Invalid OTP. Attempts left: ${window.loginAttemptsRemaining}`;
                    document.querySelectorAll('#loginOTPModal .login-otp-digit').forEach(i=>i.value='');
                    const first = document.querySelector('#loginOTPModal .login-otp-digit');
                    if(first) first.focus();
                }
            };
            
            // OTP input navigation
            document.addEventListener('DOMContentLoaded', function(){
                const inputs = document.querySelectorAll('#loginOTPModal .login-otp-digit');
                inputs.forEach((input, idx)=>{
                    input.addEventListener('input', function(){
                        if(this.value && idx<inputs.length-1){
                            inputs[idx+1].focus();
                        }
                    });
                    input.addEventListener('keydown', function(e){
                        if(e.key==='Backspace' && !this.value && idx>0){
                            inputs[idx-1].focus();
                        }
                    });
                    input.addEventListener('paste', function(e){
                        e.preventDefault();
                        const paste = (e.clipboardData || window.clipboardData).getData('text');
                        const digits = paste.replace(/\D/g, '').slice(0, 6);
                        digits.split('').forEach((digit, i) => {
                            if(inputs[i]) inputs[i].value = digit;
                        });
                        if(digits.length === 6) inputs[5].focus();
                    });
                });
            });
        })();
    </script>
    <script>
        // Global CSRF token management
        function getCSRFToken() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token) {
                console.error('CSRF token not found');
                throw new Error('CSRF token not found');
            }
            return token;
        }

        // Function to refresh CSRF token if needed
        async function refreshCSRFToken() {
            try {
                const response = await fetch('/csrf-token', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                if (response.ok) {
                    const data = await response.json();
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', data.csrf_token);
                    }
                    return data.csrf_token;
                }
            } catch (error) {
                console.error('Failed to refresh CSRF token:', error);
            }
            return null;
        }

        // Enhanced fetch function with CSRF token retry logic
        async function fetchWithCSRFRetry(url, options = {}) {
            try {
                // First attempt with current token
                const csrfToken = getCSRFToken();
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    ...options.headers
                };
                
                const response = await fetch(url, {
                    ...options,
                    headers,
                    credentials: 'same-origin'
                });
                
                // If CSRF token mismatch, try to refresh and retry once
                if (response.status === 419 || response.status === 403) {
                    console.log('CSRF token mismatch detected, refreshing token...');
                    const newToken = await refreshCSRFToken();
                    if (newToken) {
                        const retryHeaders = {
                            ...headers,
                            'X-CSRF-TOKEN': newToken
                        };
                        return await fetch(url, {
                            ...options,
                            headers: retryHeaders,
                            credentials: 'same-origin'
                        });
                    }
                }
                
                return response;
            } catch (error) {
                console.error('Fetch with CSRF retry failed:', error);
                throw error;
            }
        }

        // Function to handle CSRF token errors with user-friendly messages
        function handleCSRFError(error, context = 'operation') {
            console.error(`CSRF error in ${context}:`, error);
            
            // Show user-friendly error message
            if (window.queueSwal || Swal) {
                (window.queueSwal || Swal.fire)({
                    icon: 'error',
                    title: 'Session Expired',
                    text: 'Your session has expired. Please refresh the page and try again.',
                    confirmButtonColor: '#dc3545',
                    zIndex: 10001,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    // Optionally refresh the page
                    window.location.reload();
                });
            }
        }

        // Super Admin Modal Logic + Keyboard Shortcut (Ctrl+Alt+Shift+S)
        (function(){
            let saOtpTimer = null;
            let saToken = '';
            let saAttemptsRemaining = 2; // two retries
            const SA_LOCK_KEY = 'sa_lock_until'; // 24h lockout
            // Super Admin Reset State
            let sarToken = '';
            let sarTimer = null;

            function now(){ return Date.now(); }
            function getTs(k){ const v = parseInt(localStorage.getItem(k) || '0', 10); return isNaN(v)?0:v; }
            function setTs(k,v){ localStorage.setItem(k, String(v)); }

            window.openSuperAdminModal = function(){
                if(getTs(SA_LOCK_KEY) > now()){
                    const rem = getTs(SA_LOCK_KEY) - now();
                    const hrs = Math.ceil(rem/3600000);
                    Swal.fire({ icon:'error', title:'Locked', text:`Super Admin login locked. Try again in about ${hrs} hour(s).`, confirmButtonColor:'#dc3545', zIndex:10001 });
                    return;
                }
                const m = document.getElementById('superAdminModal'); if(!m) return;
                m.style.display = 'flex';
                resetSaToStep(1);
                const email = document.getElementById('saEmail'); if(email) email.focus();
            };

            window.closeSuperAdminModal = function(){
                const m = document.getElementById('superAdminModal'); if(!m) return;
                m.style.display = 'none';
                resetSaToStep(1);
                clearSaTimer();
                // clear fields
                const f1 = document.getElementById('saLoginForm'); if(f1) f1.reset();
                document.querySelectorAll('#superAdminModal .sa-otp-digit').forEach(i=>i.value='');
            };

            // Super Admin Reset Modal controls
            window.openSuperAdminResetModal = function(){
                const m = document.getElementById('superAdminResetModal'); if(!m) return;
                m.style.display = 'flex';
                resetSarToStep(1);
                document.getElementById('sarResetEmail')?.focus();
            };
            window.closeSuperAdminResetModal = function(triggeredByCloseBtn){
                // If closing during OTP step with no input, warn
                if (triggeredByCloseBtn && document.getElementById('sarStep2')?.classList.contains('active')){
                    const hasAny = Array.from(document.querySelectorAll('#superAdminResetModal .sar-otp-digit')).some(i=> (i.value||'').trim() !== '');
                    if(!hasAny){ (window.queueSwal || Swal.fire)({ icon:'warning', title:'OTP Required', text:'Enter the 6-digit OTP to proceed.', confirmButtonColor:'#ffc107', zIndex:10001 }); }
                }
                const m = document.getElementById('superAdminResetModal'); if(!m) return;
                m.style.display = 'none';
                resetSarToStep(1);
                clearSarTimer();
            };
            function resetSarToStep(step){
                document.querySelectorAll('#superAdminResetModal .sa-step').forEach(s=>s.classList.remove('active'));
                document.getElementById(step===1?'sarStep1':(step===2?'sarStep2':'sarStep3')).classList.add('active');
            }
            function clearSarTimer(){ if(sarTimer){ clearInterval(sarTimer); sarTimer=null; } }
            function startSarTimer(){
                let t=60; const el=document.getElementById('sarTimerCount'); const timerEl=document.getElementById('sarOtpTimer'); const btn=document.getElementById('sarResendBtn');
                clearSarTimer(); if(btn) btn.disabled = true; if(timerEl) timerEl.classList.remove('warning');
                sarTimer = setInterval(()=>{
                    t--; if(el) el.textContent = t;
                    if(t<=10 && timerEl) timerEl.classList.add('warning');
                    if(t<=0){ clearSarTimer(); if(btn) btn.disabled=false; if(timerEl){ timerEl.textContent='OTP expired. Click resend to get a new code.'; timerEl.classList.remove('warning'); } }
                },1000);
            }
            async function saPostJSON(url, payload){
                const res = await fetchWithCSRFRetry(url, { method:'POST', body: JSON.stringify(payload) });
                let data={}; try{ data=await res.json(); }catch(e){}
                return { ok: res.ok, status: res.status, data };
            }
            window.saSendResetOTP = async function(){
                const email = (document.getElementById('sarResetEmail')?.value||'').trim();
                if(!email){ (window.queueSwal || Swal.fire)({ icon:'warning', title:'Email Required', text:'Enter Super Admin email', confirmButtonColor:'#ffc107', zIndex:10001 }); return; }
                
                // Make the API call directly without SweetAlert
                saMakeOTPRequest(email);
            };
            
            async function saMakeOTPRequest(email) {
                const r = await saPostJSON('/super-admin/password/send-otp', { email });
                if(r.ok){ 
                    sarToken = r.data.token || '';
                    // Directly show Verify OTP step without SweetAlert
                    resetSarToStep(2); 
                    document.getElementById('sarOtpEmail').textContent = email; 
                    startSarTimer();
                    const first=document.querySelector('#superAdminResetModal .sar-otp-digit'); 
                    if(first) first.focus();
                }
                else {
                    document.getElementById('superAdminResetModal').style.display='none';
                    setTimeout(()=>{ (window.queueSwal || Swal.fire)({ icon:'error', title:'Failed to Send OTP', text:r.data?.message||'Try again later', confirmButtonColor:'#dc3545', zIndex:10001, allowOutsideClick:false, allowEscapeKey:false }).then(()=>{ document.getElementById('superAdminResetModal').style.display='flex'; resetSarToStep(1); }); }, 100);
                }
            }
            window.saResendResetOTP = function(){ const email=document.getElementById('sarOtpEmail').textContent; window.saSendResetOTP(email); };
            window.saVerifyResetOTP = async function(){
                const code = Array.from(document.querySelectorAll('#superAdminResetModal .sar-otp-digit')).map(i=>i.value).join('');
                if(code.length!==6){ (window.queueSwal || Swal.fire)({ icon:'warning', title:'Incomplete OTP', text:'Enter 6-digit OTP', confirmButtonColor:'#ffc107', zIndex:10001 }); return; }
                const r = await saPostJSON('/super-admin/password/verify-otp', { token: sarToken, otp: code });
                if(r.ok){ resetSarToStep(3); clearSarTimer(); }
                else {
                    document.getElementById('superAdminResetModal').style.display='none';
                    setTimeout(()=>{ (window.queueSwal || Swal.fire)({ icon:'error', title:'Invalid OTP', text:r.data?.message||'Invalid OTP', confirmButtonColor:'#dc3545', zIndex:10001, allowOutsideClick:false, allowEscapeKey:false }).then(()=>{ document.getElementById('superAdminResetModal').style.display='flex'; document.querySelectorAll('#superAdminResetModal .sar-otp-digit').forEach(i=>i.value=''); document.querySelector('#superAdminResetModal .sar-otp-digit')?.focus(); }); }, 100);
                }
            };
            window.saUpdatePassword = async function(){
                const p1 = document.getElementById('sarNewPassword')?.value||''; const p2 = document.getElementById('sarConfirmPassword')?.value||'';
                if(p1.length<6){ (window.queueSwal || Swal.fire)({ icon:'warning', title:'Password Too Short', text:'Minimum 6 characters', confirmButtonColor:'#ffc107', zIndex:10001 }); return; }
                if(p1!==p2){ (window.queueSwal || Swal.fire)({ icon:'error', title:'Passwords Do Not Match', text:'Please confirm the same password', confirmButtonColor:'#dc3545', zIndex:10001 }); return; }
                const r = await saPostJSON('/super-admin/password/update', { token: sarToken, password: p1, password_confirmation: p2 });
                if(r.ok){ document.getElementById('superAdminResetModal').style.display='none'; (window.queueSwal || Swal.fire)({ icon:'success', title:'Password Updated', text:'You can log in with the new password.', confirmButtonColor:'#28a745', zIndex:10001 }); }
                else {
                    document.getElementById('superAdminResetModal').style.display='none'; setTimeout(()=>{ (window.queueSwal || Swal.fire)({ icon:'error', title:'Failed to Update', text:r.data?.message||'Please try again', confirmButtonColor:'#dc3545', zIndex:10001 }).then(()=>{ document.getElementById('superAdminResetModal').style.display='flex'; }); }, 100);
                }
            };

            function resetSaToStep(step){
                document.querySelectorAll('#superAdminModal .sa-step').forEach(s=>s.classList.remove('active'));
                document.getElementById(step===1?'saStep1':'saStep2').classList.add('active');
            }

            function clearSaTimer(){ if(saOtpTimer){ clearInterval(saOtpTimer); saOtpTimer=null; } }
            function startSaTimer(){
                let t = 60; const timerEl = document.getElementById('saOtpTimer'); const countEl = document.getElementById('saTimerCount'); const btn = document.getElementById('saResendBtn');
                clearSaTimer(); if(btn) btn.disabled = true; if(timerEl) timerEl.classList.remove('warning');
                saOtpTimer = setInterval(()=>{
                    t--; if(countEl) countEl.textContent = t;
                    if(t<=10 && timerEl) timerEl.classList.add('warning');
                    if(t<=0){
                        clearSaTimer();
                        if(btn) btn.disabled = false;
                        if(timerEl){ timerEl.textContent = 'OTP expired. Click resend to get a new code.'; timerEl.classList.remove('warning'); }
                    }
                },1000);
            }

            async function postJSON(url, payload){
                const res = await fetchWithCSRFRetry(url, { method:'POST', body: JSON.stringify(payload) });
                let data={}; try{ data=await res.json(); }catch(e){}
                return { ok: res.ok, status: res.status, data };
            }

            window.superAdminLogin = async function(){
                if(getTs(SA_LOCK_KEY) > now()) return openSuperAdminModal();
                const email = (document.getElementById('saEmail')?.value||'').trim();
                const password = document.getElementById('saPassword')?.value||'';
                if(!email || !password){
                    Swal.fire({ icon:'warning', title:'Missing Fields', text:'Enter email and password', confirmButtonColor:'#ffc107', zIndex:10001 });
                    return;
                }
                // Immediately show OTP step for faster UX while the server sends the email
                saAttemptsRemaining = 2;
                const emailSpan = document.getElementById('saOtpEmail'); if(emailSpan) emailSpan.textContent = email;
                resetSaToStep(2);
                startSaTimer();
                const first = document.querySelector('#superAdminModal .sa-otp-digit'); if(first) first.focus();
                // Show subtle info
                Swal.fire({ icon:'info', title:'Check Your Email', text:'We are sending your OTP...', timer:1200, showConfirmButton:false, zIndex:10001 });
                // Send request in background
                const r = await postJSON('/super-admin/login', { email, password });
                if(r.ok){
                    saToken = r.data.token;
                    // stay on OTP step
                } else {
                    // revert to login step on error
                    resetSaToStep(1);
                    Swal.fire({ icon:'error', title:'Login Failed', text: r.data?.message || 'Invalid credentials or super-admin already exists', confirmButtonColor:'#dc3545', zIndex:10001 });
                }
            };

            window.resendSuperAdminOTP = async function(){
                const email = document.getElementById('saOtpEmail')?.textContent||'';
                const r = await postJSON('/super-admin/resend-otp', { token: saToken, email });
                if(r.ok){ startSaTimer(); if(r.data && r.data.debug_otp){ const hint = document.getElementById('saOtpDevHint'); if(hint){ hint.textContent='Development Mode - OTP: '+r.data.debug_otp; hint.style.display='block'; } } }
                else { Swal.fire({ icon:'error', title:'Failed', text:r.data?.message||'Failed to resend', confirmButtonColor:'#dc3545', zIndex:10001 }); }
            };

            window.verifySuperAdminOTP = async function(){
                const code = Array.from(document.querySelectorAll('#superAdminModal .sa-otp-digit')).map(i=>i.value).join('');
                if(code.length!==6){
                    Swal.fire({ icon:'warning', title:'Incomplete OTP', text:'Enter 6-digit OTP', confirmButtonColor:'#ffc107', zIndex:10001 });
                    return;
                }
                const r = await postJSON('/super-admin/verify-otp', { token: saToken, otp: code });
                if(r.ok){
                    window.location.href = '/admin-dashboard';
                } else {
                    saAttemptsRemaining = (r.data && r.data.remaining_attempts!==undefined) ? r.data.remaining_attempts : (saAttemptsRemaining-1);
                    if(saAttemptsRemaining <= 0){
                        setTs(SA_LOCK_KEY, now() + 24*60*60*1000);
                        closeSuperAdminModal();
                        Swal.fire({ icon:'error', title:'Locked', text:'Too many invalid OTP attempts. Super Admin login locked for 24 hours.', confirmButtonColor:'#dc3545', zIndex:10001 });
                        return;
                    }
                    Swal.fire({ icon:'error', title:'Invalid OTP', text:`Try again. Attempts left: ${saAttemptsRemaining}`, confirmButtonColor:'#dc3545', zIndex:10001 }).then(()=>{
                        document.querySelectorAll('#superAdminModal .sa-otp-digit').forEach(i=>i.value='');
                        const first = document.querySelector('#superAdminModal .sa-otp-digit'); if(first) first.focus();
                    });
                }
            };

            // Shortcut handler
            document.addEventListener('keydown', function(e){
                if(e.ctrlKey && e.altKey && e.shiftKey && (e.key==='S' || e.key==='s')){
                    e.preventDefault();
                    openSuperAdminModal();
                }
            });

            // OTP navigation for SA
            document.addEventListener('DOMContentLoaded', function(){
                const inputs = document.querySelectorAll('#superAdminModal .sa-otp-digit');
                inputs.forEach((input, idx)=>{
                    input.addEventListener('input', function(){ if(this.value && idx<inputs.length-1){ inputs[idx+1].focus(); } });
                    input.addEventListener('keydown', function(e){ if(e.key==='Backspace' && !this.value && idx>0){ inputs[idx-1].focus(); } });
                });

                // Check status: if already registered, keep register option; else allow registration
                fetch('/super-admin/status', { headers: { 'Accept':'application/json' }})
                    .then(r=>r.json()).then(s=>{
                        if(s && s.registered){
                            const btn = document.getElementById('saRegisterBtn');
                            if(btn){ btn.style.display = 'none'; }
                        }
                    }).catch(()=>{});
            });

            // Registration modal controls
            window.openSuperAdminRegister = function(){
                fetch('/super-admin/status', { headers: { 'Accept':'application/json' }})
                    .then(r=>r.json()).then(s=>{
                        if(s && s.registered){
                            Swal.fire({ icon:'info', title:'Already Registered', text:'A Super Admin is already registered.', confirmButtonColor:'#17a2b8', zIndex:10001 });
                        } else {
                            const m = document.getElementById('superAdminRegisterModal'); if(m){ m.style.display='flex'; document.getElementById('sarEmail').focus(); }
                        }
                    }).catch(()=>{
                        const m = document.getElementById('superAdminRegisterModal'); if(m){ m.style.display='flex'; document.getElementById('sarEmail').focus(); }
                    });
            };
            window.closeSuperAdminRegister = function(){ const m=document.getElementById('superAdminRegisterModal'); if(m){ m.style.display='none'; const f=document.getElementById('saRegisterForm'); if(f) f.reset(); } };

            window.registerSuperAdmin = async function(){
                const email = (document.getElementById('sarEmail')?.value||'').trim();
                const p1 = document.getElementById('sarPassword')?.value||'';
                const p2 = document.getElementById('sarPassword2')?.value||'';
                if(!email || !p1 || !p2){
                    Swal.fire({ icon:'warning', title:'Missing Fields', text:'Fill all fields', confirmButtonColor:'#ffc107', zIndex:10001 }); return;
                }
                if(p1.length < 6){ Swal.fire({ icon:'warning', title:'Weak Password', text:'Minimum 6 characters', confirmButtonColor:'#ffc107', zIndex:10001 }); return; }
                if(p1 !== p2){ Swal.fire({ icon:'error', title:'Password Mismatch', text:'Passwords do not match', confirmButtonColor:'#dc3545', zIndex:10001 }); return; }
                try{
                    const res = await fetch('/super-admin/register', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept':'application/json' }, credentials:'same-origin', body: JSON.stringify({ email, password: p1 }) });
                    const data = await res.json().catch(()=>({}));
                    if(res.ok){
                        closeSuperAdminRegister();
                        Swal.fire({ icon:'success', title:'Registered', text:'Super Admin registered. You can now log in.', confirmButtonColor:'#28a745', zIndex:10001 });
                        // prefill login email
                        const e = document.getElementById('saEmail'); if(e){ e.value = email; }
                    } else if(res.status===409){
                        closeSuperAdminRegister();
                        Swal.fire({ icon:'info', title:'Already Registered', text:data.message||'Super Admin already registered', confirmButtonColor:'#17a2b8', zIndex:10001 });
                    } else {
                        Swal.fire({ icon:'error', title:'Failed', text:data.message||'Registration failed', confirmButtonColor:'#dc3545', zIndex:10001 });
                    }
                }catch(e){
                    Swal.fire({ icon:'error', title:'Network Error', text:'Please try again.', confirmButtonColor:'#dc3545', zIndex:10001 });
                }
            };
        })();
    </script>
    <style>
        /* Super Admin Modal */
        #superAdminModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 10050;
            align-items: center;
            justify-content: center;
        }

        .sa-modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 520px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }

        .sa-header { text-align:center; margin-bottom: 20px; }
        .sa-header h3 { margin: 0 0 6px 0; font-size: 22px; color:#222; }
        .sa-header p { margin: 0; color:#666; font-size: 14px; }

        .sa-step { display:none; }
        .sa-step.active { display:block; }

        .sa-hint { text-align:center; color:#c00; font-weight:bold; margin-top:8px; display:none; }
        .sa-timer { text-align:center; color:#666; font-size: 14px; margin-top:8px; }
        .sa-timer.warning { color:#dc3545; }
        
        /* X-Frame-Options protection */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
    
    <!-- Clickjacking protection (best-effort in-page) -->
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none'">
    
    <!-- Google reCAPTCHA v3 Script -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LeX6fgrAAAAAHeDtz1_Aj2o5o8GN6FTRJAjHVhI"></script>
    
    <!-- X-Frame-Options Protection Script -->
    <script>
        // Prevent iframe embedding
        if (window.top !== window.self) {
            window.top.location = window.self.location;
        }
        
        // Additional protection against clickjacking
        document.addEventListener('DOMContentLoaded', function() {
            if (window.top !== window.self) {
                document.body.style.display = 'none';
                window.top.location = window.self.location;
            }
        });
    </script>
</head>
<body>
    <!-- Session Flash Messages -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                (window.queueSwal || Swal.fire)({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#28a745',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    zIndex: 10001
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                (window.queueSwal || Swal.fire)({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc3545',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    zIndex: 10001
                });
            });
        </script>
    @endif

    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                (window.queueSwal || Swal.fire)({
                    icon: 'warning',
                    title: 'Warning!',
                    text: '{{ session('warning') }}',
                    confirmButtonColor: '#ffc107',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    zIndex: 10001
                });
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let errorMessage = '';
                @foreach($errors->all() as $error)
                    errorMessage += '{{ $error }}\n';
                @endforeach
                
                (window.queueSwal || Swal.fire)({
                    icon: 'error',
                    title: 'Validation Error!',
                    text: errorMessage.trim(),
                    confirmButtonColor: '#dc3545',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    zIndex: 10001
                });
            });
        </script>
    @endif

    <!-- Access Token Modal -->
    <div id="accessTokenModal">
        <div class="access-modal-content">
            <button class="modal-close" type="button" onclick="/* persistent */ false">&times;</button>
            <div class="access-header">
                <h3>Enter Access Token</h3>
                <p>We sent a token to <span id="accessEmail" class="access-email"></span></p>
            </div>
            <div style="margin:14px 0;">
                <input id="accessTokenInput" type="text" placeholder="Paste token here" style="width:100%; padding:14px 16px; border:2px solid #007bff; border-radius:10px; font-family:monospace; font-size:14px;">
            </div>
            <div class="otp-timer" id="accessOtpTimer">Expires in <span id="accessTimerCount">600</span>s</div>
            <div class="access-resend">
                <button id="accessResendBtn" type="button" onclick="resendAccessToken()">Resend Token</button>
            </div>
            <div class="access-error" id="accessError"></div>
            <div class="access-actions">
                <button type="button" onclick="verifyAccessToken()">Verify & Login</button>
            </div>
        </div>
    </div>

    <div class="container" id="container">
        <!-- Sign Up Form -->
        <div class="form-container sign-up-container" id="registerForm">
            <div class="form-content">
                <h1>Create Account</h1>
                <p>Use your Gmail account for registration</p>
                <div id="emailVerificationStatus" style="display:none; background:#fff3cd; border:1px solid #ffeaa7; color:#856404; padding:10px; border-radius:5px; margin-bottom:15px; font-size:14px;">
                    <strong>⚠️ Email Verification Required:</strong> Please verify your Gmail address to continue registration.
                </div>
                <form method="POST" action="/register" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Full Name" required />
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Gmail Address (e.g., user@gmail.com)" required pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid Gmail address" />
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" required />
                    </div>
                    <div class="form-group">
                        <input type="password" name="password_confirmation" placeholder="Confirm Password" required />
                    </div>
                    <div class="form-group">
                        <div class="file-input-wrapper">
                            <input type="file" name="photo" accept="image/*" required class="hidden-file-input" id="photo" onchange="handleFileSelect(this)" />
                            <label for="photo" class="file-input-label">Choose Profile Picture</label>
                        </div>
                    </div>
                    <div class="form-group" style="text-align:left; font-size:14px; color:#555;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; user-select:none;">
                            <input id="termsCheckbox" type="checkbox" style="width:18px; height:18px; cursor:pointer;" />
                            <span>I agree to the <a href="#" onclick="openTermsModal(); return false;">Terms of Agreement</a></span>
                        </label>
                        <div style="margin-top:8px; font-size:12px; color:#a00; display:none;" id="termsHint">You must accept the Terms to proceed.</div>
                    </div>
                    <button type="submit" id="signUpBtn" disabled>Sign Up</button>
                </form>
            </div>
        </div>

        <!-- Sign In Form -->
        <div class="form-container sign-in-container" id="loginForm">
            <div class="form-content">
                <h1>Log In</h1>
                <p>Use your approved Gmail account to access the IT Inventory System</p>
                <form method="POST" action="/login">
                    @csrf
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Gmail Address (e.g., user@gmail.com)" required pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid Gmail address" />
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" required />
                    </div>
                    <div style="display:flex; gap:12px; align-items:center; justify-content:center; flex-wrap:wrap;">
                        <button type="submit">Log In</button>
                        <button type="button" onclick="openPasswordResetModal()" class="btn-like" style="display:inline-block; text-decoration:none;">Forgot Password?</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Overlay -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please log in with your personal info</p>
                    <button class="ghost" onclick="showForm('loginForm')">Log In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start your journey with us</p>
                    <button class="ghost" onclick="showForm('registerForm')">Sign Up</button>
                </div>
            </div>
        </div>

        <!-- Mobile Toggle (hidden on desktop) -->
        <div class="mobile-toggle">
            <button onclick="showForm('loginForm')">Log In</button>
            <button onclick="showForm('registerForm')">Sign Up</button>
        </div>
    </div>

    <!-- Terms of Agreement Modal -->
    <div id="termsModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:10070; align-items:center; justify-content:center;">
        <div class="reset-modal-content" style="max-width:700px; max-height:80vh; display:flex; flex-direction:column;">
            <button class="modal-close" onclick="closeTermsModal(true)">&times;</button>
            <div class="reset-header">
                <h3>Terms of Agreement</h3>
                <p>Please read and accept to continue registration</p>
            </div>
            <div style="flex:1; overflow:auto; border:1px solid #eee; border-radius:10px; padding:16px; line-height:1.6; color:#333;">
                <h4 style="margin:0 0 8px 0;">1. Use of the System</h4>
                <p>You agree to use this IT Inventory System responsibly and in compliance with applicable policies and laws.</p>
                <h4 style="margin:16px 0 8px 0;">2. Account and Access</h4>
                <p>You are responsible for maintaining the confidentiality of your credentials and for all activities under your account.</p>
                <h4 style="margin:16px 0 8px 0;">3. Data and Privacy</h4>
                <p>Uploaded photos and information are used for asset tracking. Data may be logged for security and auditing.</p>
                <h4 style="margin:16px 0 8px 0;">4. Acceptable Use</h4>
                <p>Do not upload malicious files or attempt to bypass security controls. Violations may result in suspension.</p>
                <h4 style="margin:16px 0 8px 0;">5. Changes</h4>
                <p>Terms may change with notice. Continued use indicates acceptance of updated terms.</p>
            </div>
            <div style="text-align:right; margin-top:12px;">
                <button type="button" class="submit-btn" onclick="acceptTerms()">I accept</button>
            </div>
        </div>
    </div>

    <!-- Email Verification Modal for Registration -->
    <div id="emailVerificationModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:10000; align-items:center; justify-content:center;">
        <div class="reset-modal-content">
            <button class="modal-close" onclick="closeEmailVerificationModal(true)">&times;</button>
            
            <!-- Email Verification Step -->
            <div id="emailVerificationStep" class="reset-step active">
                <div class="reset-header">
                    <h3>Verify Your Gmail</h3>
                    <p>We've sent a verification code to <span id="verificationEmail"></span></p>
                </div>
                <div class="otp-input">
                    <input type="text" maxlength="1" class="otp-digit" data-index="0" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="1" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="2" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="3" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="4" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="5" />
                </div>
                <div class="otp-timer" id="emailVerificationTimer">Resend OTP in <span id="emailTimerCount">60</span>s</div>
                <div id="emailVerificationDevHint" style="text-align:center; color:#c00; font-weight:bold; margin-top:8px; display:none;"></div>
                <div style="text-align: center; margin: 20px 0;">
                    <button type="button" onclick="verifyEmailOTP()" class="submit-btn">Verify Email</button>
                </div>
                <div style="text-align: center;">
                    <button type="button" id="resendEmailBtn" class="resend-btn" onclick="resendEmailOTP()" disabled>Resend OTP</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Login OTP Modal (2FA) - Only shows OTP verification -->
    <div id="loginOTPModal" style="display: none;">
        <div class="login-otp-modal-content">
            <button class="modal-close" onclick="closeLoginOTPModal(true)">&times;</button>

            <!-- OTP Verification -->
            <div class="reset-header">
                <h3>Two-Factor Authentication</h3>
                <p>Enter the 6-digit code sent to <span id="loginOtpEmail"></span></p>
            </div>
            <div class="otp-input">
                <input type="text" maxlength="1" class="login-otp-digit" data-index="0" pattern="[0-9]" inputmode="numeric" autocomplete="off" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="2" pattern="[0-9]" inputmode="numeric" autocomplete="off" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="3" pattern="[0-9]" inputmode="numeric" autocomplete="off" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="4" pattern="[0-9]" inputmode="numeric" autocomplete="off" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="5" pattern="[0-9]" inputmode="numeric" autocomplete="off" />
            </div>
            <div class="otp-timer" id="loginOtpTimer">Resend OTP in <span id="loginTimerCount">60</span>s</div>
            <div style="text-align:center; margin: 16px 0;">
                <button type="button" onclick="verifyLoginOTP()" class="submit-btn">Verify OTP</button>
            </div>
            <div style="text-align:center;">
                <button type="button" id="loginResendBtn" class="resend-btn" onclick="resendLoginOTP()" disabled>Resend OTP</button>
            </div>
            <div id="loginOtpError" style="text-align:center; color:#dc3545; margin-top:10px; font-size:14px;"></div>
        </div>
    </div>

    <!-- Super Admin Modal -->
    <div id="superAdminModal">
        <div class="sa-modal-content">
            <button class="modal-close" onclick="closeSuperAdminModal(true)">&times;</button>

            <!-- Step 1: Super Admin Login -->
            <div id="saStep1" class="sa-step active">
                <div class="sa-header">
                    <h3>Super Admin Login</h3>
                    <p>Enter Super Admin credentials</p>
                </div>
                <form id="saLoginForm">
                    <div class="form-group">
                        <input type="email" id="saEmail" placeholder="Super Admin Email" required />
                    </div>
                    <div class="form-group">
                        <input type="password" id="saPassword" placeholder="Password" required />
                    </div>
                    <div style="text-align:center; display:flex; flex-direction:column; align-items:center; gap:10px;">
                        <button type="button" onclick="superAdminLogin()" class="submit-btn">Log In</button>
                        <button type="button" onclick="openSuperAdminResetModal()" class="resend-btn" style="margin-top:6px;">Forgot Password?</button>
                    </div>
                    <div style="text-align:center; margin-top:10px;">
                        <button type="button" id="saRegisterBtn" onclick="openSuperAdminRegister()" class="resend-btn">Register Super Admin</button>
                    </div>
                </form>
            </div>

            <!-- Step 2: OTP Verification -->
            <div id="saStep2" class="sa-step">
                <div class="sa-header">
                    <h3>Verify OTP</h3>
                    <p>Enter the 6-digit code sent to <span id="saOtpEmail"></span></p>
                </div>
                <div class="otp-input">
                    <input type="text" maxlength="1" class="sa-otp-digit" data-index="0" />
                    <input type="text" maxlength="1" class="sa-otp-digit" data-index="1" />
                    <input type="text" maxlength="1" class="sa-otp-digit" data-index="2" />
                    <input type="text" maxlength="1" class="sa-otp-digit" data-index="3" />
                    <input type="text" maxlength="1" class="sa-otp-digit" data-index="4" />
                    <input type="text" maxlength="1" class="sa-otp-digit" data-index="5" />
                </div>
                <div class="sa-timer" id="saOtpTimer">Resend OTP in <span id="saTimerCount">60</span>s</div>
                <div class="sa-hint" id="saOtpDevHint"></div>
                <div style="text-align:center; margin: 16px 0;">
                    <button type="button" onclick="verifySuperAdminOTP()" class="submit-btn">Verify OTP</button>
                </div>
                <div style="text-align:center;">
                    <button type="button" id="saResendBtn" class="resend-btn" onclick="resendSuperAdminOTP()" disabled>Resend OTP</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Super Admin Registration Modal -->
    <div id="superAdminRegisterModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:10060; align-items:center; justify-content:center;">
        <div class="sa-modal-content">
            <button class="modal-close" onclick="closeSuperAdminRegister(true)">&times;</button>
            <div class="sa-header">
                <h3>Register Super Admin</h3>
                <p>Only one Super Admin account can be registered</p>
            </div>
            <form id="saRegisterForm">
                <div class="form-group"><input type="email" id="sarEmail" placeholder="Super Admin Email" required /></div>
                <div class="form-group"><input type="password" id="sarPassword" placeholder="Password (min 6)" required /></div>
                <div class="form-group"><input type="password" id="sarPassword2" placeholder="Confirm Password" required /></div>
                <div style="text-align:center;">
                    <button type="button" onclick="registerSuperAdmin()" class="submit-btn">Register</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Super Admin Password Reset Modal -->
    <div id="superAdminResetModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:10065; align-items:center; justify-content:center;">
        <div class="sa-modal-content">
            <button class="modal-close" onclick="closeSuperAdminResetModal(true)">&times;</button>
            <div class="sa-header">
                <h3>Super Admin Password Reset</h3>
                <p>Enter your Super Admin email to receive an OTP</p>
            </div>
            <div id="sarStep1" class="sa-step active">
                <form id="saResetForm1">
                    <div class="form-group"><input type="email" id="sarResetEmail" placeholder="Super Admin Email" required /></div>
                    <div style="text-align:center;"><button type="button" onclick="saSendResetOTP()" class="submit-btn">Get OTP</button></div>
                </form>
            </div>
            <div id="sarStep2" class="sa-step">
                <div class="sa-header">
                    <h3>Verify OTP</h3>
                    <p>Enter the 6-digit code sent to <span id="sarOtpEmail"></span></p>
                </div>
                <div class="otp-input">
                    <input type="text" maxlength="1" class="sar-otp-digit" data-index="0" />
                    <input type="text" maxlength="1" class="sar-otp-digit" data-index="1" />
                    <input type="text" maxlength="1" class="sar-otp-digit" data-index="2" />
                    <input type="text" maxlength="1" class="sar-otp-digit" data-index="3" />
                    <input type="text" maxlength="1" class="sar-otp-digit" data-index="4" />
                    <input type="text" maxlength="1" class="sar-otp-digit" data-index="5" />
                </div>
                <div class="sa-timer" id="sarOtpTimer">Resend OTP in <span id="sarTimerCount">60</span>s</div>
                <div style="text-align:center; margin: 16px 0;"><button type="button" onclick="saVerifyResetOTP()" class="submit-btn">Verify OTP</button></div>
                <div style="text-align:center;"><button type="button" id="sarResendBtn" class="resend-btn" onclick="saResendResetOTP()" disabled>Resend OTP</button></div>
            </div>
            <div id="sarStep3" class="sa-step">
                <div class="sa-header">
                    <h3>Set New Password</h3>
                    <p>Enter and confirm your new password</p>
                </div>
                <form id="saResetForm3">
                    <div class="form-group"><input type="password" id="sarNewPassword" placeholder="New Password" required /></div>
                    <div class="form-group"><input type="password" id="sarConfirmPassword" placeholder="Confirm New Password" required /></div>
                    <div style="text-align:center;"><button type="button" onclick="saUpdatePassword()" class="submit-btn">Update Password</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div id="passwordResetModal">
        <div class="reset-modal-content">
            <button class="modal-close" onclick="closePasswordResetModal(true)">&times;</button>
            
            <!-- Step 1: Email Input -->
            <div id="resetStep1" class="reset-step active">
                <div class="reset-header">
                    <h3>Reset Password</h3>
                    <p>Enter your Gmail address to receive an OTP</p>
                </div>
                <form id="resetForm1">
                    <div class="form-group">
                        <input type="email" id="resetEmail" placeholder="Gmail Address (e.g., user@gmail.com)" required pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid Gmail address" />
                    </div>
                    <button type="button" onclick="sendOTP()" class="submit-btn">Get OTP</button>
                </form>
            </div>

            <!-- Step 2: OTP Verification -->
            <div id="resetStep2" class="reset-step">
                <div class="reset-header">
                    <h3>Verify OTP</h3>
                    <p>Enter the 6-digit code sent to your mobile</p>
                </div>
                <div class="otp-input">
                    <input type="text" maxlength="1" class="otp-digit" data-index="0" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="1" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="2" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="3" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="4" />
                    <input type="text" maxlength="1" class="otp-digit" data-index="5" />
                </div>
                <div class="otp-timer" id="otpTimer">Resend OTP in <span id="timerCount">60</span>s</div>
                <div id="otpDevHint" style="text-align:center; color:#c00; font-weight:bold; margin-top:8px; display:none;"></div>
                <div style="text-align: center; margin: 20px 0;">
                    <button type="button" onclick="verifyOTP()" class="submit-btn">Verify OTP</button>
                </div>
                <div style="text-align: center;">
                    <button type="button" id="resendBtn" class="resend-btn" onclick="resendOTP()" disabled>Resend OTP</button>
                </div>
            </div>

            <!-- Step 3: New Password -->
            <div id="resetStep3" class="reset-step">
                <div class="reset-header">
                    <h3>New Password</h3>
                    <p>Enter your new password</p>
                </div>
                <form id="resetForm3">
                    <div class="form-group">
                        <input type="password" id="newPassword" placeholder="New Password" required />
                    </div>
                    <div class="form-group">
                        <input type="password" id="confirmPassword" placeholder="Confirm New Password" required />
                    </div>
                    <button type="button" onclick="updatePassword()" class="submit-btn">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Login OTP Modal (2FA) -->
    <div id="loginOTPModal" style="display: none;">
        <div class="login-otp-modal-content">
            <button class="modal-close" onclick="window.closeLoginOTPModal(true)">&times;</button>
            
            <div class="reset-header">
                <h3>Two-Factor Authentication</h3>
                <p>Enter the 6-digit code sent to <span id="loginOtpEmail"></span></p>
            </div>
            
            <div class="otp-input">
                <input type="text" maxlength="1" class="login-otp-digit" data-index="0" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="1" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="2" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="3" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="4" />
                <input type="text" maxlength="1" class="login-otp-digit" data-index="5" />
            </div>
            
            <div class="otp-timer" id="loginOtpTimer">Resend OTP in <span id="loginTimerCount">60</span>s</div>
            <div id="loginOtpError" style="text-align:center; color:#dc3545; font-weight:bold; margin-top:8px; min-height:20px;"></div>
            
            <div style="text-align: center; margin: 20px 0;">
                <button type="button" onclick="window.verifyLoginOTP()" class="submit-btn">Verify OTP</button>
            </div>
            
            <div style="text-align: center;">
                <button type="button" id="loginResendBtn" class="resend-btn" onclick="window.resendLoginOTP()" disabled>Resend OTP</button>
            </div>
        </div>
    </div>

    <!-- Google reCAPTCHA v3 (invisible) -->
    <div id="recaptcha-container" style="display: none;">
        <div class="g-recaptcha" data-sitekey="6LeX6fgrAAAAAHeDtz1_Aj2o5o8GN6FTRJAjHVhI" data-callback="onRecaptchaSuccess" data-size="invisible"></div>
                </div>
    

    <!-- Debug script to ensure logo is visible -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.getElementById('recaptcha-badge');
            if (badge) {
                console.log('reCAPTCHA badge found and should be visible');
                badge.style.display = 'block';
                badge.style.opacity = '1';
                badge.style.visibility = 'visible';
            } else {
                console.log('reCAPTCHA badge not found');
            }
        });
    </script>

    <script>
        // Password Reset Modal Logic
        let currentResetStep = 1;
        let otpTimer = null;
        let otpCode = '';
        let resetToken = '';

        // Email Verification Modal Logic
        let emailVerificationTimer = null;
        let emailVerificationToken = '';
        let emailVerificationOTP = '';
        let isEmailVerified = false;
        let termsAccepted = false;

        function openPasswordResetModal() {
            document.getElementById('passwordResetModal').style.display = 'flex';
            resetToStep(1);
        }

        function closePasswordResetModal(triggeredByCloseBtn) {
            // If closing during verify OTP step and no input, show warning
            if (triggeredByCloseBtn && document.getElementById('resetStep2').classList.contains('active')) {
                const hasAnyDigit = Array.from(document.querySelectorAll('#passwordResetModal .otp-digit'))
                    .some(inp => (inp.value || '').trim() !== '');
                if (!hasAnyDigit) {
                    (window.queueSwal || Swal.fire)({
                        icon: 'warning',
                        title: 'OTP Required',
                        text: 'Please enter the 6-digit OTP to proceed.',
                        confirmButtonColor: '#ffc107',
                        zIndex: 10001
                    });
                }
            }
            document.getElementById('passwordResetModal').style.display = 'none';
            resetToStep(1);
            clearOTPTimer();
        }

        // Email Verification Functions
        function openEmailVerificationModal(email) {
            document.getElementById('emailVerificationModal').style.display = 'flex';
            document.getElementById('verificationEmail').textContent = email;
            // Start countdown immediately when modal is shown
            startEmailVerificationTimer();
            sendEmailVerificationOTP(email);
        }

        function closeEmailVerificationModal(triggeredByCloseBtn) {
            // If closing during verify email OTP step and no input, show warning
            if (triggeredByCloseBtn && document.getElementById('emailVerificationStep').classList.contains('active')) {
                const hasAnyDigit = Array.from(document.querySelectorAll('#emailVerificationModal .otp-digit'))
                    .some(inp => (inp.value || '').trim() !== '');
                if (!hasAnyDigit) {
                    (window.queueSwal || Swal.fire)({
                        icon: 'warning',
                        title: 'OTP Required',
                        text: 'Please enter the 6-digit OTP to verify your email.',
                        confirmButtonColor: '#ffc107',
                        zIndex: 10001
                    });
                }
            }
            document.getElementById('emailVerificationModal').style.display = 'none';
            clearEmailVerificationTimer();
            // Clear OTP inputs
            document.querySelectorAll('#emailVerificationModal .otp-digit').forEach(input => input.value = '');
        }

        function clearEmailVerificationTimer() {
            if (emailVerificationTimer) {
                clearInterval(emailVerificationTimer);
                emailVerificationTimer = null;
            }
        }

        function startEmailVerificationTimer() {
            let timeLeft = 60;
            const timerElement = document.getElementById('emailTimerCount');
            const resendBtn = document.getElementById('resendEmailBtn');
            
            clearEmailVerificationTimer();
            resendBtn.disabled = true;
            
            emailVerificationTimer = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;
                
                if (timeLeft <= 10) {
                    document.getElementById('emailVerificationTimer').classList.add('warning');
                }
                
                if (timeLeft <= 0) {
                    clearEmailVerificationTimer();
                    resendBtn.disabled = false;
                    document.getElementById('emailVerificationTimer').textContent = 'OTP expired. Click resend to get a new code.';
                    document.getElementById('emailVerificationTimer').classList.remove('warning');
                }
            }, 1000);
        }

        async function sendEmailVerificationOTP(email) {
            try {
                const response = await fetchWithCSRFRetry('/email-verification/send-otp', {
                    method: 'POST',
                    body: JSON.stringify({ email })
                });

                let data = {};
                try { data = await response.json(); } catch(e) {}
                
                if (response.ok) {
                    emailVerificationToken = data.token;
                    emailVerificationOTP = data.otp;
                    // Timer already started on modal open; reset to full on success
                    startEmailVerificationTimer();
                    // Focus first OTP input
                    document.querySelector('#emailVerificationModal .otp-digit').focus();
                    
                    // Show debug OTP if in development mode
                    if (data.debug_otp) {
                        const devHint = document.getElementById('emailVerificationDevHint');
                        if (devHint) {
                            devHint.textContent = 'Development Mode - OTP: ' + data.debug_otp;
                            devHint.style.display = 'block';
                        }
                    }
                    
                } else {
                    // Only close modal for error messages, not success messages
                    document.getElementById('emailVerificationModal').style.display = 'none';
                    // Use setTimeout to ensure modal is closed before showing alert
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Send OTP',
                            text: data.message || 'Failed to send verification OTP',
                            confirmButtonColor: '#dc3545',
                            zIndex: 10001,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            // Reopen modal after error alert is closed
                            document.getElementById('emailVerificationModal').style.display = 'flex';
                        });
                    }, 100);
                }
            } catch (error) {
                // Handle CSRF token errors specifically
                if (error.message && error.message.includes('CSRF token')) {
                    handleCSRFError(error, 'email verification OTP');
                    return;
                }
                
                // Close any open modals first
                document.getElementById('emailVerificationModal').style.display = 'none';
                // Use setTimeout to ensure modal is closed before showing alert
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please try again.',
                        confirmButtonColor: '#dc3545',
                        zIndex: 10001,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                }, 100);
            }
        }

        function resendEmailOTP() {
            const email = document.getElementById('verificationEmail').textContent;
            sendEmailVerificationOTP(email);
        }

        async function verifyEmailOTP() {
            const otpDigits = document.querySelectorAll('#emailVerificationModal .otp-digit');
            const enteredOTP = Array.from(otpDigits).map(input => input.value).join('');
            
            if (enteredOTP.length !== 6) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete OTP',
                    text: 'Please enter complete 6-digit OTP',
                    confirmButtonColor: '#ffc107',
                    zIndex: 10001
                });
                return;
            }

            try {
                const response = await fetchWithCSRFRetry('/email-verification/verify-otp', {
                    method: 'POST',
                    body: JSON.stringify({ 
                        token: emailVerificationToken, 
                        otp: enteredOTP, 
                        email: document.getElementById('verificationEmail').textContent 
                    })
                });

                let data = {};
                try { data = await response.json(); } catch(e) {}
                
                if (response.ok) {
                    isEmailVerified = true;
                    closeEmailVerificationModal();
                    // Enable the registration form
                    enableRegistrationForm();
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Verified!',
                        text: 'You can now complete your registration.',
                        confirmButtonColor: '#28a745'
                    });
                } else {
                    // Close any open modals first
                    document.getElementById('emailVerificationModal').style.display = 'none';
                    // Use setTimeout to ensure modal is closed before showing alert
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid OTP',
                            text: data.message || 'Invalid OTP',
                            confirmButtonColor: '#dc3545',
                            zIndex: 10001,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            // Reopen modal after alert is closed
                            document.getElementById('emailVerificationModal').style.display = 'flex';
                            // Clear OTP inputs
                            otpDigits.forEach(input => input.value = '');
                            otpDigits[0].focus();
                        });
                    }, 100);
                }
            } catch (error) {
                // Handle CSRF token errors specifically
                if (error.message && error.message.includes('CSRF token')) {
                    handleCSRFError(error, 'email verification OTP verification');
                    return;
                }
                
                // Close any open modals first
                document.getElementById('emailVerificationModal').style.display = 'none';
                // Use setTimeout to ensure modal is closed before showing alert
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please try again.',
                        confirmButtonColor: '#dc3545',
                        zIndex: 10001,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // Reopen modal after alert is closed
                        document.getElementById('emailVerificationModal').style.display = 'flex';
                    });
                }, 100);
            }
        }

        function enableRegistrationForm() {
            // Enable all form fields and submit button
            const form = document.querySelector('form[action="/register"]');
            const inputs = form.querySelectorAll('input, button');
            inputs.forEach(input => {
                input.disabled = false;
            });
            
            // Add visual indicator that email is verified
            const emailInput = form.querySelector('input[name="email"]');
            emailInput.style.borderColor = '#28a745';
            emailInput.style.backgroundColor = '#f8fff9';
            
            // Hide verification status and show success message
            const statusDiv = document.getElementById('emailVerificationStatus');
            statusDiv.style.display = 'block';
            statusDiv.innerHTML = '<strong>✅ Email Verified:</strong> Your Gmail account is now bound to infotech-inventory.com. You can complete your registration.';
            statusDiv.style.background = '#d4edda';
            statusDiv.style.borderColor = '#c3e6cb';
            statusDiv.style.color = '#155724';

            // Flag for eligibility checks
            window.isEmailVerified = true;
            // Re-check controls in case file size invalid had blocked
            unblockRegistrationControlsIfEligible();
        }

        function resetToStep(step) {
            currentResetStep = step;
            document.querySelectorAll('.reset-step').forEach(s => s.classList.remove('active'));
            document.getElementById(`resetStep${step}`).classList.add('active');
        }

        function clearOTPTimer() {
            if (otpTimer) {
                clearInterval(otpTimer);
                otpTimer = null;
            }
        }

        function startOTPTimer() {
            let timeLeft = 60;
            const timerElement = document.getElementById('timerCount');
            const resendBtn = document.getElementById('resendBtn');
            
            clearOTPTimer();
            resendBtn.disabled = true;
            
            otpTimer = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;
                
                if (timeLeft <= 10) {
                    document.getElementById('otpTimer').classList.add('warning');
                }
                
                if (timeLeft <= 0) {
                    clearOTPTimer();
                    resendBtn.disabled = false;
                    document.getElementById('otpTimer').textContent = 'OTP expired. Click resend to get a new code.';
                    document.getElementById('otpTimer').classList.remove('warning');
                }
            }, 1000);
        }

        async function sendOTP() {
            const email = document.getElementById('resetEmail').value;
            if (!email) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email Required',
                    text: 'Please enter a valid email address',
                    confirmButtonColor: '#ffc107',
                    zIndex: 10001
                });
                return;
            }

            // Make the API call directly without SweetAlert
            makeOTPRequest(email);
        }

        async function makeOTPRequest(email) {
            try {
                const response = await fetchWithCSRFRetry('/password-reset/send-otp', {
                    method: 'POST',
                    body: JSON.stringify({ email })
                });

                let data = {};
                try { data = await response.json(); } catch(e) {}
                
                if (response.ok) {
                    resetToken = data.token;
                    
                    // Directly show Verify OTP step without SweetAlert
                    resetToStep(2);
                    // Prepare UI
                    const timerEl = document.getElementById('otpTimer');
                    const countEl = document.getElementById('timerCount');
                    const resendBtn = document.getElementById('resendBtn');
                    if (timerEl) timerEl.classList.remove('warning');
                    if (countEl) countEl.textContent = '60';
                    if (resendBtn) resendBtn.disabled = true;
                    // Focus the first digit input
                    const firstOtpInput = document.querySelector('.otp-digit');
                    if (firstOtpInput) firstOtpInput.focus();
                    // Start countdown
                    startOTPTimer();
                    
                    // Show debug OTP if in development mode
                    if (data.debug_otp) {
                        const devHint = document.getElementById('otpDevHint');
                        if (devHint) {
                            devHint.textContent = 'Development Mode - OTP: ' + data.debug_otp;
                            devHint.style.display = 'block';
                        }
                    }
                } else {
                    // Close any open modals first
                    document.getElementById('passwordResetModal').style.display = 'none';
                    // Use setTimeout to ensure modal is closed before showing alert
                    setTimeout(() => {
                        if (response.status === 429 && data.locked_until) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Too Many Attempts',
                                text: 'Try again later.',
                                confirmButtonColor: '#dc3545',
                                zIndex: 10001,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => { resetToStep(1); document.getElementById('passwordResetModal').style.display = 'flex'; });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to Send OTP',
                                text: data.message || 'Failed to send OTP',
                                confirmButtonColor: '#dc3545',
                                zIndex: 10001,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => { resetToStep(1); document.getElementById('passwordResetModal').style.display = 'flex'; });
                        }
                    }, 100);
                }
            } catch (error) {
                // Handle CSRF token errors specifically
                if (error.message && error.message.includes('CSRF token')) {
                    handleCSRFError(error, 'password reset OTP');
                    return;
                }
                
                // Close any open modals first
                document.getElementById('passwordResetModal').style.display = 'none';
                // Use setTimeout to ensure modal is closed before showing alert
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please try again.',
                        confirmButtonColor: '#dc3545',
                        zIndex: 10001,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => { resetToStep(1); document.getElementById('passwordResetModal').style.display = 'flex'; });
                }, 100);
            }
        }

        function resendOTP() {
            sendOTP();
        }

        async function verifyOTP() {
            const otpDigits = document.querySelectorAll('.otp-digit');
            const enteredOTP = Array.from(otpDigits).map(input => input.value).join('');
            
            if (enteredOTP.length !== 6) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete OTP',
                    text: 'Please enter complete 6-digit OTP',
                    confirmButtonColor: '#ffc107',
                    zIndex: 10001
                });
                return;
            }

            try {
                const response = await fetchWithCSRFRetry('/password-reset/verify-otp', {
                    method: 'POST',
                    body: JSON.stringify({ token: resetToken, otp: enteredOTP, email: document.getElementById('resetEmail').value })
                });

                let data = {};
                try { data = await response.json(); } catch(e) {}
                
                if (response.ok) {
                    resetToStep(3);
                    clearOTPTimer();
                } else {
                    // Close any open modals first
                    document.getElementById('passwordResetModal').style.display = 'none';
                    // Use setTimeout to ensure modal is closed before showing alert
                    setTimeout(() => {
                        if (response.status === 429 && data.locked_until) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Too Many Invalid Attempts',
                                text: 'Try again in 5 minutes.',
                                confirmButtonColor: '#dc3545',
                                zIndex: 10001,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            });
                        } else if (data && data.remaining_attempts !== undefined) {
                            // Show attempts remaining in a separate, more prominent alert
                            Swal.fire({
                                icon: 'warning',
                                title: 'Invalid OTP',
                                text: data.message || 'Invalid OTP',
                                confirmButtonColor: '#ffc107',
                                zIndex: 10001,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                // Show attempts remaining alert after the first one is closed
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Attempts Remaining',
                                    text: `You have ${data.remaining_attempts} attempt${data.remaining_attempts === 1 ? '' : 's'} left before your account is temporarily locked.`,
                                    confirmButtonColor: '#17a2b8',
                                    confirmButtonText: 'Try Again',
                                    zIndex: 10001,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    // Reopen modal after alert is closed
                                    document.getElementById('passwordResetModal').style.display = 'flex';
                                    // Clear OTP inputs
                                    otpDigits.forEach(input => input.value = '');
                                    otpDigits[0].focus();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid OTP',
                                text: data.message || 'Invalid OTP',
                                confirmButtonColor: '#dc3545',
                                zIndex: 10001,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                // Reopen modal after alert is closed
                                document.getElementById('passwordResetModal').style.display = 'flex';
                                // Clear OTP inputs
                                otpDigits.forEach(input => input.value = '');
                                otpDigits[0].focus();
                            });
                        }
                    }, 100);
                }
            } catch (error) {
                // Close any open modals first
                document.getElementById('passwordResetModal').style.display = 'none';
                // Use setTimeout to ensure modal is closed before showing alert
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please try again.',
                        confirmButtonColor: '#dc3545',
                        zIndex: 10001,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // Reopen modal after alert is closed
                        document.getElementById('passwordResetModal').style.display = 'flex';
                    });
                }, 100);
            }
        }

        async function updatePassword() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword.length < 6) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Too Short',
                    text: 'Password must be at least 6 characters',
                    confirmButtonColor: '#ffc107',
                    zIndex: 10001
                });
                return;
            }
            
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords Do Not Match',
                    text: 'Please make sure both passwords are identical',
                    confirmButtonColor: '#dc3545',
                    zIndex: 10001
                });
                return;
            }

            try {
                const response = await fetchWithCSRFRetry('/password-reset/update', {
                    method: 'POST',
                    body: JSON.stringify({ token: resetToken, password: newPassword, password_confirmation: confirmPassword })
                });

                let data = {};
                try { data = await response.json(); } catch(e) {}
                
                if (response.ok) {
                    // Close any open modals first
                    document.getElementById('passwordResetModal').style.display = 'none';
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Updated!',
                        text: 'You can now login with your new password.',
                        confirmButtonColor: '#28a745',
                        zIndex: 10001
                    }).then(() => {
                        closePasswordResetModal();
                    });
                } else {
                    // Close any open modals first
                    document.getElementById('passwordResetModal').style.display = 'none';
                    // Use setTimeout to ensure modal is closed before showing alert
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Update Password',
                            text: data.message || 'Failed to update password',
                            confirmButtonColor: '#dc3545',
                            zIndex: 10001,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            // Reopen modal after alert is closed
                            document.getElementById('passwordResetModal').style.display = 'flex';
                        });
                    }, 100);
                }
            } catch (error) {
                // Close any open modals first
                document.getElementById('passwordResetModal').style.display = 'none';
                // Use setTimeout to ensure modal is closed before showing alert
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please try again.',
                        confirmButtonColor: '#dc3545',
                        zIndex: 10001,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // Reopen modal after alert is closed
                        document.getElementById('passwordResetModal').style.display = 'flex';
                    });
                }, 100);
            }
        }

        // OTP Input Navigation
        document.addEventListener('DOMContentLoaded', function() {
            const otpInputs = document.querySelectorAll('.otp-digit');
            
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    const value = e.target.value;
                    if (value.length === 1 && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                });
                
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });
            });

            // Email verification on blur for registration form
            const emailInput = document.querySelector('form[action="/register"] input[name="email"]');
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim();
                    const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
                    
                    if (email && gmailRegex.test(email) && !isEmailVerified) {
                        // Check if this email is already verified
                        if (emailVerificationToken && document.getElementById('verificationEmail').textContent === email) {
                            return; // Already verified this email
                        }
                        // Show verification status
                        document.getElementById('emailVerificationStatus').style.display = 'block';
                        openEmailVerificationModal(email);
                    } else if (email && !gmailRegex.test(email)) {
                        // Show error for non-Gmail
                        document.getElementById('emailVerificationStatus').style.display = 'block';
                        document.getElementById('emailVerificationStatus').innerHTML = '<strong>❌ Invalid Email:</strong> Please use a valid Gmail address (e.g., user@gmail.com)';
                        document.getElementById('emailVerificationStatus').style.background = '#f8d7da';
                        document.getElementById('emailVerificationStatus').style.borderColor = '#f5c6cb';
                        document.getElementById('emailVerificationStatus').style.color = '#721c24';
                    }
                });

                emailInput.addEventListener('input', function() {
                    // Hide status when user starts typing
                    if (this.value.trim() === '') {
                        document.getElementById('emailVerificationStatus').style.display = 'none';
                    }
                });
            }

            // Disable registration form initially until email is verified
            const registrationForm = document.querySelector('form[action="/register"]');
            if (registrationForm) {
                const formInputs = registrationForm.querySelectorAll('input:not([name="email"]), button');
                formInputs.forEach(input => {
                    input.disabled = true;
                });
                // realtime password validation
                const p1 = registrationForm.querySelector('input[name="password"]');
                const p2 = registrationForm.querySelector('input[name="password_confirmation"]');
                const photo = document.getElementById('photo');
                function getStrengthClass(val){
                    if(!val) return '';
                    const hasLen = val.length >= 8;
                    const hasNum = /\d/.test(val);
                    const hasSpec = /[^\w]/.test(val);
                    const combos = (hasNum?1:0) + (hasSpec?1:0) + (/[A-Z]/.test(val)?1:0) + (/[a-z]/.test(val)?1:0);
                    if(!(hasLen && hasNum && hasSpec)) return 'strength-weak';
                    if(combos >= 3 && val.length >= 10) return 'strength-super';
                    return 'strength-strong';
                }
                function applyStrength(){
                    if(!p1) return;
                    p1.classList.remove('strength-weak','strength-strong','strength-super','input-valid','input-invalid');
                    const cls = getStrengthClass(p1.value);
                    if(cls) p1.classList.add(cls);
                    // mark valid only if meets min policy
                    const ok = p1.value.length>=8 && /\d/.test(p1.value) && /[^\w]/.test(p1.value);
                    if(ok) p1.classList.add('input-valid'); else if(p1.value) p1.classList.add('input-invalid');
                    applyConfirm();
                }
                function applyConfirm(){
                    if(!p2) return;
                    p2.classList.remove('input-valid','input-invalid');
                    if(!p2.value) return;
                    if(p1.value && p2.value === p1.value){ p2.classList.add('input-valid'); } else { p2.classList.add('input-invalid'); }
                }
                if(p1){ p1.addEventListener('input', applyStrength); }
                if(p2){ p2.addEventListener('input', applyConfirm); }
                if(photo){ photo.addEventListener('change', function(){ if(!this.files||!this.files[0]) return; const f=this.files[0]; const label=this.nextElementSibling; const tooLarge=f.size>2*1024*1024; if(tooLarge){ this.classList.add('invalid'); if(label&&label.classList.contains('file-input-label')) label.classList.add('invalid'); blockRegistrationControls(true); } else { this.classList.remove('invalid'); if(label&&label.classList.contains('file-input-label')) label.classList.remove('invalid'); unblockRegistrationControlsIfEligible(); } }); }
            }

            // Terms checkbox logic
            const termsCheckbox = document.getElementById('termsCheckbox');
            const signUpBtn = document.getElementById('signUpBtn');
            const termsHint = document.getElementById('termsHint');
            if (termsCheckbox && signUpBtn) {
                termsCheckbox.addEventListener('change', function(){
                    // If user tries to check, open modal to actually accept
                    if (this.checked && !termsAccepted) {
                        this.checked = false; // prevent direct enabling without reading
                        openTermsModal();
                    }
                    signUpBtn.disabled = !(this.checked && isEmailVerified);
                    if (signUpBtn.disabled) {
                        if (termsHint) termsHint.style.display = 'block';
                    } else {
                        if (termsHint) termsHint.style.display = 'none';
                    }
                });
            }
        });

        // Google reCAPTCHA v3 functions
        let recaptchaVerified = false;
        let recaptchaToken = '';
        
        function executeRecaptcha() {
            return new Promise((resolve, reject) => {
                if (typeof grecaptcha === 'undefined') {
                    reject('reCAPTCHA not loaded');
                    return;
                }
                
                grecaptcha.ready(function() {
                    grecaptcha.execute('6LeX6fgrAAAAAHeDtz1_Aj2o5o8GN6FTRJAjHVhI', {action: 'login'}).then(function(token) {
                        recaptchaToken = token;
                        recaptchaVerified = true;
                        resolve(token);
                    }).catch(function(error) {
                        reject(error);
                    });
                });
            });
        }
        
        function onRecaptchaSuccess(token) {
            recaptchaVerified = true;
            recaptchaToken = token;
            console.log('reCAPTCHA v3 verified successfully');
        }
        
        function resetRecaptcha() {
            recaptchaVerified = false;
            recaptchaToken = '';
        }
    </script>
    <script>
        // Terms modal controls
        function openTermsModal(){
            const m = document.getElementById('termsModal'); if(m){ m.style.display='flex'; }
        }
        function closeTermsModal(){
            const m = document.getElementById('termsModal'); if(m){ m.style.display='none'; }
        }
        function acceptTerms(){
            // Close any open modals first and show acknowledgement via SweetAlert
            termsAccepted = true;
            const cb = document.getElementById('termsCheckbox');
            if (cb) { cb.checked = true; }
            closeTermsModal();
            setTimeout(()=>{
                Swal.fire({
                    icon: 'success',
                    title: 'Terms Accepted',
                    text: 'You may now continue your registration.',
                    confirmButtonColor: '#28a745',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    zIndex: 10080
                }).then(()=>{
                    // enable Sign Up if email already verified
                    const btn = document.getElementById('signUpBtn');
                    if (btn) btn.disabled = !(document.getElementById('termsCheckbox').checked && isEmailVerified);
                    const termsHint = document.getElementById('termsHint');
                    if (termsHint) termsHint.style.display = document.getElementById('termsCheckbox').checked ? 'none' : 'block';
                });
            }, 50);
        }
    </script>
</body>
</html>