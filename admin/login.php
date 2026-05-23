<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (isAdminLoggedIn()) redirect(APP_URL . '/admin/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid request.'; }
    else {
        $email    = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) { $error = 'Please enter your credentials.'; }
        else {
            $stmt = db()->prepare("SELECT * FROM admins WHERE email = ? AND status = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];
                db()->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
                logActivity('admin', $admin['id'], 'login', 'Admin logged in');
                redirect(APP_URL . '/admin/dashboard.php');
            } else {
                $error = 'Invalid email or password.';
                // Rate limiting could be added here
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
<title>Admin Login – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<style>
body{background:var(--text-dark)!important}
.admin-login-card{background:var(--warm-white);border-radius:var(--radius-lg);padding:2.5rem;width:100%;max-width:420px;box-shadow:var(--shadow-lg)}
</style>
</head>
<body data-theme="light" style="min-height:100vh;display:flex;align-items:center;justify-content:center">
<div class="admin-login-card">
  <div class="text-center mb-4">
    <div style="width:60px;height:60px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.4rem;color:white">
      <i class="fas fa-shield-alt"></i>
    </div>
    <h4 style="font-family:var(--font-display)">Admin Panel</h4>
    <p style="color:var(--text-light);font-size:.875rem"><?= htmlspecialchars($siteName) ?></p>
  </div>
  <?php if ($error): ?>
  <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.875rem;border-radius:var(--radius-sm)">
    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>
  <form method="POST">
    <?= csrfField() ?>
    <div class="mb-3">
      <label class="form-label fw-bold small">Email Address</label>
      <div class="input-group">
        <span class="input-group-text" style="background:var(--cream-dark);border-color:var(--border)"><i class="fas fa-envelope" style="color:var(--text-light)"></i></span>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" placeholder="admin@bakery.com" required autofocus>
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label fw-bold small">Password</label>
      <div class="input-group">
        <span class="input-group-text" style="background:var(--cream-dark);border-color:var(--border)"><i class="fas fa-lock" style="color:var(--text-light)"></i></span>
        <input type="password" class="form-control" name="password" id="adminPwd" placeholder="Admin password" required>
        <button type="button" class="input-group-text" style="cursor:pointer;background:var(--cream-dark);border-color:var(--border)" onclick="const i=document.getElementById('adminPwd');i.type=i.type==='password'?'text':'password'">
          <i class="fas fa-eye" style="color:var(--text-light)"></i>
        </button>
      </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-sign-in-alt me-2"></i>Sign In to Admin</button>
  </form>
  <div class="text-center mt-3">
    <a href="<?= APP_URL ?>" style="font-size:.8rem;color:var(--text-light)"><i class="fas fa-arrow-left me-1"></i>Back to Website</a>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
