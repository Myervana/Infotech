<div style="font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#222;">
    <h2 style="color:#28a745; margin-bottom:20px;">🎉 Account Approved!</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Great news! Your account has been approved by the IT Inventory System administrator. You can now login directly to the IT Inventory System using your Gmail account.</p>
    
    <div style="background:#d4edda; border:1px solid #c3e6cb; border-radius:8px; padding:20px; margin:20px 0;">
        <h3 style="color:#155724; margin-top:0;">✅ Account Details:</h3>
        <p style="margin:5px 0;"><strong>Name:</strong> {{ $user->name }}</p>
        <p style="margin:5px 0;"><strong>Email:</strong> {{ $user->email }}</p>
        <p style="margin:5px 0;"><strong>Status:</strong> <span style="color:#28a745; font-weight:bold;">Approved & Ready to Login</span></p>
        <p style="margin:5px 0;"><strong>Access:</strong> Direct login to IT Inventory System</p>
    </div>
    
    <div style="text-align:center; margin:30px 0;">
        <a href="{{ url('/login') }}" style="background:#007bff; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold; font-size:16px;">Login to IT Inventory System</a>
    </div>
    
    <div style="background:#e3f2fd; border:1px solid #2196f3; border-radius:8px; padding:15px; margin:20px 0;">
        <h4 style="color:#1976d2; margin-top:0;">What you can do now:</h4>
        <ul style="margin:10px 0; padding-left:20px;">
            <li>Access the IT Inventory Dashboard</li>
            <li>Manage room items and equipment</li>
            <li>Track maintenance and repairs</li>
            <li>Generate reports and analytics</li>
            <li>Use the barcode scanning system</li>
        </ul>
    </div>
    
    <p style="margin-top:30px;">If you have any questions or need assistance, please contact the system administrator.</p>
    
    <p>Welcome to the IT Inventory System!<br/>IT Inventory Team<br/>iitech.inventory@gmail.com</p>
    
    <hr style="border:none; border-top:1px solid #dee2e6; margin:30px 0;">
    <p style="font-size:12px; color:#6c757d;">This is an automated notification from the IT Inventory System. Please do not reply to this email.</p>
</div>
