<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Contact Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $id = sanitizeInt($_POST['id'] ?? 0);
    if (sanitize($_POST['action']??'') === 'delete') {
        db()->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$id]);
    }
}

// Mark as read when viewing
if (isset($_GET['id'])) {
    db()->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([sanitizeInt($_GET['id'])]);
}

$view = null;
if (isset($_GET['id'])) { $s = db()->prepare("SELECT * FROM contact_messages WHERE id=?"); $s->execute([sanitizeInt($_GET['id'])]); $view = $s->fetch(); }

$messages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 50")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Contact Messages</h4></div>

<div class="row g-4">
  <!-- List -->
  <div class="col-lg-5">
    <div class="stat-card" style="padding:0;overflow:hidden">
      <?php foreach ($messages as $m): ?>
      <a href="<?= APP_URL ?>/admin/messages.php?id=<?= $m['id'] ?>"
         class="d-flex gap-3 p-3 border-bottom text-decoration-none <?= (isset($_GET['id']) && $_GET['id']==$m['id'])?'':'hover-bg' ?>"
         style="background:<?= (isset($_GET['id'])&&$_GET['id']==$m['id'])?'rgba(139,69,19,.06)':'var(--warm-white)' ?>;border-color:var(--border)!important">
        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;flex-shrink:0;font-size:.85rem"><?= strtoupper(substr($m['name'],0,1)) ?></div>
        <div class="flex-fill overflow-hidden">
          <div class="d-flex justify-content-between">
            <strong style="font-size:.875rem"><?= htmlspecialchars($m['name']) ?></strong>
            <small style="color:var(--text-light)"><?= timeAgo($m['created_at']) ?></small>
          </div>
          <div style="font-size:.8rem;color:var(--text-medium);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($m['subject']) ?></div>
          <?php if (!$m['is_read']): ?><span class="badge bg-primary" style="font-size:.65rem">New</span><?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?><div class="text-center py-5" style="color:var(--text-light)"><i class="fas fa-inbox fa-2x mb-2"></i><br>No messages yet.</div><?php endif; ?>
    </div>
  </div>

  <!-- Detail -->
  <div class="col-lg-7">
    <?php if ($view): ?>
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h6 style="font-family:var(--font-display);margin-bottom:.25rem"><?= htmlspecialchars($view['subject']) ?></h6>
          <div style="font-size:.875rem;color:var(--text-medium)">From: <strong><?= htmlspecialchars($view['name']) ?></strong> &lt;<?= htmlspecialchars($view['email']) ?>&gt;</div>
          <?php if ($view['phone']): ?><div style="font-size:.8rem;color:var(--text-light)"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($view['phone']) ?></div><?php endif; ?>
          <div style="font-size:.75rem;color:var(--text-light)"><?= formatDateTime($view['created_at']) ?></div>
        </div>
        <div class="d-flex gap-2">
          <a href="mailto:<?= htmlspecialchars($view['email']) ?>?subject=Re: <?= urlencode($view['subject']) ?>" class="btn btn-primary btn-sm"><i class="fas fa-reply me-1"></i>Reply</a>
          <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $view['id'] ?>">
            <button class="btn btn-outline-danger btn-sm" data-confirm="Delete this message?"><i class="fas fa-trash"></i></button></form>
        </div>
      </div>
      <hr style="border-color:var(--border)">
      <div style="font-size:.9rem;color:var(--text-medium);line-height:1.8;white-space:pre-wrap"><?= htmlspecialchars($view['message']) ?></div>
    </div>
    <?php else: ?>
    <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:3rem;text-align:center">
      <i class="fas fa-envelope-open" style="font-size:3rem;color:var(--border)"></i>
      <p style="color:var(--text-light);margin-top:1rem">Select a message to read</p>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
