<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . APP_URL . '/pages/shop.php'); exit; }

$stmt = db()->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug FROM products p 
    JOIN categories c ON p.category_id = c.id WHERE p.slug = ? AND p.status = 'active'");
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) { header('Location: ' . APP_URL . '/pages/shop.php'); exit; }

// Fetch variants
$variantsStmt = db()->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC");
$variantsStmt->execute([$product['id']]);
$productVariants = $variantsStmt->fetchAll();

// Increment views
db()->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$product['id']]);

// Images
$images = db()->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
$images->execute([$product['id']]);
$imgs = $images->fetchAll();

// Reviews
$reviews = db()->prepare("SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
$reviews->execute([$product['id']]);
$reviewList = $reviews->fetchAll();

$ratingStmt = db()->prepare("SELECT AVG(rating) AS avg, COUNT(*) AS cnt FROM reviews WHERE product_id = ? AND status = 'approved'");
$ratingStmt->execute([$product['id']]);
$ratingData = $ratingStmt->fetch();

// Related products
$related = db()->prepare("SELECT p.* FROM products p WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' LIMIT 4");
$related->execute([$product['category_id'], $product['id']]);
$relatedProds = $related->fetchAll();

// Track recently viewed
if (isLoggedIn()) {
    $db = db();
    $db->prepare("INSERT INTO recently_viewed (user_id, product_id) VALUES (?,?) ON DUPLICATE KEY UPDATE viewed_at = NOW()")->execute([$_SESSION['user_id'], $product['id']]);
}

// Handle review submission
$reviewError = '';
$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) $reviewError = 'Please login to submit a review.';
    elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) $reviewError = 'Invalid token.';
    else {
        $rating = sanitizeInt($_POST['rating'] ?? 0);
        $title  = sanitize($_POST['title'] ?? '');
        $body   = sanitize($_POST['body'] ?? '');
        if ($rating < 1 || $rating > 5) $reviewError = 'Please select a rating.';
        elseif (strlen($body) < 10) $reviewError = 'Review must be at least 10 characters.';
        else {
            // Check duplicate
            $dup = db()->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
            $dup->execute([$product['id'], $_SESSION['user_id']]);
            if ($dup->fetch()) $reviewError = 'You have already reviewed this product.';
            else {
                db()->prepare("INSERT INTO reviews (product_id, user_id, rating, title, body) VALUES (?,?,?,?,?)")
                    ->execute([$product['id'], $_SESSION['user_id'], $rating, $title, $body]);
                $reviewSuccess = 'Thank you! Your review is pending approval.';
            }
        }
    }
}

$siteName = getSetting('site_name', 'Crumbs & Co');
$price = $product['discount_price'] ?? $product['price'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($product['meta_title'] ?? $product['name']) ?> – <?= htmlspecialchars($siteName) ?></title>
<meta name="description" content="<?= htmlspecialchars($product['meta_description'] ?? $product['short_description'] ?? '') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body data-theme="light">

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:var(--navbar-height)">
  <!-- Breadcrumb -->
  <div style="background:var(--cream-dark);padding:1.5rem 0">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0" style="font-size:.85rem">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/pages/shop.php">Shop</a></li>
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/pages/shop.php?category=<?= urlencode($product['cat_slug']) ?>"><?= htmlspecialchars($product['cat_name']) ?></a></li>
          <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Product Detail -->
  <section class="py-5">
    <div class="container">
      <div class="row g-5">
        <!-- Images -->
        <div class="col-lg-6">
          <div id="mainImage" style="background:var(--cream-dark);border-radius:var(--radius-lg);overflow:hidden;aspect-ratio:1;display:flex;align-items:center;justify-content:center;margin-bottom:1rem">
            <?php if ($product['thumbnail']): ?>
            <img src="<?= getImageUrl($product['thumbnail']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%;height:100%;object-fit:cover" id="mainImg">
            <?php else: ?>
            <i class="fas fa-birthday-cake" style="font-size:6rem;color:var(--border)"></i>
            <?php endif; ?>
          </div>
          <?php if (!empty($imgs)): ?>
          <div class="d-flex gap-2 flex-wrap">
            <?php foreach ($imgs as $img): ?>
            <div style="width:70px;height:70px;border-radius:10px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:.2s"
                 onclick="document.getElementById('mainImg').src='<?= getImageUrl($img['image_path']) ?>';this.style.borderColor='var(--primary)'">
              <img src="<?= getImageUrl($img['image_path']) ?>" style="width:100%;height:100%;object-fit:cover">
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="col-lg-6">
          <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);font-weight:700"><?= htmlspecialchars($product['cat_name']) ?></span>
          <h1 style="font-family:var(--font-display);font-size:2.2rem;margin:.5rem 0 1rem"><?= htmlspecialchars($product['name']) ?></h1>

          <?php if ($ratingData['cnt'] > 0): ?>
          <div class="d-flex align-items-center gap-2 mb-3">
            <div style="color:var(--gold);font-size:1.1rem">
              <?php for ($i=1;$i<=5;$i++) echo $i<=round($ratingData['avg']) ? '★' : '☆'; ?>
            </div>
            <span style="color:var(--text-light);font-size:.875rem"><?= number_format($ratingData['avg'],1) ?> (<?= $ratingData['cnt'] ?> reviews)</span>
          </div>
          <?php endif; ?>

          <div class="mb-4" id="priceWrapper">
            <?php if (empty($productVariants) && $product['discount_price']): ?>
            <span class="price-current" style="font-size:2rem;font-weight:700;color:var(--primary)"><?= formatPrice($product['discount_price']) ?></span>
            <span class="price-original" style="font-size:1.1rem;color:var(--text-light);text-decoration:line-through;margin-left:.5rem"><?= formatPrice($product['price']) ?></span>
            <span class="badge price-badge ms-2" style="background:var(--accent);font-size:.75rem">
              <?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>% OFF
            </span>
            <?php else: ?>
            <span class="price-current" style="font-size:2rem;font-weight:700;color:var(--primary)"><?= formatPrice($product['price']) ?></span>
            <?php endif; ?>
          </div>

          <p style="color:var(--text-medium);line-height:1.8;margin-bottom:1.5rem"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

          <?php if (!empty($productVariants)): ?>
          <div class="mb-4">
            <label class="form-label fw-bold text-uppercase" style="font-size:.75rem;letter-spacing:.05em;color:var(--text-medium)">Select Size / Variant</label>
            <div class="d-flex gap-2 flex-wrap" id="variantSelector">
              <?php foreach ($productVariants as $index => $var): ?>
              <label class="variant-chip-label">
                <input type="radio" name="product_variant" value="<?= $var['id'] ?>" class="variant-radio-input"
                       data-price="<?= $var['price'] ?>" data-sku="<?= htmlspecialchars($var['sku']) ?>" data-stock="<?= $var['stock_quantity'] ?>"
                       <?= $index === 0 ? 'checked' : '' ?>>
                <span class="variant-chip-text"><?= htmlspecialchars($var['variant_name']) ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="d-flex align-items-center gap-3 mb-4">
            <span id="stockBadge" class="badge <?= $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>" style="font-size:.8rem;padding:.4rem .8rem">
              <?= $product['stock_quantity'] > 0 ? '✓ In Stock ('.$product['stock_quantity'].' available)' : '✗ Out of Stock' ?>
            </span>
            <span id="skuWrapper" style="font-size:.8rem;color:var(--text-light)">SKU: <?= htmlspecialchars($product['sku']) ?></span>
          </div>

          <div class="product-action-row d-flex align-items-center gap-3 mb-4" id="qtyInputWrapper" style="<?= ($product['stock_quantity'] > 0 || !empty($productVariants)) ? 'display:flex' : 'display:none' ?>">
            <div class="d-flex align-items-center border rounded" style="border-color:var(--border)!important;border-radius:var(--radius-sm)!important;overflow:hidden">
              <button onclick="this.nextElementSibling.value=Math.max(1,+this.nextElementSibling.value-1)" class="btn px-3 py-2" style="border:none;background:var(--cream-dark)">−</button>
              <input type="number" id="qtyInput" value="1" min="1" max="<?= $product['stock_quantity'] ?>" style="width:60px;text-align:center;border:none;background:transparent;color:var(--text-dark);font-weight:700">
              <button onclick="this.previousElementSibling.value=Math.min(parseInt(this.previousElementSibling.getAttribute('max'))||<?= $product['stock_quantity'] ?>,+this.previousElementSibling.value+1)" class="btn px-3 py-2" style="border:none;background:var(--cream-dark)">+</button>
            </div>
            <button class="btn btn-primary flex-fill ajax-add-cart"
                data-product-id="<?= $product['id'] ?>"
                data-product-name="<?= htmlspecialchars($product['name']) ?>">
              <i class="fas fa-shopping-bag me-2"></i>Add to Cart
            </button>
            <button class="btn-icon btn-wishlist ajax-wishlist" data-product-id="<?= $product['id'] ?>" style="width:48px;height:48px;border:2px solid var(--border);border-radius:var(--radius-sm)">
              <i class="far fa-heart" style="color:var(--text-medium)"></i>
            </button>
          </div>

          <!-- Meta info -->
          <div style="border-top:1px solid var(--border);padding-top:1.5rem">
            <div class="d-flex gap-4">
              <div class="text-center">
                <i class="fas fa-truck" style="color:var(--primary);font-size:1.3rem"></i>
                <div style="font-size:.75rem;color:var(--text-light);margin-top:.25rem">Free delivery<br>over KSh 3,000</div>
              </div>
              <div class="text-center">
                <i class="fas fa-redo" style="color:var(--primary);font-size:1.3rem"></i>
                <div style="font-size:.75rem;color:var(--text-light);margin-top:.25rem">Fresh daily<br>guarantee</div>
              </div>
              <div class="text-center">
                <i class="fas fa-leaf" style="color:var(--primary);font-size:1.3rem"></i>
                <div style="font-size:.75rem;color:var(--text-light);margin-top:.25rem">Natural<br>ingredients</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Reviews -->
  <section class="py-5" style="background:var(--cream-dark)">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-8">
          <h3 style="font-family:var(--font-display);margin-bottom:1.5rem">Customer Reviews</h3>
          <?php if (empty($reviewList)): ?>
          <p style="color:var(--text-light)">No reviews yet. Be the first to review this product!</p>
          <?php else: ?>
          <?php foreach ($reviewList as $r): ?>
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1rem">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <strong><?= htmlspecialchars($r['user_name']) ?></strong>
                <div style="color:var(--gold)">
                  <?php for ($i=1;$i<=5;$i++) echo $i<=$r['rating'] ? '★' : '☆'; ?>
                </div>
              </div>
              <small style="color:var(--text-light)"><?= formatDate($r['created_at']) ?></small>
            </div>
            <?php if ($r['title']): ?><p class="fw-bold mb-1"><?= htmlspecialchars($r['title']) ?></p><?php endif; ?>
            <p style="color:var(--text-medium);margin:0;font-size:.9rem"><?= htmlspecialchars($r['body']) ?></p>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="col-lg-4">
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
            <h5 style="font-family:var(--font-display)">Write a Review</h5>
            <?php if (!isLoggedIn()): ?>
            <p style="color:var(--text-light);font-size:.9rem">Please <a href="<?= APP_URL ?>/pages/login.php">login</a> to submit a review.</p>
            <?php else: ?>
            <?php if ($reviewSuccess): ?><div class="alert alert-success py-2 px-3" style="font-size:.875rem"><?= $reviewSuccess ?></div><?php endif; ?>
            <?php if ($reviewError): ?><div class="alert alert-danger py-2 px-3" style="font-size:.875rem"><?= $reviewError ?></div><?php endif; ?>
            <form method="POST">
              <?= csrfField() ?>
              <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:.85rem">Rating</label>
                <div class="star-picker d-flex gap-2" data-input="ratingInput" style="font-size:1.5rem;cursor:pointer;color:var(--text-light)">
                  <?php for ($i=1;$i<=5;$i++): ?><i class="far fa-star"></i><?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="0">
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:.85rem">Title (optional)</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Amazing cake!">
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:.85rem">Review</label>
                <textarea name="body" class="form-control" rows="4" placeholder="Share your experience..." required></textarea>
              </div>
              <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Review</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Related Products -->
  <?php if (!empty($relatedProds)): ?>
  <section class="py-5">
    <div class="container">
      <h3 style="font-family:var(--font-display);margin-bottom:2rem">You Might Also Like</h3>
      <div class="row g-4">
        <?php foreach ($relatedProds as $rp): ?>
        <div class="col-6 col-md-3">
          <div class="product-card">
            <div class="product-image">
              <?php if ($rp['thumbnail']): ?>
              <img src="<?= getImageUrl($rp['thumbnail']) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy">
              <?php else: ?>
              <div class="product-img-placeholder"><i class="fas fa-birthday-cake"></i></div>
              <?php endif; ?>
            </div>
            <div class="product-info">
              <h6 class="product-name"><a href="<?= APP_URL ?>/pages/product.php?slug=<?= urlencode($rp['slug']) ?>"><?= htmlspecialchars($rp['name']) ?></a></h6>
              <div class="d-flex justify-content-between align-items-center">
                <span class="price-current"><?= formatPrice($rp['discount_price'] ?? $rp['price']) ?></span>
                <button class="btn-add-cart ajax-add-cart" data-product-id="<?= $rp['id'] ?>"><i class="fas fa-plus"></i></button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
<script>
  window.APP_URL = '<?= APP_URL ?>';
  window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const variantRadios = document.querySelectorAll('.variant-radio-input');
    const priceCurrent = document.querySelector('#priceWrapper .price-current');
    const priceOriginal = document.querySelector('#priceWrapper .price-original');
    const priceBadge = document.querySelector('#priceWrapper .price-badge');
    const stockBadge = document.getElementById('stockBadge');
    const skuWrapper = document.getElementById('skuWrapper');
    const qtyInput = document.getElementById('qtyInput');
    const qtyInputWrapper = document.getElementById('qtyInputWrapper');
    const addToCartBtn = document.querySelector('.ajax-add-cart');

    function updateVariantDetails(radio) {
        if (!radio) return;
        const price = parseFloat(radio.dataset.price);
        const sku = radio.dataset.sku;
        const stock = parseInt(radio.dataset.stock);
        const variantId = radio.value;

        // Update price display
        if (priceCurrent) {
            priceCurrent.textContent = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', minimumFractionDigits: 2 }).format(price).replace('KES', 'KSh');
        }
        if (priceOriginal) priceOriginal.style.display = 'none';
        if (priceBadge) priceBadge.style.display = 'none';

        // Update SKU display
        if (skuWrapper) {
            skuWrapper.textContent = 'SKU: ' + sku;
        }

        // Update stock display and quantity input constraints
        if (stockBadge) {
            if (stock > 0) {
                stockBadge.className = 'badge bg-success';
                stockBadge.style.fontSize = '.8rem';
                stockBadge.style.padding = '.4rem .8rem';
                stockBadge.textContent = '✓ In Stock (' + stock + ' available)';
                if (qtyInput) {
                    qtyInput.max = stock;
                    qtyInput.value = Math.min(parseInt(qtyInput.value) || 1, stock);
                }
                if (addToCartBtn) {
                    addToCartBtn.disabled = false;
                    addToCartBtn.dataset.variantId = variantId;
                }
                if (qtyInputWrapper) qtyInputWrapper.style.display = 'flex';
            } else {
                stockBadge.className = 'badge bg-danger';
                stockBadge.style.fontSize = '.8rem';
                stockBadge.style.padding = '.4rem .8rem';
                stockBadge.textContent = '✗ Out of Stock';
                if (qtyInput) {
                    qtyInput.max = 0;
                    qtyInput.value = 0;
                }
                if (addToCartBtn) {
                    addToCartBtn.disabled = true;
                    addToCartBtn.dataset.variantId = variantId;
                }
                if (qtyInputWrapper) qtyInputWrapper.style.display = 'none';
            }
        }
    }

    variantRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            updateVariantDetails(this);
        });
    });

    // Initialize with selected radio (if any)
    const checkedRadio = document.querySelector('.variant-radio-input:checked');
    if (checkedRadio) {
        updateVariantDetails(checkedRadio);
    }
});
</script>
</body></html>
