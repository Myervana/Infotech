<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login OTP - IT Inventory System</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, rgb(170, 39, 39), rgb(2, 3, 3)); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">IT Inventory System</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #ddd;">
        <h2 style="color: #333; margin-top: 0;">Two-Factor Authentication</h2>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>You have requested to login to your IT Inventory System account. Please use the following One-Time Password (OTP) to complete your login:</p>
        
        <div style="background: white; border: 2px solid #aa2727; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;">
            <h1 style="color: #aa2727; font-size: 36px; letter-spacing: 8px; margin: 0; font-family: 'Courier New', monospace;">{{ $otp }}</h1>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            <strong>Important:</strong>
            <ul style="color: #666;">
                <li>This OTP is valid for 5 minutes only</li>
                <li>Do not share this OTP with anyone</li>
                <li>If you did not request this login, please ignore this email</li>
            </ul>
        </p>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            If you have any concerns, please contact the system administrator immediately.
        </p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px; text-align: center; margin: 0;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>
</body>
</html>

