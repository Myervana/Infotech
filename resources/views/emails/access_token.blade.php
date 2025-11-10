<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Access Token</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color:#1a202c; }
        .card { max-width:540px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:24px; }
        .title { font-size:20px; font-weight:700; margin:0 0 8px; }
        .desc { margin:0 0 16px; color:#4a5568; }
        .token { font-size:28px; font-weight:800; letter-spacing:6px; background:#edf2f7; padding:12px 16px; border-radius:8px; display:inline-block; }
        .note { margin-top:16px; color:#718096; font-size:13px; }
    </style>
    </head>
<body>
    <div class="card">
        <div class="title">Your Access Token</div>
        <p class="desc">Hi {{ $user->name ?? 'User' }}, copy the token below and paste it into the Access Token modal to activate your account.</p>
        <div class="token" style="word-break:break-all;">{{ $token }}</div>
        <p class="note">This token expires in 10 minutes. If you did not request this, please ignore this email.</p>
    </div>
</body>
</html>


