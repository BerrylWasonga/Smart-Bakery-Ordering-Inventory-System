<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = APP_URL;
$today   = date('Y-m-d');

// Fetch products and blog posts
$products = db()->query("SELECT slug, updated_at FROM products WHERE status='active'")->fetchAll();
$posts    = db()->query("SELECT slug, updated_at FROM blog_posts WHERE status='published'")->fetchAll();
$categories = db()->query("SELECT slug FROM categories WHERE status=1")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- Static Pages -->
  <url><loc><?= $baseUrl ?>/</loc><lastmod><?= $today ?></lastmod><changefreq>weekly</changefreq><priority>1.0</priority></url>
  <url><loc><?= $baseUrl ?>/pages/shop.php</loc><lastmod><?= $today ?></lastmod><changefreq>daily</changefreq><priority>0.9</priority></url>
  <url><loc><?= $baseUrl ?>/pages/blog.php</loc><lastmod><?= $today ?></lastmod><changefreq>weekly</changefreq><priority>0.7</priority></url>
  <url><loc><?= $baseUrl ?>/pages/about.php</loc><lastmod><?= $today ?></lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>
  <url><loc><?= $baseUrl ?>/pages/contact.php</loc><lastmod><?= $today ?></lastmod><changefreq>monthly</changefreq><priority>0.5</priority></url>

  <!-- Category Pages -->
  <?php foreach ($categories as $cat): ?>
  <url><loc><?= $baseUrl ?>/pages/shop.php?category=<?= htmlspecialchars($cat['slug']) ?></loc><lastmod><?= $today ?></lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>
  <?php endforeach; ?>

  <!-- Product Pages -->
  <?php foreach ($products as $p): ?>
  <url>
    <loc><?= $baseUrl ?>/pages/product.php?slug=<?= htmlspecialchars($p['slug']) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
    <changefreq>weekly</changefreq><priority>0.8</priority>
  </url>
  <?php endforeach; ?>

  <!-- Blog Posts -->
  <?php foreach ($posts as $post): ?>
  <url>
    <loc><?= $baseUrl ?>/pages/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($post['updated_at'])) ?></lastmod>
    <changefreq>monthly</changefreq><priority>0.6</priority>
  </url>
  <?php endforeach; ?>
</urlset>
