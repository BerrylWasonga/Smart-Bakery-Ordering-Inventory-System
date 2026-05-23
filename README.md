# 🍞 Crumbs & Co — Bakery Management System

A complete, production-ready bakery website and management system built with PHP, MySQL, Bootstrap 5, and vanilla JavaScript.

---

## 📋 Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Admin Credentials](#admin-credentials)
5. [Project Structure](#project-structure)
6. [Features Overview](#features-overview)
7. [Security Notes](#security-notes)
8. [Payment Integration](#payment-integration)
9. [Customisation](#customisation)
10. [Troubleshooting](#troubleshooting)

---

## ✅ Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.1 or higher |
| MySQL | 5.7 / MariaDB 10.3+ |
| Apache | 2.4+ with mod_rewrite |
| WAMP / XAMPP / LAMP | Any recent version |
| PHP Extensions | PDO, PDO_MySQL, GD, FileInfo, mbstring |

---

## 🚀 Installation

### Step 1 — Copy Files

Copy the entire `bakery/` folder into your web server root:

```
C:\wamp64\www\bakery\        (WAMP on Windows)
/var/www/html/bakery/        (LAMP on Linux)
/Applications/MAMP/htdocs/bakery/  (MAMP on Mac)
```

### Step 2 — Create the Database

1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Create a new database named `bakery_db` (UTF8MB4, Unicode CI)
3. Select the `bakery_db` database
4. Click **Import** → choose `database/bakery.sql` → click **Go**

### Step 3 — Configure the Application

1. Copy `config/config.example.php` → `config/config.php`
2. Open `config/config.php` and update:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bakery_db');
define('DB_USER', 'root');      // your MySQL username
define('DB_PASS', '');          // your MySQL password

define('APP_URL', 'http://localhost/bakery');   // your base URL
define('SECRET_KEY', 'your-random-64-character-secret-here');
```

### Step 4 — Set Permissions (Linux/Mac only)

```bash
chmod 755 uploads/
chmod 755 uploads/products/ uploads/banners/ uploads/blog/ uploads/avatars/ uploads/reviews/
```

### Step 5 — Visit the Site

| URL | Purpose |
|-----|---------|
| `http://localhost/bakery/` | Public homepage |
| `http://localhost/bakery/admin/login.php` | Admin panel |
| `http://localhost/bakery/pages/register.php` | Customer registration |

---

## ⚙️ Configuration

All site settings (name, logo, colors, social links, payment details) can be managed via the **Admin Panel → Settings** without touching any code.

### Key `config.php` settings

| Constant | Description |
|----------|-------------|
| `APP_URL` | Full base URL (no trailing slash) |
| `DB_*` | Database credentials |
| `SECRET_KEY` | 64-char random string for security |
| `MAIL_*` | PHPMailer SMTP settings |
| `MPESA_*` | M-Pesa Daraja API credentials |
| `STRIPE_*` | Stripe API keys |
| `PAYPAL_*` | PayPal API credentials |

---

## 🔐 Admin Credentials

Default admin account (change immediately after first login):

| Field | Value |
|-------|-------|
| Email | `admin@bakery.com` |
| Password | `Admin@123` |
| URL | `/admin/login.php` |

**⚠️ Change the admin password immediately in Admin → Settings or via phpMyAdmin.**

---

## 📁 Project Structure

```
bakery/
├── admin/                    # Admin panel
│   ├── includes/             # Admin header & footer
│   │   ├── header.php
│   │   └── footer.php
│   ├── dashboard.php         # Analytics overview
│   ├── products.php          # Product CRUD
│   ├── categories.php        # Category management
│   ├── orders.php            # Order management
│   ├── customers.php         # Customer management
│   ├── coupons.php           # Coupon & discounts
│   ├── blog.php              # Blog post management
│   ├── banners.php           # Homepage banners
│   ├── testimonials.php      # Testimonial management
│   ├── reviews.php           # Review moderation
│   ├── messages.php          # Contact messages inbox
│   ├── inventory.php         # Stock management
│   ├── newsletter.php        # Subscriber list
│   ├── reports.php           # Sales reports & charts
│   ├── settings.php          # Full CMS settings
│   ├── login.php
│   └── logout.php
│
├── ajax/                     # AJAX endpoints
│   ├── cart.php              # Add/update/remove cart
│   ├── wishlist.php          # Toggle wishlist
│   ├── coupon.php            # Apply coupon
│   ├── newsletter.php        # Newsletter subscribe
│   └── quick-view.php        # Product quick view
│
├── assets/
│   ├── css/style.css         # Main stylesheet (CSS variables, dark mode)
│   └── js/main.js            # Main JavaScript (AJAX, toasts, theme)
│
├── classes/                  # PHP classes (extendable)
├── config/
│   ├── config.php            # App configuration (create from example)
│   ├── config.example.php    # Configuration template
│   └── database.php          # PDO singleton
│
├── database/
│   └── bakery.sql            # Full database schema + sample data
│
├── includes/                 # Shared PHP includes
│   ├── bootstrap.php         # App bootstrap (session, config, functions)
│   ├── functions.php         # Helper functions
│   ├── navbar.php            # Shared navigation
│   └── user-sidebar.php      # User dashboard sidebar
│
├── pages/                    # Public-facing pages
│   ├── shop.php              # Product listing
│   ├── product.php           # Single product + reviews
│   ├── cart.php              # Shopping cart
│   ├── checkout.php          # Checkout form
│   ├── order-confirmation.php
│   ├── order-detail.php
│   ├── orders.php
│   ├── dashboard.php         # User dashboard
│   ├── profile.php
│   ├── addresses.php
│   ├── wishlist.php
│   ├── login.php
│   ├── register.php
│   ├── forgot-password.php
│   ├── reset-password.php
│   ├── change-password.php
│   ├── logout.php
│   ├── about.php
│   ├── blog.php
│   ├── blog-post.php
│   └── contact.php
│
├── uploads/                  # User-uploaded files (writable)
│   ├── products/
│   ├── banners/
│   ├── blog/
│   ├── avatars/
│   └── reviews/
│
├── templates/emails/         # Email templates (PHPMailer)
├── .htaccess                 # Apache config (security + caching)
├── robots.txt
├── sitemap.xml.php           # Dynamic sitemap generator
└── index.php                 # Homepage
```

---

## 🌟 Features Overview

### Customer Side
- ✅ Responsive homepage with hero, categories, products, testimonials, blog, newsletter
- ✅ Product shop with search, category filter, sort, pagination
- ✅ Product detail page with image gallery, reviews, related products
- ✅ Shopping cart with AJAX updates, coupon codes, shipping/tax calculation
- ✅ Checkout with billing details, order notes, payment method selection
- ✅ Order confirmation & tracking
- ✅ User registration, login, forgot/reset password
- ✅ User dashboard: orders, wishlist, profile, addresses, password change
- ✅ Product reviews & ratings (pending admin approval)
- ✅ Dark/light mode toggle
- ✅ WhatsApp chat button
- ✅ Newsletter subscription
- ✅ Contact form with FAQ accordion

### Admin Panel
- ✅ Dashboard with KPI cards, revenue chart (Chart.js), top products, recent orders
- ✅ Full product CRUD (image upload, SKU, stock, featured, bestseller)
- ✅ Category management
- ✅ Order management with status workflow (pending → processing → ready → delivered)
- ✅ Customer management with order history and account status control
- ✅ Coupon & discount system (percentage and fixed, with expiry)
- ✅ Blog management with draft/publish workflow
- ✅ Banner/slider management
- ✅ Testimonial management
- ✅ Review moderation with admin reply
- ✅ Contact messages inbox
- ✅ Inventory management with stock adjustment logs
- ✅ Newsletter subscriber list with CSV export
- ✅ Sales reports with Charts.js charts
- ✅ Full CMS settings (site name, logo, colors, social links, SEO, payment, contact)
- ✅ Low stock alerts
- ✅ Activity logging

---

## 🔒 Security Notes

- All user inputs sanitised via `sanitize()`, `sanitizeEmail()`, `sanitizeInt()`
- PDO prepared statements used throughout — SQL injection protected
- CSRF tokens on all forms
- Password hashing with `password_hash(PASSWORD_BCRYPT, cost:12)`
- Separate admin and user sessions
- Upload validation: MIME type checking via `finfo`, size limits, secure filenames
- Direct PHP execution blocked in `/uploads/` via `.htaccess`
- Admin panel requires `requireAdmin()` middleware on every page
- XSS prevention via `htmlspecialchars()` on all output

---

## 💳 Payment Integration

### M-Pesa (Daraja API)
1. Register at [developer.safaricom.co.ke](https://developer.safaricom.co.ke)
2. Get Consumer Key, Consumer Secret, Passkey, and Paybill
3. Add to `config/config.php`
4. Create `ajax/mpesa-callback.php` for STK Push callback

### Stripe
1. Get API keys from [dashboard.stripe.com](https://dashboard.stripe.com)
2. Add `STRIPE_PUBLIC_KEY` and `STRIPE_SECRET_KEY` to `config.php`
3. Include Stripe.js on checkout page
4. Handle `payment_intent` in server-side callback

### PayPal
1. Create app at [developer.paypal.com](https://developer.paypal.com)
2. Add Client ID and Secret to `config.php`
3. Implement PayPal JS SDK on checkout

---

## 🎨 Customisation

### Change Brand Colors
Via Admin Panel → Settings → Appearance, or directly in `assets/css/style.css`:
```css
:root {
    --primary: #8B4513;   /* Main brown */
    --accent:  #D2691E;   /* Lighter brown / orange */
}
```

### Add Google Maps to Contact Page
Replace the placeholder div in `pages/contact.php` with:
```html
<iframe src="https://maps.google.com/maps?q=YOUR+ADDRESS&output=embed"
        width="100%" height="220" style="border:0;border-radius:12px" loading="lazy"></iframe>
```

### Enable Email (PHPMailer)
1. Install via Composer: `composer require phpmailer/phpmailer`
2. Configure `MAIL_*` constants in `config.php`
3. Implement `sendMail()` wrapper in `includes/functions.php`
4. Use email templates from `templates/emails/`

### Update Sample Products
Log in to Admin Panel → Products to edit or replace the 12 sample products loaded with the SQL file.

---

## 🛠️ Troubleshooting

| Problem | Solution |
|---------|----------|
| Blank page / 500 error | Enable `display_errors` in `config.php` (`APP_ENV = 'development'`) |
| Database connection failed | Check `DB_*` constants in `config.php` match your MySQL setup |
| Images not uploading | Check `uploads/` folder exists and is writable (`chmod 755`) |
| 404 on any page | Ensure Apache `mod_rewrite` is enabled and `.htaccess` is being read |
| Admin password forgotten | Run in phpMyAdmin: `UPDATE admins SET password='$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewFGGpk2gNMmCnxW' WHERE email='admin@bakery.com';` (sets to `Admin@123`) |
| Cart not working (AJAX) | Check `APP_URL` in `config.php` matches your actual URL exactly |
| Session issues | Ensure `session.save_path` is writable in your PHP config |

---

## 📞 Support

- Review the code comments throughout each file for implementation details
- All AJAX responses return `{ success: bool, message: string }` JSON
- Database schema is fully documented in `database/bakery.sql`
- Security functions are centralised in `includes/functions.php`

---

*Built with ❤️ — Crumbs & Co Bakery Management System v1.0.0*
