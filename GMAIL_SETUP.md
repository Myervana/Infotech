# Gmail SMTP Setup Guide

## Required Configuration

To enable OTP email sending from `iitech.inventory@gmail.com`, you need to configure the following:

### 1. Create .env file in the project root with these settings:

```env
# Mail Configuration for Gmail SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=iitech.inventory@gmail.com
MAIL_PASSWORD=your-app-specific-password-here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=iitech.inventory@gmail.com
MAIL_FROM_NAME="IT Inventory System"
```

### 2. Gmail Account Setup

1. **Enable 2-Factor Authentication** on the Gmail account `iitech.inventory@gmail.com`
2. **Generate App Password**:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a new app password for "Mail"
   - Use this 16-character password in the `MAIL_PASSWORD` field

### 3. Alternative: Use Gmail SMTP with OAuth2 (Recommended for production)

For better security, consider using OAuth2 instead of app passwords.

### 4. Test Configuration

After setting up the .env file, test the email functionality by:
1. Going to the login page
2. Clicking "Forgot Password?"
3. Entering a valid Gmail address
4. Checking if the OTP email is received

### 5. Troubleshooting

- **Authentication failed**: Check if the app password is correct
- **Connection refused**: Check network settings and firewall
- **SMTP_NOT_CONFIGURED**: Ensure .env file exists and MAIL_PASSWORD is set

### 6. Security Notes

- Never commit the .env file to version control
- Use app passwords instead of the main Gmail password
- Consider using environment-specific configurations for production

