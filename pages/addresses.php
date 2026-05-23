<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
$user = getCurrentUser();
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid token.'; }
    else {
        $act = sanitize($_POST['action'] ?? '');
        if ($act === 'save') {
            $id        = sanitizeInt($_POST['id'] ?? 0);
            $label     = sanitize($_POST['label'] ?? 'Home');
            $fullName  = sanitize($_POST['full_name'] ?? '');
            $phone     = sanitize($_POST['phone'] ?? '');
            $addr1     = sanitize($_POST['address_line1'] ?? '');
            $addr2     = sanitize($_POST['address_line2'] ?? '');
            $city      = sanitize($_POST['city'] ?? '');
            $state     = sanitize($_POST['state'] ?? '');
            $postal    = sanitize($_POST['postal_code'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;

            if (!$fullName || !$phone || !$addr1 || !$city) { $error = 'Please fill required fields.'; }
            else {
                if ($isDefault) db()->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$user['id']]);
                if ($id) {
                    db()->prepare("UPDATE user_addresses SET label=?,full_name=?,phone=?,address_line1=?,address_line2=?,city=?,state=?,postal_code=?,is_default=? WHERE id=? AND user_id=?")
                        ->execute([$label,$fullName,$phone,$addr1,$addr2,$city,$state,$postal,$isDefault,$id,$user['id']]);
                } else {
                    db()->prepare("INSERT INTO user_addresses (user_id,label,full_name,phone,address_line1,address_line2,city,state,postal_code,is_default) VALUES (?,?,?,?,?,?,?,?,?,?)")
                        ->execute([$user['id'],$label,$fullName,$phone,$addr1,$addr2,$city,$state,$postal,$isDefault]);
                }
                $success = 'Address saved successfully!';
            }
        } elseif ($act === 'delete') {
            db()->prepare("DELETE FROM user_addresses WHERE id=? AND user_id=?")->execute([sanitizeInt($_POST['id']??0), $user['id']]);
            $success = 'Address removed.';
        } elseif ($act === 'set_default') {
            db()->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$user['id']]);
            db()->prepare("UPDATE user_addresses SET is_default=1 WHERE id=? AND user_id=?")->execute([sanitizeInt($_POST['id']??0), $user['id']]);
            $success = 'Default address updated.';
        }
    }
}

$editAddr = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare("SELECT * FROM user_addresses WHERE id=? AND user_id=?"); $s->execute([sanitizeInt($_GET['edit']), $user['id']]); $editAddr = $s->fetch();
}

$addresses = db()->prepare("SELECT * FROM user_addresses WHERE user_id=? ORDER BY is_default DESC, id ASC");
$addresses->execute([$user['id']]); $addresses = $addresses->fetchAll();
$siteName = getSetting('site_name', 'Crumbs & Co');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Addresses – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<!-- favicon -->
<link rel="icon" type="image/jpeg" href="../assets/images/Favicon2.jpg">
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div style="padding-top:var(--navbar-height);background:var(--cream);min-height:100vh">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-lg-3"><?php include __DIR__ . '/../includes/user-sidebar.php'; ?></div>
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 style="font-family:var(--font-display);margin:0">Saved Addresses</h4>
          <a href="<?= APP_URL ?>/pages/addresses.php?add=1" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Address</a>
        </div>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($editAddr || isset($_GET['add'])): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:2rem">
          <h6 style="font-family:var(--font-display);margin-bottom:1.25rem"><?= $editAddr?'Edit Address':'Add New Address' ?></h6>
          <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">
            <?php if ($editAddr): ?><input type="hidden" name="id" value="<?= $editAddr['id'] ?>"><?php endif; ?>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label fw-bold small">Label</label>
                <select class="form-select" name="label">
                  <?php foreach (['Home','Work','Other'] as $l): ?><option value="<?= $l ?>" <?= ($editAddr['label']??'')===$l?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
                </select></div>
              <div class="col-md-8"><label class="form-label fw-bold small">Full Name *</label>
                <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($editAddr['full_name']??$user['name']) ?>" required></div>
              <div class="col-md-6"><label class="form-label fw-bold small">Phone *</label>
                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($editAddr['phone']??$user['phone']??'') ?>" required></div>
              <div class="col-12"><label class="form-label fw-bold small">Address Line 1 *</label>
                <input type="text" class="form-control" name="address_line1" value="<?= htmlspecialchars($editAddr['address_line1']??'') ?>" required></div>
              <div class="col-12"><label class="form-label fw-bold small">Address Line 2</label>
                <input type="text" class="form-control" name="address_line2" value="<?= htmlspecialchars($editAddr['address_line2']??'') ?>"></div>
              <div class="col-md-4"><label class="form-label fw-bold small">City *</label>
                <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($editAddr['city']??'Nairobi') ?>" required></div>
              <div class="col-md-4"><label class="form-label fw-bold small">State</label>
                <input type="text" class="form-control" name="state" value="<?= htmlspecialchars($editAddr['state']??'Nairobi') ?>"></div>
              <div class="col-md-4"><label class="form-label fw-bold small">Postal Code</label>
                <input type="text" class="form-control" name="postal_code" value="<?= htmlspecialchars($editAddr['postal_code']??'') ?>"></div>
              <div class="col-12"><div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_default" id="isDefault" <?= !empty($editAddr['is_default'])?'checked':'' ?>>
                <label class="form-check-label" for="isDefault" style="font-size:.875rem">Set as default address</label>
              </div></div>
            </div>
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-primary"><?= $editAddr?'Update':'Save' ?> Address</button>
              <a href="<?= APP_URL ?>/pages/addresses.php" class="btn btn-outline-primary">Cancel</a>
            </div>
          </form>
        </div>
        <?php endif; ?>

        <?php if (empty($addresses) && !isset($_GET['add'])): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:3rem;text-align:center">
          <i class="fas fa-map-marker-alt" style="font-size:3rem;color:var(--border)"></i>
          <h5 style="margin-top:1rem;color:var(--text-light)">No saved addresses</h5>
          <a href="?add=1" class="btn btn-primary mt-2">Add First Address</a>
        </div>
        <?php else: ?>
        <div class="row g-3">
          <?php foreach ($addresses as $addr): ?>
          <div class="col-md-6">
            <div style="background:var(--warm-white);border:2px solid <?= $addr['is_default']?'var(--primary)':'var(--border)' ?>;border-radius:var(--radius);padding:1.5rem;position:relative">
              <?php if ($addr['is_default']): ?><span style="position:absolute;top:.75rem;right:.75rem;background:var(--primary);color:white;font-size:.7rem;padding:.2rem .6rem;border-radius:50px;font-weight:700">Default</span><?php endif; ?>
              <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($addr['label']) ?></div>
              <strong style="font-size:.9rem"><?= htmlspecialchars($addr['full_name']) ?></strong>
              <div style="font-size:.85rem;color:var(--text-medium);margin-top:.25rem;line-height:1.6">
                <?= htmlspecialchars($addr['address_line1']) ?><br>
                <?php if ($addr['address_line2']): ?><?= htmlspecialchars($addr['address_line2']) ?><br><?php endif; ?>
                <?= htmlspecialchars($addr['city']) ?><?= $addr['state']?', '.htmlspecialchars($addr['state']):'' ?><br>
                <i class="fas fa-phone me-1"></i><?= htmlspecialchars($addr['phone']) ?>
              </div>
              <div class="d-flex gap-2 mt-3">
                <a href="?edit=<?= $addr['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <?php if (!$addr['is_default']): ?>
                <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="set_default"><input type="hidden" name="id" value="<?= $addr['id'] ?>">
                  <button class="btn btn-sm btn-outline-secondary">Set Default</button></form>
                <?php endif; ?>
                <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $addr['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" data-confirm="Remove this address?"><i class="fas fa-trash"></i></button></form>
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
