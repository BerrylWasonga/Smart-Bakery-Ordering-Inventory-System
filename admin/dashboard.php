<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Dashboard';

// Stats
$totalRevenue  = (float) db()->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='paid'")->fetchColumn();
$totalOrders   = (int) db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCustomers= (int) db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = (int) db()->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
$pendingOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn();
$lowStockItems = db()->query("SELECT id,name,stock_quantity FROM products WHERE stock_quantity <= low_stock_threshold AND status='active' ORDER BY stock_quantity ASC LIMIT 5")->fetchAll();
$recentOrders  = db()->query("SELECT o.*,u.name AS customer FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();

// Monthly revenue (last 6 months)
$monthlyData = db()->query("SELECT DATE_FORMAT(created_at,'%b') AS month, COALESCE(SUM(total_amount),0) AS revenue FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at ASC")->fetchAll();

// Top products
$topProducts = db()->query("SELECT p.name, SUM(oi.quantity) AS total_sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/includes/header.php';
$statusColors = ['pending'=>'warning','processing'=>'info','ready'=>'primary','delivered'=>'success','cancelled'=>'danger'];
?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
  <?php
  $stats = [
    ['label'=>'Total Revenue','value'=>formatPrice($totalRevenue),'icon'=>'wallet','color'=>'#22c55e','bg'=>'rgba(34,197,94,.1)','trend'=>'+12%'],
    ['label'=>'Total Orders','value'=>number_format($totalOrders),'icon'=>'box','color'=>'var(--primary)','bg'=>'rgba(139,69,19,.1)','trend'=>'+8%'],
    ['label'=>'Customers','value'=>number_format($totalCustomers),'icon'=>'users','color'=>'#3b82f6','bg'=>'rgba(59,130,246,.1)','trend'=>'+15%'],
    ['label'=>'Active Products','value'=>number_format($totalProducts),'icon'=>'birthday-cake','color'=>'var(--gold)','bg'=>'rgba(200,150,30,.1)','trend'=>''],
  ];
  foreach ($stats as $s): ?>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-label"><?= $s['label'] ?></div>
          <div class="stat-value mt-1"><?= $s['value'] ?></div>
          <?php if ($s['trend']): ?>
          <div style="font-size:.75rem;color:#22c55e;margin-top:.25rem"><i class="fas fa-arrow-up me-1"></i><?= $s['trend'] ?> this month</div>
          <?php endif; ?>
        </div>
        <div style="width:48px;height:48px;background:<?= $s['bg'] ?>;border-radius:12px;display:flex;align-items:center;justify-content:center">
          <i class="fas fa-<?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:1.2rem"></i>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if ($pendingOrders > 0): ?>
<div class="alert d-flex align-items-center gap-3 mb-4" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:var(--radius-sm);color:var(--text-dark)">
  <i class="fas fa-clock" style="color:#f59e0b;font-size:1.2rem"></i>
  <div><strong><?= $pendingOrders ?> pending orders</strong> need your attention. <a href="<?= APP_URL ?>/admin/orders.php?status=pending" style="color:var(--primary)">Review now →</a></div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <!-- Revenue Chart -->
  <div class="col-lg-8">
    <div class="stat-card h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 style="font-family:var(--font-display);margin:0">Revenue Overview</h6>
        <span style="font-size:.75rem;color:var(--text-light)">Last 6 months</span>
      </div>
      <canvas id="revenueChart" height="100"></canvas>
    </div>
  </div>
  <!-- Top Products -->
  <div class="col-lg-4">
    <div class="stat-card h-100">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem">Top Selling Products</h6>
      <?php if (empty($topProducts)): ?>
      <p style="color:var(--text-light);font-size:.875rem">No sales data yet.</p>
      <?php else: ?>
      <?php
      $maxSold = $topProducts[0]['total_sold'] ?? 1;
      foreach ($topProducts as $i => $tp): ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between mb-1">
          <span style="font-size:.825rem;font-weight:600"><?= htmlspecialchars(truncate($tp['name'],25)) ?></span>
          <span style="font-size:.8rem;color:var(--text-light)"><?= $tp['total_sold'] ?> sold</span>
        </div>
        <div style="height:6px;background:var(--cream-dark);border-radius:3px;overflow:hidden">
          <div style="height:100%;width:<?= round(($tp['total_sold']/$maxSold)*100) ?>%;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:3px"></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent Orders -->
  <div class="col-lg-8">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 style="font-family:var(--font-display);margin:0">Recent Orders</h6>
        <a href="<?= APP_URL ?>/admin/orders.php" style="font-size:.8rem;color:var(--primary)">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr>
            <th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th>
          </tr></thead>
          <tbody>
          <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/orders.php?id=<?= $o['id'] ?>" style="font-weight:600;color:var(--primary)"><?= htmlspecialchars($o['order_number']) ?></a></td>
            <td style="font-size:.875rem"><?= htmlspecialchars($o['customer'] ?? $o['billing_name']) ?></td>
            <td style="font-weight:700"><?= formatPrice($o['total_amount']) ?></td>
            <td><span class="badge bg-<?= $o['payment_status']==='paid'?'success':($o['payment_status']==='failed'?'danger':'warning') ?>" style="font-size:.7rem"><?= ucfirst($o['payment_status']) ?></span></td>
            <td><span class="badge bg-<?= $statusColors[$o['order_status']]??'secondary' ?>" style="font-size:.7rem"><?= ucfirst($o['order_status']) ?></span></td>
            <td style="font-size:.8rem;color:var(--text-light)"><?= formatDate($o['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Low Stock -->
  <div class="col-lg-4">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 style="font-family:var(--font-display);margin:0"><i class="fas fa-exclamation-triangle me-2" style="color:#f59e0b"></i>Low Stock Alert</h6>
        <a href="<?= APP_URL ?>/admin/inventory.php" style="font-size:.8rem;color:var(--primary)">Manage</a>
      </div>
      <?php if (empty($lowStockItems)): ?>
      <div class="text-center py-3">
        <i class="fas fa-check-circle" style="font-size:2rem;color:#22c55e"></i>
        <p style="color:var(--text-light);font-size:.875rem;margin-top:.5rem">All products have healthy stock levels.</p>
      </div>
      <?php else: ?>
      <?php foreach ($lowStockItems as $p): ?>
      <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom:1px solid var(--border)">
        <div>
          <div style="font-size:.85rem;font-weight:600"><?= htmlspecialchars(truncate($p['name'],28)) ?></div>
          <div style="font-size:.75rem;color:var(--text-light)">SKU tracking</div>
        </div>
        <span class="badge bg-<?= $p['stock_quantity'] <= 2 ? 'danger' : 'warning' ?> text-dark" style="font-size:.75rem">
          <?= $p['stock_quantity'] ?> left
        </span>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$labels  = json_encode(array_column($monthlyData, 'month') ?: ['Jan','Feb','Mar','Apr','May','Jun']);
$values  = json_encode(array_map('floatval', array_column($monthlyData, 'revenue')) ?: [0,0,0,0,0,0]);
$extraScripts = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: {$labels},
    datasets: [{
      label: 'Revenue (KSh)',
      data: {$values},
      borderColor: '#8B4513',
      backgroundColor: 'rgba(139,69,19,0.08)',
      borderWidth: 2.5,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#8B4513',
      pointRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 11 } } },
      y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 }, callback: v => 'KSh ' + v.toLocaleString() } }
    }
  }
});
</script>
JS;

include __DIR__ . '/includes/footer.php';
