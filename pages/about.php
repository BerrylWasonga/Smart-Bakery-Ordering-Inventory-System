<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$siteName     = getSetting('site_name', 'Crumbs & Co Bakery');
$siteTagline  = getSetting('site_tagline', 'Baked with Love, Every Single Day');
$phone        = getSetting('site_phone', '');
$email        = getSetting('site_email', '');
$address      = getSetting('site_address', '');
$hours        = getSetting('working_hours', '');
$wa           = getSetting('whatsapp_number', '');
$cartCount    = getCartCount();

$testimonials = db()->query("SELECT * FROM testimonials WHERE status=1 ORDER BY sort_order LIMIT 4")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>About Us – <?= htmlspecialchars($siteName) ?></title>
<meta name="description" content="Learn about <?= htmlspecialchars($siteName) ?> – our story, our bakers, and our passion for handcrafted baked goods.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
<link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
<!-- favicon -->
<link rel="icon" type="image/jpeg" href="../assets/images/Favicon2.jpg">
</head>
<body data-theme="light">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:var(--navbar-height)">

  <!-- Hero -->
  <section style="background:linear-gradient(135deg,var(--cream-dark) 0%,var(--cream) 100%);padding:5rem 0 4rem;position:relative;overflow:hidden">
    <div style="position:absolute;top:-60px;right:-60px;width:300px;height:300px;border-radius:50%;background:rgba(210,105,30,.08)"></div>
    <div style="position:absolute;bottom:-40px;left:10%;width:200px;height:200px;border-radius:50%;background:rgba(139,69,19,.06)"></div>
    <div class="container position-relative" style="z-index:1">
      <div class="row align-items-center g-5">
        <div class="col-lg-6" data-aos="fade-right">
          <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.15em;color:var(--accent);font-weight:700">Our Story</span>
          <h1 style="font-family:var(--font-display);font-size:clamp(2.2rem,4vw,3.5rem);margin:.75rem 0 1.5rem">Baked with Heart,<br><em style="color:var(--primary)">Shared with Love</em></h1>
          <p style="font-size:1.05rem;color:var(--text-medium);line-height:1.9;margin-bottom:1.5rem">
            Founded in 2014, <?= htmlspecialchars($siteName) ?> began as a small home kitchen in Nairobi, where our founder Sarah Kamau turned a lifelong passion for baking into something the whole city could enjoy.
          </p>
          <p style="color:var(--text-medium);line-height:1.9">
            Today, from our bakery in Westlands, we craft over 50 products daily — from sourdough loaves to wedding cakes — each made from scratch with the finest local ingredients. No preservatives. No shortcuts. Just honest baking.
          </p>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
          <div style="position:relative;padding:1.5rem">
            <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius-lg);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;font-size:8rem;color:var(--accent);box-shadow:var(--shadow-lg)">🎂</div>
            <div style="position:absolute;bottom:0;right:0;background:white;border-radius:var(--radius);padding:1rem 1.5rem;box-shadow:var(--shadow);border:1px solid var(--border)"> 
              <div style="font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--primary)">10+</div>
              <div style="font-size:.8rem;color:var(--text-light)">Years of Baking</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats -->
  <section style="background:linear-gradient(135deg,var(--primary),var(--accent));padding:3rem 0" data-aos="fade-up">
    <div class="container">
      <div class="row g-4 text-center text-white">
        <?php foreach ([['500+','Happy Customers'],['50+','Products Daily'],['10+','Years Baking'],['1','Nairobi Location']] as $stat): ?>
        <div class="col-6 col-md-3">
          <div style="font-family:var(--font-display);font-size:2.5rem;font-weight:700"><?= $stat[0] ?></div>
          <div style="opacity:.85;font-size:.9rem"><?= $stat[1] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Values -->
  <section style="padding:5rem 0" data-aos="fade-up">
    <div class="container">
      <div class="text-center mb-5">
        <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.15em;color:var(--accent);font-weight:700">What We Stand For</span>
        <h2 style="font-family:var(--font-display);font-size:2.5rem;margin-top:.5rem">Our Values</h2>
      </div>
      <div class="row g-4">
        <?php
        $values = [
          ['icon'=>'leaf','color'=>'#22c55e','bg'=>'rgba(34,197,94,.1)','title'=>'All-Natural Ingredients','desc'=>'We source fresh, seasonal ingredients from trusted Kenyan farmers. No artificial additives, ever.'],
          ['icon'=>'heart','color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)','title'=>'Made from Scratch','desc'=>'Every item is mixed, shaped and baked by hand. We never use pre-made mixes or frozen doughs.'],
          ['icon'=>'recycle','color'=>'#3b82f6','bg'=>'rgba(59,130,246,.1)','title'=>'Sustainability','desc'=>'Eco-friendly packaging, minimal food waste, and partnerships with local suppliers.'],
          ['icon'=>'users','color'=>'var(--primary)','bg'=>'rgba(139,69,19,.1)','title'=>'Community First','desc'=>'We donate unsold bread daily to local shelters and support Nairobi youth culinary programs.'],
        ];
        foreach ($values as $i => $v): ?>
        <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="<?= $i*100 ?>">
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;text-align:center;height:100%;transition:all .3s" class="h-100">
            <div style="width:64px;height:64px;background:<?= $v['bg'] ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.5rem">
              <i class="fas fa-<?= $v['icon'] ?>" style="color:<?= $v['color'] ?>"></i>
            </div>
            <h6 style="font-family:var(--font-display);font-weight:700;margin-bottom:.75rem"><?= $v['title'] ?></h6>
            <p style="font-size:.875rem;color:var(--text-light);margin:0;line-height:1.7"><?= $v['desc'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Team -->
  <section style="padding:5rem 0;background:var(--cream-dark)" data-aos="fade-up">
    <div class="container">
      <div class="text-center mb-5">
        <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.15em;color:var(--accent);font-weight:700">The People</span>
        <h2 style="font-family:var(--font-display);font-size:2.5rem;margin-top:.5rem">Meet Our Bakers</h2>
      </div>
      <div class="row g-4 justify-content-center">
        <?php
        $team = [
          ['name'=>'Sarah Kamau','role'=>'Founder & Head Baker','emoji'=>'👩‍🍳','desc'=>'Trained in Paris, Sarah brings 15 years of pastry expertise and a contagious love for baking.'],
          ['name'=>'James Odhiambo','role'=>'Artisan Bread Baker','emoji'=>'👨‍🍳','desc'=>'James has perfected the art of sourdough over 8 years. His loaves are sold out by 10 AM daily.'],
          ['name'=>'Amina Wanjiku','role'=>'Cake Decorator','emoji'=>'🎨','desc'=>'Amina turns sugar and fondant into edible art. She has designed over 500 custom wedding cakes.'],
        ];
        foreach ($team as $i => $member): ?>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i*100 ?>">
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;text-align:center">
            <div style="font-size:4rem;margin-bottom:1rem"><?= $member['emoji'] ?></div>
            <h6 style="font-family:var(--font-display);font-weight:700;margin-bottom:.25rem"><?= $member['name'] ?></h6>
            <div style="font-size:.8rem;color:var(--accent);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.75rem"><?= $member['role'] ?></div>
            <p style="font-size:.875rem;color:var(--text-light);margin:0;line-height:1.7"><?= $member['desc'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <?php if (!empty($testimonials)): ?>
  <section style="padding:5rem 0" data-aos="fade-up">
    <div class="container">
      <div class="text-center mb-5">
        <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.15em;color:var(--accent);font-weight:700">Reviews</span>
        <h2 style="font-family:var(--font-display);font-size:2.5rem;margin-top:.5rem">What People Say</h2>
      </div>
      <div class="row g-4">
        <?php foreach ($testimonials as $t): ?>
        <div class="col-md-6">
          <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem">
            <div style="color:var(--gold);margin-bottom:.75rem;font-size:1rem"><?= str_repeat('★',(int)$t['rating']) ?></div>
            <p style="font-style:italic;color:var(--text-medium);margin-bottom:1.25rem;line-height:1.8">"<?= htmlspecialchars($t['content']) ?>"</p>
            <div class="d-flex align-items-center gap-3">
              <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;flex-shrink:0"><?= strtoupper(substr($t['name'],0,1)) ?></div>
              <div><strong style="font-size:.9rem"><?= htmlspecialchars($t['name']) ?></strong><?php if ($t['role']): ?><br><small style="color:var(--text-light)"><?= htmlspecialchars($t['role']) ?></small><?php endif; ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA -->
  <section style="padding:4rem 0;background:linear-gradient(135deg,var(--primary),var(--accent))" data-aos="fade-up">
    <div class="container text-center text-white">
      <h2 style="font-family:var(--font-display);color:white;margin-bottom:1rem">Ready to taste the difference?</h2>
      <p style="opacity:.9;margin-bottom:2rem;font-size:1.05rem">Order online for same-day delivery across Nairobi.</p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-lg" style="background:white;color:var(--primary);font-weight:700;border-radius:var(--radius)">Shop Now</a>
        <a href="<?= APP_URL ?>/pages/contact.php" class="btn btn-lg btn-outline-light" style="border-radius:var(--radius)">Contact Us</a>
      </div>
    </div>
  </section>

</div>

<?php if ($wa): ?><a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$wa) ?>" class="whatsapp-float" target="_blank"><i class="fab fa-whatsapp"></i></a><?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>AOS.init({duration:700,once:true});</script>
</body></html>
