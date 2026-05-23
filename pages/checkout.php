<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

// Get cart
$stmt = db()->prepare("SELECT c.id, c.quantity, c.product_variant_id, 
    p.id AS pid, p.name, p.slug, p.thumbnail, p.price, p.discount_price, p.sku, p.stock_quantity,
    v.variant_name, v.price AS variant_price, v.stock_quantity AS variant_stock, v.sku AS variant_sku
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    LEFT JOIN product_variants v ON c.product_variant_id = v.id
    WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();
if (empty($cartItems)) { redirect(APP_URL . '/pages/cart.php'); }

$subtotal = array_sum(array_map(fn($i) => ($i['product_variant_id'] !== null ? $i['variant_price'] : ($i['discount_price'] ?? $i['price'])) * $i['quantity'], $cartItems));
$coupon   = $_SESSION['coupon'] ?? null;
$discount = 0;
if ($coupon) $discount = $coupon['type'] === 'percentage' ? $subtotal * ($coupon['value']/100) : min($coupon['value'], $subtotal);
$shipping   = $subtotal >= 3000 ? 0 : 200;
$taxRate    = (float) getSetting('tax_rate', '16');
$taxAmount  = ($subtotal - $discount) * ($taxRate / 100);
$total      = $subtotal - $discount + $shipping + $taxAmount;

$user    = getCurrentUser();
$address = db()->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1");
$address->execute([$user['id']]);
$defaultAddr = $address->fetch();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid token.'; }
    else {
        $billing = [
            'name'    => sanitize($_POST['billing_name'] ?? ''),
            'email'   => sanitizeEmail($_POST['billing_email'] ?? ''),
            'phone'   => sanitize($_POST['billing_phone'] ?? ''),
            'address' => sanitize($_POST['billing_address'] ?? ''),
            'city'    => sanitize($_POST['billing_city'] ?? ''),
            'state'   => sanitize($_POST['billing_state'] ?? ''),
            'postal'  => sanitize($_POST['billing_postal'] ?? ''),
        ];
        $payment = sanitize($_POST['payment_method'] ?? 'cod');
        $notes   = sanitize($_POST['order_notes'] ?? '');

        if (!$billing['name'] || !$billing['email'] || !$billing['phone'] || !$billing['address'] || !$billing['city']) {
            $error = 'Please fill in all required billing fields.';
        } else {
            $orderNum = generateOrderNumber();
            $db = db();
            $db->beginTransaction();
            try {
                $db->prepare("INSERT INTO orders (order_number, user_id, coupon_id, billing_name, billing_email, billing_phone, billing_address, billing_city, billing_state, billing_postal, subtotal, discount_amount, shipping_cost, tax_amount, total_amount, payment_method, order_notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$orderNum, $user['id'], $coupon['id'] ?? null, $billing['name'], $billing['email'], $billing['phone'], $billing['address'], $billing['city'], $billing['state'], $billing['postal'], $subtotal, $discount, $shipping, $taxAmount, $total, $payment, $notes]);
                $orderId = $db->lastInsertId();

                foreach ($cartItems as $item) {
                    $isVariant = ($item['product_variant_id'] !== null);
                    $ip = $isVariant ? $item['variant_price'] : ($item['discount_price'] ?? $item['price']);
                    $itemSku = $isVariant ? $item['variant_sku'] : $item['sku'];
                    
                    $db->prepare("INSERT INTO order_items (order_id, product_id, product_variant_id, variant_name, product_name, product_sku, product_image, quantity, unit_price, total_price) VALUES (?,?,?,?,?,?,?,?,?,?)")
                        ->execute([$orderId, $item['pid'], $item['product_variant_id'], $item['variant_name'], $item['name'], $itemSku, $item['thumbnail'], $item['quantity'], $ip, $ip * $item['quantity']]);
                    
                    if ($isVariant) {
                        $db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?")
                            ->execute([$item['quantity'], $item['product_variant_id'], $item['quantity']]);
                    } else {
                        $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?")
                            ->execute([$item['quantity'], $item['pid'], $item['quantity']]);
                    }
                    
                    $qtyBefore = $isVariant ? $item['variant_stock'] : $item['stock_quantity'];
                    $qtyAfter = $qtyBefore - $item['quantity'];
                    $note = "Order $orderNum" . ($isVariant ? " (Variant: {$item['variant_name']})" : "");
                    $db->prepare("INSERT INTO inventory_logs (product_id, action, quantity_change, quantity_before, quantity_after, note) VALUES (?,?,?,?,?,?)")
                        ->execute([$item['pid'], 'sale', -$item['quantity'], $qtyBefore, $qtyAfter, $note]);
                }

                if ($coupon) { $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$coupon['id']]); }

                // Clear cart
                $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user['id']]);
                unset($_SESSION['coupon']);

                // Notification
                $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)")
                    ->execute([$user['id'], 'order', 'Order Placed!', "Your order {$orderNum} has been placed successfully.", APP_URL . '/pages/orders.php']);

                $db->commit();
                logActivity('user', $user['id'], 'order_placed', "Order $orderNum placed");
                redirect(APP_URL . '/pages/order-confirmation.php?order=' . $orderNum);
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Failed to place order. Please try again.';
            }
        }
    }
}

$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Checkout – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:var(--navbar-height)">
  <div style="background:var(--cream-dark);padding:3rem 0 2rem">
    <div class="container">
      <h1 style="font-family:var(--font-display)">Checkout</h1>
      <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li><li class="breadcrumb-item"><a href="<?= APP_URL ?>/pages/cart.php">Cart</a></li><li class="breadcrumb-item active">Checkout</li></ol></nav>
    </div>
  </div>

  <div class="container py-5">
    <?php if ($error): ?><div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="row g-4">
        <!-- Billing -->
        <div class="col-lg-8">
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:1.5rem">
            <h5 style="font-family:var(--font-display);margin-bottom:1.5rem"><i class="fas fa-user me-2" style="color:var(--primary)"></i>Billing Details</h5>
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label fw-bold small">Full Name *</label>
                <input type="text" class="form-control" name="billing_name" value="<?= htmlspecialchars($user['name']) ?>" required></div>
              <div class="col-md-6"><label class="form-label fw-bold small">Email *</label>
                <input type="email" class="form-control" name="billing_email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
              <div class="col-md-6"><label class="form-label fw-bold small">Phone *</label>
                <input type="tel" class="form-control" name="billing_phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required></div>
              <div class="col-12"><label class="form-label fw-bold small">Address *</label>
                <input type="text" class="form-control" name="billing_address" value="<?= htmlspecialchars($defaultAddr['address_line1'] ?? '') ?>" placeholder="Street address" required></div>
              <div class="col-md-4"><label class="form-label fw-bold small">City *</label>
                <input type="text" class="form-control" name="billing_city" value="<?= htmlspecialchars($defaultAddr['city'] ?? 'Nairobi') ?>" required></div>
              <div class="col-md-4"><label class="form-label fw-bold small">State</label>
                <input type="text" class="form-control" name="billing_state" value="<?= htmlspecialchars($defaultAddr['state'] ?? 'Nairobi') ?>"></div>
              <div class="col-md-4"><label class="form-label fw-bold small">Postal Code</label>
                <input type="text" class="form-control" name="billing_postal" value="<?= htmlspecialchars($defaultAddr['postal_code'] ?? '') ?>"></div>
            </div>
          </div>

          <!-- Payment -->
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:1.5rem">
            <h5 style="font-family:var(--font-display);margin-bottom:1.5rem"><i class="fas fa-credit-card me-2" style="color:var(--primary)"></i>Payment Method</h5>
            <?php
            $methods = [
                'cod'    => ['icon' => 'money-bill-wave', 'label' => 'Cash on Delivery', 'desc' => 'Pay when your order arrives'],
                'mpesa'  => ['icon' => 'mobile-alt', 'label' => 'M-Pesa', 'desc' => 'Pay via M-Pesa Paybill: ' . getSetting('mpesa_paybill', '123456')],
                'stripe' => ['icon' => 'credit-card', 'label' => 'Credit / Debit Card', 'desc' => 'Secure payment via Stripe'],
                'paypal' => ['icon' => 'paypal', 'label' => 'PayPal', 'desc' => 'Pay with your PayPal account'],
            ];
            foreach ($methods as $val => $m): ?>
            <div class="form-check mb-2 p-3" style="background:var(--cream);border-radius:var(--radius-sm);border:1px solid var(--border)">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_<?= $val ?>" value="<?= $val ?>" <?= $val === 'cod' ? 'checked' : '' ?>>
              <label class="form-check-label d-flex align-items-center gap-2" for="pm_<?= $val ?>" style="cursor:pointer;width:100%">
                <i class="fas fa-<?= $m['icon'] ?>" style="color:var(--primary);width:20px"></i>
                <div><strong style="font-size:.9rem"><?= $m['label'] ?></strong><br><small style="color:var(--text-light)"><?= $m['desc'] ?></small></div>
              </label>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Notes -->
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem">
            <h5 style="font-family:var(--font-display);margin-bottom:1rem"><i class="fas fa-sticky-note me-2" style="color:var(--primary)"></i>Order Notes</h5>
            <textarea class="form-control" name="order_notes" rows="3" placeholder="Special delivery instructions, allergies, customisation requests..."></textarea>
          </div>
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;position:sticky;top:90px">
            <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Order Summary</h5>
            <?php foreach ($cartItems as $item):
              $ip = $item['product_variant_id'] !== null ? $item['variant_price'] : ($item['discount_price'] ?? $item['price']); ?>
            <div class="d-flex gap-2 mb-2 pb-2" style="border-bottom:1px solid var(--border)">
              <div style="width:48px;height:48px;border-radius:8px;overflow:hidden;background:var(--cream-dark);flex-shrink:0">
                <?php if ($item['thumbnail']): ?><img src="<?= getImageUrl($item['thumbnail']) ?>" style="width:100%;height:100%;object-fit:cover"><?php else: ?><div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center"><i class="fas fa-birthday-cake" style="color:var(--border);font-size:.8rem"></i></div><?php endif; ?>
              </div>
              <div class="flex-fill">
                <div style="font-size:.85rem;font-weight:600"><?= htmlspecialchars($item['name']) ?></div>
                <?php if ($item['product_variant_id'] !== null): ?>
                <div style="font-size:.75rem;color:var(--text-medium)">Variant: <span class="badge bg-secondary"><?= htmlspecialchars($item['variant_name']) ?></span></div>
                <?php endif; ?>
                <div style="font-size:.8rem;color:var(--text-light)">×<?= $item['quantity'] ?></div>
              </div>
              <div style="font-weight:700;font-size:.85rem;color:var(--primary)"><?= formatPrice($ip * $item['quantity']) ?></div>
            </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between mt-3 small"><span style="color:var(--text-medium)">Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
            <?php if ($discount > 0): ?><div class="d-flex justify-content-between small"><span style="color:var(--text-medium)">Discount</span><span class="text-success">-<?= formatPrice($discount) ?></span></div><?php endif; ?>
            <div class="d-flex justify-content-between small"><span style="color:var(--text-medium)">Shipping</span><span><?= $shipping === 0 ? '<span class="text-success">FREE</span>' : formatPrice($shipping) ?></span></div>
            <div class="d-flex justify-content-between small"><span style="color:var(--text-medium)">Tax (<?= $taxRate ?>%)</span><span><?= formatPrice($taxAmount) ?></span></div>
            <hr style="border-color:var(--border)">
            <div class="d-flex justify-content-between mb-4"><strong>Total</strong><strong style="color:var(--primary);font-size:1.2rem"><?= formatPrice($total) ?></strong></div>
            <button type="submit" class="btn btn-primary w-100 py-2">
              <i class="fas fa-check-circle me-2"></i>Place Order
            </button>
            <p style="font-size:.75rem;color:var(--text-light);text-align:center;margin-top:.75rem">
              <i class="fas fa-lock me-1"></i>Your payment information is secure
            </p>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  window.APP_URL = '<?= APP_URL ?>';
  window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
