<?php
// Shared navbar – included on inner pages
$siteName  = getSetting('site_name', 'Crumbs & Co');
$cartCount = getCartCount();
?>
<script>
  window.APP_URL = '<?= APP_URL ?>';
  window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<nav class="navbar navbar-expand-lg bakery-navbar fixed-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>">
      <div class="brand-icon"><i class="fas fa-bread-slice"></i></div>
      <span class="brand-name"><?= htmlspecialchars($siteName) ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/about.php">About</a></li>
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
            <li class="px-3 py-2"><strong><?= htmlspecialchars($u['name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($u['email'] ?? '') ?></small></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
            <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
            <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </div>
        <?php else: ?>
        <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-outline-primary btn-sm nav-btn">Login</a>
        <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-primary btn-sm nav-btn">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
