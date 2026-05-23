<?php
/**
 * Bakery Management System - Configuration
 * Copy this file to config.php and update with your settings
 */

// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'bakeryDB');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// APP CONFIGURATION
// ============================================================
define('APP_NAME', 'Crumbs & Co Bakery');
define('APP_URL', 'http://localhost/bakery');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // 'development' or 'production'

// ============================================================
// SECURITY
// ============================================================
define('SECRET_KEY', 'change-this-to-a-random-64-char-string-in-production');
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour
define('SESSION_LIFETIME', 86400); // 24 hours
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days

// ============================================================
// FILE UPLOADS
// ============================================================
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ============================================================
// EMAIL (PHPMailer)
// ============================================================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_FROM', 'hello@crumbsandco.com');
define('MAIL_FROM_NAME', 'Crumbs & Co Bakery');
define('MAIL_ENCRYPTION', 'tls');

// ============================================================
// PAYMENT GATEWAYS
// ============================================================
// M-Pesa Daraja
define('MPESA_CONSUMER_KEY', '');
define('MPESA_CONSUMER_SECRET', '');
define('MPESA_PAYBILL', '');
define('MPESA_PASSKEY', '');
define('MPESA_ENV', 'sandbox'); // 'sandbox' or 'production'

// Stripe
define('STRIPE_PUBLIC_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');

// PayPal
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_CLIENT_SECRET', '');
define('PAYPAL_ENV', 'sandbox');

// ============================================================
// TIMEZONE & LOCALE
// ============================================================
define('TIMEZONE', 'Africa/Nairobi');
define('DATE_FORMAT', 'd M Y');
define('DATETIME_FORMAT', 'd M Y H:i');
define('CURRENCY', 'KES');
define('CURRENCY_SYMBOL', 'KSh');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
