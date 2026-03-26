<?php
session_start();
require_once 'db.php';
$depth = 0;
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];

// Remove from wishlist action
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['remove_id'])) {
    $pdo->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?")->execute([$userId,(int)$_POST['remove_id']]);
    header('Location: wishlist.php?removed=1'); exit;
}
// Move to cart
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cart_id'])) {
    $pid = (int)$_POST['cart_id'];
    $pdo->prepare("INSERT INTO cart (user_id,product_id,quantity) VALUES (?,?,1) ON DUPLICATE KEY UPDATE quantity=quantity+1")->execute([$userId,$pid]);
    $pdo->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?")->execute([$userId,$pid]);
    header('Location: cart.php'); exit;
}

$st = $pdo->prepare("SELECT w.product_id, p.* FROM wishlist w JOIN products p ON w.product_id=p.id WHERE w.user_id=? ORDER BY w.added_at DESC");
$st->execute([$userId]); $items = $st->fetchAll();

$pageTitle = 'My Wishlist – NextTrendBag';
$cartCount = getCartCount($pdo); $wishlistCount = getWishlistCount($pdo); $currentPage = 'wishlist';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="breadcrumb"><a href="index.php">Home</a> / <span>Wishlist</span></div>
<div style="max-width:1200px;margin:0 auto;padding:24px 24px 60px;">
  <h1 style="font-size:1.8rem;margin-bottom:8px;">My Wishlist</h1>
  <p style="color:var(--gray-500);margin-bottom:28px;"><?= count($items) ?> item<?= count($items)!==1?'s':'' ?> saved</p>

  <?php if (isset($_GET['removed'])): ?><div class="flash flash-success">Item removed from wishlist.</div><?php endif; ?>

  <?php if (empty($items)): ?>
  <div style="text-align:center;padding:60px 20px;">
    <div style="font-size:60px;margin-bottom:20px;">❤️</div>
    <h2 style="margin-bottom:12px;">Your wishlist is empty</h2>
    <p style="color:var(--gray-500);margin-bottom:28px;">Save items you love and find them here.</p>
    <a href="shop.php" class="btn btn-primary">Start Shopping</a>
  </div>
  <?php else: ?>
  <div class="wishlist-grid">
    <?php foreach ($items as $p): ?>
    <div class="product-card">
      <div class="product-img-wrap" onclick="location.href='product.php?id=<?= $p['product_id'] ?>'">
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
        <?php if ($p['badge']): ?><span class="product-badge"><?= $p['badge'] ?></span><?php endif; ?>
      </div>
      <div class="product-info">
        <div class="product-cat"><?= ucfirst($p['category']) ?></div>
        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-price" style="margin-bottom:12px;">
          <span class="price-current">₹<?= number_format($p['price']) ?></span>
          <?php if ($p['original_price']): ?><span class="price-original">₹<?= number_format($p['original_price']) ?></span><?php endif; ?>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <form method="POST"><input type="hidden" name="cart_id" value="<?= $p['product_id'] ?>"><button type="submit" class="btn btn-primary btn-sm btn-block">Move to Cart</button></form>
          <form method="POST" onsubmit="return confirm('Remove from wishlist?')"><input type="hidden" name="remove_id" value="<?= $p['product_id'] ?>"><button type="submit" class="btn btn-outline btn-sm btn-block">Remove</button></form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
