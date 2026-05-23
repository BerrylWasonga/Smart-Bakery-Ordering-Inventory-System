<?php
// User sidebar – included on dashboard pages
if (!isset($user)) $user = getCurrentUser();
$current = basename($_SERVER['PHP_SELF']);
$navLinks = [
  ['href'=>'dashboard.php','icon'=>'tachometer-alt','label'=>'Dashboard'],
  ['href'=>'orders.php','icon'=>'box','label'=>'My Orders'],
  ['href'=>'wishlist.php','icon'=>'heart','label'=>'Wishlist'],
  ['href'=>'profile.php','icon'=>'user-edit','label'=>'Profile'],
  ['href'=>'addresses.php','icon'=>'map-marker-alt','label'=>'Addresses'],
  ['href'=>'change-password.php','icon'=>'lock','label'=>'Change Password'],
];
?>
<div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
  <div style="background:linear-gradient(135deg,var(--primary),var(--accent));padding:1.5rem;text-align:center;color:white">
    <div style="width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.5rem;font-weight:700">
      <?= strtoupper(substr($user['name'],0,1)) ?>
    </div>
    <div style="font-weight:700;color:white;font-size:.95rem"><?= htmlspecialchars($user['name']) ?></div>
    <small style="opacity:.8;font-size:.8rem"><?= htmlspecialchars($user['email']) ?></small>
  </div>
  <nav class="p-2">
    <?php foreach ($navLinks as $link): ?>
    <a href="<?= APP_URL ?>/pages/<?= $link['href'] ?>"
       class="d-flex align-items-center gap-2 px-3 py-2 rounded mb-1"
       style="text-decoration:none;font-size:.875rem;font-weight:<?= $current===$link['href']?'700':'500' ?>;color:<?= $current===$link['href']?'var(--primary)':'var(--text-medium)' ?>;background:<?= $current===$link['href']?'rgba(139,69,19,.08)':'transparent' ?>">
      <i class="fas fa-<?= $link['icon'] ?>" style="width:16px"></i><?= $link['label'] ?>
    </a>
    <?php endforeach; ?>
    <hr style="border-color:var(--border);margin:.5rem 0">
    <a href="<?= APP_URL ?>/pages/logout.php" class="d-flex align-items-center gap-2 px-3 py-2 rounded" style="text-decoration:none;color:#ef4444;font-size:.875rem;font-weight:500">
      <i class="fas fa-sign-out-alt" style="width:16px"></i>Logout
    </a>
  </nav>
</div>
