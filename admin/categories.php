<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Categories';

$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');

    if ($act === 'save') {
        $id     = sanitizeInt($_POST['id'] ?? 0);
        $name   = sanitize($_POST['name'] ?? '');
        $desc   = sanitize($_POST['description'] ?? '');
        $order  = sanitizeInt($_POST['sort_order'] ?? 0);
        $status = sanitizeInt($_POST['status'] ?? 1);
        $slug   = createSlug($name);

        $image = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $up = uploadImage($_FILES['image'], 'banners');
            if ($up) { if ($image) deleteFile($image); $image = $up; }
        }

        if (!$name) { $msg = 'Name is required.'; $msgType = 'danger'; }
        elseif ($id) {
            db()->prepare("UPDATE categories SET name=?,slug=?,description=?,image=?,sort_order=?,status=? WHERE id=?")
                ->execute([$name,$slug,$desc,$image,$order,$status,$id]);
            $msg = 'Category updated.';
        } else {
            db()->prepare("INSERT INTO categories (name,slug,description,image,sort_order,status) VALUES (?,?,?,?,?,?)")
                ->execute([$name,$slug,$desc,$image,$order,$status]);
            $msg = 'Category created.';
        }
    } elseif ($act === 'delete') {
        $id = sanitizeInt($_POST['id'] ?? 0);
        $count = db()->prepare("SELECT COUNT(*) FROM products WHERE category_id=?"); $count->execute([$id]);
        if ($count->fetchColumn() > 0) { $msg = 'Cannot delete: category has products.'; $msgType = 'danger'; }
        else { db()->prepare("DELETE FROM categories WHERE id=?")->execute([$id]); $msg = 'Category deleted.'; }
    }
}

$editCat = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare("SELECT * FROM categories WHERE id=?"); $s->execute([sanitizeInt($_GET['edit'])]); $editCat = $s->fetch();
}

$categories = db()->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id) AS product_count FROM categories c ORDER BY c.sort_order,c.name")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Categories</h4></div>

<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
  <!-- Form -->
  <div class="col-lg-4">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem"><?= $editCat ? 'Edit Category' : 'Add Category' ?></h6>
      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($editCat): ?>
        <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editCat['image']??'') ?>">
        <?php endif; ?>
        <div class="mb-3"><label class="form-label fw-bold small">Name *</label>
          <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($editCat['name']??'') ?>" required></div>
        <div class="mb-3"><label class="form-label fw-bold small">Description</label>
          <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($editCat['description']??'') ?></textarea></div>
        <div class="mb-3"><label class="form-label fw-bold small">Category Image</label>
          <?php if (!empty($editCat['image'])): ?>
          <div style="margin-bottom:.5rem;border-radius:8px;overflow:hidden;border:1px solid var(--border)"><img src="<?= getImageUrl($editCat['image']) ?>" id="catImgPreview" style="width:100%;max-height:120px;object-fit:cover"></div>
          <?php else: ?>
          <img id="catImgPreview" style="display:none;width:100%;max-height:120px;object-fit:cover;border-radius:8px;margin-bottom:.5rem">
          <?php endif; ?>
          <input type="file" class="form-control" name="image" accept="image/*" data-preview="catImgPreview">
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label fw-bold small">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= $editCat['sort_order']??0 ?>" min="0"></div>
          <div class="col-6"><label class="form-label fw-bold small">Status</label>
            <select class="form-select" name="status">
              <option value="1" <?= ($editCat['status']??1)?'selected':'' ?>>Active</option>
              <option value="0" <?= isset($editCat) && !$editCat['status']?'selected':'' ?>>Inactive</option>
            </select></div>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill"><?= $editCat ? 'Update' : 'Create' ?></button>
          <?php if ($editCat): ?><a href="<?= APP_URL ?>/admin/categories.php" class="btn btn-outline-primary">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- List -->
  <div class="col-lg-8">
    <div class="stat-card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Category</th><th>Slug</th><th>Products</th><th>Sort</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($categories as $c): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if ($c['image']): ?><div style="width:36px;height:36px;border-radius:6px;overflow:hidden;flex-shrink:0"><img src="<?= getImageUrl($c['image']) ?>" style="width:100%;height:100%;object-fit:cover"></div><?php endif; ?>
                <span style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($c['name']) ?></span>
              </div>
            </td>
            <td style="font-size:.8rem;color:var(--text-light)"><?= htmlspecialchars($c['slug']) ?></td>
            <td><span class="badge bg-secondary"><?= $c['product_count'] ?></span></td>
            <td style="font-size:.875rem"><?= $c['sort_order'] ?></td>
            <td><span class="badge bg-<?= $c['status']?'success':'secondary' ?>"><?= $c['status']?'Active':'Inactive' ?></span></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= APP_URL ?>/admin/categories.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                <form method="POST" class="d-inline">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" data-confirm="Delete '<?= htmlspecialchars(addslashes($c['name'])) ?>'?"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
