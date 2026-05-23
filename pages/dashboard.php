<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$user = getCurrentUser();
$uid  = $user['id'];

// Stats
$totalOrders  = db()->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?"); $totalOrders->execute([$uid]);
$pendingOrders= db()->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND order_status IN ('pending','processing')"); $pendingOrders->execute([$uid]);
$totalSpent   = db()->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id = ? AND payment_status='paid'"); $totalSpent->execute([$uid]);
$wishlistCount= db()->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?"); $wishlistCount->execute([$uid]);

// Recent orders
$recentOrders = db()->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5"); $recentOrders->execute([$uid]);
$orders = $recentOrders->fetchAll();

// Notifications
$notifs = db()->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5"); $notifs->execute([$uid]);
$notifications = $notifs->fetchAll();

$siteName = getSetting('site_name', 'Crumbs & Co');
$statusColors = ['pending'=>'warning','processing'=>'info','ready'=>'primary','delivered'=>'success','cancelled'=>'danger'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard – <?= htmlspecialchars($siteName) ?></title>
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
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
          <div style="background:linear-gradient(135deg,var(--primary),var(--accent));padding:2rem;text-align:center;color:white">
            <div style="width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.8rem">
              <?= strtoupper(substr($user['name'],0,1)) ?>
            </div>
            <h6 style="margin:0;color:white;font-family:var(--font-display)"><?= htmlspecialchars($user['name']) ?></h6>
            <small style="opacity:.8"><?= htmlspecialchars($user['email']) ?></small>
          </div>
          <nav class="p-2">
            <?php
            $navLinks = [
              ['href'=>'dashboard.php','icon'=>'tachometer-alt','label'=>'Dashboard'],
              ['href'=>'orders.php','icon'=>'box','label'=>'My Orders'],
              ['href'=>'wishlist.php','icon'=>'heart','label'=>'Wishlist'],
              ['href'=>'profile.php','icon'=>'user-edit','label'=>'Profile'],
              ['href'=>'addresses.php','icon'=>'map-marker-alt','label'=>'Addresses'],
              ['href'=>'change-password.php','icon'=>'lock','label'=>'Change Password'],
            ];
            $current = basename($_SERVER['PHP_SELF']);
            foreach ($navLinks as $link): ?>
            <a href="<?= APP_URL ?>/pages/<?= $link['href'] ?>"
               class="d-flex align-items-center gap-2 px-3 py-2 rounded mb-1"
               style="text-decoration:none;color:<?= $current===$link['href'] ? 'var(--primary)' : 'var(--text-medium)' ?>;background:<?= $current===$link['href'] ? 'rgba(139,69,19,.08)' : 'transparent' ?>;font-size:.875rem;font-weight:<?= $current===$link['href'] ? '700' : '500' ?>">
              <i class="fas fa-<?= $link['icon'] ?>" style="width:16px"></i><?= $link['label'] ?>
            </a>
            <?php endforeach; ?>
            <hr style="border-color:var(--border)">
            <a href="<?= APP_URL ?>/pages/logout.php" class="d-flex align-items-center gap-2 px-3 py-2 rounded" style="text-decoration:none;color:#ef4444;font-size:.875rem;font-weight:500">
              <i class="fas fa-sign-out-alt" style="width:16px"></i>Logout
            </a>
          </nav>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">
        <h4 style="font-family:var(--font-display);margin-bottom:1.5rem">Welcome back, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>! 👋</h4>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <?php
          $stats = [
            ['label'=>'Total Orders','value'=>$totalOrders->fetchColumn(),'icon'=>'box','color'=>'var(--primary)'],
            ['label'=>'Pending Orders','value'=>$pendingOrders->fetchColumn(),'icon'=>'clock','color'=>'#f59e0b'],
            ['label'=>'Total Spent','value'=>formatPrice($totalSpent->fetchColumn()),'icon'=>'wallet','color'=>'#22c55e'],
            ['label'=>'Wishlist Items','value'=>$wishlistCount->fetchColumn(),'icon'=>'heart','color'=>'#ef4444'],
          ];
          foreach ($stats as $s): ?>
          <div class="col-6 col-md-3">
            <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;text-align:center">
              <div style="width:44px;height:44px;background:<?= $s['color'] ?>1a;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem">
                <i class="fas fa-<?= $s['icon'] ?>" style="color:<?= $s['color'] ?>"></i>
              </div>
              <div style="font-size:1.3rem;font-weight:700;font-family:var(--font-display);color:var(--primary)"><?= $s['value'] ?></div>
              <div style="font-size:.75rem;color:var(--text-light)"><?= $s['label'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Notifications -->
        <?php if (!empty($notifications)): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem">
          <h6 style="font-family:var(--font-display);margin-bottom:1rem"><i class="fas fa-bell me-2" style="color:var(--primary)"></i>Notifications</h6>
          <?php foreach ($notifications as $n): ?>
          <div class="d-flex gap-3 align-items-start mb-2 pb-2" style="border-bottom:1px solid var(--border)">
            <div style="width:36px;height:36px;background:rgba(139,69,19,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-info" style="color:var(--primary);font-size:.8rem"></i>
            </div>
            <div class="flex-fill">
              <div style="font-size:.875rem;font-weight:600"><?= htmlspecialchars($n['title']) ?></div>
              <div style="font-size:.8rem;color:var(--text-light)"><?= htmlspecialchars($n['message']) ?></div>
            </div>
            <small style="color:var(--text-light);font-size:.75rem;white-space:nowrap"><?= timeAgo($n['created_at']) ?></small>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Recent Orders -->
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
          <div class="d-flex justify-content-between align-items-center mb-1rem" style="margin-bottom:1rem">
            <h6 style="font-family:var(--font-display);margin:0"><i class="fas fa-box me-2" style="color:var(--primary)"></i>Recent Orders</h6>
            <a href="<?= APP_URL ?>/pages/orders.php" style="font-size:.8rem;color:var(--primary)">View All</a>
          </div>
          <?php if (empty($orders)): ?>
          <div class="text-center py-4">
            <i class="fas fa-box-open" style="font-size:2.5rem;color:var(--border)"></i>
            <p style="color:var(--text-light);margin-top:.75rem;font-size:.9rem">No orders yet.</p>
            <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary btn-sm">Start Shopping</a>
          </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table" style="font-size:.875rem">
              <thead><tr style="color:var(--text-light);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em">
                <th style="font-weight:700;border-color:var(--border)">Order #</th>
                <th style="font-weight:700;border-color:var(--border)">Date</th>
                <th style="font-weight:700;border-color:var(--border)">Total</th>
                <th style="font-weight:700;border-color:var(--border)">Status</th>
                <th style="font-weight:700;border-color:var(--border)"></th>
              </tr></thead>
              <tbody>
              <?php foreach ($orders as $o): ?>
              <tr>
                <td style="border-color:var(--border);font-weight:600"><?= htmlspecialchars($o['order_number']) ?></td>
                <td style="border-color:var(--border);color:var(--text-light)"><?= formatDate($o['created_at']) ?></td>
                <td style="border-color:var(--border);font-weight:700;color:var(--primary)"><?= formatPrice($o['total_amount']) ?></td>
                <td style="border-color:var(--border)">
                  <span class="badge bg-<?= $statusColors[$o['order_status']] ?? 'secondary' ?>"><?= ucfirst($o['order_status']) ?></span>
                </td>
                <td style="border-color:var(--border)">
                  <a href="<?= APP_URL ?>/pages/order-detail.php?id=<?= $o['id'] ?>" style="font-size:.8rem;color:var(--primary)">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
