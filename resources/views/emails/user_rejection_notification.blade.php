<div style="font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#222;">
    <h2 style="color:#dc3545; margin-bottom:20px;">Account Registration Rejected</h2>
    
    <p>Hello,</p>
    
    <p>We regret to inform you that your account registration for the IT Inventory System has been rejected by the administrator.</p>
    
    <div style="background:#f8d7da; border:1px solid #f5c6cb; border-radius:8px; padding:20px; margin:20px 0;">
        <h3 style="color:#721c24; margin-top:0;">❌ Registration Details:</h3>
        <p style="margin:5px 0;"><strong>Email:</strong> {{ $userEmail }}</p>
        <p style="margin:5px 0;"><strong>Status:</strong> <span style="color:#dc3545; font-weight:bold;">Rejected</span></p>
        <p style="margin:5px 0;"><strong>Date:</strong> {{ now()->format('M d, Y \a\t g:i A') }}</p>
    </div>
    
    <div style="background:#fff3cd; border:1px solid #ffeaa7; border-radius:8px; padding:15px; margin:20px 0;">
        <h4 style="color:#856404; margin-top:0;">Possible Reasons for Rejection:</h4>
        <ul style="margin:10px 0; padding-left:20px; color:#856404;">
            <li>Incomplete or inaccurate registration information</li>
            <li>Email address does not meet organization requirements</li>
            <li>Duplicate or suspicious registration attempt</li>
            <li>Administrator review determined access is not needed</li>
        </ul>
    </div>
    
    <div style="background:#e3f2fd; border:1px solid #2196f3; border-radius:8px; padding:15px; margin:20px 0;">
        <h4 style="color:#1976d2; margin-top:0;">What happens next:</h4>
        <p style="margin:5px 0; color:#1976d2;">• Your account has been permanently deleted from the system</p>
        <p style="margin:5px 0; color:#1976d2;">• You will not receive any further notifications</p>
        <p style="margin:5px 0; color:#1976d2;">• If you believe this is an error, contact the administrator</p>
    </div>
    
    <p style="margin-top:30px;">If you believe this rejection was made in error or if you have questions about the registration process, please contact the system administrator at <strong>iitech.inventory@gmail.com</strong>.</p>
    
    <p>Thank you for your interest in the IT Inventory System.<br/>IT Inventory Team<br/>iitech.inventory@gmail.com</p>
    
    <hr style="border:none; border-top:1px solid #dee2e6; margin:30px 0;">
    <p style="font-size:12px; color:#6c757d;">This is an automated notification from the IT Inventory System. Please do not reply to this email.</p>
</div>

