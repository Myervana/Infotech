<div style="font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#222;">
    <p>Hi {{ isset($user) ? $user->name : 'there' }},</p>
    <p>Your password reset One-Time Password (OTP) for your infotech-inventory.com account is:</p>
    <p style="font-size:24px; font-weight:bold; letter-spacing:3px;">{{ $otp }}</p>
    <p>This code is valid for 5 minutes. If you did not request this, you can ignore this email.</p>
    <p>This OTP was sent to your Gmail account that is bound to your infotech-inventory.com account.</p>
    <p>Thanks,<br/>IT Inventory System Team<br/>iitech.inventory@gmail.com</p>
</div>


