/* ===================================================
   NextTrendBag – bag.js
   UI Interactions: menu, search, gallery, qty, AJAX cart
   =================================================== */

/* ── Mobile Menu ────────────────────────────────── */
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  if (menu) menu.classList.toggle('open');
}

/* ── Search Bar ─────────────────────────────────── */
function toggleSearch() {
  const bar = document.getElementById('searchBar');
  if (!bar) return;
  const isOpen = bar.classList.toggle('open');
  if (isOpen) {
    setTimeout(() => {
      const inp = document.getElementById('searchInput');
      if (inp) inp.focus();
    }, 80);
  }
}

// Close search on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    const bar = document.getElementById('searchBar');
    if (bar) bar.classList.remove('open');
  }
});

/* ── Product Image Gallery ──────────────────────── */
function switchImage(src, el) {
  const main = document.getElementById('mainProductImg');
  if (main) {
    main.style.opacity = '0';
    setTimeout(() => { main.src = src; main.style.opacity = '1'; }, 180);
  }
  // Update active thumbnail
  document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
  if (el) el.closest('.thumb').classList.add('active');
}

/* ── Quantity Picker ────────────────────────────── */
function changeQty(delta) {
  const el = document.getElementById('productQty');
  if (!el) return;
  let v = Math.max(1, parseInt(el.textContent) + delta);
  el.textContent = v;
}

/* ── Cart AJAX ──────────────────────────────────── */
function addToCart(productId, qty = 1) {
  // Use absolute path so it works from any page depth
  const url = '/NextTrendBag/cart_action.php';
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${productId}&qty=${qty}`
  })
  .then(r => {
    if (!r.ok) throw new Error('Server error');
    return r.json();
  })
  .then(d => {
    if (d.success) {
      updateCartBadge(d.cart_count);
      showToast('Added to cart! 🛍️');
    } else {
      if (d.redirect) {
        showToast('Please login to add items to cart', 'error');
        setTimeout(() => window.location.href = d.redirect, 1500);
      } else {
        showToast(d.message || 'Could not add to cart', 'error');
      }
    }
  })
  .catch(() => showToast('Please login first to add items to cart', 'error'));
}

function updateCartCount(newCount) {
  updateCartBadge(newCount);
}
function updateCartBadge(count) {
  const badge = document.getElementById('cartCount');
  if (badge) badge.textContent = count;
}

function toggleWishlist(productId) {
  fetch('/NextTrendBag/wishlist_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}`
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) showToast(d.message);
    else if (d.redirect) {
      showToast('Please login to use wishlist', 'error');
      setTimeout(() => window.location.href = d.redirect, 1500);
    }
  })
  .catch(() => showToast('Please login first', 'error'));
}

/* ── Countdown Timer ────────────────────────────── */
function startCountdown(targetDate) {
  function update() {
    const now  = new Date().getTime();
    const diff = new Date(targetDate).getTime() - now;
    if (diff <= 0) { clearInterval(timer); return; }
    const days  = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000);
    const mins  = Math.floor((diff % 3600000) / 60000);
    const secs  = Math.floor((diff % 60000) / 1000);
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = String(val).padStart(2,'0'); };
    set('cdDays', days); set('cdHours', hours); set('cdMins', mins); set('cdSecs', secs);
  }
  update();
  const timer = setInterval(update, 1000);
}

/* ── Toast Notification ─────────────────────────── */
function showToast(msg, type = 'success') {
  let toast = document.getElementById('globalToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'globalToast';
    Object.assign(toast.style, {
      position: 'fixed', bottom: '32px', right: '32px', zIndex: '9999',
      padding: '14px 24px', borderRadius: '12px', fontSize: '14px',
      fontWeight: '600', fontFamily: 'Inter, sans-serif',
      boxShadow: '0 8px 32px rgba(0,0,0,.15)',
      transition: 'all .3s ease', opacity: '0', transform: 'translateY(16px)',
      minWidth: '220px', maxWidth: '360px'
    });
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.style.background = type === 'error' ? '#fee2e2' : '#dcfce7';
  toast.style.color       = type === 'error' ? '#991b1b' : '#166534';
  toast.style.border      = `1px solid ${type === 'error' ? '#fecaca' : '#bbf7d0'}`;
  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(16px)';
  }, 3000);
}

/* ── Price range display ────────────────────────── */
const priceRange = document.getElementById('priceRange');
const priceDisplay = document.getElementById('priceDisplay');
if (priceRange && priceDisplay) {
  const fmt = v => '₹' + Number(v).toLocaleString('en-IN');
  priceDisplay.textContent = fmt(priceRange.value);
  priceRange.addEventListener('input', () => priceDisplay.textContent = fmt(priceRange.value));
}

/* ── Smooth scroll-reveal animation ────────────── */
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('fade-in');
      observer.unobserve(e.target);
    }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.product-card, .cat-card, .feature-card, .testimonial-card').forEach(el => observer.observe(el));

/* ── Countdown auto-start ───────────────────────── */
window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('cdDays')) {
    startCountdown('2026-04-15T23:59:59');
  }
});
