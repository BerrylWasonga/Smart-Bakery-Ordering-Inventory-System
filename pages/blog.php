<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$siteName  = getSetting('site_name', 'Crumbs & Co');
$search    = sanitize($_GET['search'] ?? '');
$category  = sanitize($_GET['category'] ?? '');
$page      = max(1, sanitizeInt($_GET['page'] ?? 1));
$perPage   = 9;

$where = ["b.status='published'"]; $params = [];
if ($search)   { $where[] = "(b.title LIKE ? OR b.excerpt LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($category) { $where[] = "b.category=?"; $params[] = $category; }
$whereSQL = 'WHERE ' . implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM blog_posts b $whereSQL");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pag   = paginate($total, $perPage, $page);

$posts = db()->prepare("SELECT b.*, a.name AS author FROM blog_posts b JOIN admins a ON b.admin_id=a.id $whereSQL ORDER BY b.published_at DESC LIMIT $perPage OFFSET {$pag['offset']}");
$posts->execute($params);
$posts = $posts->fetchAll();

$categories = db()->query("SELECT DISTINCT category FROM blog_posts WHERE status='published' AND category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$cartCount  = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Blog – <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<!-- favicon -->
<link rel="icon" type="image/jpeg" href="../assets/images/Favicon2.jpg">
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:var(--navbar-height)">
  <div style="background:var(--cream-dark);padding:4rem 0 3rem">
    <div class="container text-center">
      <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.15em;color:var(--accent);font-weight:700">From Our Kitchen</span>
      <h1 style="font-family:var(--font-display);margin:.5rem 0">Baking Tips & Stories</h1>
      <p style="color:var(--text-light);max-width:480px;margin:0 auto">Recipes, behind-the-scenes stories, and tips from our bakers.</p>
    </div>
  </div>

  <div class="container py-5">
    <div class="row g-4">
      <!-- Sidebar -->
      <div class="col-lg-3">
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem">
          <form method="GET">
            <label class="form-label fw-bold small">Search Posts</label>
            <div class="input-group">
              <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search…">
              <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
          </form>
        </div>
        <?php if (!empty($categories)): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
          <h6 style="font-family:var(--font-display);margin-bottom:1rem">Categories</h6>
          <a href="<?= APP_URL ?>/pages/blog.php" 
            class="d-block py-1 small <?= !$category ? 'fw-bold' : '' ?>" 
            style="color:var(--text-medium);text-decoration:none">
            All Posts
          </a>
          <?php foreach ($categories as $cat): ?>
          <a href="?category=<?= urlencode($cat) ?>" 
            class="d-block py-1 small <?= $category === $cat ? 'fw-bold' : '' ?>" 
            style="color:<?= $category === $cat ? 'var(--primary)' : 'var(--text-medium)' ?>;text-decoration:none">
            <?= htmlspecialchars($cat) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Posts -->
      <div class="col-lg-9">
        <?php if (empty($posts)): ?>
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:3rem;text-align:center">
          <i class="fas fa-pen-nib" style="font-size:3rem;color:var(--border)"></i>
          <h5 style="margin-top:1rem;color:var(--text-light)">No posts found</h5>
          <a href="<?= APP_URL ?>/pages/blog.php" class="btn btn-primary mt-2">View All Posts</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
          <?php foreach ($posts as $i => $post): ?>
          <div class="col-md-6 col-lg-4">
            <article class="blog-card h-100">
              <div class="blog-image">
                <?php if ($post['image']): ?>
                <img src="<?= getImageUrl($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                <?php else: ?>
                <div class="blog-img-placeholder"><i class="fas fa-pen-nib"></i></div>
                <?php endif; ?>
                <span class="blog-category"><?= htmlspecialchars($post['category']) ?></span>
              </div>
              <div class="blog-content">
                <div class="blog-meta">
                  <i class="far fa-calendar-alt me-1"></i><?= formatDate($post['published_at'] ?? $post['created_at']) ?>
                  <span class="mx-2">·</span><i class="far fa-user me-1"></i><?= htmlspecialchars($post['author']) ?>
                </div>
                <h6 class="blog-title"><a href="<?= APP_URL ?>/pages/blog-post.php?slug=<?= urlencode($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h6>
                <p class="blog-excerpt"><?= htmlspecialchars(truncate($post['excerpt'] ?? '', 110)) ?></p>
                <a href="<?= APP_URL ?>/pages/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="blog-link">Read More <i class="fas fa-arrow-right ms-1"></i></a>
              </div>
            </article>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($pag['total_pages'] > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
          <ul class="pagination gap-1">
            <?php if ($pag['has_prev']): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$pag['current_page']-1])) ?>">‹</a></li><?php endif; ?>
            <?php for ($i=1;$i<=$pag['total_pages'];$i++): ?><li class="page-item <?= $i===$pag['current_page']?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a></li><?php endfor; ?>
            <?php if ($pag['has_next']): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$pag['current_page']+1])) ?>">›</a></li><?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
