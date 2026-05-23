<?php
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid request']); exit; }
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { echo json_encode(['success'=>false,'message'=>'Invalid token']); exit; }

$code = strtoupper(trim($_POST['code'] ?? ''));
if (!$code) { echo json_encode(['success'=>false,'message'=>'Please enter a coupon code']); exit; }

$stmt = db()->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1 AND (expires_at IS NULL OR expires_at > NOW())");
$stmt->execute([$code]);
$coupon = $stmt->fetch();

if (!$coupon) { echo json_encode(['success'=>false,'message'=>'Invalid or expired coupon code']); exit; }

if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
    echo json_encode(['success'=>false,'message'=>'This coupon has reached its usage limit']); exit;
}

// Store in session
$_SESSION['coupon'] = ['id' => $coupon['id'], 'code' => $coupon['code'], 'type' => $coupon['type'], 'value' => $coupon['value']];
$discount = $coupon['type'] === 'percentage' ? $coupon['value'] . '%' : 'KSh ' . number_format($coupon['value'], 2);
echo json_encode(['success'=>true,'message'=>"Coupon applied! You save {$discount}",'coupon' => $_SESSION['coupon']]);
