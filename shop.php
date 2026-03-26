<?php
session_start();
require_once 'db.php';
$pageTitle = 'Shop – NextTrendBag';
$depth = 0;

// Filters
$search      = trim($_GET['search'] ?? '');
$category    = $_GET['category'] ?? '';
$maxPrice    = (int)($_GET['max_price'] ?? 10000);
$sort        = $_GET['sort'] ?? 'featured';
$ratingMin   = (float)($_GET['rating'] ?? 0);
$colorFilter = $_GET['color'] ?? '';

$where  = ['stock > 0'];
$params = [];

if ($search) {
    $where[]  = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category && in_array($category, ['backpacks','handbags','laptop','travel'])) {
    $where[]  = "category = ?";
    $params[] = $category;
}
if ($maxPrice < 10000) {
    $where[]  = "price <= ?";
    $params[] = $maxPrice;
}
if ($ratingMin > 0) {
    $where[]  = "rating >= ?";
    $params[] = $ratingMin;
}
if ($colorFilter) {
    $where[]  = "color = ?";
    $params[] = $colorFilter;
}

$orderBy = match($sort) {
    'price-low'  => 'price ASC',
    'price-high' => 'price DESC',
    'rating'     => 'rating DESC',
    'newest'     => 'created_at DESC',
    default      => 'is_featured DESC, rating DESC',
};

$sql = "SELECT * FROM products WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy";
$st  = $pdo->prepare($sql);
$st->execute($params);
$products = $st->fetchAll();

$cartCount     = getCartCount($pdo);
$wishlistCount = getWishlistCount($pdo);
$currentPage   = 'shop';
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

<!-- Page Banner -->
<div class="page-banner">
  <h1>Our Collection</h1>
  <p>Discover premium bags crafted for every journey</p>
</div>

<!-- Breadcrumb -->
<div class="breadcrumb">
  <a href="index.php">Home</a> /
  <span><?= $category ? ucfirst($category) : ($search ? "Search: $search" : 'Shop') ?></span>
</div>

<div class="shop-layout">

  <!-- ── Sidebar Filters ── -->
  <aside class="filter-sidebar">
    <div class="filter-header">
      <h3>Filters</h3>
      <a href="shop.php" class="filter-clear btn-sm">Clear All</a>
    </div>

    <form id="filterForm" method="GET" action="shop.php">
      <?php if ($search): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>

      <!-- Category -->
      <div class="filter-group">
        <label style="font-size:13px;font-weight:600;color:var(--gray-900);">Category</label>
        <?php foreach (['backpacks'=>'Backpacks','handbags'=>'Handbags','laptop'=>'Laptop Bags','travel'=>'Travel Bags'] as $slug => $label): ?>
        <div class="filter-option">
          <input type="radio" name="category" value="<?= $slug ?>" id="cat_<?= $slug ?>"
            <?= $category === $slug ? 'checked' : '' ?> onchange="this.form.submit()">
          <label for="cat_<?= $slug ?>"><?= $label ?></label>
        </div>
        <?php endforeach; ?>
        <?php if ($category): ?>
        <div class="filter-option">
          <input type="radio" name="category" value="" id="cat_all" <?= !$category ? 'checked' : '' ?> onchange="this.form.submit()">
          <label for="cat_all">All Categories</label>
        </div>
        <?php endif; ?>
      </div>

      <!-- Price -->
      <div class="filter-group">
        <label style="font-size:13px;font-weight:600;color:var(--gray-900);">Max Price</label>
        <div class="price-range">
          <input type="range" id="priceRange" name="max_price" min="500" max="10000" step="100" value="<?= $maxPrice ?>" onchange="this.form.submit()">
          <div class="price-display">
            <span>₹500</span>
            <span id="priceDisplay">₹<?= number_format($maxPrice) ?></span>
          </div>
        </div>
      </div>

      <!-- Color -->
      <div class="filter-group">
        <label style="font-size:13px;font-weight:600;color:var(--gray-900);">Color</label>
        <div class="color-swatches">
          <?php foreach (['black','brown','beige','tan','navy'] as $c): ?>
          <div class="color-swatch <?= $c ?> <?= $colorFilter===$c?'active':'' ?>"
               title="<?= ucfirst($c) ?>"
               onclick="document.getElementById('colorInput').value='<?= $colorFilter===$c?'':$c ?>'; document.getElementById('filterForm').submit()"></div>
          <?php endforeach; ?>
        </div>
        <input type="hidden" id="colorInput" name="color" value="<?= htmlspecialchars($colorFilter) ?>">
      </div>

      <!-- Rating -->
      <div class="filter-group">
        <label style="font-size:13px;font-weight:600;color:var(--gray-900);">Rating</label>
        <?php foreach ([4=>'4★ & up',3=>'3★ & up'] as $stars => $label): ?>
        <div class="filter-option">
          <input type="radio" name="rating" value="<?= $stars ?>" id="r<?= $stars ?>"
            <?= (int)$ratingMin===$stars?'checked':'' ?> onchange="this.form.submit()">
          <label for="r<?= $stars ?>"><?= $label ?></label>
        </div>
        <?php endforeach; ?>
      </div>
    </form>
  </aside>

  <!-- ── Products Area ── -->
  <div class="shop-products">
    <div class="shop-toolbar">
      <p class="shop-count">Showing <strong><?= count($products) ?></strong> products</p>
      <form method="GET" action="shop.php" id="sortForm" style="display:flex;gap:8px;align-items:center;">
        <?php foreach (['category'=>$category,'max_price'=>$maxPrice,'color'=>$colorFilter,'rating'=>$ratingMin,'search'=>$search] as $k=>$v): ?>
          <?php if ($v): ?><input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>"><?php endif; ?>
        <?php endforeach; ?>
        <select name="sort" class="sort-select" onchange="this.form.submit()">
          <option value="featured"   <?= $sort==='featured'   ?'selected':'' ?>>Featured</option>
          <option value="price-low"  <?= $sort==='price-low'  ?'selected':'' ?>>Price: Low to High</option>
          <option value="price-high" <?= $sort==='price-high' ?'selected':'' ?>>Price: High to Low</option>
          <option value="rating"     <?= $sort==='rating'     ?'selected':'' ?>>Highest Rated</option>
          <option value="newest"     <?= $sort==='newest'     ?'selected':'' ?>>Newest</option>
        </select>
      </form>
    </div>

    <?php if (empty($products)): ?>
    <div class="no-products">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      <h3>No products found</h3>
      <p>Try adjusting your filters or <a href="shop.php" style="color:var(--brown);">browse all</a></p>
    </div>
    <?php else: ?>
    <div class="shop-grid">
      <?php foreach ($products as $p): ?>
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
            <span class="stars"><?= str_repeat('★', (int)$p['rating']) ?></span>
            <span class="rating-count">(<?= rand(50,300) ?>)</span>
          </div>
          <div class="product-price">
            <span class="price-current">₹<?= number_format($p['price']) ?></span>
            <?php if ($p['original_price']): ?>
              <span class="price-original">₹<?= number_format($p['original_price']) ?></span>
              <span class="price-off"><?= round((1-$p['price']/$p['original_price'])*100) ?>% OFF</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
