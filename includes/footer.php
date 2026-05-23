<?php
// includes/footer.php — shared footer for all public pages
$siteName = getSetting('site_name', 'Crumbs & Co Bakery');
$wa = getSetting('whatsapp_number', '');
$categories = db()->query("SELECT name,slug FROM categories WHERE status=1 ORDER BY sort_order LIMIT 6")->fetchAll();
?>
<footer class="bakery-footer">
  <div class="container">
    <div class="row g-4 py-5">
      <div class="col-lg-4">
        <div class="footer-brand">
          <div class="brand-icon mb-3"><i class="fas fa-bread-slice"></i></div>
          <h5><?= htmlspecialchars($siteName) ?></h5>
          <p><?= htmlspecialchars(getSetting('site_tagline','Baked with Love, Every Single Day')) ?></p>
          <div class="social-links">
            <?php if ($fb = getSetting('facebook_url')): ?><a href="<?= htmlspecialchars($fb) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
            <?php if ($ig = getSetting('instagram_url')): ?><a href="<?= htmlspecialchars($ig) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a><?php endif; ?>
            <?php if ($tw = getSetting('twitter_url')): ?><a href="<?= htmlspecialchars($tw) ?>" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Quick Links</h6>
        <ul class="footer-links">
          <li><a href="<?= APP_URL ?>">Home</a></li>
          <li><a href="<?= APP_URL ?>/pages/shop.php">Shop</a></li>
          <li><a href="<?= APP_URL ?>/pages/about.php">About Us</a></li>
          <li><a href="<?= APP_URL ?>/pages/blog.php">Blog</a></li>
          <li><a href="<?= APP_URL ?>/pages/contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Categories</h6>
        <ul class="footer-links">
          <?php foreach ($categories as $cat): ?>
          <li><a href="<?= APP_URL ?>/pages/shop.php?category=<?= urlencode($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6 class="footer-heading">Contact Us</h6>
        <ul class="footer-contact">
          <?php if ($addr = getSetting('site_address')): ?><li><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($addr) ?></li><?php endif; ?>
          <?php if ($ph = getSetting('site_phone')): ?><li><i class="fas fa-phone"></i><a href="tel:<?= preg_replace('/[^0-9+]/','',$ph) ?>" style="color:inherit"><?= htmlspecialchars($ph) ?></a></li><?php endif; ?>
          <?php if ($em = getSetting('site_email')): ?><li><i class="fas fa-envelope"></i><a href="mailto:<?= htmlspecialchars($em) ?>" style="color:inherit"><?= htmlspecialchars($em) ?></a></li><?php endif; ?>
          <?php if ($hrs = getSetting('working_hours')): ?><li><i class="fas fa-clock"></i><?= htmlspecialchars($hrs) ?></li><?php endif; ?>
        </ul>
        <?php if ($wa): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$wa) ?>" target="_blank" class="btn btn-success btn-sm mt-2 d-inline-flex align-items-center gap-2">
          <i class="fab fa-whatsapp"></i>WhatsApp Us
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. All Rights Reserved.</p>
      <div class="footer-bottom-links">
        <a href="<?= APP_URL ?>/pages/privacy.php">Privacy Policy</a>
        <a href="<?= APP_URL ?>/pages/terms.php">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

<?php if ($wa): ?>
<a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$wa) ?>" class="whatsapp-float" target="_blank" rel="noopener" title="Chat on WhatsApp">
  <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
