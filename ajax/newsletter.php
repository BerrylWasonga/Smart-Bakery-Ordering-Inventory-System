<?php
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid request']); exit; }
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { echo json_encode(['success'=>false,'message'=>'Invalid token']); exit; }

$email = sanitizeEmail($_POST['email'] ?? '');
if (!$email || !validateEmail($email)) { echo json_encode(['success'=>false,'message'=>'Please enter a valid email address']); exit; }

$check = db()->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) { echo json_encode(['success'=>false,'message'=>'This email is already subscribed!']); exit; }

db()->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)")->execute([$email]);
echo json_encode(['success'=>true,'message'=>'Thank you for subscribing! 🎉']);
