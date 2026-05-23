<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Customers';

$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');
    $id  = sanitizeInt($_POST['user_id'] ?? 0);
    if ($act === 'set_status' && $id) {
        $status = sanitize($_POST['status'] ?? 'active');
        if (in_array($status, ['active','banned','suspended'])) {
            db()->prepare("UPDATE users SET status=? WHERE id=?")->execute([$status, $id]);
            $msg = 'Customer status updated.';
        }
    }
}

$search = sanitize($_GET['search'] ?? '');
$page   = max(1, sanitizeInt($_GET['page'] ?? 1)); $perPage = 20;
$where  = $search ? "WHERE u.name LIKE ? OR u.email LIKE ?" : '';
$params = $search ? ["%$search%", "%$search%"] : [];

$countStmt = db()->prepare("SELECT COUNT(*) FROM users u $where"); $countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pag = paginate($total, $perPage, $page);

$stmt = db()->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS order_count, (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id=u.id AND payment_status='paid') AS total_spent FROM users u $where ORDER BY u.created_at DESC LIMIT $perPage OFFSET {$pag['offset']}");
$stmt->execute($params);
$customers = $stmt->fetchAll();

// View single customer
$viewCustomer = null; $custOrders = [];
if (isset($_GET['id'])) {
    $cs = db()->prepare("SELECT * FROM users WHERE id=?"); $cs->execute([sanitizeInt($_GET['id'])]); $viewCustomer = $cs->fetch();
    if ($viewCustomer) { $co = db()->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 10"); $co->execute([$viewCustomer['id']]); $custOrders = $co->fetchAll(); }
}

$statusColors = ['active'=>'success','banned'=>'danger','suspended'=>'warning'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Customers</h4></div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<?php if ($viewCustomer): ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 style="font-family:var(--font-display)"><?= htmlspecialchars($viewCustomer['name']) ?></h5>
  <a href="<?= APP_URL ?>/admin/customers.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row g-4">
  <div class="col-md-4">
    <div class="stat-card mb-3">
      <div class="text-center mb-3">
        <div style="width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.8rem;color:white;font-weight:700"><?= strtoupper(substr($viewCustomer['name'],0,1)) ?></div>
        <h6 style="margin-top:.75rem;font-family:var(--font-display)"><?= htmlspecialchars($viewCustomer['name']) ?></h6>
        <small style="color:var(--text-light)"><?= htmlspecialchars($viewCustomer['email']) ?></small><br>
        <span class="badge bg-<?= $statusColors[$viewCustomer['status']]??'secondary' ?> mt-1"><?= ucfirst($viewCustomer['status']) ?></span>
      </div>
      <div style="font-size:.85rem;color:var(--text-medium)">
        <div class="d-flex justify-content-between mb-1"><span>Phone</span><span><?= htmlspecialchars($viewCustomer['phone']??'—') ?></span></div>
        <div class="d-flex justify-content-between mb-1"><span>Joined</span><span><?= formatDate($viewCustomer['created_at']) ?></span></div>
        <div class="d-flex justify-content-between"><span>Last Login</span><span><?= $viewCustomer['last_login'] ? formatDate($viewCustomer['last_login']) : 'Never' ?></span></div>
      </div>
    </div>
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1rem">Change Status</h6>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="set_status">
        <input type="hidden" name="user_id" value="<?= $viewCustomer['id'] ?>">
        <select class="form-select form-select-sm mb-2" name="status">
          <?php foreach (['active','suspended','banned'] as $s): ?><option value="<?= $s ?>" <?= $viewCustomer['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
      </form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1rem">Order History</h6>
      <?php if (empty($custOrders)): ?><p style="color:var(--text-light);font-size:.875rem">No orders yet.</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Order #</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($custOrders as $o): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/orders.php?id=<?= $o['id'] ?>" style="color:var(--primary);font-weight:600"><?= htmlspecialchars($o['order_number']) ?></a></td>
            <td style="font-weight:700"><?= formatPrice($o['total_amount']) ?></td>
            <td><span class="badge bg-<?= ['pending'=>'warning','processing'=>'info','delivered'=>'success','cancelled'=>'danger'][$o['order_status']]??'secondary' ?>"><?= ucfirst($o['order_status']) ?></span></td>
            <td style="font-size:.8rem;color:var(--text-light)"><?= formatDate($o['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php else: ?>
<div class="stat-card">
  <form method="GET" class="d-flex gap-2 mb-4">
    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email…" style="max-width:300px">
    <button class="btn btn-primary"><i class="fas fa-search me-1"></i>Search</button>
    <?php if ($search): ?><a href="<?= APP_URL ?>/admin/customers.php" class="btn btn-outline-primary">Clear</a><?php endif; ?>
  </form>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Customer</th><th>Phone</th><th>Orders</th><th>Spent</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($customers as $c): ?>
      <tr>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:.85rem;flex-shrink:0"><?= strtoupper(substr($c['name'],0,1)) ?></div>
            <div><div style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($c['name']) ?></div><div style="font-size:.75rem;color:var(--text-light)"><?= htmlspecialchars($c['email']) ?></div></div>
          </div>
        </td>
        <td style="font-size:.875rem"><?= htmlspecialchars($c['phone']??'—') ?></td>
        <td><span class="badge bg-secondary"><?= $c['order_count'] ?></span></td>
        <td style="font-weight:700;color:var(--primary)"><?= formatPrice($c['total_spent']) ?></td>
        <td><span class="badge bg-<?= $statusColors[$c['status']]??'secondary' ?>"><?= ucfirst($c['status']) ?></span></td>
        <td style="font-size:.8rem;color:var(--text-light)"><?= formatDate($c['created_at']) ?></td>
        <td><a href="<?= APP_URL ?>/admin/customers.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($customers)): ?><tr><td colspan="7" class="text-center py-4" style="color:var(--text-light)">No customers found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pag['total_pages'] > 1): ?>
  <div class="d-flex justify-content-center mt-4">
    <nav><ul class="pagination gap-1 mb-0">
      <?php for ($i=1;$i<=$pag['total_pages'];$i++): ?><li class="page-item <?= $i===$pag['current_page']?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?><?= $search?"&search=".urlencode($search):'' ?>"><?= $i ?></a></li><?php endfor; ?>
    </ul></nav>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
