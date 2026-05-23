<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid token.'; }
    else {
        $email = sanitizeEmail($_POST['email'] ?? '');
        if (!validateEmail($email)) { $error = 'Please enter a valid email address.'; }
        else {
            $stmt = db()->prepare("SELECT id,name FROM users WHERE email=? AND status='active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            // Always show success to prevent email enumeration
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                db()->prepare("UPDATE users SET reset_token=?,reset_token_expires=? WHERE id=?")->execute([hash('sha256',$token),$expires,$user['id']]);
                $resetLink = APP_URL . '/pages/reset-password.php?token=' . $token . '&email=' . urlencode($email);
                // In production: send email via PHPMailer with $resetLink
                // For demo, show the link
                $success = 'Password reset instructions sent! <br><small style="opacity:.8">Demo link: <a href="'.$resetLink.'" style="color:inherit">'.$resetLink.'</a></small>';
            } else {
                $success = 'If that email is registered, you\'ll receive reset instructions shortly.';
            }
        }
    }
}
$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password – <?= htmlspecialchars($siteName) ?></title>
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
      <h4 style="font-family:var(--font-display)">Forgot Password?</h4>
      <p style="color:var(--text-light);font-size:.875rem">Enter your email and we'll send reset instructions.</p>
    </div>
    <?php if ($success): ?>
    <div class="alert alert-success" style="font-size:.875rem;border-radius:var(--radius-sm)"><?= $success ?></div>
    <?php else: ?>
    <?php if ($error): ?><div class="alert alert-danger" style="font-size:.875rem"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="mb-4"><label class="form-label fw-bold small">Email Address</label>
        <div class="input-group">
          <span class="input-group-text" style="background:var(--cream-dark);border-color:var(--border)"><i class="fas fa-envelope" style="color:var(--text-light)"></i></span>
          <input type="email" class="form-control" name="email" placeholder="your@email.com" required autofocus>
        </div></div>
      <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-paper-plane me-2"></i>Send Reset Link</button>
    </form>
    <?php endif; ?>
    <p class="text-center mt-3 mb-0" style="font-size:.875rem;color:var(--text-light)">
      Remember it? <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--primary);font-weight:700">Sign In</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
