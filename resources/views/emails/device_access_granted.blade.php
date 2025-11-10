<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Access Granted</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Device Access Granted</h1>
    </div>
    
    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;">
        <p style="margin: 0 0 20px 0;">Hello {{ $user->name }},</p>
        
        <p style="margin: 0 0 20px 0;">A new device has been granted access to your account using your share token.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="margin: 0 0 15px 0; color: #667eea;">Device Information:</h3>
            <p style="margin: 5px 0;"><strong>Device Name:</strong> {{ $deviceName }}</p>
            <p style="margin: 5px 0;"><strong>IP Address:</strong> {{ $ipAddress }}</p>
            <p style="margin: 5px 0;"><strong>User Agent:</strong> {{ $userAgent }}</p>
            <p style="margin: 5px 0;"><strong>Access Time:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
        </div>
        
        <p style="margin: 20px 0 0 0; color: #666; font-size: 14px;">
            <strong>Important:</strong> This device now has access to your data. If you did not authorize this access, please contact support immediately.
        </p>
        
        <p style="margin: 30px 0 0 0;">Best regards,<br>
        <strong>IT Inventory System</strong></p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #999; font-size: 12px;">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>

