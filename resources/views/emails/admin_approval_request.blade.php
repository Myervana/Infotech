<div style="font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#222;">
    <h2 style="color:#333; margin-bottom:20px;">New Account Registration - Approval Required</h2>
    
    <p>Hello IT Inventory Administrator,</p>
    
    <p>A new user has registered and is requesting approval to access the IT Inventory System. Please review the details below and approve or reject the account.</p>
    
    <div style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:20px; margin:20px 0;">
        <h3 style="color:#495057; margin-top:0;">Registration Details:</h3>
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="padding:8px 0; font-weight:bold; width:30%;">Name:</td>
                <td style="padding:8px 0;">{{ $user->name }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0; font-weight:bold;">Email:</td>
                <td style="padding:8px 0;">{{ $user->email }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0; font-weight:bold;">Registration Date:</td>
                <td style="padding:8px 0;">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0; font-weight:bold;">Status:</td>
                <td style="padding:8px 0; color:#dc3545; font-weight:bold;">Pending Approval</td>
            </tr>
        </table>
    </div>
    
    <div style="background:#e3f2fd; border:1px solid #2196f3; border-radius:8px; padding:15px; margin:20px 0;">
        <h4 style="color:#1976d2; margin-top:0;">Account Verification Information:</h4>
        <p style="margin:5px 0;">✅ Gmail Account: <strong>{{ $user->email }}</strong></p>
        <p style="margin:5px 0;">✅ Email Verified: <strong>Yes</strong></p>
        <p style="margin:5px 0;">✅ Ready for: <strong>Direct IT Inventory System Access</strong></p>
    </div>
    
    <div style="text-align:center; margin:30px 0;">
        <a href="{{ $approveUrl }}" style="background:#28a745; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; display:inline-block; margin:0 10px; font-weight:bold; font-size:16px;">✅ APPROVE ACCOUNT</a>
        <a href="{{ $rejectUrl }}" style="background:#dc3545; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; display:inline-block; margin:0 10px; font-weight:bold; font-size:16px;">❌ REJECT ACCOUNT</a>
    </div>
    
    <div style="text-align:center; margin:20px 0;">
        <a href="{{ $approvalUrl }}" style="color:#6c757d; text-decoration:none; font-size:14px;">Or view in admin panel</a>
    </div>
    
    <div style="background:#fff3cd; border:1px solid #ffeaa7; border-radius:5px; padding:15px; margin:20px 0;">
        <h4 style="color:#856404; margin-top:0;">⚠️ Security Notice:</h4>
        <p style="margin:5px 0; color:#856404;">This account has been verified through Gmail OTP and is bound to the infotech-inventory.com system. Please review the user details before approving access.</p>
    </div>
    
    <p style="margin-top:30px;">Please log in to the admin panel to review and approve this account.</p>
    
    <p>Best regards,<br/>IT Inventory System<br/>iitech.inventory@gmail.com</p>
    
    <hr style="border:none; border-top:1px solid #dee2e6; margin:30px 0;">
    <p style="font-size:12px; color:#6c757d;">This is an automated notification from the IT Inventory System. Please do not reply to this email.</p>
</div>
