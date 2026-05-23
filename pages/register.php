<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid request.'; }
    else {
        $name     = sanitize($_POST['name'] ?? '');
        $email    = sanitizeEmail($_POST['email'] ?? '');
        $phone    = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (strlen($name) < 2)          $errors[] = 'Name must be at least 2 characters.';
        if (!validateEmail($email))      $errors[] = 'Please enter a valid email address.';
        if (strlen($password) < 8)       $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)      $errors[] = 'Passwords do not match.';
        if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter.';
        if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number.';

        if (empty($errors)) {
            $check = db()->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) { $errors[] = 'An account with this email already exists.'; }
            else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $token = bin2hex(random_bytes(32));
                db()->prepare("INSERT INTO users (name, email, phone, password, verification_token) VALUES (?,?,?,?,?)")
                    ->execute([$name, $email, $phone, $hash, $token]);
                $userId = db()->lastInsertId();
                $_SESSION['user_id']   = $userId;
                $_SESSION['user_name'] = $name;
                logActivity('user', $userId, 'register', 'New user registered');
                redirect(APP_URL . '/pages/dashboard.php');
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
<title>Sign Up – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<style>
.auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--cream);padding:2rem}
.auth-card{background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2.5rem;width:100%;max-width:480px;box-shadow:var(--shadow)}
</style>
</head>
<body data-theme="light">
<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div class="brand-icon" style="width:56px;height:56px;margin:0 auto 1rem;font-size:1.4rem"><i class="fas fa-bread-slice"></i></div>
      <h4 style="font-family:var(--font-display)">Create Account</h4>
      <p style="color:var(--text-light);font-size:.9rem">Join <?= htmlspecialchars($siteName) ?> today!</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.875rem;border-radius:var(--radius-sm)">
      <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST">
      <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label fw-bold" style="font-size:.85rem">Full Name</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Jane Doe" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold" style="font-size:.85rem">Email Address</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="jane@example.com" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold" style="font-size:.85rem">Phone Number</label>
        <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+254 7XX XXX XXX">
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold" style="font-size:.85rem">Password</label>
        <div class="input-group">
          <input type="password" class="form-control" name="password" id="pwd1" placeholder="Min 8 chars, 1 uppercase, 1 number" required>
          <button type="button" class="input-group-text" style="cursor:pointer;background:var(--cream-dark);border-color:var(--border)" onclick="const i=document.getElementById('pwd1');i.type=i.type==='password'?'text':'password'"><i class="fas fa-eye" style="color:var(--text-light)"></i></button>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-bold" style="font-size:.85rem">Confirm Password</label>
        <input type="password" class="form-control" name="confirm_password" id="pwd2" placeholder="Repeat password" required>
      </div>
      <p style="font-size:.8rem;color:var(--text-light);margin-bottom:1.25rem">By creating an account you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.</p>
      <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-user-plus me-2"></i>Create Account</button>
    </form>

    <p class="text-center mt-3 mb-0" style="font-size:.875rem;color:var(--text-light)">
      Already have an account? <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--primary);font-weight:700">Sign In</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
