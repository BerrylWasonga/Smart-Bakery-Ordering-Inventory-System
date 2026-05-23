<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) redirect(APP_URL . '/pages/blog.php');

$stmt = db()->prepare("SELECT b.*, a.name AS author FROM blog_posts b JOIN admins a ON b.admin_id=a.id WHERE b.slug=? AND b.status='published'");
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) redirect(APP_URL . '/pages/blog.php');

// Increment views
db()->prepare("UPDATE blog_posts SET views=views+1 WHERE id=?")->execute([$post['id']]);

// Related posts
$related = db()->prepare("SELECT * FROM blog_posts WHERE category=? AND id!=? AND status='published' ORDER BY published_at DESC LIMIT 3");
$related->execute([$post['category'], $post['id']]);
$relatedPosts = $related->fetchAll();

$siteName  = getSetting('site_name', 'Crumbs & Co');
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($post['meta_title'] ?? $post['title']) ?> – <?= htmlspecialchars($siteName) ?></title>
<meta name="description" content="<?= htmlspecialchars($post['meta_description'] ?? $post['excerpt'] ?? '') ?>">
<?php if ($post['image']): ?><meta property="og:image" content="<?= getImageUrl($post['image']) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<style>
.post-body { font-size:1.05rem; line-height:1.9; color:var(--text-medium); }
.post-body h2,.post-body h3,.post-body h4 { font-family:var(--font-display); color:var(--text-dark); margin:2rem 0 1rem; }
.post-body p { margin-bottom:1.25rem; }
.post-body ul,.post-body ol { margin-bottom:1.25rem; padding-left:1.5rem; }
.post-body li { margin-bottom:.5rem; }
.post-body blockquote { border-left:4px solid var(--primary); padding:.75rem 1.5rem; background:var(--cream-dark); border-radius:0 var(--radius-sm) var(--radius-sm) 0; font-style:italic; margin:1.5rem 0; }
.post-body img { max-width:100%; border-radius:var(--radius-sm); margin:1rem 0; }
</style>
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:var(--navbar-height)">

  <!-- Hero Image -->
  <?php if ($post['image']): ?>
  <div style="max-height:480px;overflow:hidden">
    <img src="<?= getImageUrl($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width:100%;object-fit:cover;max-height:480px">
  </div>
  <?php endif; ?>

  <div class="container py-5">
    <div class="row g-5">
      <!-- Post Content -->
      <div class="col-lg-8">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb" style="font-size:.8rem">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/pages/blog.php">Blog</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars(truncate($post['title'],40)) ?></li>
          </ol>
        </nav>

        <span style="background:var(--primary);color:white;font-size:.72rem;font-weight:700;padding:.3rem .8rem;border-radius:50px;text-transform:uppercase;letter-spacing:.05em"><?= htmlspecialchars($post['category']) ?></span>
        <h1 style="font-family:var(--font-display);font-size:clamp(1.8rem,4vw,2.8rem);margin:1rem 0"><?= htmlspecialchars($post['title']) ?></h1>

        <div class="d-flex flex-wrap gap-4 mb-4 pb-4" style="border-bottom:1px solid var(--border)">
          <div class="d-flex align-items-center gap-2" style="font-size:.875rem;color:var(--text-light)">
            <i class="fas fa-user-circle" style="color:var(--primary)"></i><?= htmlspecialchars($post['author']) ?>
          </div>
          <div class="d-flex align-items-center gap-2" style="font-size:.875rem;color:var(--text-light)">
            <i class="fas fa-calendar" style="color:var(--primary)"></i><?= formatDate($post['published_at'] ?? $post['created_at']) ?>
          </div>
          <div class="d-flex align-items-center gap-2" style="font-size:.875rem;color:var(--text-light)">
            <i class="fas fa-eye" style="color:var(--primary)"></i><?= number_format($post['views']) ?> views
          </div>
        </div>

        <?php if ($post['excerpt']): ?>
        <p style="font-size:1.1rem;font-weight:500;color:var(--text-medium);font-style:italic;margin-bottom:2rem;line-height:1.8"><?= htmlspecialchars($post['excerpt']) ?></p>
        <?php endif; ?>

        <div class="post-body"><?= nl2br(htmlspecialchars($post['body'])) ?></div>

        <!-- Tags -->
        <?php if ($post['tags']): ?>
        <div class="mt-4 pt-4" style="border-top:1px solid var(--border)">
          <span style="font-size:.8rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.08em">Tags: </span>
          <?php foreach (explode(',', $post['tags']) as $tag): ?>
          <a href="<?= APP_URL ?>/pages/blog.php?search=<?= urlencode(trim($tag)) ?>" style="display:inline-block;background:var(--cream-dark);border:1px solid var(--border);color:var(--text-medium);padding:.2rem .7rem;border-radius:50px;font-size:.78rem;margin:.2rem;text-decoration:none"><?= htmlspecialchars(trim($tag)) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Share -->
        <div class="mt-4 pt-4" style="border-top:1px solid var(--border)">
          <span style="font-size:.875rem;font-weight:700;color:var(--text-medium)">Share: </span>
          <?php $shareUrl = urlencode(APP_URL . '/pages/blog-post.php?slug=' . $post['slug']); $shareTitle = urlencode($post['title']); ?>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="btn btn-sm ms-2" style="background:#1877f2;color:white;border-radius:6px"><i class="fab fa-facebook-f"></i></a>
          <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" class="btn btn-sm ms-1" style="background:#1da1f2;color:white;border-radius:6px"><i class="fab fa-twitter"></i></a>
          <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" class="btn btn-sm ms-1" style="background:#25d366;color:white;border-radius:6px"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem">
          <h6 style="font-family:var(--font-display);margin-bottom:1rem">Related Posts</h6>
          <?php foreach ($relatedPosts as $rp): ?>
          <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--border)">
            <div style="width:60px;height:60px;border-radius:8px;overflow:hidden;background:var(--cream-dark);flex-shrink:0">
              <?php if ($rp['image']): ?><img src="<?= getImageUrl($rp['image']) ?>" style="width:100%;height:100%;object-fit:cover"><?php else: ?><div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--border)"><i class="fas fa-pen-nib"></i></div><?php endif; ?>
            </div>
            <div>
              <a href="<?= APP_URL ?>/pages/blog-post.php?slug=<?= urlencode($rp['slug']) ?>" style="font-size:.85rem;font-weight:600;color:var(--text-dark);text-decoration:none;line-height:1.4;display:block"><?= htmlspecialchars(truncate($rp['title'],50)) ?></a>
              <small style="color:var(--text-light)"><?= formatDate($rp['published_at']??$rp['created_at']) ?></small>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- CTA -->
        <div style="background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:var(--radius);padding:1.5rem;color:white;text-align:center">
          <i class="fas fa-birthday-cake" style="font-size:2.5rem;opacity:.8;margin-bottom:.75rem;display:block"></i>
          <h6 style="color:white;font-family:var(--font-display)">Try our baked goods!</h6>
          <p style="opacity:.85;font-size:.85rem;margin-bottom:1.25rem">Order fresh-baked items online for same-day delivery.</p>
          <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-sm" style="background:white;color:var(--primary);font-weight:700">Shop Now</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
