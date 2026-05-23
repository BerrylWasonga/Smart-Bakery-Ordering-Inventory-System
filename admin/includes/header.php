<?php
// Admin header – included at top of every admin page AFTER requireAdmin()
$admin   = getCurrentAdmin();
$pgTitle = $adminPageTitle ?? 'Dashboard';

// Unread notifications count
$unreadMsg = (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
$lowStock  = (int) db()->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold AND status='active'")->fetchColumn();

$adminNav = [
  ['section'=>'Main'],
  ['href'=>'dashboard.php','icon'=>'tachometer-alt','label'=>'Dashboard'],
  ['section'=>'Catalogue'],
  ['href'=>'products.php','icon'=>'birthday-cake','label'=>'Products'],
  ['href'=>'categories.php','icon'=>'tags','label'=>'Categories'],
  ['section'=>'Sales'],
  ['href'=>'orders.php','icon'=>'box','label'=>'Orders'],
  ['href'=>'coupons.php','icon'=>'ticket-alt','label'=>'Coupons'],
  ['section'=>'Content'],
  ['href'=>'blog.php','icon'=>'pen-nib','label'=>'Blog Posts'],
  ['href'=>'banners.php','icon'=>'images','label'=>'Banners'],
  ['href'=>'testimonials.php','icon'=>'star','label'=>'Testimonials'],
  ['section'=>'Users'],
  ['href'=>'customers.php','icon'=>'users','label'=>'Customers'],
  ['href'=>'reviews.php','icon'=>'comments','label'=>'Reviews'],
  ['href'=>'messages.php','icon'=>'envelope','label'=>'Messages','badge'=>$unreadMsg],
  ['section'=>'System'],
  ['href'=>'inventory.php','icon'=>'warehouse','label'=>'Inventory','badge'=>$lowStock,'badge_type'=>'warning'],
  ['href'=>'newsletter.php','icon'=>'mail-bulk','label'=>'Newsletter'],
  ['href'=>'settings.php','icon'=>'cog','label'=>'Settings'],
  ['href'=>'reports.php','icon'=>'chart-bar','label'=>'Reports'],
];

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pgTitle) ?> – Admin | <?= htmlspecialchars(getSetting('site_name','Crumbs & Co')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<style>
.admin-sidebar{overflow-y:auto;display:flex;flex-direction:column}
.sidebar-bottom{margin-top:auto;padding:1rem;border-top:1px solid rgba(255,255,255,.1)}
.admin-content{padding:2rem}
@media(max-width:992px){.admin-content{padding:1rem}}
.table th{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700;border-color:var(--border)}
.table td{border-color:var(--border);vertical-align:middle}
.table-hover tbody tr:hover{background:rgba(139,69,19,.03)}
.page-header{margin-bottom:1.5rem}
.page-header h4{font-family:var(--font-display);margin:0}
</style>
</head>
<body data-theme="light">
<script>
  window.APP_URL = '<?= APP_URL ?>';
  window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
  <!-- Logo -->
  <div class="sidebar-logo">
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
      <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:.9rem">
        <i class="fas fa-bread-slice"></i>
      </div>
      <div style="color:white">
        <div style="font-family:var(--font-display);font-weight:700;font-size:.95rem;line-height:1.2"><?= htmlspecialchars(getSetting('site_name','Crumbs & Co')) ?></div>
        <div style="font-size:.7rem;opacity:.5;text-transform:uppercase;letter-spacing:.1em">Admin Panel</div>
      </div>
    </a>
  </div>

  <!-- Nav -->
  <div class="sidebar-menu flex-fill" style="overflow-y:auto">
    <?php foreach ($adminNav as $item):
      if (isset($item['section'])): ?>
      <div class="sidebar-group-label mt-2"><?= $item['section'] ?></div>
      <?php else: ?>
      <a href="<?= APP_URL ?>/admin/<?= $item['href'] ?>" class="<?= $current===$item['href']?'active':'' ?>">
        <i class="fas fa-<?= $item['icon'] ?>"></i>
        <span style="flex:1"><?= $item['label'] ?></span>
        <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
        <span class="badge" style="background:<?= ($item['badge_type']??'')===('warning')?'var(--gold)':'#ef4444' ?>;font-size:.65rem"><?= $item['badge'] ?></span>
        <?php endif; ?>
      </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- Bottom -->
  <div class="sidebar-bottom">
    <a href="<?= APP_URL ?>" target="_blank" class="d-flex align-items-center gap-2 mb-2" style="color:rgba(255,255,255,.5);font-size:.8rem;text-decoration:none"><i class="fas fa-external-link-alt" style="width:14px"></i>View Website</a>
    <a href="<?= APP_URL ?>/admin/logout.php" class="d-flex align-items-center gap-2" style="color:rgba(239,68,68,.8);font-size:.8rem;text-decoration:none"><i class="fas fa-sign-out-alt" style="width:14px"></i>Logout</a>
  </div>
</aside>

<!-- Main -->
<div class="admin-main" id="adminMain">
  <!-- Topbar -->
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn-icon d-lg-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
      <h6 style="margin:0;font-family:var(--font-display);font-size:.95rem"><?= htmlspecialchars($pgTitle) ?></h6>
    </div>
    <div class="d-flex align-items-center gap-3">
      <button class="btn-icon" id="themeToggle" title="Toggle theme"><i class="fas fa-moon" id="themeIcon"></i></button>
      <?php if ($lowStock > 0): ?>
      <a href="<?= APP_URL ?>/admin/inventory.php" class="btn-icon position-relative" title="<?= $lowStock ?> low stock alerts">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i>
        <span style="position:absolute;top:-4px;right:-4px;background:#f59e0b;color:white;font-size:.6rem;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center"><?= $lowStock ?></span>
      </a>
      <?php endif; ?>
      <div class="dropdown">
        <button class="d-flex align-items-center gap-2 btn-icon px-2" style="border-radius:var(--radius-sm);width:auto" data-bs-toggle="dropdown">
          <div style="width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:.85rem">
            <?= strtoupper(substr($admin['name'],0,1)) ?>
          </div>
          <span style="font-size:.875rem;font-weight:600;color:var(--text-dark)"><?= htmlspecialchars(explode(' ',$admin['name'])[0]) ?></span>
          <i class="fas fa-chevron-down" style="font-size:.7rem;color:var(--text-light)"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li class="px-3 py-2">
            <div style="font-weight:700;font-size:.875rem"><?= htmlspecialchars($admin['name']) ?></div>
            <small class="text-muted"><?= ucfirst($admin['role']) ?></small>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
          <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Page Content starts here (closed in footer.php) -->
  <div class="admin-content">
