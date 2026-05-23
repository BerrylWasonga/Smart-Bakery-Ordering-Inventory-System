<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
$user = getCurrentUser();
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid token.'; }
    else {
        $name  = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        if (strlen($name) < 2) { $error = 'Name must be at least 2 characters.'; }
        else {
            $avatarPath = $user['avatar'];
            if (!empty($_FILES['avatar']['name'])) {
                $uploaded = uploadImage($_FILES['avatar'], 'avatars');
                if ($uploaded) { if ($avatarPath) deleteFile($avatarPath); $avatarPath = $uploaded; }
                else $error = 'Invalid image. Max 5MB, JPG/PNG/WebP only.';
            }
            if (!$error) {
                db()->prepare("UPDATE users SET name=?,phone=?,avatar=? WHERE id=?")->execute([$name,$phone,$avatarPath,$user['id']]);
                $_SESSION['user_name'] = $name;
                $user = getCurrentUser();
                $success = 'Profile updated successfully!';
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
<title>Profile – <?= htmlspecialchars($siteName) ?></title>
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
        <h4 style="font-family:var(--font-display);margin-bottom:1.5rem">Edit Profile</h4>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem">
          <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <!-- Avatar -->
            <div class="mb-4 text-center">
              <div style="width:100px;height:100px;border-radius:50%;background:var(--cream-dark);margin:0 auto 1rem;overflow:hidden;border:3px solid var(--border)">
                <?php if ($user['avatar']): ?>
                <img src="<?= getImageUrl($user['avatar']) ?>" id="avatarPreview" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:var(--text-light);font-weight:700">
                  <?= strtoupper(substr($user['name'],0,1)) ?>
                </div>
                <img id="avatarPreview" style="display:none;width:100%;height:100%;object-fit:cover">
                <?php endif; ?>
              </div>
              <label class="btn btn-outline-primary btn-sm" for="avatarInput"><i class="fas fa-camera me-2"></i>Change Photo</label>
              <input type="file" id="avatarInput" name="avatar" accept="image/*" class="d-none" data-preview="avatarPreview">
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small">Full Name *</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small">Email Address</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:var(--cream-dark)">
                <small style="color:var(--text-light)">Email cannot be changed</small>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small">Phone Number</label>
                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
              </div>
            </div>
            <div class="mt-4">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
