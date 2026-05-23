<?php
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid request']); exit; }
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { echo json_encode(['success'=>false,'message'=>'Invalid token']); exit; }
if (!isLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Please login to use wishlist']); exit; }

$productId = sanitizeInt($_POST['product_id'] ?? 0);
if (!$productId) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }

$check = db()->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$check->execute([$_SESSION['user_id'], $productId]);

if ($check->fetch()) {
    db()->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$_SESSION['user_id'], $productId]);
    echo json_encode(['success'=>true,'added'=>false,'message'=>'Removed from wishlist']);
} else {
    db()->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?,?)")->execute([$_SESSION['user_id'], $productId]);
    echo json_encode(['success'=>true,'added'=>true,'message'=>'Added to wishlist']);
}
