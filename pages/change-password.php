<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
$user = getCurrentUser();
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid token.'; }
    else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = db()->prepare("SELECT password FROM users WHERE id=?"); $stmt->execute([$user['id']]); $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash))        $error = 'Current password is incorrect.';
        elseif (strlen($new) < 8)                     $error = 'New password must be at least 8 characters.';
        elseif (!preg_match('/[A-Z]/', $new))          $error = 'Password must contain at least one uppercase letter.';
        elseif (!preg_match('/[0-9]/', $new))          $error = 'Password must contain at least one number.';
        elseif ($new !== $confirm)                     $error = 'Passwords do not match.';
        else {
            db()->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_BCRYPT, ['cost'=>12]), $user['id']]);
            logActivity('user', $user['id'], 'password_change', 'Password changed');
            $success = 'Password changed successfully!';
        }
    }
}
$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change Password – <?= htmlspecialchars($siteName) ?></title>
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
        <h4 style="font-family:var(--font-display);margin-bottom:1.5rem">Change Password</h4>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;max-width:520px">
          <form method="POST">
            <?= csrfField() ?>
            <div class="mb-3"><label class="form-label fw-bold small">Current Password</label>
              <input type="password" class="form-control" name="current_password" required></div>
            <div class="mb-3"><label class="form-label fw-bold small">New Password</label>
              <input type="password" class="form-control" name="new_password" required placeholder="Min 8 chars, 1 uppercase, 1 number"></div>
            <div class="mb-4"><label class="form-label fw-bold small">Confirm New Password</label>
              <input type="password" class="form-control" name="confirm_password" required></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-lock me-2"></i>Change Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
