<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$orderId = sanitizeInt($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) { redirect(APP_URL . '/pages/orders.php'); }

$items = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$order['id']]);
$orderItems = $items->fetchAll();

$statusColors = ['pending'=>'warning','processing'=>'info','ready'=>'primary','delivered'=>'success','cancelled'=>'danger'];
$user = getCurrentUser();
$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order <?= htmlspecialchars($order['order_number']) ?> – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div style="padding-top:var(--navbar-height);background:var(--cream);min-height:100vh">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-lg-3"><?php include __DIR__ . '/../includes/user-sidebar.php'; ?></div>
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 style="font-family:var(--font-display);margin:0">Order <?= htmlspecialchars($order['order_number']) ?></h4>
          <a href="<?= APP_URL ?>/pages/orders.php" style="color:var(--primary);font-size:.875rem"><i class="fas fa-arrow-left me-1"></i>Back to Orders</a>
        </div>

        <!-- Status timeline -->
        <?php
        $steps = ['pending'=>0,'processing'=>1,'ready'=>2,'delivered'=>3];
        $curStep = $steps[$order['order_status']] ?? 0;
        $stepLabels = ['Order Placed','Processing','Ready','Delivered'];
        $stepIcons = ['check','cog','box','truck'];
        if ($order['order_status'] === 'cancelled') $curStep = -1;
        ?>
        <?php if ($order['order_status'] !== 'cancelled'): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:1.5rem">
          <h6 style="font-family:var(--font-display);margin-bottom:1.5rem">Order Status</h6>
          <div class="d-flex justify-content-between position-relative" style="--bs-gutter-x:0">
            <div style="position:absolute;top:22px;left:10%;right:10%;height:3px;background:var(--border);z-index:0">
              <div style="height:100%;width:<?= $curStep > 0 ? min(100,($curStep/3)*100) : 0 ?>%;background:var(--primary);transition:width .5s"></div>
            </div>
            <?php foreach ($stepLabels as $i => $label): $done = $i <= $curStep; ?>
            <div class="text-center" style="flex:1;position:relative;z-index:1">
              <div style="width:44px;height:44px;border-radius:50%;background:<?= $done?'var(--primary)':'var(--cream-dark)' ?>;border:3px solid <?= $done?'var(--primary)':'var(--border)' ?>;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem">
                <i class="fas fa-<?= $stepIcons[$i] ?>" style="color:<?= $done?'white':'var(--text-light)' ?>;font-size:.8rem"></i>
              </div>
              <div style="font-size:.75rem;font-weight:<?= $done?'700':'400' ?>;color:<?= $done?'var(--primary)':'var(--text-light)' ?>"><?= $label ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php else: ?>
        <div class="alert alert-danger mb-4"><i class="fas fa-times-circle me-2"></i>This order has been cancelled.</div>
        <?php endif; ?>

        <div class="row g-4">
          <!-- Items -->
          <div class="col-md-8">
            <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
              <h6 style="font-family:var(--font-display);margin-bottom:1.25rem">Items</h6>
              <?php foreach ($orderItems as $item): ?>
              <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--border)">
                <div style="width:60px;height:60px;border-radius:10px;background:var(--cream-dark);overflow:hidden;flex-shrink:0">
                  <?php if ($item['product_image']): ?>
                  <img src="<?= getImageUrl($item['product_image']) ?>" style="width:100%;height:100%;object-fit:cover">
                  <?php else: ?>
                  <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--border)"><i class="fas fa-birthday-cake"></i></div>
                  <?php endif; ?>
                </div>
                <div class="flex-fill">
                  <div style="font-weight:600;font-size:.9rem"><?= htmlspecialchars($item['product_name']) ?></div>
                  <?php if ($item['product_variant_id'] !== null): ?>
                  <div style="font-size:.75rem;color:var(--text-medium);margin-top:.25rem">Variant: <span class="badge bg-secondary"><?= htmlspecialchars($item['variant_name']) ?></span></div>
                  <?php endif; ?>
                  <div style="font-size:.8rem;color:var(--text-light);margin-top:.25rem">Qty <?= $item['quantity'] ?> × <?= formatPrice($item['unit_price']) ?></div>
                </div>
                <div style="font-weight:700;color:var(--primary)"><?= formatPrice($item['total_price']) ?></div>
              </div>
              <?php endforeach; ?>
              <div class="d-flex justify-content-between text-sm mt-2"><span style="color:var(--text-medium)">Subtotal</span><span><?= formatPrice($order['subtotal']) ?></span></div>
              <?php if ($order['discount_amount'] > 0): ?><div class="d-flex justify-content-between text-sm"><span style="color:var(--text-medium)">Discount</span><span class="text-success">-<?= formatPrice($order['discount_amount']) ?></span></div><?php endif; ?>
              <div class="d-flex justify-content-between text-sm"><span style="color:var(--text-medium)">Shipping</span><span><?= $order['shipping_cost'] == 0 ? '<span class="text-success">FREE</span>' : formatPrice($order['shipping_cost']) ?></span></div>
              <div class="d-flex justify-content-between text-sm"><span style="color:var(--text-medium)">Tax</span><span><?= formatPrice($order['tax_amount']) ?></span></div>
              <hr style="border-color:var(--border)">
              <div class="d-flex justify-content-between"><strong>Total</strong><strong style="color:var(--primary);font-size:1.1rem"><?= formatPrice($order['total_amount']) ?></strong></div>
            </div>
          </div>
          <!-- Info -->
          <div class="col-md-4">
            <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1rem">
              <h6 style="font-family:var(--font-display);margin-bottom:1rem">Delivery Address</h6>
              <address style="font-size:.875rem;color:var(--text-medium);margin:0">
                <strong><?= htmlspecialchars($order['billing_name']) ?></strong><br>
                <?= htmlspecialchars($order['billing_address']) ?><br>
                <?= htmlspecialchars($order['billing_city']) ?>, <?= htmlspecialchars($order['billing_state']) ?><br>
                <?= htmlspecialchars($order['billing_phone']) ?>
              </address>
            </div>
            <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
              <h6 style="font-family:var(--font-display);margin-bottom:1rem">Payment Info</h6>
              <div style="font-size:.875rem;color:var(--text-medium)">
                <div class="d-flex justify-content-between mb-1"><span>Method</span><span class="fw-bold"><?= strtoupper($order['payment_method']) ?></span></div>
                <div class="d-flex justify-content-between"><span>Status</span>
                  <span class="badge bg-<?= $order['payment_status']==='paid'?'success':($order['payment_status']==='failed'?'danger':'warning') ?>"><?= ucfirst($order['payment_status']) ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  window.APP_URL = '<?= APP_URL ?>';
  window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
