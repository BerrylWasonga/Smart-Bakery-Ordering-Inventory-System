<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$siteName = getSetting('site_name', 'Crumbs & Co');
$categorySlug = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'latest');
$page = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage = 12;

// Build query
$where = ["p.status = 'active'"];
$params = [];

if ($categorySlug) {
    $catStmt = db()->prepare("SELECT id, name FROM categories WHERE slug = ?");
    $catStmt->execute([$categorySlug]);
    $currentCat = $catStmt->fetch();
    if ($currentCat) {
        $where[] = "p.category_id = ?";
        $params[] = $currentCat['id'];
    }
}

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = implode(' AND ', $where);
$orderSQL = match($sort) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'popular' => 'p.views DESC',
    default => 'p.created_at DESC',
};

// Total count
$countStmt = db()->prepare("SELECT COUNT(*) FROM products p WHERE $whereSQL");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pag = paginate($total, $perPage, $page);

// Products
$prodStmt = db()->prepare("SELECT p.*, c.name AS cat_name FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE $whereSQL ORDER BY $orderSQL LIMIT {$perPage} OFFSET {$pag['offset']}");
$prodStmt->execute($params);
$products = $prodStmt->fetchAll();

// All categories for filter
$categories = db()->query("SELECT * FROM categories WHERE status=1 ORDER BY sort_order")->fetchAll();
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Shop – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<!-- favicon -->
<link rel="icon" type="image/jpeg" href="../assets/images/Favicon2.jpg"> 
</head>
<body data-theme="light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bakery-navbar fixed-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>">
      <div class="brand-icon"><i class="fas fa-bread-slice"></i></div>
      <span class="brand-name"><?= htmlspecialchars($siteName) ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="<?= APP_URL ?>/pages/shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/contact.php">Contact</a></li>
      </ul>
      <div class="navbar-actions d-flex align-items-center gap-3">
        <button class="theme-toggle btn-icon" id="themeToggle"><i class="fas fa-moon" id="themeIcon"></i></button>
        <a href="<?= APP_URL ?>/pages/cart.php" class="btn-icon position-relative">
          <i class="fas fa-shopping-bag"></i>
          <?php if ($cartCount > 0): ?><span class="cart-badge" id="cartBadge"><?= $cartCount ?></span><?php endif; ?>
        </a>
        <?php if (isLoggedIn()): $u = getCurrentUser(); ?>
        <div class="dropdown">
          <button class="btn-icon" data-bs-toggle="dropdown"><i class="fas fa-user-circle"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="px-3 py-2"><strong><?= htmlspecialchars($u['name']) ?></strong></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </div>
        <?php else: ?>
        <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-outline-primary btn-sm">Login</a>
        <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-primary btn-sm">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div style="background:var(--cream-dark);padding:6rem 0 3rem;margin-top:var(--navbar-height)">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
        <li class="breadcrumb-item active">Shop</li>
        <?php if (!empty($currentCat)): ?>
        <li class="breadcrumb-item active"><?= htmlspecialchars($currentCat['name']) ?></li>
        <?php endif; ?>
      </ol>
    </nav>
    <h1 style="font-family:var(--font-display);font-size:2.5rem">
      <?= !empty($currentCat) ? htmlspecialchars($currentCat['name']) : ($search ? 'Search: "'.htmlspecialchars($search).'"' : 'Our Products') ?>
    </h1>
    <p style="color:var(--text-light)"><?= $total ?> products found</p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">

    <!-- Sidebar Filters -->
    <div class="col-lg-3">
      <div class="card border-0" style="background:var(--warm-white);border:1px solid var(--border)!important;border-radius:var(--radius)">
        <div class="card-body p-4">
          <!-- Search -->
          <form method="GET" class="mb-4">
            <label class="fw-bold mb-2 d-block" style="font-size:.85rem;color:var(--text-medium)">Search Products</label>
            <div class="input-group">
              <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. chocolate cake">
              <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
          </form>

          <!-- Categories -->
          <div class="mb-4">
            <h6 class="fw-bold mb-3" style="font-family:var(--font-display)">Categories</h6>
            <ul class="list-unstyled m-0">
              <li class="mb-1">
                <a href="<?= APP_URL ?>/pages/shop.php" class="d-flex justify-content-between align-items-center py-1 <?= !$categorySlug ? 'fw-bold' : '' ?>" style="color:var(--text-medium);text-decoration:none;font-size:.9rem">
                  All Products
                </a>
              </li>
              <?php foreach ($categories as $cat): ?>
              <li class="mb-1">
                <a href="<?= APP_URL ?>/pages/shop.php?category=<?= urlencode($cat['slug']) ?>"
                   class="d-flex justify-content-between align-items-center py-1 <?= $categorySlug === $cat['slug'] ? 'fw-bold' : '' ?>"
                   style="color:<?= $categorySlug === $cat['slug'] ? 'var(--primary)' : 'var(--text-medium)' ?>;text-decoration:none;font-size:.9rem">
                  <?= htmlspecialchars($cat['name']) ?>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Sort -->
          <div>
            <h6 class="fw-bold mb-3" style="font-family:var(--font-display)">Sort By</h6>
            <?php
            $sorts = ['latest' => 'Latest', 'popular' => 'Most Popular', 'price_asc' => 'Price: Low to High', 'price_desc' => 'Price: High to Low'];
            foreach ($sorts as $val => $label): ?>
            <div class="form-check mb-1">
              <input class="form-check-input" type="radio" name="sort_radio" id="sort_<?= $val ?>"
                     onchange="location='<?= APP_URL ?>/pages/shop.php?<?= http_build_query(array_merge($_GET, ['sort'=>$val, 'page'=>1])) ?>'"
                     <?= $sort === $val ? 'checked' : '' ?>>
              <label class="form-check-label" for="sort_<?= $val ?>" style="font-size:.875rem;cursor:pointer"><?= $label ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="col-lg-9">
      <?php if (empty($products)): ?>
      <div class="text-center py-5">
        <i class="fas fa-search" style="font-size:3rem;color:var(--border)"></i>
        <h5 class="mt-3" style="color:var(--text-light)">No products found</h5>
        <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary mt-3">View All Products</a>
      </div>
      <?php else: ?>
      <div class="row g-4">
        <?php foreach ($products as $product): ?>
        <div class="col-6 col-md-4">
          <div class="product-card">
            <?php if ($product['discount_price']): ?><span class="product-badge badge-sale">Sale</span><?php endif; ?>
            <div class="product-image">
              <?php if ($product['thumbnail']): ?>
              <img src="<?= getImageUrl($product['thumbnail']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
              <?php else: ?>
              <div class="product-img-placeholder"><i class="fas fa-birthday-cake"></i></div>
              <?php endif; ?>
              <div class="product-overlay">
                <button class="btn-quick-view" data-product-id="<?= $product['id'] ?>"><i class="fas fa-eye"></i></button>
                <button class="btn-wishlist ajax-wishlist" data-product-id="<?= $product['id'] ?>"><i class="far fa-heart"></i></button>
              </div>
            </div>
            <div class="product-info">
              <span class="product-category"><?= htmlspecialchars($product['cat_name']) ?></span>
              <h6 class="product-name">
                <a href="<?= APP_URL ?>/product/<?= urlencode($product['slug']) ?>"><?= htmlspecialchars($product['name']) ?></a>
              </h6>
              <div class="product-footer d-flex justify-content-between align-items-center">
                <div class="product-price">
                  <?php if ($product['discount_price']): ?>
                  <span class="price-current"><?= formatPrice($product['discount_price']) ?></span>
                  <span class="price-original"><?= formatPrice($product['price']) ?></span>
                  <?php else: ?>
                  <span class="price-current"><?= formatPrice($product['price']) ?></span>
                  <?php endif; ?>
                </div>
                <button class="btn-add-cart ajax-add-cart" data-product-id="<?= $product['id'] ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>" <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pag['total_pages'] > 1): ?>
      <nav class="mt-5 d-flex justify-content-center">
        <ul class="pagination gap-1">
          <?php if ($pag['has_prev']): ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pag['current_page']-1])) ?>" style="border-radius:var(--radius-sm)">‹</a>
          </li>
          <?php endif; ?>
          <?php for ($i=1; $i<=$pag['total_pages']; $i++): ?>
          <li class="page-item <?= $i === $pag['current_page'] ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" style="border-radius:var(--radius-sm)"><?= $i ?></a>
          </li>
          <?php endfor; ?>
          <?php if ($pag['has_next']): ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pag['current_page']+1])) ?>" style="border-radius:var(--radius-sm)">›</a>
          </li>
          <?php endif; ?>
        </ul>
      </nav>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="quickViewContent"><div class="text-center py-5"><div class="spinner-border" style="color:var(--primary)"></div></div></div>
    </div>
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
