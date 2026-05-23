<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid token.'; }
    else {
        $name    = sanitize($_POST['name'] ?? '');
        $email   = sanitizeEmail($_POST['email'] ?? '');
        $phone   = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        if (!$name || !$email || !$subject || !$message) $error = 'Please fill in all required fields.';
        elseif (!validateEmail($email)) $error = 'Please enter a valid email address.';
        else {
            db()->prepare("INSERT INTO contact_messages (name,email,phone,subject,message) VALUES (?,?,?,?,?)")->execute([$name,$email,$phone,$subject,$message]);
            $success = "Thank you, $name! We'll get back to you within 24 hours.";
        }
    }
}
$siteName = getSetting('site_name', 'Crumbs & Co');
$phone    = getSetting('site_phone', '');
$emailS   = getSetting('site_email', '');
$address  = getSetting('site_address', '');
$hours    = getSetting('working_hours', '');
$wa       = getSetting('whatsapp_number', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact – <?= htmlspecialchars($siteName) ?></title>
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
      <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.15em;color:var(--accent);font-weight:700">Get in Touch</span>
      <h1 style="font-family:var(--font-display);margin:.5rem 0">Contact Us</h1>
      <p style="color:var(--text-light);max-width:500px;margin:0 auto">We'd love to hear from you — custom orders, questions, or just to say hi!</p>
    </div>
  </div>

  <div class="container py-5">
    <div class="row g-5">
      <!-- Info -->
      <div class="col-lg-4">
        <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Find Us</h5>
        <?php
        $info = [
          ['icon'=>'map-marker-alt','label'=>'Address','value'=>$address],
          ['icon'=>'phone','label'=>'Phone','value'=>$phone],
          ['icon'=>'envelope','label'=>'Email','value'=>$emailS],
          ['icon'=>'clock','label'=>'Hours','value'=>$hours],
        ];
        foreach ($info as $i): if (!$i['value']) continue; ?>
        <div class="d-flex gap-3 mb-3">
          <div style="width:44px;height:44px;background:rgba(139,69,19,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-<?= $i['icon'] ?>" style="color:var(--primary)"></i>
          </div>
          <div>
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700"><?= $i['label'] ?></div>
            <div style="font-size:.9rem;color:var(--text-medium)"><?= htmlspecialchars($i['value']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if ($wa): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$wa) ?>" target="_blank"
           class="btn btn-success d-flex align-items-center gap-2 mt-3">
          <i class="fab fa-whatsapp fa-lg"></i>Chat on WhatsApp
        </a>
        <?php endif; ?>
        <!-- Map placeholder -->
        <div style="margin-top:1.5rem;height:220px;background:var(--cream-dark);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;border:1px solid var(--border)">
          <div class="text-center">
            <i class="fas fa-map-marked-alt" style="font-size:2rem;color:var(--border)"></i>
            <p style="color:var(--text-light);font-size:.8rem;margin-top:.5rem"><iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d31918.52028027689!2d34.75728298853712!3d-0.07352739524780802!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2ske!4v1779039747430!5m2!1sen!2ske" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></p>
          </div>
          
        </div>
      </div>

      <!-- Form -->
      <div class="col-lg-8">
        <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius);padding:2.5rem">
          <h5 style="font-family:var(--font-display);margin-bottom:1.5rem">Send a Message</h5>
          <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <form method="POST">
            <?= csrfField() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small">Full Name *</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small">Email Address *</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small">Phone</label>
                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small">Subject *</label>
                <select class="form-select" name="subject" required>
                  <option value="">Select subject</option>
                  <?php foreach (['Custom Cake Order','General Inquiry','Delivery Question','Feedback','Partnership','Other'] as $s): ?>
                  <option value="<?= $s ?>" <?= ($_POST['subject']??'') === $s ? 'selected' : '' ?>><?= $s ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold small">Message *</label>
                <textarea class="form-control" name="message" rows="5" required placeholder="Tell us how we can help..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4 px-4"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
          </form>
        </div>
      </div>
    </div>

    <!-- FAQ -->
    <div class="mt-5">
      <h4 style="font-family:var(--font-display);text-align:center;margin-bottom:2rem">Frequently Asked Questions</h4>
      <div class="accordion" id="faqAccordion">
        <?php
        $faqs = [
          ['q'=>'Do you do custom cake orders?','a'=>'Absolutely! We love creating custom cakes for weddings, birthdays and special events. Contact us at least 5 days in advance for custom orders.'],
          ['q'=>'What are your delivery areas?','a'=>'We deliver across Nairobi and select areas in the Nairobi metropolitan region. Same-day delivery is available for orders placed before 11 AM.'],
          ['q'=>'Are your products suitable for people with allergies?','a'=>'We use common allergens including gluten, dairy, eggs and nuts. Please specify any allergies in your order notes and we will advise if we can accommodate you.'],
          ['q'=>'How far in advance should I order?','a'=>'Standard orders can be placed 24 hours ahead. For large or custom orders, we recommend 3-7 days in advance to ensure the best quality.'],
          ['q'=>'Do you offer bulk or corporate orders?','a'=>'Yes! We offer special pricing for bulk orders (10+ items) and corporate events. Contact us for a custom quote.'],
        ];
        foreach ($faqs as $i => $faq): ?>
        <div class="accordion-item" style="background:var(--warm-white);border:1px solid var(--border)!important;border-radius:var(--radius-sm)!important;margin-bottom:.5rem">
          <h2 class="accordion-header">
            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>"
              style="background:var(--warm-white);color:var(--text-dark);font-weight:600;font-size:.9rem;border-radius:var(--radius-sm)!important">
              <?= htmlspecialchars($faq['q']) ?>
            </button>
          </h2>
          <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
            <div class="accordion-body" style="font-size:.875rem;color:var(--text-medium);"><?= htmlspecialchars($faq['a']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($wa): ?>
<a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$wa) ?>" class="whatsapp-float" target="_blank"><i class="fab fa-whatsapp"></i></a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body></html>
