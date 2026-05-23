<?php
// pages/logout.php
require_once __DIR__ . '/../includes/bootstrap.php';
session_destroy();
setcookie('remember_token','',time()-3600,'/');
header('Location: ' . APP_URL . '/pages/login.php'); exit;
