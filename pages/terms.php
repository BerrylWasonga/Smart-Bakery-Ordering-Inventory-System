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
<title>Terms of Service – <?= htmlspecialchars($siteName) ?></title>
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
      <h1 style="font-family:var(--font-display)">Terms of Service</h1>
      <p style="color:var(--text-light)">Last updated: <?= date('F d, Y') ?></p>
    </div>
  </div>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8 policy-body">
        <p>Welcome to <?= htmlspecialchars($siteName) ?>. By accessing or using our website and services, you agree to be bound by these Terms of Service.</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By placing an order or creating an account on our website, you confirm that you are at least 18 years old (or have parental consent) and agree to these terms.</p>

        <h2>2. Products & Pricing</h2>
        <ul>
          <li>All prices are listed in Kenyan Shillings (KSh) and include applicable taxes.</li>
          <li>We reserve the right to change prices at any time without prior notice.</li>
          <li>Product images are for illustration purposes only; actual products may vary slightly.</li>
          <li>We make every effort to display accurate stock levels, but availability is not guaranteed.</li>
        </ul>

        <h2>3. Orders & Payment</h2>
        <ul>
          <li>Orders are confirmed only after successful payment processing.</li>
          <li>We reserve the right to cancel any order due to pricing errors, stock issues, or suspected fraud.</li>
          <li>Payment is due at the time of ordering. Unpaid orders will be automatically cancelled after 2 hours.</li>
          <li>For cash-on-delivery orders, payment is required upon receipt of goods.</li>
        </ul>

        <h2>4. Delivery Policy</h2>
        <ul>
          <li>Same-day delivery is available for orders placed before 11:00 AM within Nairobi.</li>
          <li>Delivery times are estimates and not guaranteed; delays may occur due to traffic or weather.</li>
          <li>Standard delivery fee is KSh 200. Orders over KSh 3,000 qualify for free delivery.</li>
          <li>We deliver within Nairobi and selected surrounding areas. Contact us for out-of-area delivery.</li>
          <li>Someone must be available to receive the order. Re-delivery may incur additional charges.</li>
        </ul>

        <h2>5. Freshness Guarantee & Returns</h2>
        <ul>
          <li>All products are baked fresh daily and come with a freshness guarantee.</li>
          <li>If you receive a product that does not meet our quality standards, contact us within 2 hours of delivery with a photo.</li>
          <li>We will replace or refund defective products at our discretion.</li>
          <li>We do not accept returns on food items for hygiene and safety reasons, except in cases of product defects.</li>
          <li>Custom and special-order cakes are non-refundable once production has begun.</li>
        </ul>

        <h2>6. Custom & Special Orders</h2>
        <ul>
          <li>Custom cake orders require a minimum of 5 days' notice and a 50% deposit.</li>
          <li>Deposits are non-refundable if the order is cancelled within 48 hours of the delivery/collection date.</li>
          <li>Final design may differ slightly from mockups due to the handcrafted nature of our products.</li>
        </ul>

        <h2>7. Allergen Disclaimer</h2>
        <p>Our products are made in a kitchen that handles wheat (gluten), dairy, eggs, nuts, and soy. While we take precautions, we cannot guarantee products are free from allergen cross-contamination. Please inform us of any allergies before ordering. We are not liable for allergic reactions where allergen information was not disclosed at time of order.</p>

        <h2>8. Intellectual Property</h2>
        <p>All content on this website including text, images, logos, and designs is the property of <?= htmlspecialchars($siteName) ?> and may not be reproduced without written permission.</p>

        <h2>9. Limitation of Liability</h2>
        <p>To the maximum extent permitted by Kenyan law, <?= htmlspecialchars($siteName) ?> shall not be liable for any indirect, incidental, or consequential damages arising from use of our services or products.</p>

        <h2>10. Governing Law</h2>
        <p>These terms are governed by the laws of the Republic of Kenya. Any disputes shall be subject to the jurisdiction of Kenyan courts.</p>

        <h2>11. Changes to Terms</h2>
        <p>We may update these terms at any time. Continued use of our website after changes constitutes acceptance of the new terms.</p>

        <h2>12. Contact</h2>
        <p>Questions about these terms? Contact us at <a href="mailto:<?= htmlspecialchars($siteEmail) ?>"><?= htmlspecialchars($siteEmail) ?></a>.</p>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
