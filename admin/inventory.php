<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Inventory';

$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $productId = sanitizeInt($_POST['product_id'] ?? 0);
    $action    = sanitize($_POST['stock_action'] ?? 'restock');
    $qty       = sanitizeInt($_POST['quantity'] ?? 0);
    $note      = sanitize($_POST['note'] ?? '');

    if ($productId && $qty > 0) {
        $prod = db()->prepare("SELECT stock_quantity FROM products WHERE id=?"); $prod->execute([$productId]); $prod = $prod->fetch();
        if ($prod) {
            $before = $prod['stock_quantity'];
            $change = $action === 'restock' ? $qty : -$qty;
            $after  = max(0, $before + $change);
            db()->prepare("UPDATE products SET stock_quantity=? WHERE id=?")->execute([$after, $productId]);
            db()->prepare("INSERT INTO inventory_logs (product_id,admin_id,action,quantity_change,quantity_before,quantity_after,note) VALUES (?,?,?,?,?,?,?)")
                ->execute([$productId, $_SESSION['admin_id'], $action, $change, $before, $after, $note]);
            $msg = "Stock updated from $before to $after.";
        }
    }
}

// Low stock products
$lowStock = db()->query("SELECT * FROM products WHERE stock_quantity <= low_stock_threshold AND status='active' ORDER BY stock_quantity ASC")->fetchAll();

// All products
$products = db()->query("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.status='active' ORDER BY p.name")->fetchAll();

// Recent logs
$logs = db()->query("SELECT il.*, p.name AS product_name, a.name AS admin_name FROM inventory_logs il JOIN products p ON il.product_id=p.id LEFT JOIN admins a ON il.admin_id=a.id ORDER BY il.created_at DESC LIMIT 30")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Inventory Management</h4></div>
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<!-- Low Stock Alert -->
<?php if (!empty($lowStock)): ?>
<div class="alert d-flex align-items-center gap-2 mb-4" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);color:var(--text-dark)">
  <i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:1.2rem"></i>
  <strong><?= count($lowStock) ?> products</strong> are below their low-stock threshold!
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Adjust Stock Form -->
  <div class="col-lg-4">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem">Adjust Stock</h6>
      <form method="POST">
        <?= csrfField() ?>
        <div class="mb-3"><label class="form-label fw-bold small">Product *</label>
          <select class="form-select" name="product_id" required>
            <option value="">Select product…</option>
            <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['stock_quantity'] ?> in stock)</option>
            <?php endforeach; ?>
          </select></div>
        <div class="mb-3"><label class="form-label fw-bold small">Action</label>
          <select class="form-select" name="stock_action">
            <option value="restock">Add Stock (Restock)</option>
            <option value="adjustment">Remove (Adjustment)</option>
            <option value="return">Add (Customer Return)</option>
          </select></div>
        <div class="mb-3"><label class="form-label fw-bold small">Quantity *</label>
          <input type="number" class="form-control" name="quantity" min="1" required></div>
        <div class="mb-3"><label class="form-label fw-bold small">Note</label>
          <input type="text" class="form-control" name="note" placeholder="e.g. Weekly restock from supplier"></div>
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-boxes me-2"></i>Update Stock</button>
      </form>
    </div>
  </div>

  <!-- Stock Table -->
  <div class="col-lg-8">
    <div class="stat-card mb-4">
      <h6 style="font-family:var(--font-display);margin-bottom:1rem">Current Stock Levels</h6>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Product</th><th>Category</th><th>In Stock</th><th>Threshold</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($p['name']) ?></td>
            <td style="font-size:.8rem;color:var(--text-light)"><?= htmlspecialchars($p['cat_name']) ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div style="flex:1;max-width:80px;height:6px;background:var(--cream-dark);border-radius:3px;overflow:hidden">
                  <?php $pct = $p['low_stock_threshold']>0 ? min(100,round(($p['stock_quantity']/$p['low_stock_threshold'])*50)) : 100; ?>
                  <div style="height:100%;width:<?= $pct ?>%;background:<?= $p['stock_quantity']<=0?'#ef4444':($p['stock_quantity']<=$p['low_stock_threshold']?'#f59e0b':'#22c55e') ?>;border-radius:3px"></div>
                </div>
                <strong style="font-size:.875rem"><?= $p['stock_quantity'] ?></strong>
              </div>
            </td>
            <td style="font-size:.875rem;color:var(--text-light)"><?= $p['low_stock_threshold'] ?></td>
            <td>
              <span class="badge bg-<?= $p['stock_quantity']<=0?'danger':($p['stock_quantity']<=$p['low_stock_threshold']?'warning text-dark':'success') ?>">
                <?= $p['stock_quantity']<=0?'Out of Stock':($p['stock_quantity']<=$p['low_stock_threshold']?'Low Stock':'In Stock') ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Logs -->
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1rem">Recent Inventory Logs</h6>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Product</th><th>Action</th><th>Change</th><th>Before → After</th><th>By</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($logs as $l): ?>
          <tr>
            <td style="font-size:.8rem;font-weight:600"><?= htmlspecialchars(truncate($l['product_name'],25)) ?></td>
            <td><span class="badge bg-<?= $l['action']==='restock'?'success':($l['action']==='sale'?'primary':'secondary') ?>" style="font-size:.7rem"><?= ucfirst($l['action']) ?></span></td>
            <td style="font-weight:700;color:<?= $l['quantity_change']>=0?'#22c55e':'#ef4444' ?>"><?= ($l['quantity_change']>=0?'+':'').$l['quantity_change'] ?></td>
            <td style="font-size:.8rem"><?= $l['quantity_before'] ?> → <?= $l['quantity_after'] ?></td>
            <td style="font-size:.8rem;color:var(--text-light)"><?= htmlspecialchars($l['admin_name']??'System') ?></td>
            <td style="font-size:.75rem;color:var(--text-light)"><?= timeAgo($l['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
