<?php
// admin/logout.php
require_once __DIR__ . '/../includes/bootstrap.php';
if (isAdminLoggedIn()) logActivity('admin', $_SESSION['admin_id'], 'logout', 'Admin logged out');
unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
redirect(APP_URL . '/admin/login.php');
