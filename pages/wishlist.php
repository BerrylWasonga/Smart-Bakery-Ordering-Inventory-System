<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
$user = getCurrentUser();

$stmt = db()->prepare("SELECT w.id AS wid, p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.added_at DESC");
$stmt->execute([$user['id']]);
$wishlist = $stmt->fetchAll();
$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Wishlist – <?= htmlspecialchars($siteName) ?></title>
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
        <h4 style="font-family:var(--font-display);margin-bottom:1.5rem">My Wishlist (<?= count($wishlist) ?>)</h4>
        <?php if (empty($wishlist)): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:3rem;text-align:center">
          <i class="fas fa-heart" style="font-size:3rem;color:var(--border)"></i>
          <h5 style="margin-top:1rem;color:var(--text-light)">Your wishlist is empty</h5>
          <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary mt-2">Browse Products</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
          <?php foreach ($wishlist as $p): ?>
          <div class="col-6 col-md-4">
            <div class="product-card">
              <div class="product-image">
                <?php if ($p['thumbnail']): ?>
                <img src="<?= getImageUrl($p['thumbnail']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                <?php else: ?>
                <div class="product-img-placeholder"><i class="fas fa-birthday-cake"></i></div>
                <?php endif; ?>
              </div>
              <div class="product-info">
                <h6 class="product-name"><a href="<?= APP_URL ?>/product/<?= urlencode($p['slug']) ?>"><?= htmlspecialchars($p['name']) ?></a></h6>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="price-current"><?= formatPrice($p['discount_price'] ?? $p['price']) ?></span>
                  <div class="d-flex gap-2">
                    <button class="btn-add-cart ajax-add-cart" data-product-id="<?= $p['id'] ?>" title="Add to Cart"><i class="fas fa-cart-plus"></i></button>
                    <button class="btn-icon ajax-wishlist" data-product-id="<?= $p['id'] ?>" title="Remove" style="width:36px;height:36px;color:#ef4444;border:1px solid var(--border);border-radius:50%">
                      <i class="fas fa-heart"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>const APP_URL='<?= APP_URL ?>',CSRF_TOKEN='<?= generateCSRFToken() ?>';</script>
</body></html>
