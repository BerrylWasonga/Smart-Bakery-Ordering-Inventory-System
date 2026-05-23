<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Coupons';

$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');
    if ($act === 'save') {
        $id       = sanitizeInt($_POST['id'] ?? 0);
        $code     = strtoupper(sanitize($_POST['code'] ?? ''));
        $type     = sanitize($_POST['type'] ?? 'percentage');
        $value    = (float)($_POST['value'] ?? 0);
        $minOrder = (float)($_POST['min_order_amount'] ?? 0);
        $limit    = strlen($_POST['usage_limit']??'') ? sanitizeInt($_POST['usage_limit']) : null;
        $perUser  = sanitizeInt($_POST['per_user_limit'] ?? 1);
        $expires  = sanitize($_POST['expires_at'] ?? '') ?: null;
        $status   = sanitizeInt($_POST['status'] ?? 1);

        if (!$code || $value <= 0) { $msg = 'Code and value are required.'; $msgType = 'danger'; }
        else {
            if ($id) {
                db()->prepare("UPDATE coupons SET code=?,type=?,value=?,min_order_amount=?,usage_limit=?,per_user_limit=?,expires_at=?,status=? WHERE id=?")
                    ->execute([$code,$type,$value,$minOrder,$limit,$perUser,$expires,$status,$id]);
                $msg = 'Coupon updated.';
            } else {
                $exists = db()->prepare("SELECT id FROM coupons WHERE code=?"); $exists->execute([$code]);
                if ($exists->fetch()) { $msg = 'Coupon code already exists.'; $msgType = 'danger'; }
                else {
                    db()->prepare("INSERT INTO coupons (code,type,value,min_order_amount,usage_limit,per_user_limit,expires_at,status) VALUES (?,?,?,?,?,?,?,?)")
                        ->execute([$code,$type,$value,$minOrder,$limit,$perUser,$expires,$status]);
                    $msg = 'Coupon created.';
                }
            }
        }
    } elseif ($act === 'delete') {
        db()->prepare("DELETE FROM coupons WHERE id=?")->execute([sanitizeInt($_POST['id'] ?? 0)]);
        $msg = 'Coupon deleted.';
    }
}

$editCoupon = null;
if (isset($_GET['edit'])) { $s = db()->prepare("SELECT * FROM coupons WHERE id=?"); $s->execute([sanitizeInt($_GET['edit'])]); $editCoupon = $s->fetch(); }

$coupons = db()->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Coupons & Discounts</h4></div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem"><?= $editCoupon ? 'Edit Coupon' : 'Create Coupon' ?></h6>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($editCoupon): ?><input type="hidden" name="id" value="<?= $editCoupon['id'] ?>"><?php endif; ?>
        <div class="mb-3"><label class="form-label fw-bold small">Coupon Code *</label>
          <input type="text" class="form-control text-uppercase" name="code" value="<?= htmlspecialchars($editCoupon['code']??'') ?>" placeholder="e.g. SAVE20" required style="letter-spacing:.1em;font-weight:700"></div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label fw-bold small">Type</label>
            <select class="form-select" name="type">
              <option value="percentage" <?= ($editCoupon['type']??'')==='percentage'?'selected':'' ?>>Percentage (%)</option>
              <option value="fixed" <?= ($editCoupon['type']??'')==='fixed'?'selected':'' ?>>Fixed (KSh)</option>
            </select></div>
          <div class="col-6"><label class="form-label fw-bold small">Value *</label>
            <input type="number" class="form-control" name="value" value="<?= $editCoupon['value']??'' ?>" min="0" step="0.01" required></div>
        </div>
        <div class="mb-3"><label class="form-label fw-bold small">Min Order Amount (KSh)</label>
          <input type="number" class="form-control" name="min_order_amount" value="<?= $editCoupon['min_order_amount']??0 ?>" min="0" step="0.01"></div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label fw-bold small">Usage Limit</label>
            <input type="number" class="form-control" name="usage_limit" value="<?= $editCoupon['usage_limit']??'' ?>" min="1" placeholder="Unlimited"></div>
          <div class="col-6"><label class="form-label fw-bold small">Per User</label>
            <input type="number" class="form-control" name="per_user_limit" value="<?= $editCoupon['per_user_limit']??1 ?>" min="1"></div>
        </div>
        <div class="mb-3"><label class="form-label fw-bold small">Expiry Date</label>
          <input type="datetime-local" class="form-control" name="expires_at" value="<?= $editCoupon['expires_at'] ? date('Y-m-d\TH:i', strtotime($editCoupon['expires_at'])) : '' ?>"></div>
        <div class="mb-3"><label class="form-label fw-bold small">Status</label>
          <select class="form-select" name="status">
            <option value="1" <?= ($editCoupon['status']??1)?'selected':'' ?>>Active</option>
            <option value="0" <?= isset($editCoupon)&&!$editCoupon['status']?'selected':'' ?>>Inactive</option>
          </select></div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill"><?= $editCoupon ? 'Update' : 'Create' ?></button>
          <?php if ($editCoupon): ?><a href="<?= APP_URL ?>/admin/coupons.php" class="btn btn-outline-primary">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="stat-card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Used / Limit</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($coupons as $c): ?>
          <tr>
            <td><code style="font-size:.9rem;color:var(--primary);font-weight:700"><?= htmlspecialchars($c['code']) ?></code></td>
            <td style="font-size:.875rem"><?= ucfirst($c['type']) ?></td>
            <td style="font-weight:700"><?= $c['type']==='percentage' ? $c['value'].'%' : formatPrice($c['value']) ?></td>
            <td><span class="badge bg-secondary"><?= $c['used_count'] ?> / <?= $c['usage_limit'] ?? '∞' ?></span></td>
            <td style="font-size:.8rem;color:var(--text-light)"><?= $c['expires_at'] ? formatDate($c['expires_at']) : 'No expiry' ?></td>
            <td>
              <?php $expired = $c['expires_at'] && strtotime($c['expires_at']) < time(); ?>
              <span class="badge bg-<?= $expired?'secondary':($c['status']?'success':'warning') ?>"><?= $expired?'Expired':($c['status']?'Active':'Inactive') ?></span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= APP_URL ?>/admin/coupons.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" data-confirm="Delete coupon <?= htmlspecialchars(addslashes($c['code'])) ?>?"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($coupons)): ?><tr><td colspan="7" class="text-center py-4" style="color:var(--text-light)">No coupons yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
