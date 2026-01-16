# Gmail Setup Guide

## Method 1: App Password (Recommended)

### Step 1: Enable 2-Factor Authentication
1. Go to https://myaccount.google.com/security
2. Turn on "2-Step Verification"
3. Follow the setup process

### Step 2: Generate App Password
1. Go to https://myaccount.google.com/apppasswords
2. Select "Mail" from dropdown
3. Select "Other" and enter "Workflow System"
4. Copy the 16-character password

### Step 3: Update .env file
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="Workflow Management System"
```

## Method 2: Less Secure Apps (Not Recommended)

### Step 1: Enable Less Secure Apps
1. Go to https://myaccount.google.com/security
2. Turn on "Less secure app access"
3. ⚠️ This is less secure

### Step 2: Use Regular Password
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-regular-gmail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="Workflow Management System"
```

## Method 3: OAuth2 (Advanced)

For production use, OAuth2 is most secure but requires more setup.

## Test Configuration

After updating .env file:
```bash
php artisan config:cache
php artisan test:notifications
```

## Troubleshooting

### Error: "Username and Password not accepted"
- Make sure 2FA is enabled
- Use App Password, not regular password
- Check if "Less secure apps" is enabled (if using regular password)

### Error: "Connection refused"
- Check internet connection
- Verify Gmail SMTP settings
- Try different port (465 with SSL)

## Current Status
✅ Notification system: Working
✅ Database notifications: Working
✅ Email templates: Ready
⚠️ Email sending: Needs proper Gmail credentials
