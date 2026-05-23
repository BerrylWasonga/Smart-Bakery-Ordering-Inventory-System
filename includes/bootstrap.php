<?php
/**
 * Bootstrap - included at top of every page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Define constants that depend on config
if (!defined('DATE_FORMAT'))     define('DATE_FORMAT', 'd M Y');
if (!defined('DATETIME_FORMAT')) define('DATETIME_FORMAT', 'd M Y H:i');
if (!defined('CURRENCY'))        define('CURRENCY', 'KES');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', 'KSh');

startSecureSession();

// Check maintenance mode (skip for admin)
if (!isAdminLoggedIn() && getSetting('maintenance_mode') === '1') {
    if (!str_contains($_SERVER['REQUEST_URI'], '/admin')) {
        include __DIR__ . '/../templates/maintenance.php';
        exit;
    }
}
