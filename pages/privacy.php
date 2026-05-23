<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$siteName  = getSetting('site_name', 'Crumbs & Co Bakery');
$siteEmail = getSetting('site_email', '');
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Privacy Policy – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<style>.policy-body h2{font-family:var(--font-display);font-size:1.4rem;margin:2rem 0 .75rem;color:var(--primary)}.policy-body p,.policy-body li{color:var(--text-medium);line-height:1.9;font-size:.95rem}.policy-body ul{padding-left:1.5rem}</style>
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div style="padding-top:var(--navbar-height)">
  <div style="background:var(--cream-dark);padding:3.5rem 0 2.5rem">
    <div class="container">
      <h1 style="font-family:var(--font-display)">Privacy Policy</h1>
      <p style="color:var(--text-light)">Last updated: <?= date('F d, Y') ?></p>
    </div>
  </div>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8 policy-body">
        <p>At <strong><?= htmlspecialchars($siteName) ?></strong>, we respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, and safeguard your information.</p>

        <h2>1. Information We Collect</h2>
        <p>We collect information you provide directly to us when you:</p>
        <ul>
          <li>Create an account (name, email address, phone number)</li>
          <li>Place an order (billing/shipping address, payment method details)</li>
          <li>Contact us (name, email, message content)</li>
          <li>Subscribe to our newsletter (email address)</li>
          <li>Submit a product review (name, rating, review text)</li>
        </ul>
        <p>We also automatically collect certain information when you use our website, including IP address, browser type, pages visited, and referring URLs through standard server logs.</p>

        <h2>2. How We Use Your Information</h2>
        <ul>
          <li>Process and fulfill your orders</li>
          <li>Send order confirmations, updates, and delivery notifications</li>
          <li>Respond to your enquiries and customer support requests</li>
          <li>Send promotional emails (only with your consent — you can unsubscribe at any time)</li>
          <li>Improve our website and services</li>
          <li>Comply with legal obligations</li>
        </ul>

        <h2>3. Payment Information</h2>
        <p>We do not store your full payment card details on our servers. Payments are processed through secure third-party payment processors (M-Pesa, Stripe, PayPal) that comply with PCI-DSS standards. We only store a transaction reference for order tracking purposes.</p>

        <h2>4. Sharing Your Information</h2>
        <p>We do not sell, trade, or rent your personal information to third parties. We may share information with:</p>
        <ul>
          <li><strong>Delivery partners</strong> — your name, address, and phone number for order delivery</li>
          <li><strong>Payment processors</strong> — as required to complete transactions</li>
          <li><strong>Legal authorities</strong> — when required by law</li>
        </ul>

        <h2>5. Data Retention</h2>
        <p>We retain your personal data for as long as your account is active or as needed to provide services. Order records are retained for 7 years for accounting and legal compliance. You may request deletion of your data at any time (subject to legal retention requirements).</p>

        <h2>6. Cookies</h2>
        <p>We use essential session cookies required for the website to function (shopping cart, login state). We do not use advertising tracking cookies. You can disable cookies in your browser settings, though this may affect website functionality.</p>

        <h2>7. Your Rights</h2>
        <p>You have the right to:</p>
        <ul>
          <li>Access the personal data we hold about you</li>
          <li>Correct inaccurate data</li>
          <li>Request deletion of your data</li>
          <li>Opt out of marketing communications at any time</li>
          <li>Data portability (receive your data in a machine-readable format)</li>
        </ul>

        <h2>8. Security</h2>
        <p>We implement industry-standard security measures including SSL encryption, password hashing, and prepared SQL statements to protect your data. However, no method of internet transmission is 100% secure.</p>

        <h2>9. Contact Us</h2>
        <p>For any privacy-related questions or to exercise your rights, contact us at:<br>
        <a href="mailto:<?= htmlspecialchars($siteEmail) ?>"><?= htmlspecialchars($siteEmail) ?></a></p>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
