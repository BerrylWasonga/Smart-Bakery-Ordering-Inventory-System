<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Settings';

$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $fields = ['site_name','site_tagline','site_email','site_phone','site_address','currency','currency_symbol',
               'tax_rate','shipping_cost','free_shipping_min','hero_title','hero_subtitle','working_hours',
               'facebook_url','instagram_url','twitter_url','mpesa_paybill','whatsapp_number',
               'meta_description','primary_color','accent_color','maintenance_mode','reviews_auto_approve'];
    foreach ($fields as $key) {
        if (array_key_exists($key, $_POST)) {
            $value = sanitize($_POST[$key]);
            db()->prepare("UPDATE settings SET value=? WHERE `key`=?")->execute([$value, $key]);
        }
    }
    // Handle logo upload
    if (!empty($_FILES['logo']['name'])) {
        $up = uploadImage($_FILES['logo'], 'banners');
        if ($up) db()->prepare("UPDATE settings SET value=? WHERE `key`='site_logo'")->execute([$up]);
    }
    logActivity('admin', $_SESSION['admin_id'], 'settings_update', 'Site settings updated');
    $msg = 'Settings saved successfully!';
}

// Load all settings
$allSettings = db()->query("SELECT `key`,value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$s = function(string $key, string $default='') use ($allSettings): string { return $allSettings[$key] ?? $default; };

$activeTab = sanitize($_GET['tab'] ?? 'general');
$tabs = ['general'=>'General','homepage'=>'Homepage','appearance'=>'Appearance','payment'=>'Payment','social'=>'Social & SEO','contact'=>'Contact','system'=>'System'];

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h4>Site Settings</h4></div>
<?php if ($msg): ?><div class="alert alert-success alert-dismissible fade show mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
  <!-- Tab Nav -->
  <div class="col-lg-2">
    <div class="stat-card p-2">
      <?php foreach ($tabs as $tab => $label): ?>
      <a href="?tab=<?= $tab ?>" class="d-flex align-items-center gap-2 px-3 py-2 rounded mb-1 text-decoration-none"
         style="font-size:.875rem;font-weight:<?= $activeTab===$tab?'700':'500' ?>;color:<?= $activeTab===$tab?'var(--primary)':'var(--text-medium)' ?>;background:<?= $activeTab===$tab?'rgba(139,69,19,.08)':'transparent' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tab Content -->
  <div class="col-lg-10">
    <form method="POST" enctype="multipart/form-data">
      <?= csrfField() ?>
      <div class="stat-card">
        <?php if ($activeTab === 'general'): ?>
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">General Settings</h5>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-bold small">Site Name</label><input type="text" class="form-control" name="site_name" value="<?= htmlspecialchars($s('site_name')) ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Tagline</label><input type="text" class="form-control" name="site_tagline" value="<?= htmlspecialchars($s('site_tagline')) ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Contact Email</label><input type="email" class="form-control" name="site_email" value="<?= htmlspecialchars($s('site_email')) ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Phone Number</label><input type="text" class="form-control" name="site_phone" value="<?= htmlspecialchars($s('site_phone')) ?>"></div>
          <div class="col-12"><label class="form-label fw-bold small">Address</label><input type="text" class="form-control" name="site_address" value="<?= htmlspecialchars($s('site_address')) ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Currency Code</label><input type="text" class="form-control" name="currency" value="<?= htmlspecialchars($s('currency','KES')) ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Currency Symbol</label><input type="text" class="form-control" name="currency_symbol" value="<?= htmlspecialchars($s('currency_symbol','KSh')) ?>"></div>
          <div class="col-md-4"><label class="form-label fw-bold small">Tax Rate (%)</label><input type="number" class="form-control" name="tax_rate" value="<?= htmlspecialchars($s('tax_rate','16')) ?>" step="0.1"></div>
          <div class="col-md-4"><label class="form-label fw-bold small">Shipping Cost</label><input type="number" class="form-control" name="shipping_cost" value="<?= htmlspecialchars($s('shipping_cost','200')) ?>"></div>
          <div class="col-md-4"><label class="form-label fw-bold small">Free Shipping Min</label><input type="number" class="form-control" name="free_shipping_min" value="<?= htmlspecialchars($s('free_shipping_min','3000')) ?>"></div>
          <div class="col-12"><label class="form-label fw-bold small">Site Logo</label>
            <?php if ($s('site_logo')): ?><div style="margin-bottom:.5rem"><img src="<?= getImageUrl($s('site_logo')) ?>" style="max-height:60px"></div><?php endif; ?>
            <input type="file" class="form-control" name="logo" accept="image/*">
          </div>
        </div>

        <?php elseif ($activeTab === 'homepage'): ?>
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Homepage Content</h5>
        <div class="row g-3">
          <div class="col-12"><label class="form-label fw-bold small">Hero Title</label><textarea class="form-control" name="hero_title" rows="3"><?= htmlspecialchars($s('hero_title')) ?></textarea><small style="color:var(--text-light)">Use newlines for line breaks</small></div>
          <div class="col-12"><label class="form-label fw-bold small">Hero Subtitle</label><textarea class="form-control" name="hero_subtitle" rows="2"><?= htmlspecialchars($s('hero_subtitle')) ?></textarea></div>
        </div>

        <?php elseif ($activeTab === 'appearance'): ?>
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Appearance</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold small">Primary Color</label>
            <div class="d-flex align-items-center gap-2">
              <input type="color" class="form-control form-control-color" name="primary_color" value="<?= htmlspecialchars($s('primary_color','#8B4513')) ?>" style="width:60px;height:44px">
              <input type="text" class="form-control" value="<?= htmlspecialchars($s('primary_color','#8B4513')) ?>" oninput="this.previousElementSibling.value=this.value" name="_pc_text">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Accent Color</label>
            <div class="d-flex align-items-center gap-2">
              <input type="color" class="form-control form-control-color" name="accent_color" value="<?= htmlspecialchars($s('accent_color','#D2691E')) ?>" style="width:60px;height:44px">
              <input type="text" class="form-control" value="<?= htmlspecialchars($s('accent_color','#D2691E')) ?>" oninput="this.previousElementSibling.value=this.value" name="_ac_text">
            </div>
          </div>
        </div>

        <?php elseif ($activeTab === 'payment'): ?>
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Payment Settings</h5>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-bold small">M-Pesa Paybill</label><input type="text" class="form-control" name="mpesa_paybill" value="<?= htmlspecialchars($s('mpesa_paybill')) ?>"></div>
          <div class="col-12"><div class="alert alert-info py-2 px-3" style="font-size:.8rem;border-radius:var(--radius-sm)"><i class="fas fa-info-circle me-2"></i>For Stripe and PayPal API keys, edit <code>config/config.php</code> directly to keep them secure.</div></div>
        </div>

        <?php elseif ($activeTab === 'social'): ?>
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Social & SEO</h5>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-bold small">Facebook URL</label><input type="url" class="form-control" name="facebook_url" value="<?= htmlspecialchars($s('facebook_url')) ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Instagram URL</label><input type="url" class="form-control" name="instagram_url" value="<?= htmlspecialchars($s('instagram_url')) ?>"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">Twitter URL</label><input type="url" class="form-control" name="twitter_url" value="<?= htmlspecialchars($s('twitter_url')) ?>"></div>
          <div class="col-12"><label class="form-label fw-bold small">Default Meta Description</label><textarea class="form-control" name="meta_description" rows="3"><?= htmlspecialchars($s('meta_description')) ?></textarea></div>
        </div>

        <?php elseif ($activeTab === 'contact'): ?>
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Contact Details</h5>
        <div class="row g-3">
          <div class="col-12"><label class="form-label fw-bold small">Working Hours</label><input type="text" class="form-control" name="working_hours" value="<?= htmlspecialchars($s('working_hours')) ?>" placeholder="e.g. Mon-Sat: 7AM-8PM | Sun: 8AM-6PM"></div>
          <div class="col-md-6"><label class="form-label fw-bold small">WhatsApp Number</label><input type="text" class="form-control" name="whatsapp_number" value="<?= htmlspecialchars($s('whatsapp_number')) ?>" placeholder="+254700000000"></div>
        </div>

        <?php elseif ($activeTab === 'system'): ?>
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">System Settings</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold small">Maintenance Mode</label>
            <select class="form-select" name="maintenance_mode">
              <option value="0" <?= !$s('maintenance_mode')?'selected':'' ?>>Off</option>
              <option value="1" <?= $s('maintenance_mode')==='1'?'selected':'' ?>>On (site under maintenance)</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Auto-approve Reviews</label>
            <select class="form-select" name="reviews_auto_approve">
              <option value="0" <?= !$s('reviews_auto_approve')?'selected':'' ?>>No (manual approval)</option>
              <option value="1" <?= $s('reviews_auto_approve')==='1'?'selected':'' ?>>Yes (auto-approve)</option>
            </select>
          </div>
        </div>
        <?php endif; ?>

        <div class="mt-4 pt-3" style="border-top:1px solid var(--border)">
          <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Save Settings</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
