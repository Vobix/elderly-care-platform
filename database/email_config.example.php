<?php
/**
 * Email Configuration for PHPMailer
 * Centralized email settings
 * 
 * IMPORTANT: Copy this file to email_config.php and update with your credentials
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
define('SMTP_AUTH', true);

// Email Credentials - Update these with your actual credentials
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password-here');

// Sender Information
define('MAIL_FROM_ADDRESS', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'Mind Mosaic');

/*
 * Gmail Setup Instructions:
 * 1. Enable 2-Factor Authentication on your Google account
 * 2. Go to: https://myaccount.google.com/apppasswords
 * 3. Generate an App Password for "Mail"
 * 4. Use that 16-character password as SMTP_PASSWORD (no spaces)
 */
