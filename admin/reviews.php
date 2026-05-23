<?php
// admin/reviews.php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Reviews';

$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');
    $id  = sanitizeInt($_POST['id'] ?? 0);
    if ($act === 'approve') { db()->prepare("UPDATE reviews SET status='approved' WHERE id=?")->execute([$id]); $msg = 'Review approved.'; }
    elseif ($act === 'reject') { db()->prepare("UPDATE reviews SET status='rejected' WHERE id=?")->execute([$id]); $msg = 'Review rejected.'; }
    elseif ($act === 'delete') { db()->prepare("DELETE FROM reviews WHERE id=?")->execute([$id]); $msg = 'Review deleted.'; }
    elseif ($act === 'reply') {
        $reply = sanitize($_POST['reply'] ?? '');
        db()->prepare("UPDATE reviews SET admin_reply=? WHERE id=?")->execute([$reply, $id]);
        $msg = 'Reply saved.';
    }
}

$status = sanitize($_GET['status'] ?? '');
$where  = $status ? "WHERE r.status='$status'" : '';
$reviews = db()->query("SELECT r.*, u.name AS user_name, p.name AS product_name FROM reviews r JOIN users u ON r.user_id=u.id JOIN products p ON r.product_id=p.id $where ORDER BY r.created_at DESC LIMIT 50")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h4>Reviews</h4>
  <div class="d-flex gap-2">
    <?php foreach ([''=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $v=>$l): ?>
    <a href="?<?= $v?"status=$v":'' ?>" class="btn btn-sm btn-<?= $status===$v?'primary':'outline-primary' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
</div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="stat-card">
  <?php foreach ($reviews as $r): ?>
  <div class="p-3 mb-3" style="background:var(--cream);border-radius:var(--radius-sm);border:1px solid var(--border)">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
      <div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <strong style="font-size:.9rem"><?= htmlspecialchars($r['user_name']) ?></strong>
          <span class="badge bg-secondary" style="font-size:.7rem"><?= htmlspecialchars($r['product_name']) ?></span>
          <div style="color:var(--gold)"><?= str_repeat('★',$r['rating']) . str_repeat('☆',5-$r['rating']) ?></div>
          <span class="badge bg-<?= $r['status']==='approved'?'success':($r['status']==='rejected'?'danger':'warning text-dark') ?>"><?= ucfirst($r['status']) ?></span>
        </div>
        <?php if ($r['title']): ?><div style="font-weight:600;font-size:.875rem;margin-top:.25rem"><?= htmlspecialchars($r['title']) ?></div><?php endif; ?>
        <p style="font-size:.875rem;color:var(--text-medium);margin:.25rem 0 0"><?= htmlspecialchars($r['body']) ?></p>
        <small style="color:var(--text-light)"><?= formatDate($r['created_at']) ?></small>
      </div>
      <div class="d-flex gap-1 flex-wrap">
        <?php if ($r['status'] !== 'approved'): ?>
        <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Approve</button></form>
        <?php endif; ?>
        <?php if ($r['status'] !== 'rejected'): ?>
        <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-warning">Reject</button></form>
        <?php endif; ?>
        <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-outline-danger" data-confirm="Delete this review?"><i class="fas fa-trash"></i></button></form>
      </div>
    </div>
    <!-- Reply -->
    <form method="POST" class="mt-2">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="reply">
      <input type="hidden" name="id" value="<?= $r['id'] ?>">
      <div class="input-group input-group-sm">
        <input type="text" class="form-control" name="reply" value="<?= htmlspecialchars($r['admin_reply']??'') ?>" placeholder="Admin reply (optional)…">
        <button type="submit" class="btn btn-outline-primary">Save Reply</button>
      </div>
    </form>
  </div>
  <?php endforeach; ?>
  <?php if (empty($reviews)): ?><div class="text-center py-4" style="color:var(--text-light)">No reviews found.</div><?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
