<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/bootstrap.php';

// Fetch featured products
$featuredStmt = db()->query("SELECT p.*, c.name as category_name FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 AND p.status = 'active' 
    LIMIT 8");
$featuredProducts = $featuredStmt->fetchAll();

// Fetch bestsellers
$bestStmt = db()->query("SELECT p.*, c.name as category_name FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_bestseller = 1 AND p.status = 'active' 
    LIMIT 4");
$bestsellers = $bestStmt->fetchAll();

// Fetch categories
$catStmt = db()->query("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order LIMIT 8");
$categories = $catStmt->fetchAll();

// Fetch testimonials
$testiStmt = db()->query("SELECT * FROM testimonials WHERE status = 1 ORDER BY sort_order LIMIT 4");
$testimonials = $testiStmt->fetchAll();

// Fetch blog posts
$blogStmt = db()->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 3");
$blogPosts = $blogStmt->fetchAll();

$siteName = getSetting('site_name', 'Crumbs & Co Bakery');
$heroTitle = getSetting('hero_title', 'Baked with Love, Every Single Day');
$heroSubtitle = getSetting('hero_subtitle', 'Artisan pastries, cakes, and breads crafted from the finest ingredients.');
$cartCount = getCartCount();

$pageTitle = $siteName . ' - ' . getSetting('site_tagline', 'Premium Artisan Bakery');
$metaDesc = getSetting('meta_description', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta name="theme-color" content="#8B4513">
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:type" content="website">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animations -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
    <!-- favicon -->
    <link rel="icon" type="image/jpeg" href="assets/images/Favicon2.jpg"> 
</head>
<body class="bakery-body" data-theme="light">

<!-- ========== NAVBAR ========== -->
<nav class="navbar navbar-expand-lg bakery-navbar fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>">
            <div class="brand-icon">
                <i class="fas fa-bread-slice"></i>
            </div>
            <div>
                <span class="brand-name"><?= htmlspecialchars($siteName) ?></span>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item"><a class="nav-link active" href="<?= APP_URL ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/shop.php">Shop</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $cat): ?>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/shop.php?category=<?= urlencode($cat['slug']) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/blog.php">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/pages/contact.php">Contact</a></li>
            </ul>
            <div class="navbar-actions d-flex align-items-center gap-3">
                <!-- Theme Toggle -->
                <button class="theme-toggle btn-icon" id="themeToggle" title="Toggle theme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <!-- Cart -->
                <a href="<?= APP_URL ?>/pages/cart.php" class="btn-icon position-relative" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if ($cartCount > 0): ?>
                    <span class="cart-badge" id="cartBadge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <!-- Account -->
                <?php if (isLoggedIn()): 
                    $user = getCurrentUser(); ?>
                <div class="dropdown">
                    <button class="btn-icon" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <strong><?= htmlspecialchars($user['name']) ?></strong>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-outline-primary btn-sm nav-btn">Login</a>
                <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-primary btn-sm nav-btn">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- ========== HERO SECTION ========== -->
<section class="hero-section" id="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                <div class="hero-content">
                    <span class="hero-badge">
                        <i class="fas fa-award me-2"></i>Nairobi's Premium Bakery
                    </span>
                    <h1 class="hero-title">
                        <?= nl2br(htmlspecialchars($heroTitle)) ?>
                    </h1>
                    <p class="hero-subtitle"><?= htmlspecialchars($heroSubtitle) ?></p>
                    <div class="hero-actions d-flex flex-wrap gap-3">
                        <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary btn-lg hero-btn">
                            <i class="fas fa-shopping-bag me-2"></i>Shop Now
                        </a>
                        <a href="<?= APP_URL ?>/pages/about.php" class="btn btn-outline-light btn-lg hero-btn">
                            Our Story
                        </a>
                    </div>
                    <div class="hero-stats d-flex gap-4 mt-5">
                        <div class="stat-item">
                            <strong>500+</strong>
                            <span>Happy Clients</span>
                        </div>
                        <div class="vr"></div>
                        <div class="stat-item">
                            <strong>50+</strong>
                            <span>Products</span>
                        </div>
                        <div class="vr"></div>
                        <div class="stat-item">
                            <strong>10+</strong>
                            <span>Years Baking</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left" data-aos-delay="300">
                <div class="hero-visual">
                    <div class="hero-circle-1"></div>
                    <div class="hero-circle-2"></div>
                    <div">
                        <img src="assets/images/Vanilla_Dream_Cake-removebg-preview.png" alt="Delicious cake">
                    </div>
                    <!-- Floating cards -->
                    <div class="floating-card fc-1">
                        <i class="fas fa-star text-warning"></i>
                        <div>
                            <strong>4.9/5</strong>
                            <small>Rating</small>
                        </div>
                    </div>
                    <div class="floating-card fc-2">
                        <i class="fas fa-truck text-success"></i>
                        <div>
                            <strong>Free Delivery</strong>
                            <small>Over KSh 3,000</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 
    Scroll indicator 
    <div class="scroll-indicator">
        <div class="scroll-dot"></div>
    </div>
    -->
</section>

<!-- ========== CATEGORIES ========== -->
<section class="section-categories py-5">
    <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <span class="section-label">Browse</span>
            <h2 class="section-title">Our Categories</h2>
        </div>
        <div class="row g-3">
            <?php foreach ($categories as $i => $cat): ?>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="<?= $i * 50 ?>">
                <a href="<?= APP_URL ?>/pages/shop.php?category=<?= urlencode($cat['slug']) ?>" class="category-card">
                    <div class="cat-icon">
                        <?php
                        $icons = ['Cakes' => 'birthday-cake', 'Bread' => 'bread-slice', 'Pastries' => 'cookie-bite', 
                                  'Cookies' => 'cookie', 'Cupcakes' => 'ice-cream', 'Donuts' => 'circle-notch',
                                  'Pies & Tarts' => 'circle', 'Beverages' => 'mug-hot'];
                        $icon = $icons[$cat['name']] ?? 'utensils';
                        ?>
                        <i class="fas fa-<?= $icon ?>"></i>
                    </div>
                    <h6><?= htmlspecialchars($cat['name']) ?></h6>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========== FEATURED PRODUCTS ========== -->
<section class="section-products py-5 bg-light-cream">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-end mb-5" data-aos="fade-up">
            <div>
                <span class="section-label">Handpicked</span>
                <h2 class="section-title">Featured Products</h2>
            </div>
            <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredProducts as $i => $product): ?>
            <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 80 ?>">
                <div class="product-card">
                    <?php if ($product['discount_price']): ?>
                    <span class="product-badge badge-sale">Sale</span>
                    <?php endif; ?>
                    <?php if ($product['is_bestseller']): ?>
                    <span class="product-badge badge-hot" style="top:2.5rem">🔥 Hot</span>
                    <?php endif; ?>
                    <div class="product-image">
                        <?php if ($product['thumbnail']): ?>
                        <img src="<?= getImageUrl($product['thumbnail']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        <?php else: ?>
                        <div class="product-img-placeholder">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <button class="btn-quick-view" data-product-id="<?= $product['id'] ?>" title="Quick View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-wishlist <?= isLoggedIn() ? 'ajax-wishlist' : '' ?>" 
                                    data-product-id="<?= $product['id'] ?>" title="Add to Wishlist">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?= htmlspecialchars($product['category_name']) ?></span>
                        <h6 class="product-name">
                            <a href="<?= APP_URL ?>/product/<?= urlencode($product['slug']) ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h6>
                        <p class="product-desc"><?= htmlspecialchars(truncate($product['short_description'] ?? '', 60)) ?></p>
                        <div class="product-footer d-flex justify-content-between align-items-center">
                            <div class="product-price">
                                <?php if ($product['discount_price']): ?>
                                <span class="price-current"><?= formatPrice($product['discount_price']) ?></span>
                                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                                <?php else: ?>
                                <span class="price-current"><?= formatPrice($product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn-add-cart ajax-add-cart" 
                                    data-product-id="<?= $product['id'] ?>"
                                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                    <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========== PROMO BANNER ========== -->
<section class="section-promo py-5" data-aos="fade-up">
    <div class="container">
        <div class="promo-banner">
            <div class="promo-content">
                <span class="promo-label">Limited Time</span>
                <h3>First Order Discount</h3>
                <p>Use code <strong>WELCOME10</strong> for 10% off your first order!</p>
                <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary">Order Now</a>
            </div>
            <div class="promo-decor">
                <i class="fas fa-birthday-cake"></i>
            </div>
        </div>
    </div>
</section>

<!-- ========== BESTSELLERS ========== -->
<?php if (!empty($bestsellers)): ?>
<section class="section-bestsellers py-5">
    <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <span class="section-label">Most Loved</span>
            <h2 class="section-title">Bestsellers</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($bestsellers as $i => $product): ?>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="product-card bestseller-card">
                    <div class="bs-rank">#<?= $i + 1 ?></div>
                    <div class="product-image">
                        <?php if ($product['thumbnail']): ?>
                        <img src="<?= getImageUrl($product['thumbnail']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        <?php else: ?>
                        <div class="product-img-placeholder"><i class="fas fa-cookie-bite"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h6 class="product-name">
                            <a href="<?= APP_URL ?>/pages/product.php?slug=<?= urlencode($product['slug']) ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price-current"><?= formatPrice($product['discount_price'] ?? $product['price']) ?></span>
                            <button class="btn-add-cart ajax-add-cart" data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== WHY CHOOSE US ========== -->
<section class="section-features py-5 bg-light-cream" data-aos="fade-up">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-label">Our Promise</span>
            <h2 class="section-title">Why Choose Crumbs & Co?</h2>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['icon' => 'leaf', 'color' => 'green', 'title' => 'Fresh Ingredients', 'desc' => 'We use only the finest, locally-sourced ingredients in every bake.'],
                ['icon' => 'heart', 'color' => 'red', 'title' => 'Made with Love', 'desc' => 'Every product is handcrafted by our passionate bakers.'],
                ['icon' => 'truck', 'color' => 'blue', 'title' => 'Fast Delivery', 'desc' => 'Same-day delivery in Nairobi. Free over KSh 3,000.'],
                ['icon' => 'award', 'color' => 'gold', 'title' => 'Premium Quality', 'desc' => 'Award-winning recipes perfected over 10 years of baking.'],
            ];
            foreach ($features as $i => $f): ?>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="<?= $i * 100 ?>">
                <div class="feature-card">
                    <div class="feature-icon icon-<?= $f['color'] ?>">
                        <i class="fas fa-<?= $f['icon'] ?>"></i>
                    </div>
                    <h6><?= $f['title'] ?></h6>
                    <p><?= $f['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========== TESTIMONIALS ========== -->
<?php if (!empty($testimonials)): ?>
<section class="section-testimonials py-5" data-aos="fade-up">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-label">Reviews</span>
            <h2 class="section-title">What Our Customers Say</h2>
        </div>
        <div class="testimonials-slider" id="testimonialsSlider">
            <?php foreach ($testimonials as $testi): ?>
            <div class="testimonial-card">
                <div class="testi-stars">
                    <?php for ($i = 0; $i < $testi['rating']; $i++): ?>
                    <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <p class="testi-text">"<?= htmlspecialchars($testi['content']) ?>"</p>
                <div class="testi-author">
                    <div class="testi-avatar">
                        <?= strtoupper(substr($testi['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($testi['name']) ?></strong>
                        <?php if ($testi['role']): ?>
                        <small><?= htmlspecialchars($testi['role']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== BLOG PREVIEW ========== -->
<?php if (!empty($blogPosts)): ?>
<section class="section-blog py-5 bg-light-cream" data-aos="fade-up">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-end mb-5">
            <div>
                <span class="section-label">From Our Kitchen</span>
                <h2 class="section-title">Baking Tips & Stories</h2>
            </div>
            <a href="<?= APP_URL ?>/pages/blog.php" class="btn btn-outline-primary">All Posts <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($blogPosts as $i => $post): ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <article class="blog-card">
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
                            <i class="far fa-calendar-alt"></i>
                            <?= formatDate($post['published_at'] ?? $post['created_at']) ?>
                        </div>
                        <h6 class="blog-title">
                            <a href="<?= APP_URL ?>/pages/blog-post.php?slug=<?= urlencode($post['slug']) ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h6>
                        <p class="blog-excerpt"><?= htmlspecialchars(truncate($post['excerpt'] ?? '', 100)) ?></p>
                        <a href="<?= APP_URL ?>/pages/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="blog-link">
                            Read More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== NEWSLETTER ========== -->
<section class="section-newsletter py-5" data-aos="fade-up">
    <div class="container">
        <div class="newsletter-box">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="section-label">Stay Updated</span>
                    <h3>Subscribe to Our Newsletter</h3>
                    <p>Get the latest recipes, offers and bakery news delivered to your inbox.</p>
                </div>
                <div class="col-md-6">
                    <form class="newsletter-form" id="newsletterForm">
                        <?= csrfField() ?>
                        <div class="input-group">
                            <input type="email" class="form-control" name="email" placeholder="Your email address" required>
                            <button class="btn btn-primary" type="submit">
                                Subscribe <i class="fas fa-paper-plane ms-1"></i>
                            </button>
                        </div>
                        <div id="newsletterMsg" class="mt-2"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="bakery-footer">
    <div class="container">
        <div class="row g-4 py-5">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <div class="brand-icon mb-3">
                        <i class="fas fa-bread-slice"></i>
                    </div>
                    <h5><?= htmlspecialchars($siteName) ?></h5>
                    <p>Artisan breads, cakes and pastries baked fresh every day in the heart of Nairobi.</p>
                    <div class="social-links">
                        <a href="<?= htmlspecialchars(getSetting('facebook_url', '#')) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= htmlspecialchars(getSetting('instagram_url', '#')) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="<?= htmlspecialchars(getSetting('twitter_url', '#')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
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
                    <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
                    <li><a href="<?= APP_URL ?>/pages/shop.php?category=<?= urlencode($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="footer-heading">Contact Us</h6>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(getSetting('site_address', '')) ?></li>
                    <li><i class="fas fa-phone"></i> <?= htmlspecialchars(getSetting('site_phone', '')) ?></li>
                    <li><i class="fas fa-envelope"></i> <?= htmlspecialchars(getSetting('site_email', '')) ?></li>
                    <li><i class="fas fa-clock"></i> <?= htmlspecialchars(getSetting('working_hours', '')) ?></li>
                </ul>
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

<!-- WhatsApp Button -->
<?php $wa = getSetting('whatsapp_number'); if ($wa): ?>
<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $wa) ?>" class="whatsapp-float" target="_blank" title="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    window.APP_URL = '<?= APP_URL ?>';
    window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
    AOS.init({ duration: 700, once: true, easing: 'ease-out-cubic' });
</script>
</body>
</html>
