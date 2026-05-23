<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Blog Posts';

$msg = ''; $msgType = 'success';
$action = sanitize($_GET['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');
    if ($act === 'save') {
        $id      = sanitizeInt($_POST['id'] ?? 0);
        $title   = sanitize($_POST['title'] ?? '');
        $cat     = sanitize($_POST['category'] ?? 'General');
        $excerpt = sanitize($_POST['excerpt'] ?? '');
        $body    = $_POST['body'] ?? ''; // Allow HTML
        $body    = strip_tags($body, '<p><br><b><strong><i><em><ul><ol><li><h2><h3><h4><a><img><blockquote>');
        $tags    = sanitize($_POST['tags'] ?? '');
        $metaT   = sanitize($_POST['meta_title'] ?? '');
        $metaD   = sanitize($_POST['meta_description'] ?? '');
        $status  = sanitize($_POST['status'] ?? 'draft');
        $slug    = createSlug($title);

        $image = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $up = uploadImage($_FILES['image'], 'blog');
            if ($up) { if ($image) deleteFile($image); $image = $up; }
        }

        $published = $status === 'published' ? date('Y-m-d H:i:s') : null;

        if (!$title) { $msg = 'Title is required.'; $msgType = 'danger'; }
        elseif ($id) {
            db()->prepare("UPDATE blog_posts SET title=?,slug=?,category=?,excerpt=?,body=?,image=?,tags=?,meta_title=?,meta_description=?,status=?,published_at=COALESCE(published_at,?) WHERE id=?")
                ->execute([$title,$slug,$cat,$excerpt,$body,$image,$tags,$metaT,$metaD,$status,$published,$id]);
            $msg = 'Post updated.';
        } else {
            db()->prepare("INSERT INTO blog_posts (admin_id,title,slug,category,excerpt,body,image,tags,meta_title,meta_description,status,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$_SESSION['admin_id'],$title,$slug,$cat,$excerpt,$body,$image,$tags,$metaT,$metaD,$status,$published]);
            $msg = 'Post created.';
        }
        $action = 'list';
    } elseif ($act === 'delete') {
        $id = sanitizeInt($_POST['id'] ?? 0);
        $p = db()->prepare("SELECT image FROM blog_posts WHERE id=?"); $p->execute([$id]); $p = $p->fetch();
        if ($p && $p['image']) deleteFile($p['image']);
        db()->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
        $msg = 'Post deleted.';
    }
}

$editPost = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $s = db()->prepare("SELECT * FROM blog_posts WHERE id=?"); $s->execute([sanitizeInt($_GET['id'])]); $editPost = $s->fetch();
    if (!$editPost) $action = 'list';
}

$posts = db()->query("SELECT bp.*, a.name AS author FROM blog_posts bp JOIN admins a ON bp.admin_id=a.id ORDER BY bp.created_at DESC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h4>Blog Posts</h4>
  <a href="<?= APP_URL ?>/admin/blog.php?action=new" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Post</a>
</div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<div class="stat-card">
  <h5 style="font-family:var(--font-display);margin-bottom:1.5rem"><?= $action==='edit'?'Edit Post':'New Blog Post' ?></h5>
  <form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($editPost): ?><input type="hidden" name="id" value="<?= $editPost['id'] ?>"><input type="hidden" name="existing_image" value="<?= htmlspecialchars($editPost['image']??'') ?>"><?php endif; ?>
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="mb-3"><label class="form-label fw-bold small">Post Title *</label>
          <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editPost['title']??'') ?>" required></div>
        <div class="mb-3"><label class="form-label fw-bold small">Excerpt</label>
          <textarea class="form-control" name="excerpt" rows="2" placeholder="Brief summary…"><?= htmlspecialchars($editPost['excerpt']??'') ?></textarea></div>
        <div class="mb-3"><label class="form-label fw-bold small">Content *</label>
          <textarea class="form-control" name="body" rows="14" placeholder="Write your post content here…"><?= htmlspecialchars($editPost['body']??'') ?></textarea></div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-bold small">Meta Title</label><input type="text" class="form-control" name="meta_title" value="<?= htmlspecialchars($editPost['meta_title']??'') ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Tags (comma-separated)</label><input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($editPost['tags']??'') ?>" placeholder="cake, recipe, tips"></div>
          <div class="col-12"><label class="form-label fw-bold small">Meta Description</label><textarea class="form-control" name="meta_description" rows="2"><?= htmlspecialchars($editPost['meta_description']??'') ?></textarea></div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="mb-3"><label class="form-label fw-bold small">Featured Image</label>
          <?php if (!empty($editPost['image'])): ?><div style="border-radius:8px;overflow:hidden;margin-bottom:.5rem;border:1px solid var(--border)"><img src="<?= getImageUrl($editPost['image']) ?>" id="blogImgPreview" style="width:100%;max-height:160px;object-fit:cover"></div>
          <?php else: ?><img id="blogImgPreview" style="display:none;width:100%;max-height:140px;object-fit:cover;border-radius:8px;margin-bottom:.5rem"><?php endif; ?>
          <input type="file" class="form-control" name="image" accept="image/*" data-preview="blogImgPreview">
        </div>
        <div class="mb-3"><label class="form-label fw-bold small">Category</label>
          <select class="form-select" name="category">
            <?php foreach (['General','Recipes','Tips & Tricks','Behind the Scenes','Events','Seasonal'] as $c): ?>
            <option value="<?= $c ?>" <?= ($editPost['category']??'')===$c?'selected':'' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="mb-3"><label class="form-label fw-bold small">Status</label>
          <select class="form-select" name="status">
            <option value="draft" <?= ($editPost['status']??'')==='draft'?'selected':'' ?>>Draft</option>
            <option value="published" <?= ($editPost['status']??'')==='published'?'selected':'' ?>>Published</option>
          </select></div>
        <div class="d-flex gap-2 flex-column">
          <button type="submit" class="btn btn-primary"><?= $action==='edit'?'Update Post':'Publish Post' ?></button>
          <a href="<?= APP_URL ?>/admin/blog.php" class="btn btn-outline-primary">Cancel</a>
        </div>
      </div>
    </div>
  </form>
</div>

<?php else: ?>
<div class="stat-card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Views</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($posts as $p): ?>
      <tr>
        <td>
          <?php if ($p['image']): ?><div style="width:48px;height:36px;border-radius:6px;overflow:hidden;float:left;margin-right:.5rem"><img src="<?= getImageUrl($p['image']) ?>" style="width:100%;height:100%;object-fit:cover"></div><?php endif; ?>
          <div style="font-weight:600;font-size:.875rem"><?= htmlspecialchars(truncate($p['title'],50)) ?></div>
        </td>
        <td><span class="badge bg-secondary" style="font-size:.72rem"><?= htmlspecialchars($p['category']) ?></span></td>
        <td style="font-size:.875rem"><?= htmlspecialchars($p['author']) ?></td>
        <td><span class="badge bg-<?= $p['status']==='published'?'success':'warning text-dark' ?>"><?= ucfirst($p['status']) ?></span></td>
        <td style="font-size:.875rem"><?= number_format($p['views']) ?></td>
        <td style="font-size:.8rem;color:var(--text-light)"><?= formatDate($p['created_at']) ?></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= APP_URL ?>/admin/blog.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
            <?php if ($p['status']==='published'): ?>
            <a href="<?= APP_URL ?>/pages/blog-post.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-eye"></i></a>
            <?php endif; ?>
            <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" data-confirm="Delete '<?= htmlspecialchars(addslashes(truncate($p['title'],30))) ?>'?"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($posts)): ?><tr><td colspan="7" class="text-center py-4" style="color:var(--text-light)">No posts yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
