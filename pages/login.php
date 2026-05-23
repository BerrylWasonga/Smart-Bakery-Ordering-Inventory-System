<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (isLoggedIn()) { redirect(APP_URL . '/pages/dashboard.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid request. Please try again.'; }
    else {
        $email    = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (!$email || !$password) { $error = 'Please fill in all fields.'; }
        else {
            $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    db()->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([hash('sha256', $token), $user['id']]);
                    setcookie('remember_token', $token, time() + REMEMBER_ME_LIFETIME, '/', '', false, true);
                }
                logActivity('user', $user['id'], 'login', 'User logged in');

                $redirect = $_SESSION['redirect_after_login'] ?? (APP_URL . '/pages/dashboard.php');
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                $error = 'Invalid email or password.';
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
<title>Login – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<style>
.auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--cream);padding:2rem}
.auth-card{background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2.5rem;width:100%;max-width:440px;box-shadow:var(--shadow)}
.auth-logo{text-align:center;margin-bottom:2rem}
.auth-logo .brand-icon{width:56px;height:56px;margin:0 auto 1rem;font-size:1.4rem}
</style>
</head>
<body data-theme="light">
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="brand-icon"><i class="fas fa-bread-slice"></i></div>
      <h4 style="font-family:var(--font-display)"><?= htmlspecialchars($siteName) ?></h4>
      <p style="color:var(--text-light);font-size:.9rem">Welcome back! Sign in to continue.</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.875rem;border-radius:var(--radius-sm)">
      <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label fw-bold" style="font-size:.85rem">Email Address</label>
        <div class="input-group">
          <span class="input-group-text" style="background:var(--cream-dark);border-color:var(--border)"><i class="fas fa-envelope" style="color:var(--text-light)"></i></span>
          <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="you@example.com" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold" style="font-size:.85rem">Password</label>
        <div class="input-group">
          <span class="input-group-text" style="background:var(--cream-dark);border-color:var(--border)"><i class="fas fa-lock" style="color:var(--text-light)"></i></span>
          <input type="password" class="form-control" name="password" id="pwdInput" placeholder="Your password" required>
          <button type="button" class="input-group-text" style="cursor:pointer;background:var(--cream-dark);border-color:var(--border)" onclick="const i=document.getElementById('pwdInput');i.type=i.type==='password'?'text':'password'">
            <i class="fas fa-eye" style="color:var(--text-light)"></i>
          </button>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="remember" id="remember">
          <label class="form-check-label" for="remember" style="font-size:.875rem">Remember me</label>
        </div>
        <a href="<?= APP_URL ?>/pages/forgot-password.php" style="font-size:.875rem;color:var(--primary)">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="fas fa-sign-in-alt me-2"></i>Sign In
      </button>
    </form>

    <p class="text-center mt-3 mb-0" style="font-size:.875rem;color:var(--text-light)">
      Don't have an account? <a href="<?= APP_URL ?>/pages/register.php" style="color:var(--primary);font-weight:700">Sign Up</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
