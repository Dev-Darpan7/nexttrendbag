<?php
session_start();
require_once 'db.php';

$pageTitle = 'NextTrendBag – Premium Bags for Every Lifestyle';
$pageDesc  = 'Shop premium backpacks, handbags, laptop bags and travel bags at NextTrendBag.';
$depth = 0;

// Fetch featured products
$featuredStmt = $pdo->query("SELECT * FROM products WHERE is_featured=1 LIMIT 8");
$featuredProducts = $featuredStmt->fetchAll();

// Fetch all products for best-sellers (top rated)
$bestSellersStmt = $pdo->query("SELECT * FROM products ORDER BY rating DESC, id ASC LIMIT 8");
$bestSellers = $bestSellersStmt->fetchAll();

$cartCount     = getCartCount($pdo);
$wishlistCount = getWishlistCount($pdo);
$currentPage   = 'index';

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $pageDesc ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<?php if (isset($_GET['welcome'])): ?>
<div style="background:#dcfce7;color:#166534;text-align:center;padding:14px;font-weight:500;">
  🎉 Welcome to NextTrendBag! Happy shopping!
</div>
<?php endif; ?>

<!-- ── HERO ─────────────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-blob hero-blob-1"></div>
  <div class="hero-blob hero-blob-2"></div>
  <div class="hero-inner">
    <div>
      <div class="hero-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        New Collection 2026
      </div>
      <h1 class="hero-title">Carry Style.<br><span>Carry Confidence.</span></h1>
      <p class="hero-text">Premium bags crafted for the modern lifestyle. Designed for students and professionals who demand both style and functionality.</p>
      <div class="hero-cta">
        <a href="shop.php" class="btn btn-primary">Shop Now</a>
        <a href="about.php" class="btn btn-outline">Our Story</a>
      </div>
      <div class="hero-stats">
        <div>
          <div class="hero-stat-num">50K+</div>
          <div class="hero-stat-label">Happy Customers</div>
        </div>
        <div class="hero-divider"></div>
        <div>
          <div class="hero-stat-num">4.9</div>
          <div class="hero-stat-label">Avg. Rating</div>
        </div>
        <div class="hero-divider"></div>
        <div>
          <div class="hero-stat-num">200+</div>
          <div class="hero-stat-label">Products</div>
        </div>
      </div>
    </div>
    <div>
      <div class="hero-image-wrap">
        <img src="images/Screenshot 2026-02-24 093022.png" alt="Premium Bag" loading="eager">
        <!-- Float cards -->
        <div class="hero-float-card top-right" style="display:flex;align-items:center;gap:10px;">
          <div class="card-icon" style="background:#dcfce7;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
          </div>
          <div>
            <div class="card-label">Premium Quality</div>
            <div class="card-sub">Handcrafted</div>
          </div>
        </div>
        <div class="hero-float-card bottom-left" style="display:flex;align-items:center;gap:10px;">
          <div style="display:flex;margin-right:4px;">
            <div style="width:28px;height:28px;border-radius:50%;background:var(--beige-300);border:2px solid white;"></div>
            <div style="width:28px;height:28px;border-radius:50%;background:var(--beige-400,#c9ac85);border:2px solid white;margin-left:-8px;"></div>
            <div style="width:28px;height:28px;border-radius:50%;background:var(--brown);border:2px solid white;margin-left:-8px;"></div>
          </div>
          <div>
            <div class="card-label">2.5k+ Reviews</div>
            <div style="color:#f59e0b;font-size:13px;">★★★★★</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CATEGORIES ─────────────────────────────────────────────── -->
<section class="section-pad bg-white">
  <div class="container">
    <div class="section-head">
      <span class="section-label">Browse Collection</span>
      <h2>Featured Categories</h2>
      <p>Explore our curated collection of premium bags designed for every occasion</p>
    </div>
    <div class="categories-grid">
      <?php
      $cats = [
        ['backpacks', 'Backpacks',   '24 Products', 'images/Screenshot 2026-02-24 093022.png'],
        ['handbags',  'Handbags',    '32 Products', 'images/bag5.jpeg'],
        ['laptop',    'Laptop Bags', '18 Products', 'images/breifcase.jpeg'],
        ['travel',    'Travel Bags', '15 Products', 'images/Screenshot 2026-02-24 093311.png'],
      ];
      foreach ($cats as [$slug, $label, $count, $img]): ?>
      <a href="shop.php?category=<?= $slug ?>" class="cat-card">
        <img src="<?= $img ?>" alt="<?= $label ?>">
        <div class="cat-overlay"></div>
        <div class="cat-view-all">View All</div>
        <div class="cat-info">
          <h3><?= $label ?></h3>
          <p><?= $count ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── BEST SELLERS ───────────────────────────────────────────── -->
<section class="section-pad bg-beige">
  <div class="container">
    <div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-end;text-align:left;margin-bottom:36px;">
      <div>
        <span class="section-label">Top Picks</span>
        <h2>Best Sellers</h2>
      </div>
      <a href="shop.php" class="btn btn-outline-brown btn-sm">View All →</a>
    </div>
    <div class="products-grid">
      <?php foreach ($bestSellers as $p): ?>
      <div class="product-card" onclick="location.href='product.php?id=<?= $p['id'] ?>'">
        <div class="product-img-wrap">
          <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
          <?php if ($p['badge']): ?>
            <span class="product-badge"><?= htmlspecialchars($p['badge']) ?></span>
          <?php endif; ?>
          <div class="product-actions">
            <button class="product-action-btn" onclick="event.stopPropagation();addToCart(<?= $p['id'] ?>)">Add to Cart</button>
            <button class="product-wishlist-btn" onclick="event.stopPropagation();toggleWishlist(<?= $p['id'] ?>)" title="Wishlist">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </button>
          </div>
        </div>
        <div class="product-info">
          <div class="product-cat"><?= ucfirst($p['category']) ?></div>
          <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="product-rating">
            <span class="stars"><?= str_repeat('★', (int)$p['rating']) ?><?= $p['rating'] != (int)$p['rating'] ? '½' : '' ?></span>
            <span class="rating-count">(<?= rand(80,300) ?>)</span>
          </div>
          <div class="product-price">
            <span class="price-current">₹<?= number_format($p['price']) ?></span>
            <?php if ($p['original_price']): ?>
              <span class="price-original">₹<?= number_format($p['original_price']) ?></span>
              <span class="price-off"><?= round((1 - $p['price']/$p['original_price'])*100) ?>% OFF</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── OFFER BANNER ───────────────────────────────────────────── -->
<section class="offer-banner">
  <div class="dots-bg"></div>
  <div class="offer-banner-inner">
    <div>
      <span class="offer-label">⚡ Limited Time Offer</span>
      <h2 class="offer-title">Get 30% Off<br>On Your First Order</h2>
      <p class="offer-code">Use code <span>NEXT30</span> at checkout</p>
      <a href="shop.php" class="btn" style="background:white;color:var(--brown);padding:14px 32px;">Shop Now</a>
    </div>
    <div class="countdown-box">
      <p class="countdown-label">Offer ends in</p>
      <div class="countdown-grid">
        <div class="countdown-unit"><div class="countdown-num" id="cdDays">00</div><div class="countdown-name">Days</div></div>
        <div class="countdown-unit"><div class="countdown-num" id="cdHours">00</div><div class="countdown-name">Hours</div></div>
        <div class="countdown-unit"><div class="countdown-num" id="cdMins">00</div><div class="countdown-name">Mins</div></div>
        <div class="countdown-unit"><div class="countdown-num" id="cdSecs">00</div><div class="countdown-name">Secs</div></div>
      </div>
    </div>
  </div>
</section>

<!-- ── WHY CHOOSE US ──────────────────────────────────────────── -->
<section class="section-pad bg-white">
  <div class="container">
    <div class="section-head">
      <span class="section-label">Our Promise</span>
      <h2>Why Choose Us</h2>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg></div>
        <h3>Free Shipping</h3>
        <p>On orders above ₹999</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
        <h3>Easy Returns</h3>
        <p>30-day return policy</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg></div>
        <h3>Premium Quality</h3>
        <p>Handcrafted materials</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
        <h3>Secure Payment</h3>
        <p>100% secure checkout</p>
      </div>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ───────────────────────────────────────────── -->
<section class="section-pad bg-beige">
  <div class="container">
    <div class="section-head">
      <span class="section-label">Testimonials</span>
      <h2>What Our Customers Say</h2>
    </div>
    <div class="testimonials-grid">
      <?php
      $testimonials = [
        ['PS','Priya Sharma','Software Engineer','The quality of these bags is exceptional! I bought the laptop backpack and it\'s perfect for my daily commute. Stylish and functional.'],
        ['AK','Ananya Kapoor','Marketing Manager','Best bag purchase I\'ve ever made! The leather handbag is gorgeous and the craftsmanship is top-notch. Highly recommend NextTrendBag!'],
        ['RV','Rahul Verma','Content Creator','Great travel bag for weekend trips! Spacious, durable, and looks premium. The customer service was also excellent.'],
      ];
      foreach ($testimonials as [$init, $name, $role, $text]): ?>
      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">"<?= $text ?>"</p>
        <div class="testimonial-author">
          <div class="author-avatar"><?= $init ?></div>
          <div>
            <div class="author-name"><?= $name ?></div>
            <div class="author-role"><?= $role ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── NEWSLETTER ─────────────────────────────────────────────── -->
<section class="newsletter-section">
  <div class="newsletter-inner">
    <h2>Stay in the Loop</h2>
    <p>Subscribe to get exclusive offers, new arrivals, and style tips delivered to your inbox.</p>
    <?php if (isset($_GET['subscribed'])): ?>
      <p style="color:#4ade80;font-weight:600;font-size:15px;">🎉 Thank you for subscribing!</p>
    <?php else: ?>
    <form action="subscribe.php" method="POST" class="newsletter-form">
      <input type="email" name="email" required placeholder="Enter your email address">
      <button type="submit" class="btn btn-primary">Subscribe</button>
    </form>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
