<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Fetch cart items
if (isLoggedIn()) {
    $stmt = db()->prepare("SELECT c.id, c.quantity, c.product_variant_id, 
        p.id AS product_id, p.name, p.slug, p.thumbnail, p.price, p.discount_price, p.stock_quantity,
        v.variant_name, v.price AS variant_price, v.stock_quantity AS variant_stock
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        LEFT JOIN product_variants v ON c.product_variant_id = v.id
        WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = db()->prepare("SELECT c.id, c.quantity, c.product_variant_id, 
        p.id AS product_id, p.name, p.slug, p.thumbnail, p.price, p.discount_price, p.stock_quantity,
        v.variant_name, v.price AS variant_price, v.stock_quantity AS variant_stock
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        LEFT JOIN product_variants v ON c.product_variant_id = v.id
        WHERE c.session_id = ?");
    $stmt->execute([session_id()]);
}
$cartItems = $stmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $itemPrice = $item['product_variant_id'] !== null ? $item['variant_price'] : ($item['discount_price'] ?? $item['price']);
    $subtotal += $itemPrice * $item['quantity'];
}

// Coupon from session
$coupon = $_SESSION['coupon'] ?? null;
$discount = 0;
if ($coupon) {
    $discount = $coupon['type'] === 'percentage'
        ? $subtotal * ($coupon['value'] / 100)
        : min($coupon['value'], $subtotal);
}

$shipping = $subtotal >= 3000 ? 0 : (empty($cartItems) ? 0 : 200);
$taxRate = (float) getSetting('tax_rate', '16');
$taxAmount = ($subtotal - $discount) * ($taxRate / 100);
$total = $subtotal - $discount + $shipping + $taxAmount;

$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cart – <?= htmlspecialchars($siteName) ?></title>
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
      <h1 style="font-family:var(--font-display)">Your Cart</h1>
      <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li><li class="breadcrumb-item active">Cart</li></ol></nav>
    </div>
  </div>

  <div class="container py-5">
    <?php if (empty($cartItems)): ?>
    <div class="text-center py-5">
      <i class="fas fa-shopping-bag" style="font-size:4rem;color:var(--border)"></i>
      <h4 class="mt-3" style="color:var(--text-light)">Your cart is empty</h4>
      <p style="color:var(--text-light)">Looks like you haven't added anything yet!</p>
      <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary mt-2">Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
      <!-- Cart Items -->
      <div class="col-lg-8">
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius)">
          <?php foreach ($cartItems as $i => $item):
            $itemPrice = $item['product_variant_id'] !== null ? $item['variant_price'] : ($item['discount_price'] ?? $item['price']);
            $itemTotal = $itemPrice * $item['quantity'];
          ?>
          <div class="cart-item-row d-flex align-items-center gap-3 p-3 <?= $i < count($cartItems)-1 ? 'border-bottom' : '' ?>" style="border-color:var(--border)!important">
            <!-- Image -->
            <div style="width:80px;height:80px;border-radius:10px;overflow:hidden;background:var(--cream-dark);flex-shrink:0">
              <?php if ($item['thumbnail']): ?>
              <img src="<?= getImageUrl($item['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
              <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--border)"><i class="fas fa-birthday-cake fa-lg"></i></div>
              <?php endif; ?>
            </div>
            <!-- Info -->
            <div class="flex-fill info-wrapper">
              <a href="<?= APP_URL ?>/product/<?= urlencode($item['slug']) ?>" style="font-weight:600;color:var(--text-dark);font-size:.95rem"><?= htmlspecialchars($item['name']) ?></a>
              <?php if ($item['product_variant_id'] !== null): ?>
              <div style="font-size:.8rem;color:var(--text-medium);margin-top:.25rem">
                Size / Variant: <span class="badge bg-secondary"><?= htmlspecialchars($item['variant_name']) ?></span>
              </div>
              <?php endif; ?>
              <div style="color:var(--primary);font-weight:700;font-size:.9rem;margin-top:.25rem"><?= formatPrice($itemPrice) ?></div>
            </div>
            <!-- Qty -->
            <div class="d-flex align-items-center border rounded qty-wrapper" style="border-color:var(--border)!important;border-radius:var(--radius-sm)!important;overflow:hidden">
              <button class="cart-qty-btn btn px-2 py-1" data-cart-id="<?= $item['id'] ?>" data-action="decrease" style="border:none;background:var(--cream-dark);font-size:.8rem">−</button>
              <span class="px-3" style="font-weight:700;font-size:.9rem;min-width:36px;text-align:center"><?= $item['quantity'] ?></span>
              <button class="cart-qty-btn btn px-2 py-1" data-cart-id="<?= $item['id'] ?>" data-action="increase" style="border:none;background:var(--cream-dark);font-size:.8rem">+</button>
            </div>
            <!-- Subtotal -->
            <div class="subtotal-wrapper" style="font-weight:700;color:var(--primary);min-width:90px;text-align:right"><?= formatPrice($itemTotal) ?></div>
            <!-- Remove -->
            <button class="cart-qty-btn btn-icon remove-btn" data-cart-id="<?= $item['id'] ?>" data-action="remove" style="color:#ef4444;font-size:.9rem" title="Remove">
              <i class="fas fa-trash"></i>
            </button>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <a href="<?= APP_URL ?>/pages/shop.php" style="color:var(--primary);font-size:.875rem"><i class="fas fa-arrow-left me-1"></i>Continue Shopping</a>
        </div>
      </div>

      <!-- Summary -->
      <div class="col-lg-4">
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
          <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Order Summary</h5>

          <!-- Coupon -->
          <div class="mb-4">
            <label class="form-label fw-bold" style="font-size:.85rem">Coupon Code</label>
            <?php if ($coupon): ?>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-success"><?= htmlspecialchars($coupon['code']) ?> applied!</span>
              <a href="<?= APP_URL ?>/ajax/coupon.php?remove=1" style="font-size:.8rem;color:#ef4444">Remove</a>
            </div>
            <?php else: ?>
            <div class="input-group">
              <input type="text" class="form-control" id="couponCode" placeholder="Enter code">
              <button class="btn btn-outline-primary" id="applyCoupon">Apply</button>
            </div>
            <?php endif; ?>
          </div>

          <div class="d-flex justify-content-between mb-2" style="font-size:.9rem">
            <span style="color:var(--text-medium)">Subtotal</span><span><?= formatPrice($subtotal) ?></span>
          </div>
          <?php if ($discount > 0): ?>
          <div class="d-flex justify-content-between mb-2" style="font-size:.9rem">
            <span style="color:var(--text-medium)">Discount</span><span class="text-success">-<?= formatPrice($discount) ?></span>
          </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between mb-2" style="font-size:.9rem">
            <span style="color:var(--text-medium)">Shipping</span>
            <span><?= $shipping === 0 ? '<span class="text-success">FREE</span>' : formatPrice($shipping) ?></span>
          </div>
          <div class="d-flex justify-content-between mb-2" style="font-size:.9rem">
            <span style="color:var(--text-medium)">Tax (<?= $taxRate ?>%)</span><span><?= formatPrice($taxAmount) ?></span>
          </div>
          <?php if ($subtotal < 3000 && $subtotal > 0): ?>
          <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.8rem;border-radius:8px">
            <i class="fas fa-truck me-1"></i>Add <?= formatPrice(3000 - $subtotal) ?> more for free delivery!
          </div>
          <?php endif; ?>
          <hr style="border-color:var(--border)">
          <div class="d-flex justify-content-between mb-4">
            <strong>Total</strong><strong style="color:var(--primary);font-size:1.2rem"><?= formatPrice($total) ?></strong>
          </div>
          <a href="<?= APP_URL ?>/pages/checkout.php" class="btn btn-primary w-100 py-2">
            Proceed to Checkout <i class="fas fa-arrow-right ms-2"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
<script>
  window.APP_URL = '<?= APP_URL ?>';
  window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
