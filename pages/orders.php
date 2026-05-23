<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
$uid = $_SESSION['user_id'];

$page    = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage = 10;
$total   = (int) db()->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?")->execute([$uid]) ? db()->query("SELECT COUNT(*) FROM orders WHERE user_id = $uid")->fetchColumn() : 0;

$stmt = db()->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT $perPage OFFSET " . (($page-1)*$perPage));
$stmt->execute([$uid]);
$orders = $stmt->fetchAll();

$statusColors = ['pending'=>'warning','processing'=>'info','ready'=>'primary','delivered'=>'success','cancelled'=>'danger'];
$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Orders – <?= htmlspecialchars($siteName) ?></title>
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
      <!-- Sidebar -->
      <div class="col-lg-3">
        <?php include __DIR__ . '/../includes/user-sidebar.php'; ?>
      </div>
      <!-- Content -->
      <div class="col-lg-9">
        <h4 style="font-family:var(--font-display);margin-bottom:1.5rem">My Orders</h4>
        <?php if (empty($orders)): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:3rem;text-align:center">
          <i class="fas fa-box-open" style="font-size:3rem;color:var(--border)"></i>
          <h5 style="margin-top:1rem;color:var(--text-light)">No orders yet</h5>
          <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary mt-2">Shop Now</a>
        </div>
        <?php else: ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
          <?php foreach ($orders as $i => $o): ?>
          <div class="p-4 <?= $i < count($orders)-1 ? 'border-bottom' : '' ?>" style="border-color:var(--border)!important">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <strong style="font-size:.95rem"><?= htmlspecialchars($o['order_number']) ?></strong>
                  <span class="badge bg-<?= $statusColors[$o['order_status']] ?? 'secondary' ?>"><?= ucfirst($o['order_status']) ?></span>
                  <?php if ($o['payment_status'] === 'paid'): ?>
                  <span class="badge bg-success">Paid</span>
                  <?php endif; ?>
                </div>
                <div style="font-size:.8rem;color:var(--text-light)">
                  <i class="fas fa-calendar-alt me-1"></i><?= formatDate($o['created_at']) ?> &nbsp;|&nbsp;
                  <i class="fas fa-credit-card me-1"></i><?= strtoupper($o['payment_method']) ?>
                </div>
              </div>
              <div class="text-end">
                <div style="font-size:1.1rem;font-weight:700;color:var(--primary)"><?= formatPrice($o['total_amount']) ?></div>
                <a href="<?= APP_URL ?>/pages/order-detail.php?id=<?= $o['id'] ?>" class="btn btn-outline-primary btn-sm mt-1">View Details</a>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
