<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$orderNum = sanitize($_GET['order'] ?? '');
if (!$orderNum) redirect(APP_URL . '/pages/dashboard.php');

$stmt = db()->prepare("SELECT o.*, u.name AS user_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.order_number = ? AND o.user_id = ?");
$stmt->execute([$orderNum, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) redirect(APP_URL . '/pages/dashboard.php');

$items = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$order['id']]);
$orderItems = $items->fetchAll();

$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order Confirmed – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:var(--navbar-height)">
  <div class="container py-5">
    <div style="max-width:700px;margin:0 auto">
      <!-- Success Banner -->
      <div class="text-center mb-5" style="padding:3rem;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:var(--radius-lg);color:white">
        <div style="width:80px;height:80px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem">✓</div>
        <h2 style="font-family:var(--font-display);color:white">Order Placed!</h2>
        <p style="opacity:.9;margin-bottom:.5rem">Thank you, <?= htmlspecialchars($order['billing_name']) ?>!</p>
        <strong style="font-size:1.1rem"><?= htmlspecialchars($order['order_number']) ?></strong>
      </div>

      <!-- Order Details -->
      <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:1.5rem">
        <h5 style="font-family:var(--font-display);margin-bottom:1.25rem">Order Details</h5>
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700">Order Number</div>
            <div style="font-weight:700;font-size:.95rem"><?= htmlspecialchars($order['order_number']) ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700">Date</div>
            <div style="font-size:.95rem"><?= formatDate($order['created_at']) ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700">Payment</div>
            <div style="font-size:.95rem"><?= strtoupper($order['payment_method']) ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700">Status</div>
            <span class="badge bg-warning text-dark"><?= ucfirst($order['order_status']) ?></span>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:1.5rem">
        <h5 style="font-family:var(--font-display);margin-bottom:1.25rem">Items Ordered</h5>
        <?php foreach ($orderItems as $item): ?>
        <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--border)">
          <div style="width:56px;height:56px;border-radius:8px;overflow:hidden;background:var(--cream-dark);flex-shrink:0">
            <?php if ($item['product_image']): ?>
            <img src="<?= getImageUrl($item['product_image']) ?>" style="width:100%;height:100%;object-fit:cover">
            <?php endif; ?>
          </div>
          <div class="flex-fill">
            <div style="font-weight:600"><?= htmlspecialchars($item['product_name']) ?></div>
            <div style="font-size:.8rem;color:var(--text-light)">Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['unit_price']) ?></div>
          </div>
          <div style="font-weight:700;color:var(--primary)"><?= formatPrice($item['total_price']) ?></div>
        </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between mt-3">
          <strong>Total Paid</strong>
          <strong style="font-size:1.2rem;color:var(--primary)"><?= formatPrice($order['total_amount']) ?></strong>
        </div>
      </div>

      <!-- Actions -->
      <div class="d-flex gap-3 flex-wrap justify-content-center">
        <a href="<?= APP_URL ?>/pages/orders.php" class="btn btn-primary">View My Orders</a>
        <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-outline-primary">Continue Shopping</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
