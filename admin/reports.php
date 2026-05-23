<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Reports';

$range = sanitize($_GET['range'] ?? '30');
$days  = in_array($range, ['7','30','90','365']) ? (int)$range : 30;

// Revenue stats
$revenue    = (float) db()->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='paid' AND created_at>=DATE_SUB(NOW(),INTERVAL $days DAY)")->fetchColumn();
$orderCount = (int)   db()->query("SELECT COUNT(*) FROM orders WHERE created_at>=DATE_SUB(NOW(),INTERVAL $days DAY)")->fetchColumn();
$newUsers   = (int)   db()->query("SELECT COUNT(*) FROM users WHERE created_at>=DATE_SUB(NOW(),INTERVAL $days DAY)")->fetchColumn();
$avgOrder   = $orderCount > 0 ? $revenue / $orderCount : 0;

// Daily revenue chart data
$daily = db()->query("SELECT DATE(created_at) AS day, SUM(total_amount) AS revenue FROM orders WHERE payment_status='paid' AND created_at>=DATE_SUB(NOW(),INTERVAL $days DAY) GROUP BY DATE(created_at) ORDER BY day ASC")->fetchAll();

// Top products
$topProds = db()->query("SELECT p.name, SUM(oi.quantity) AS qty, SUM(oi.total_price) AS revenue FROM order_items oi JOIN products p ON oi.product_id=p.id JOIN orders o ON oi.order_id=o.id WHERE o.created_at>=DATE_SUB(NOW(),INTERVAL $days DAY) GROUP BY oi.product_id ORDER BY revenue DESC LIMIT 10")->fetchAll();

// Orders by status
$byStatus = db()->query("SELECT order_status, COUNT(*) AS cnt FROM orders WHERE created_at>=DATE_SUB(NOW(),INTERVAL $days DAY) GROUP BY order_status")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h4>Sales Reports</h4>
  <div class="d-flex gap-2">
    <?php foreach (['7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','365'=>'1 Year'] as $v=>$l): ?>
    <a href="?range=<?= $v ?>" class="btn btn-sm btn-<?= $range===$v?'primary':'outline-primary' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label'=>'Revenue','value'=>formatPrice($revenue),'icon'=>'wallet','color'=>'#22c55e'],
    ['label'=>'Orders','value'=>number_format($orderCount),'icon'=>'box','color'=>'var(--primary)'],
    ['label'=>'Avg. Order','value'=>formatPrice($avgOrder),'icon'=>'chart-line','color'=>'#3b82f6'],
    ['label'=>'New Customers','value'=>number_format($newUsers),'icon'=>'user-plus','color'=>'var(--gold)'],
  ];
  foreach ($cards as $c): ?>
  <div class="col-6 col-lg-3">
    <div class="stat-card text-center">
      <div style="width:44px;height:44px;background:<?= $c['color'] ?>1a;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem">
        <i class="fas fa-<?= $c['icon'] ?>" style="color:<?= $c['color'] ?>"></i>
      </div>
      <div class="stat-value"><?= $c['value'] ?></div>
      <div class="stat-label"><?= $c['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
  <!-- Revenue Chart -->
  <div class="col-lg-8">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem">Daily Revenue</h6>
      <canvas id="dailyChart" height="100"></canvas>
    </div>
  </div>
  <!-- Orders by Status Pie -->
  <div class="col-lg-4">
    <div class="stat-card">
      <h6 style="font-family:var(--font-display);margin-bottom:1.25rem">Orders by Status</h6>
      <canvas id="statusChart" height="200"></canvas>
    </div>
  </div>
</div>

<!-- Top Products -->
<div class="stat-card">
  <h6 style="font-family:var(--font-display);margin-bottom:1rem">Top Products by Revenue</h6>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Product</th><th>Units Sold</th><th>Revenue</th><th>Share</th></tr></thead>
      <tbody>
      <?php $totalProdRev = array_sum(array_column($topProds,'revenue')) ?: 1;
      foreach ($topProds as $i => $p): ?>
      <tr>
        <td style="color:var(--text-light);font-weight:700"><?= $i+1 ?></td>
        <td style="font-weight:600"><?= htmlspecialchars($p['name']) ?></td>
        <td><?= number_format($p['qty']) ?></td>
        <td style="font-weight:700;color:var(--primary)"><?= formatPrice($p['revenue']) ?></td>
        <td style="min-width:120px">
          <div class="d-flex align-items-center gap-2">
            <div style="flex:1;height:6px;background:var(--cream-dark);border-radius:3px;overflow:hidden"><div style="height:100%;width:<?= round(($p['revenue']/$totalProdRev)*100) ?>%;background:var(--primary);border-radius:3px"></div></div>
            <span style="font-size:.75rem;color:var(--text-light)"><?= round(($p['revenue']/$totalProdRev)*100) ?>%</span>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($topProds)): ?><tr><td colspan="5" class="text-center py-4" style="color:var(--text-light)">No sales data for this period.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$dailyLabels = json_encode(array_column($daily,'day'));
$dailyValues = json_encode(array_map('floatval', array_column($daily,'revenue')));
$statusLabels = json_encode(array_map(fn($r) => ucfirst($r['order_status']), $byStatus));
$statusValues = json_encode(array_column($byStatus,'cnt'));
$extraScripts = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('dailyChart'), {
  type:'bar',
  data:{ labels:{$dailyLabels}, datasets:[{ label:'Revenue', data:{$dailyValues}, backgroundColor:'rgba(139,69,19,0.7)', borderRadius:6 }] },
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false},ticks:{font:{size:10}}}, y:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:10},callback:v=>'KSh '+v.toLocaleString()}} } }
});
new Chart(document.getElementById('statusChart'), {
  type:'doughnut',
  data:{ labels:{$statusLabels}, datasets:[{ data:{$statusValues}, backgroundColor:['#f59e0b','#3b82f6','#8B4513','#22c55e','#ef4444'], borderWidth:0 }] },
  options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{font:{size:11}}}} }
});
</script>
JS;
include __DIR__ . '/includes/footer.php'; ?>
