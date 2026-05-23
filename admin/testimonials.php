<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Testimonials';

$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');

    if ($act === 'save') {
        $id      = sanitizeInt($_POST['id'] ?? 0);
        $name    = sanitize($_POST['name'] ?? '');
        $role    = sanitize($_POST['role'] ?? '');
        $content = sanitize($_POST['content'] ?? '');
        $rating  = max(1, min(5, sanitizeInt($_POST['rating'] ?? 5)));
        $order   = sanitizeInt($_POST['sort_order'] ?? 0);
        $status  = sanitizeInt($_POST['status'] ?? 1);

        $avatar = $_POST['existing_avatar'] ?? '';
        if (!empty($_FILES['avatar']['name'])) {
            $up = uploadImage($_FILES['avatar'], 'avatars');
            if ($up) { if ($avatar) deleteFile($avatar); $avatar = $up; }
        }

        if (!$name || !$content) {
            $msg = 'Name and content are required.'; $msgType = 'danger';
        } elseif ($id) {
            db()->prepare("UPDATE testimonials SET name=?,role=?,avatar=?,content=?,rating=?,sort_order=?,status=? WHERE id=?")
                ->execute([$name,$role,$avatar,$content,$rating,$order,$status,$id]);
            $msg = 'Testimonial updated.';
        } else {
            db()->prepare("INSERT INTO testimonials (name,role,avatar,content,rating,sort_order,status) VALUES (?,?,?,?,?,?,?)")
                ->execute([$name,$role,$avatar,$content,$rating,$order,$status]);
            $msg = 'Testimonial added.';
        }
    } elseif ($act === 'delete') {
        $id = sanitizeInt($_POST['id'] ?? 0);
        $t = db()->prepare("SELECT avatar FROM testimonials WHERE id=?"); $t->execute([$id]); $t = $t->fetch();
        if ($t && $t['avatar']) deleteFile($t['avatar']);
        db()->prepare("DELETE FROM testimonials WHERE id=?")->execute([$id]);
        $msg = 'Testimonial deleted.';
    }
}

$editItem = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare("SELECT * FROM testimonials WHERE id=?");
    $s->execute([sanitizeInt($_GET['edit'])]);
    $editItem = $s->fetch();
}

$testimonials = db()->query("SELECT * FROM testimonials ORDER BY sort_order, id DESC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Testimonials</h4></div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
  <!-- Form -->
  <div class="col-lg-4">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem"><?= $editItem ? 'Edit Testimonial' : 'Add Testimonial' ?></h6>
      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
        <input type="hidden" name="existing_avatar" value="<?= htmlspecialchars($editItem['avatar']??'') ?>">
        <?php endif; ?>

        <div class="mb-3">
          <label class="form-label fw-bold small">Photo</label>
          <?php if (!empty($editItem['avatar'])): ?>
          <div style="margin-bottom:.5rem"><img src="<?= getImageUrl($editItem['avatar']) ?>" id="tAvatar" style="width:60px;height:60px;border-radius:50%;object-fit:cover"></div>
          <?php else: ?>
          <img id="tAvatar" style="display:none;width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:.5rem">
          <?php endif; ?>
          <input type="file" class="form-control" name="avatar" accept="image/*" data-preview="tAvatar">
        </div>
        <div class="mb-3"><label class="form-label fw-bold small">Full Name *</label>
          <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($editItem['name']??'') ?>" required></div>
        <div class="mb-3"><label class="form-label fw-bold small">Role / Title</label>
          <input type="text" class="form-control" name="role" value="<?= htmlspecialchars($editItem['role']??'') ?>" placeholder="e.g. Regular Customer"></div>
        <div class="mb-3"><label class="form-label fw-bold small">Review *</label>
          <textarea class="form-control" name="content" rows="4" required><?= htmlspecialchars($editItem['content']??'') ?></textarea></div>
        <div class="row g-2 mb-3">
          <div class="col-4"><label class="form-label fw-bold small">Rating</label>
            <select class="form-select" name="rating">
              <?php for ($i=5;$i>=1;$i--): ?><option value="<?= $i ?>" <?= ($editItem['rating']??5)==$i?'selected':'' ?>><?= $i ?> ⭐</option><?php endfor; ?>
            </select></div>
          <div class="col-4"><label class="form-label fw-bold small">Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= $editItem['sort_order']??0 ?>" min="0"></div>
          <div class="col-4"><label class="form-label fw-bold small">Active</label>
            <select class="form-select" name="status">
              <option value="1" <?= ($editItem['status']??1)?'selected':'' ?>>Yes</option>
              <option value="0" <?= isset($editItem)&&!$editItem['status']?'selected':'' ?>>No</option>
            </select></div>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill"><?= $editItem?'Update':'Add' ?></button>
          <?php if ($editItem): ?><a href="<?= APP_URL ?>/admin/testimonials.php" class="btn btn-outline-primary">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- List -->
  <div class="col-lg-8">
    <div class="stat-card">
      <?php foreach ($testimonials as $t): ?>
      <div class="d-flex gap-3 p-3 mb-2" style="background:var(--cream);border-radius:var(--radius-sm);border:1px solid var(--border)">
        <div style="flex-shrink:0">
          <?php if ($t['avatar']): ?>
          <img src="<?= getImageUrl($t['avatar']) ?>" style="width:52px;height:52px;border-radius:50%;object-fit:cover">
          <?php else: ?>
          <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.2rem"><?= strtoupper(substr($t['name'],0,1)) ?></div>
          <?php endif; ?>
        </div>
        <div class="flex-fill">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
            <div>
              <strong style="font-size:.9rem"><?= htmlspecialchars($t['name']) ?></strong>
              <?php if ($t['role']): ?><small style="color:var(--text-light);margin-left:.5rem"><?= htmlspecialchars($t['role']) ?></small><?php endif; ?>
              <div style="color:var(--gold);font-size:.85rem"><?= str_repeat('★',(int)$t['rating']) ?><?= str_repeat('☆',5-(int)$t['rating']) ?></div>
            </div>
            <div class="d-flex gap-1 align-items-center">
              <span class="badge bg-<?= $t['status']?'success':'secondary' ?>"><?= $t['status']?'Active':'Hidden' ?></span>
              <a href="?edit=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
              <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" data-confirm="Delete this testimonial?"><i class="fas fa-trash"></i></button></form>
            </div>
          </div>
          <p style="font-size:.85rem;color:var(--text-medium);margin:.5rem 0 0;font-style:italic">"<?= htmlspecialchars(truncate($t['content'],120)) ?>"</p>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($testimonials)): ?><div class="text-center py-4" style="color:var(--text-light)">No testimonials yet.</div><?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
