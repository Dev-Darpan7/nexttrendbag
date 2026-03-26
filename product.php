<?php
session_start();
require_once 'db.php';
$depth = 0;

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: shop.php'); exit; }

$st = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { header('Location: shop.php'); exit; }

// Related products (same category, excluding current)
$st2 = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$st2->execute([$p['category'], $id]);
$related = $st2->fetchAll();

$pageTitle = htmlspecialchars($p['name']) . ' – NextTrendBag';
$cartCount     = getCartCount($pdo);
$wishlistCount = getWishlistCount($pdo);
$currentPage   = 'product';
$discount = $p['original_price'] ? round((1-$p['price']/$p['original_price'])*100) : 0;

// Images (use same image multiple times as thumbnails for demo)
$images = [$p['image'], $p['image'], $p['image'], $p['image']];
$highlights = [
    'Premium water-resistant material',
    'Reinforced zippers and stitching',
    'Ergonomic design for all-day comfort',
    'Multiple compartments for organisation',
    'Warranty: 12 months manufacturer\'s warranty',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<!-- Breadcrumb -->
<div class="breadcrumb">
  <a href="index.php">Home</a> /
  <a href="shop.php">Shop</a> /
  <a href="shop.php?category=<?= $p['category'] ?>"><?= ucfirst($p['category']) ?></a> /
  <span><?= htmlspecialchars($p['name']) ?></span>
</div>

<div class="prodetail-layout">
  <!-- Gallery -->
  <div class="prodetail-gallery">
    <div class="main-img">
      <img id="mainProductImg" src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    </div>
    <div class="thumb-row">
      <?php foreach ($images as $i => $img): ?>
      <div class="thumb <?= $i===0?'active':'' ?>" onclick="switchImage('<?= htmlspecialchars($img) ?>', this)">
        <img src="<?= htmlspecialchars($img) ?>" alt="View <?= $i+1 ?>">
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Info -->
  <div class="prodetail-info">
    <div class="prodetail-cat"><?= ucfirst($p['category']) ?></div>
    <h1 class="prodetail-name"><?= htmlspecialchars($p['name']) ?></h1>

    <div class="prodetail-rating">
      <span class="stars"><?= str_repeat('★', (int)$p['rating']) ?></span>
      <span style="color:var(--gray-500);font-size:14px;">(<?= rand(80,250) ?> reviews)</span>
    </div>

    <div class="prodetail-price">
      <span class="prodetail-price-current">₹<?= number_format($p['price']) ?></span>
      <?php if ($p['original_price']): ?>
        <span class="prodetail-price-original">₹<?= number_format($p['original_price']) ?></span>
        <span class="prodetail-price-off"><?= $discount ?>% OFF</span>
      <?php endif; ?>
    </div>

    <p class="prodetail-stock">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
      In Stock (<?= $p['stock'] ?> units)
    </p>

    <!-- Highlights -->
    <div class="prodetail-highlights">
      <h4>Highlights</h4>
      <ul>
        <?php foreach ($highlights as $h): ?>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
          <?= htmlspecialchars($h) ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Quantity & Actions -->
    <div class="qty-row">
      <span class="qty-label">Quantity:</span>
      <div class="qty-control">
        <button class="qty-btn" onclick="changeQty(-1)">−</button>
        <span class="qty-num" id="productQty">1</span>
        <button class="qty-btn" onclick="changeQty(1)">+</button>
      </div>
    </div>

    <div class="prodetail-cta">
      <button class="btn btn-primary btn-block" id="addToCartBtn" onclick="addToCartFromDetail(<?= $p['id'] ?>)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        Add to Cart
      </button>
      <button class="btn btn-outline btn-block" id="buyNowBtn" onclick="buyNowFromDetail(<?= $p['id'] ?>)" style="text-align:center;">
        ⚡ Buy Now
      </button>
      <button class="btn btn-outline-brown btn-block" onclick="toggleWishlist(<?= $p['id'] ?>)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        Add to Wishlist
      </button>
    </div>

    <!-- Description -->
    <div class="prodetail-desc">
      <h4>Description</h4>
      <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
    </div>
  </div>
</div>

<!-- Related Products -->
<?php if ($related): ?>
<section class="section-pad bg-beige">
  <div class="container">
    <h2 style="margin-bottom:32px;">Related Products</h2>
    <div class="products-grid">
      <?php foreach ($related as $r): ?>
      <div class="product-card" onclick="location.href='product.php?id=<?= $r['id'] ?>'">
        <div class="product-img-wrap">
          <img src="<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
          <?php if ($r['badge']): ?>
            <span class="product-badge"><?= htmlspecialchars($r['badge']) ?></span>
          <?php endif; ?>
        </div>
        <div class="product-info">
          <div class="product-name"><?= htmlspecialchars($r['name']) ?></div>
          <div class="product-price">
            <span class="price-current">₹<?= number_format($r['price']) ?></span>
            <?php if ($r['original_price']): ?>
              <span class="price-original">₹<?= number_format($r['original_price']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
<script>
function getQty() {
  return parseInt(document.getElementById('productQty').textContent) || 1;
}

function addToCartFromDetail(productId) {
  const qty = getQty();
  const btn = document.getElementById('addToCartBtn');
  btn.disabled = true;
  btn.textContent = 'Adding…';

  fetch('/NextTrendBag/cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${productId}&qty=${qty}`
  })
  .then(r => r.json())
  .then(d => {
    btn.disabled = false;
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg> Add to Cart';
    if (d.success) {
      updateCartBadge(d.cart_count);
      showToast('Added to cart! 🛍️');
    } else {
      if (d.redirect) window.location.href = d.redirect;
      else showToast(d.message || 'Could not add to cart', 'error');
    }
  })
  .catch(() => {
    btn.disabled = false;
    btn.innerHTML = 'Add to Cart';
    showToast('Could not reach server. Are you logged in?', 'error');
  });
}

function buyNowFromDetail(productId) {
  const qty = getQty();
  const btn = document.getElementById('buyNowBtn');
  btn.disabled = true;
  btn.textContent = 'Processing…';

  fetch('/NextTrendBag/cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${productId}&qty=${qty}`
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      // Go straight to checkout
      window.location.href = 'checkout.php';
    } else {
      btn.disabled = false;
      btn.textContent = '⚡ Buy Now';
      if (d.redirect) window.location.href = d.redirect; // login redirect
      else showToast(d.message || 'Could not proceed', 'error');
    }
  })
  .catch(() => {
    btn.disabled = false;
    btn.textContent = '⚡ Buy Now';
    showToast('Could not reach server. Are you logged in?', 'error');
  });
}
</script>
</body>
</html>
