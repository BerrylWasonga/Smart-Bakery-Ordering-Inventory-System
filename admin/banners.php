<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Banners';

$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');
    if ($act === 'save') {
        $id       = sanitizeInt($_POST['id'] ?? 0);
        $title    = sanitize($_POST['title'] ?? '');
        $subtitle = sanitize($_POST['subtitle'] ?? '');
        $link     = sanitize($_POST['link_url'] ?? '');
        $btnText  = sanitize($_POST['button_text'] ?? '');
        $position = sanitize($_POST['position'] ?? 'hero');
        $order    = sanitizeInt($_POST['sort_order'] ?? 0);
        $status   = sanitizeInt($_POST['status'] ?? 1);

        $image = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $up = uploadImage($_FILES['image'], 'banners');
            if ($up) { if ($image) deleteFile($image); $image = $up; }
        }
        if (!$image && !$id) { $msg = 'Please upload a banner image.'; $msgType = 'danger'; }
        elseif ($id) {
            db()->prepare("UPDATE banners SET title=?,subtitle=?,image=COALESCE(NULLIF(?,\"\"),image),link_url=?,button_text=?,position=?,sort_order=?,status=? WHERE id=?")
                ->execute([$title,$subtitle,$image,$link,$btnText,$position,$order,$status,$id]);
            $msg = 'Banner updated.';
        } else {
            db()->prepare("INSERT INTO banners (title,subtitle,image,link_url,button_text,position,sort_order,status) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$title,$subtitle,$image,$link,$btnText,$position,$order,$status]);
            $msg = 'Banner created.';
        }
    } elseif ($act === 'delete') {
        $id = sanitizeInt($_POST['id'] ?? 0);
        $b = db()->prepare("SELECT image FROM banners WHERE id=?"); $b->execute([$id]); $b = $b->fetch();
        if ($b && $b['image']) deleteFile($b['image']);
        db()->prepare("DELETE FROM banners WHERE id=?")->execute([$id]);
        $msg = 'Banner deleted.';
    }
}

$editBanner = null;
if (isset($_GET['edit'])) { $s = db()->prepare("SELECT * FROM banners WHERE id=?"); $s->execute([sanitizeInt($_GET['edit'])]); $editBanner = $s->fetch(); }
$banners = db()->query("SELECT * FROM banners ORDER BY sort_order,created_at DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Banners & Sliders</h4></div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem"><?= $editBanner ? 'Edit Banner' : 'Add Banner' ?></h6>
      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($editBanner): ?><input type="hidden" name="id" value="<?= $editBanner['id'] ?>"><input type="hidden" name="existing_image" value="<?= htmlspecialchars($editBanner['image']??'') ?>"><?php endif; ?>
        <div class="mb-3"><label class="form-label fw-bold small">Image *</label>
          <?php if (!empty($editBanner['image'])): ?><div style="margin-bottom:.5rem;border-radius:8px;overflow:hidden"><img src="<?= getImageUrl($editBanner['image']) ?>" id="bannerPreview" style="width:100%;max-height:120px;object-fit:cover"></div>
          <?php else: ?><img id="bannerPreview" style="display:none;width:100%;max-height:120px;object-fit:cover;border-radius:8px;margin-bottom:.5rem"><?php endif; ?>
          <input type="file" class="form-control" name="image" accept="image/*" data-preview="bannerPreview"></div>
        <div class="mb-3"><label class="form-label fw-bold small">Title</label><input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editBanner['title']??'') ?>"></div>
        <div class="mb-3"><label class="form-label fw-bold small">Subtitle</label><input type="text" class="form-control" name="subtitle" value="<?= htmlspecialchars($editBanner['subtitle']??'') ?>"></div>
        <div class="mb-3"><label class="form-label fw-bold small">Link URL</label><input type="url" class="form-control" name="link_url" value="<?= htmlspecialchars($editBanner['link_url']??'') ?>"></div>
        <div class="mb-3"><label class="form-label fw-bold small">Button Text</label><input type="text" class="form-control" name="button_text" value="<?= htmlspecialchars($editBanner['button_text']??'') ?>" placeholder="e.g. Shop Now"></div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label fw-bold small">Position</label>
            <select class="form-select" name="position">
              <?php foreach (['hero'=>'Hero Slider','promo'=>'Promo Banner','sidebar'=>'Sidebar'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($editBanner['position']??'')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="col-3"><label class="form-label fw-bold small">Order</label><input type="number" class="form-control" name="sort_order" value="<?= $editBanner['sort_order']??0 ?>" min="0"></div>
          <div class="col-3"><label class="form-label fw-bold small">Active</label>
            <select class="form-select" name="status"><option value="1" <?= ($editBanner['status']??1)?'selected':'' ?>>Yes</option><option value="0" <?= isset($editBanner)&&!$editBanner['status']?'selected':'' ?>>No</option></select></div>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill"><?= $editBanner?'Update':'Add Banner' ?></button>
          <?php if ($editBanner): ?><a href="<?= APP_URL ?>/admin/banners.php" class="btn btn-outline-primary">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="stat-card">
      <div class="row g-3">
        <?php foreach ($banners as $b): ?>
        <div class="col-md-6">
          <div style="background:var(--cream);border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden">
            <div style="aspect-ratio:16/7;overflow:hidden;background:var(--cream-dark)">
              <img src="<?= getImageUrl($b['image']) ?>" style="width:100%;height:100%;object-fit:cover">
            </div>
            <div class="p-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div style="font-weight:600;font-size:.875rem"><?= htmlspecialchars(truncate($b['title']??'Untitled',30)) ?></div>
                  <span class="badge bg-secondary" style="font-size:.7rem"><?= ucfirst($b['position']) ?></span>
                  <span class="badge bg-<?= $b['status']?'success':'secondary' ?> ms-1" style="font-size:.7rem"><?= $b['status']?'Active':'Inactive' ?></span>
                </div>
                <div class="d-flex gap-1">
                  <a href="?edit=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                  <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" data-confirm="Delete this banner?"><i class="fas fa-trash"></i></button></form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($banners)): ?><div class="col-12 text-center py-4" style="color:var(--text-light)">No banners yet. Add one!</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
