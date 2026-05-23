<?php
// admin/newsletter.php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Newsletter';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    if (sanitize($_POST['action']??'') === 'delete') {
        db()->prepare("DELETE FROM newsletter_subscribers WHERE id=?")->execute([sanitizeInt($_POST['id']??0)]);
    } elseif (sanitize($_POST['action']??'') === 'toggle') {
        db()->prepare("UPDATE newsletter_subscribers SET status=IF(status=1,0,1) WHERE id=?")->execute([sanitizeInt($_POST['id']??0)]);
    }
}

$total  = (int) db()->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();
$active = (int) db()->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status=1")->fetchColumn();
$page   = max(1, sanitizeInt($_GET['page']??1)); $perPage = 30;
$pag    = paginate($total, $perPage, $page);
$subs   = db()->prepare("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC LIMIT $perPage OFFSET {$pag['offset']}");
$subs->execute(); $subs = $subs->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h4>Newsletter Subscribers</h4>
  <div class="d-flex gap-3">
    <div class="stat-card py-2 px-3 d-flex gap-3">
      <div class="text-center"><div style="font-size:1.2rem;font-weight:700;color:var(--primary)"><?= $active ?></div><div style="font-size:.7rem;color:var(--text-light)">Active</div></div>
      <div class="vr"></div>
      <div class="text-center"><div style="font-size:1.2rem;font-weight:700"><?= $total ?></div><div style="font-size:.7rem;color:var(--text-light)">Total</div></div>
    </div>
    <a href="?export=1" class="btn btn-outline-primary btn-sm"><i class="fas fa-download me-1"></i>Export CSV</a>
  </div>
</div>

<?php
// CSV Export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="subscribers_'.date('Y-m-d').'.csv"');
    $all = db()->query("SELECT email,name,status,subscribed_at FROM newsletter_subscribers WHERE status=1")->fetchAll();
    echo "Email,Name,Status,Date\n";
    foreach ($all as $r) echo '"'.addslashes($r['email']).'","'.addslashes($r['name']??'').'","'.($r['status']?'Active':'Inactive').'","'.$r['subscribed_at']."\"\n";
    exit;
}
?>

<div class="stat-card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Email</th><th>Subscribed</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($subs as $sub): ?>
      <tr>
        <td style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($sub['email']) ?></td>
        <td style="font-size:.8rem;color:var(--text-light)"><?= formatDate($sub['subscribed_at']) ?></td>
        <td>
          <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $sub['id'] ?>">
            <button class="badge border-0 bg-<?= $sub['status']?'success':'secondary' ?>" style="cursor:pointer"><?= $sub['status']?'Active':'Inactive' ?></button>
          </form>
        </td>
        <td>
          <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $sub['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" data-confirm="Remove <?= htmlspecialchars(addslashes($sub['email'])) ?>?"><i class="fas fa-trash"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
