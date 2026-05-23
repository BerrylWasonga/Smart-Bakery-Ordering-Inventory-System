<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$id = sanitizeInt($_GET['id'] ?? 0);
if (!$id) { echo '<p class="text-center py-4 text-muted">Product not found.</p>'; exit; }

$stmt = db()->prepare("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { echo '<p class="text-center py-4 text-muted">Product not found.</p>'; exit; }

$images = db()->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
$images->execute([$id]);
$imgs = $images->fetchAll();

$reviewStmt = db()->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = ? AND status = 'approved'");
$reviewStmt->execute([$id]);
$reviewData = $reviewStmt->fetch();
$price = $p['discount_price'] ?? $p['price'];
$currSym = CURRENCY_SYMBOL;
?>
<div class="row g-0">
  <div class="col-md-5">
    <div class="quick-view-img" style="background:var(--cream-dark);border-radius:12px;overflow:hidden;aspect-ratio:1;display:flex;align-items:center;justify-content:center">
      <?php if ($p['thumbnail']): ?>
      <img src="<?= getImageUrl($p['thumbnail']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%;height:100%;object-fit:cover">
      <?php else: ?>
      <i class="fas fa-birthday-cake" style="font-size:5rem;color:var(--border)"></i>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-7 p-4">
    <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);font-weight:700"><?= htmlspecialchars($p['cat_name']) ?></span>
    <h4 class="mt-1 mb-2" style="font-family:var(--font-display)"><?= htmlspecialchars($p['name']) ?></h4>
    <?php if ($reviewData['count'] > 0): ?>
    <div class="d-flex align-items-center gap-2 mb-3">
      <div style="color:var(--gold)">
        <?php for ($i=1;$i<=5;$i++) echo $i<=round($reviewData['avg_rating']) ? '★' : '☆'; ?>
      </div>
      <small style="color:var(--text-light)">(<?= $reviewData['count'] ?> reviews)</small>
    </div>
    <?php endif; ?>
    <div class="mb-3">
      <?php if ($p['discount_price']): ?>
      <span style="font-size:1.6rem;font-weight:700;color:var(--primary)"><?= $currSym ?> <?= number_format($p['discount_price'],2) ?></span>
      <span style="font-size:1rem;color:var(--text-light);text-decoration:line-through;margin-left:.5rem"><?= $currSym ?> <?= number_format($p['price'],2) ?></span>
      <?php else: ?>
      <span style="font-size:1.6rem;font-weight:700;color:var(--primary)"><?= $currSym ?> <?= number_format($p['price'],2) ?></span>
      <?php endif; ?>
    </div>
    <p style="font-size:.9rem;color:var(--text-medium);line-height:1.7"><?= htmlspecialchars($p['short_description'] ?? '') ?></p>
    <div class="d-flex align-items-center gap-2 mb-3">
      <span style="font-size:.8rem;color:var(--text-light)">SKU: <?= htmlspecialchars($p['sku']) ?></span>
      <span class="badge <?= $p['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>">
        <?= $p['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
      </span>
    </div>
    <div class="d-flex gap-3">
      <button class="btn btn-primary ajax-add-cart flex-fill"
        data-product-id="<?= $p['id'] ?>"
        data-product-name="<?= htmlspecialchars($p['name']) ?>"
        <?= $p['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
        <i class="fas fa-shopping-bag me-2"></i>Add to Cart
      </button>
      <a href="<?= APP_URL ?>/product/<?= urlencode($p['slug']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-eye"></i>
      </a>
    </div>
  </div>
</div>
