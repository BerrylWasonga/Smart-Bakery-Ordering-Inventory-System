<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Orders';

$msg = ''; $msgType = 'success';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $act = sanitize($_POST['action'] ?? '');
    $id  = sanitizeInt($_POST['order_id'] ?? 0);

    if ($act === 'update_status' && $id) {
        $newStatus = sanitize($_POST['order_status'] ?? '');
        $adminNote = sanitize($_POST['admin_note'] ?? '');
        $validStatuses = ['pending','processing','ready','delivered','cancelled'];
        if (in_array($newStatus, $validStatuses)) {
            $delivered = $newStatus === 'delivered' ? ', delivered_at = NOW()' : '';
            db()->prepare("UPDATE orders SET order_status = ?, admin_notes = CONCAT(IFNULL(admin_notes,''), ?) $delivered WHERE id = ?")
                ->execute([$newStatus, $adminNote ? "\n[".date('d M Y')."] $adminNote" : '', $id]);

            // Notify user
            $order = db()->prepare("SELECT user_id, order_number FROM orders WHERE id = ?"); $order->execute([$id]); $order = $order->fetch();
            if ($order && $order['user_id']) {
                db()->prepare("INSERT INTO notifications (user_id,type,title,message) VALUES (?,?,?,?)")
                    ->execute([$order['user_id'], 'order', 'Order Update', "Your order {$order['order_number']} is now: " . ucfirst($newStatus)]);
            }
            logActivity('admin', $_SESSION['admin_id'], 'order_status_update', "Order #$id → $newStatus");
            $msg = 'Order status updated.';
        }
    } elseif ($act === 'update_payment' && $id) {
        $payStatus = sanitize($_POST['payment_status'] ?? '');
        if (in_array($payStatus, ['pending','paid','failed','refunded'])) {
            db()->prepare("UPDATE orders SET payment_status=? WHERE id=?")->execute([$payStatus, $id]);
            $msg = 'Payment status updated.';
        }
    }
}

// Filters
$statusFilter = sanitize($_GET['status'] ?? '');
$search       = sanitize($_GET['search'] ?? '');
$page         = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage      = 15;

$where = []; $params = [];
if ($statusFilter) { $where[] = "o.order_status = ?"; $params[] = $statusFilter; }
if ($search) { $where[] = "(o.order_number LIKE ? OR o.billing_name LIKE ? OR o.billing_email LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = (int) db()->prepare("SELECT COUNT(*) FROM orders o $whereSQL")->execute($params) ? db()->query("SELECT COUNT(*) FROM orders o " . ($where ? 'WHERE '.implode(' AND ',$where) : '') . " LIMIT 1")->rowCount() : 0;
// Simple count
$countStmt = db()->prepare("SELECT COUNT(*) FROM orders o $whereSQL"); $countStmt->execute($params); $total = (int) $countStmt->fetchColumn();
$pag = paginate($total, $perPage, $page);

$orders = db()->prepare("SELECT o.*, u.name AS customer_name FROM orders o LEFT JOIN users u ON o.user_id=u.id $whereSQL ORDER BY o.created_at DESC LIMIT $perPage OFFSET {$pag['offset']}");
$orders->execute($params);
$orders = $orders->fetchAll();

// Detail view
$viewOrder = null; $viewItems = [];
if (isset($_GET['id'])) {
    $viewOrder = db()->prepare("SELECT o.*,u.name AS cname,u.email AS cemail FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=?");
    $viewOrder->execute([sanitizeInt($_GET['id'])]);
    $viewOrder = $viewOrder->fetch();
    $viewItems = db()->prepare("SELECT * FROM order_items WHERE order_id=?")->execute([$viewOrder['id']??0]) ? db()->prepare("SELECT * FROM order_items WHERE order_id=?") : [];
    if ($viewOrder) { $vi = db()->prepare("SELECT * FROM order_items WHERE order_id=?"); $vi->execute([$viewOrder['id']]); $viewItems = $vi->fetchAll(); }
}

$statusColors = ['pending'=>'warning','processing'=>'info','ready'=>'primary','delivered'=>'success','cancelled'=>'danger'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h4>Orders</h4></div>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($viewOrder): ?>
<!-- ── Order Detail ── -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 style="font-family:var(--font-display)">Order <?= htmlspecialchars($viewOrder['order_number']) ?></h5>
  <a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Orders</a>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="stat-card mb-4">
      <h6 style="font-family:var(--font-display);margin-bottom:1rem">Items</h6>
      <?php foreach ($viewItems as $item): ?>
      <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--border)">
        <div style="width:56px;height:56px;border-radius:8px;background:var(--cream-dark);overflow:hidden;flex-shrink:0">
          <?php if ($item['product_image']): ?><img src="<?= getImageUrl($item['product_image']) ?>" style="width:100%;height:100%;object-fit:cover"><?php endif; ?>
        </div>
        <div class="flex-fill">
          <div style="font-weight:600;font-size:.9rem"><?= htmlspecialchars($item['product_name']) ?></div>
          <div style="font-size:.8rem;color:var(--text-light)">SKU: <?= htmlspecialchars($item['product_sku']) ?> | Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['unit_price']) ?></div>
        </div>
        <div style="font-weight:700;color:var(--primary)"><?= formatPrice($item['total_price']) ?></div>
      </div>
      <?php endforeach; ?>
      <div class="d-flex justify-content-between small"><span style="color:var(--text-medium)">Subtotal</span><span><?= formatPrice($viewOrder['subtotal']) ?></span></div>
      <?php if ($viewOrder['discount_amount']>0): ?><div class="d-flex justify-content-between small"><span style="color:var(--text-medium)">Discount</span><span class="text-success">-<?= formatPrice($viewOrder['discount_amount']) ?></span></div><?php endif; ?>
      <div class="d-flex justify-content-between small"><span style="color:var(--text-medium)">Shipping</span><span><?= formatPrice($viewOrder['shipping_cost']) ?></span></div>
      <div class="d-flex justify-content-between small"><span style="color:var(--text-medium)">Tax</span><span><?= formatPrice($viewOrder['tax_amount']) ?></span></div>
      <hr style="border-color:var(--border)">
      <div class="d-flex justify-content-between"><strong>Total</strong><strong style="color:var(--primary);font-size:1.1rem"><?= formatPrice($viewOrder['total_amount']) ?></strong></div>
    </div>
  </div>

  <div class="col-lg-4">
    <!-- Update Status -->
    <div class="stat-card mb-3">
      <h6 style="font-family:var(--font-display);margin-bottom:1rem">Update Order</h6>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <div class="mb-2">
          <label class="form-label small fw-bold">Order Status</label>
          <select class="form-select form-select-sm" name="order_status">
            <?php foreach (['pending','processing','ready','delivered','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $viewOrder['order_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small fw-bold">Admin Note (optional)</label>
          <input type="text" class="form-control form-control-sm" name="admin_note" placeholder="Internal note…">
        </div>
        <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
      </form>
      <hr style="border-color:var(--border)">
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="update_payment">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <div class="mb-2">
          <label class="form-label small fw-bold">Payment Status</label>
          <select class="form-select form-select-sm" name="payment_status">
            <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
            <option value="<?= $s ?>" <?= $viewOrder['payment_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-outline-primary btn-sm w-100">Update Payment</button>
      </form>
    </div>

    <!-- Customer & Address -->
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1rem">Customer</h6>
      <div style="font-size:.875rem;color:var(--text-medium)">
        <strong><?= htmlspecialchars($viewOrder['billing_name']) ?></strong><br>
        <?= htmlspecialchars($viewOrder['billing_email']) ?><br>
        <?= htmlspecialchars($viewOrder['billing_phone']) ?><br><br>
        <i class="fas fa-map-marker-alt me-1" style="color:var(--primary)"></i><?= htmlspecialchars($viewOrder['billing_address']) ?>, <?= htmlspecialchars($viewOrder['billing_city']) ?><br><br>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge bg-<?= $statusColors[$viewOrder['order_status']]??'secondary' ?>"><?= ucfirst($viewOrder['order_status']) ?></span>
          <span class="badge bg-<?= $viewOrder['payment_status']==='paid'?'success':($viewOrder['payment_status']==='failed'?'danger':'warning') ?>"><?= ucfirst($viewOrder['payment_status']) ?></span>
          <span class="badge bg-secondary"><?= strtoupper($viewOrder['payment_method']) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ── Orders List ── -->
<div class="stat-card">
  <!-- Filters -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <form method="GET" class="d-flex gap-2 flex-wrap w-100">
      <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search order # or customer…" style="max-width:250px">
      <select class="form-select" name="status" style="max-width:180px" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach (['pending','processing','ready','delivered','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Search</button>
      <?php if ($search||$statusFilter): ?><a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-outline-primary">Clear</a><?php endif; ?>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr>
        <th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="<?= APP_URL ?>/admin/orders.php?id=<?= $o['id'] ?>" style="font-weight:700;color:var(--primary)"><?= htmlspecialchars($o['order_number']) ?></a></td>
        <td>
          <div style="font-size:.875rem;font-weight:600"><?= htmlspecialchars($o['customer_name'] ?? $o['billing_name']) ?></div>
          <div style="font-size:.75rem;color:var(--text-light)"><?= htmlspecialchars($o['billing_email']) ?></div>
        </td>
        <td style="font-weight:700;color:var(--primary)"><?= formatPrice($o['total_amount']) ?></td>
        <td><span class="badge bg-<?= $o['payment_status']==='paid'?'success':($o['payment_status']==='failed'?'danger':'warning') ?> text-<?= $o['payment_status']==='paid'?'white':'dark' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
        <td><span class="badge bg-<?= $statusColors[$o['order_status']]??'secondary' ?>"><?= ucfirst($o['order_status']) ?></span></td>
        <td style="font-size:.8rem;color:var(--text-light)"><?= formatDateTime($o['created_at']) ?></td>
        <td><a href="<?= APP_URL ?>/admin/orders.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>View</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
      <tr><td colspan="7" class="text-center py-4" style="color:var(--text-light)">No orders found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pag['total_pages'] > 1): ?>
  <div class="d-flex justify-content-center mt-4">
    <nav><ul class="pagination gap-1 mb-0">
      <?php if ($pag['has_prev']): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$pag['current_page']-1])) ?>">‹</a></li><?php endif; ?>
      <?php for ($i=1;$i<=$pag['total_pages'];$i++): ?><li class="page-item <?= $i===$pag['current_page']?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a></li><?php endfor; ?>
      <?php if ($pag['has_next']): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$pag['current_page']+1])) ?>">›</a></li><?php endif; ?>
    </ul></nav>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
