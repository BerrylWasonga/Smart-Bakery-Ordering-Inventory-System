<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$token = sanitize($_GET['token'] ?? '');
$email = sanitizeEmail($_GET['email'] ?? '');
$success = ''; $error = '';

// Validate token
$user = null;
if ($token && $email) {
    $stmt = db()->prepare("SELECT * FROM users WHERE email=? AND reset_token=? AND reset_token_expires > NOW() AND status='active'");
    $stmt->execute([$email, hash('sha256', $token)]);
    $user = $stmt->fetch();
}

if (!$user && !$success) {
    $error = 'This reset link is invalid or has expired. Please request a new one.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid token.'; }
    else {
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($new) < 8)                  $error = 'Password must be at least 8 characters.';
        elseif (!preg_match('/[A-Z]/', $new))   $error = 'Must contain at least one uppercase letter.';
        elseif (!preg_match('/[0-9]/', $new))   $error = 'Must contain at least one number.';
        elseif ($new !== $confirm)              $error = 'Passwords do not match.';
        else {
            db()->prepare("UPDATE users SET password=?,reset_token=NULL,reset_token_expires=NULL WHERE id=?")
                ->execute([password_hash($new, PASSWORD_BCRYPT, ['cost'=>12]), $user['id']]);
            $success = 'Password reset successfully! You can now sign in.';
        }
    }
}
$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<style>.auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--cream);padding:2rem}</style>
</head>
<body data-theme="light">
<div class="auth-page">
  <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2.5rem;width:100%;max-width:430px;box-shadow:var(--shadow)">
    <div class="text-center mb-4">
      <div class="brand-icon" style="width:56px;height:56px;margin:0 auto 1rem;font-size:1.4rem"><i class="fas fa-bread-slice"></i></div>
      <h4 style="font-family:var(--font-display)">Reset Password</h4>
    </div>
    <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
    <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-primary w-100">Sign In Now</a>
    <?php elseif ($error && !$user): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <a href="<?= APP_URL ?>/pages/forgot-password.php" class="btn btn-primary w-100">Request New Link</a>
    <?php else: ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="mb-3"><label class="form-label fw-bold small">New Password</label>
        <input type="password" class="form-control" name="new_password" required placeholder="Min 8 chars, 1 uppercase, 1 number"></div>
      <div class="mb-4"><label class="form-label fw-bold small">Confirm Password</label>
        <input type="password" class="form-control" name="confirm_password" required></div>
      <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-lock me-2"></i>Reset Password</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
