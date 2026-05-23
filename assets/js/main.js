/**
 * Crumbs & Co Bakery – Main JS
 */
const APP_URL    = window.APP_URL    || '';
const CSRF_TOKEN = window.CSRF_TOKEN || '';

/* ── Navbar scroll ── */
window.addEventListener('scroll', () => {
  document.getElementById('mainNav')?.classList.toggle('scrolled', window.scrollY > 50);
});

/* ── Theme toggle ── */
const themeKey = 'bakery_theme';
const savedTheme = localStorage.getItem(themeKey) || 'light';
applyTheme(savedTheme);

document.getElementById('themeToggle')?.addEventListener('click', () => {
  const cur  = document.body.getAttribute('data-theme') || 'light';
  const next = cur === 'light' ? 'dark' : 'light';
  applyTheme(next);
  localStorage.setItem(themeKey, next);
});

function applyTheme(theme) {
  document.body.setAttribute('data-theme', theme);
  document.documentElement.setAttribute('data-theme', theme);
  const icon = document.getElementById('themeIcon');
  if (icon) icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

/* ── Toast ── */
function showToast(msg, type = 'success') {
  const icons  = { success: 'check-circle', error: 'times-circle', warning: 'exclamation-circle', info: 'info-circle' };
  const colors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
  const id   = 'toast_' + Date.now();
  const col  = colors[type] || colors.info;
  const html = `<div id="${id}" class="toast show align-items-center mb-2" role="alert"
    style="border-left:4px solid ${col};min-width:280px;background:var(--warm-white,#fff);border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.12)">
    <div class="d-flex align-items-center p-3 gap-2">
      <i class="fas fa-${icons[type]||'info-circle'}" style="color:${col};font-size:1.1rem"></i>
      <span style="font-size:.9rem;color:var(--text-dark,#333);flex:1">${msg}</span>
      <button type="button" class="btn-close btn-sm ms-auto" onclick="document.getElementById('${id}').remove()"></button>
    </div></div>`;
  const c = document.getElementById('toastContainer');
  if (c) { c.insertAdjacentHTML('beforeend', html); setTimeout(() => document.getElementById(id)?.remove(), 4000); }
}
window.showToast = showToast;

/* ── AJAX Add to Cart ── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.ajax-add-cart');
  if (!btn || btn.disabled) return;
  const productId = btn.dataset.productId;
  const name = btn.dataset.productName || 'Item';
  
  // Resolve variant ID if any
  let variantId = btn.dataset.variantId || '';
  if (!variantId) {
    const varEl = document.querySelector('input[name="product_variant"]:checked') || document.querySelector('select[name="product_variant"]');
    if (varEl) {
      variantId = varEl.value;
    }
  }

  // Resolve quantity
  let quantity = 1;
  const qtyInput = document.getElementById('qtyInput');
  if (qtyInput && (btn.classList.contains('flex-fill') || btn.closest('.py-5'))) {
    quantity = parseInt(qtyInput.value) || 1;
  }

  btn.disabled = true;
  const origHTML = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  try {
    const res  = await fetch(`${APP_URL}/ajax/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=add&product_id=${productId}&variant_id=${variantId}&quantity=${quantity}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
    });
    const data = await res.json();
    if (data.success) {
      showToast(`<strong>${name}</strong> added to cart!`, 'success');
      updateCartBadge(data.cart_count);
    } else {
      showToast(data.message || 'Error adding to cart', 'error');
    }
  } catch { showToast('Connection error. Please try again.', 'error'); }
  finally { btn.disabled = false; btn.innerHTML = origHTML; }
});

function updateCartBadge(count) {
  let badge = document.getElementById('cartBadge');
  if (!badge) {
    const cartLink = document.querySelector('a[href*="cart.php"]');
    if (cartLink) {
      cartLink.style.position = 'relative';
      cartLink.insertAdjacentHTML('beforeend', `<span class="cart-badge" id="cartBadge">${count}</span>`);
    }
  } else {
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  }
}

/* ── AJAX Wishlist ── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.ajax-wishlist');
  if (!btn) return;
  try {
    const res  = await fetch(`${APP_URL}/ajax/wishlist.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=toggle&product_id=${btn.dataset.productId}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
    });
    const data = await res.json();
    if (data.success) {
      const icon = btn.querySelector('i');
      if (data.added) { icon.className = 'fas fa-heart'; btn.style.color = '#e74c3c'; showToast('Added to wishlist!', 'success'); }
      else            { icon.className = 'far fa-heart'; btn.style.color = ''; showToast('Removed from wishlist', 'info'); }
    } else { showToast(data.message || 'Please login to use wishlist', 'warning'); }
  } catch { showToast('Error. Please try again.', 'error'); }
});

/* ── Quick View ── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.btn-quick-view');
  if (!btn) return;
  const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
  modal.show();
  const content = document.getElementById('quickViewContent');
  content.innerHTML = '<div class="text-center py-5"><div class="spinner-border" style="color:var(--primary)"></div></div>';
  try {
    const res  = await fetch(`${APP_URL}/ajax/quick-view.php?id=${btn.dataset.productId}`);
    content.innerHTML = await res.text();
  } catch { content.innerHTML = '<p class="text-center text-muted py-4">Could not load product.</p>'; }
});

/* ── Newsletter ── */
document.getElementById('newsletterForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form  = e.target;
  const btn   = form.querySelector('button[type=submit]');
  const msgEl = document.getElementById('newsletterMsg');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Subscribing...';
  try {
    const res  = await fetch(`${APP_URL}/ajax/newsletter.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `email=${encodeURIComponent(form.querySelector('[name=email]').value)}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
    });
    const data = await res.json();
    if (msgEl) msgEl.innerHTML = `<small class="text-${data.success ? 'success' : 'danger'}">${data.message}</small>`;
    if (data.success) form.reset();
  } catch { if (msgEl) msgEl.innerHTML = '<small class="text-danger">Error. Please try again.</small>'; }
  finally { btn.disabled = false; btn.innerHTML = 'Subscribe <i class="fas fa-paper-plane ms-1"></i>'; }
});

/* ── Cart qty buttons ── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.cart-qty-btn');
  if (!btn) return;
  btn.disabled = true;
  try {
    const res  = await fetch(`${APP_URL}/ajax/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=${btn.dataset.action}&cart_id=${btn.dataset.cartId}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
    });
    const data = await res.json();
    if (data.success) location.reload();
    else showToast(data.message || 'Error', 'error');
  } catch { showToast('Connection error', 'error'); }
  finally { btn.disabled = false; }
});

/* ── Coupon ── */
document.getElementById('applyCoupon')?.addEventListener('click', async () => {
  const code = document.getElementById('couponCode')?.value?.trim();
  if (!code) return;
  try {
    const res  = await fetch(`${APP_URL}/ajax/coupon.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `code=${encodeURIComponent(code)}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
    });
    const data = await res.json();
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1000);
  } catch { showToast('Error applying coupon', 'error'); }
});

/* ── Star rating picker ── */
document.querySelectorAll('.star-picker').forEach(picker => {
  const stars = picker.querySelectorAll('i');
  const input = document.getElementById(picker.dataset.input);
  stars.forEach((star, i) => {
    star.addEventListener('mouseover', () => stars.forEach((s, j) => s.className = j <= i ? 'fas fa-star text-warning' : 'far fa-star text-muted'));
    star.addEventListener('mouseout',  () => { const v = parseInt(input?.value || 0); stars.forEach((s, j) => s.className = j < v ? 'fas fa-star text-warning' : 'far fa-star text-muted'); });
    star.addEventListener('click',     () => { if (input) input.value = i + 1; stars.forEach((s, j) => s.className = j <= i ? 'fas fa-star text-warning' : 'far fa-star text-muted'); });
  });
});

/* ── File preview ── */
document.querySelectorAll('input[type=file][data-preview]').forEach(input => {
  input.addEventListener('change', () => {
    const preview = document.getElementById(input.dataset.preview);
    if (!preview || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  });
});

/* ── Confirm delete ── */
document.addEventListener('click', e => {
  const el = e.target.closest('[data-confirm]');
  if (el && !confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
});

/* ── Admin sidebar toggle ── */
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  document.querySelector('.admin-sidebar')?.classList.toggle('open');
});

/* ── Admin sidebar overlay close ── */
document.addEventListener('click', e => {
  const sidebar = document.querySelector('.admin-sidebar');
  if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !e.target.closest('#sidebarToggle')) {
    sidebar.classList.remove('open');
  }
});

/* ── Smooth scroll for anchor links ── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});
