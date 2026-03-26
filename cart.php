<?php
session_start();
require_once 'db.php';
$depth = 0;

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'cart.php';
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Fetch cart items with product info
$st = $pdo->prepare("
    SELECT c.product_id, c.quantity, p.name, p.price, p.original_price, p.image, p.category
    FROM cart c JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$st->execute([$userId]);
$cartItems = $st->fetchAll();

// Coupon logic
$couponMsg     = '';
$couponOK      = false;
$discountPct   = 0;
$appliedCoupon = $_SESSION['applied_coupon'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    $c = $pdo->prepare("SELECT * FROM coupons WHERE code=? AND is_active=1 AND (expires_at IS NULL OR expires_at >= CURDATE())");
    $c->execute([$code]);
    $coupon = $c->fetch();
    if ($coupon && $coupon['used'] < $coupon['max_uses']) {
        $_SESSION['applied_coupon']    = $code;
        $_SESSION['coupon_discount']   = $coupon['discount_percent'];
        $appliedCoupon = $code;
        $couponMsg = "✅ Coupon <strong>$code</strong> applied — {$coupon['discount_percent']}% off!";
        $couponOK  = true;
    } else {
        unset($_SESSION['applied_coupon'], $_SESSION['coupon_discount']);
        $couponMsg = "❌ Invalid or expired coupon code.";
    }
}
if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['applied_coupon'], $_SESSION['coupon_discount']);
    $appliedCoupon = '';
}

$discountPct = (float)($_SESSION['coupon_discount'] ?? 0);

// Totals
$subtotal  = array_sum(array_map(fn($i) => $i['price']*$i['quantity'], $cartItems));
$shipping  = $subtotal > 999 ? 0 : 79;
$discount  = round($subtotal * $discountPct / 100, 2);
$total     = $subtotal + $shipping - $discount;

$pageTitle = 'My Cart – NextTrendBag';
$cartCount     = getCartCount($pdo);
$wishlistCount = getWishlistCount($pdo);
$currentPage   = 'cart';
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

<div class="breadcrumb">
  <a href="index.php">Home</a> / <span>Cart</span>
</div>

<div style="max-width:1200px;margin:0 auto;padding:0 24px 16px;">
  <h1 style="font-size:2rem;">Shopping Cart</h1>
</div>

<?php if (empty($cartItems)): ?>
<div style="max-width:500px;margin:60px auto;text-align:center;padding:0 24px;">
  <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--gray-300)" stroke-width="1" style="display:block;margin:0 auto 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
  <h2 style="margin-bottom:12px;">Your cart is empty</h2>
  <p style="color:var(--gray-500);margin-bottom:28px;">Looks like you haven't added any items yet.</p>
  <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
</div>
<?php else: ?>

<div class="cart-layout">
  <!-- Cart Table -->
  <div class="cart-table-wrap">
    <table>
      <thead>
        <tr>
          <th colspan="2">Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="cartTableBody">
        <?php foreach ($cartItems as $item): ?>
        <tr id="cartRow<?= $item['product_id'] ?>">
          <td style="width:90px;">
            <img class="cart-item-img" src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          </td>
          <td>
            <a href="product.php?id=<?= $item['product_id'] ?>" style="text-decoration:none;">
              <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="cart-item-cat"><?= ucfirst($item['category']) ?></div>
            </a>
          </td>
          <td>₹<?= number_format($item['price']) ?></td>
          <td>
            <div class="qty-control" style="width:fit-content;">
              <button class="qty-btn" onclick="updateCartQty(<?= $item['product_id'] ?>, -1, <?= $item['quantity'] ?>)">−</button>
              <span class="qty-num" id="qty<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
              <button class="qty-btn" onclick="updateCartQty(<?= $item['product_id'] ?>, 1, <?= $item['quantity'] ?>)">+</button>
            </div>
          </td>
          <td style="font-weight:700;" id="sub<?= $item['product_id'] ?>">₹<?= number_format($item['price']*$item['quantity']) ?></td>
          <td>
            <button class="remove-btn" onclick="removeFromCart(<?= $item['product_id'] ?>)" title="Remove">✕</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Order Summary -->
  <div class="order-summary-box">
    <h3>Order Summary</h3>

    <!-- Coupon -->
    <form method="POST" class="coupon-form">
      <input type="text" class="coupon-input" name="coupon_code" placeholder="Discount code" value="<?= htmlspecialchars($appliedCoupon) ?>">
      <button type="submit" name="apply_coupon" class="btn btn-outline-brown btn-sm">Apply</button>
    </form>
    <?php if ($couponMsg): ?>
      <p class="coupon-msg <?= $couponOK?'success':'error' ?>"><?= $couponMsg ?></p>
    <?php endif; ?>
    <?php if ($appliedCoupon && !isset($_POST['apply_coupon'])): ?>
      <p class="coupon-msg success">✅ Coupon <strong><?= htmlspecialchars($appliedCoupon) ?></strong> applied – <?= $discountPct ?>% off</p>
      <form method="POST" style="margin-bottom:14px;">
        <button type="submit" name="remove_coupon" style="background:none;border:none;color:var(--danger);font-size:13px;cursor:pointer;padding:0;">✕ Remove coupon</button>
      </form>
    <?php endif; ?>

    <div class="summary-row"><span>Subtotal</span><span>₹<?= number_format($subtotal) ?></span></div>
    <div class="summary-row"><span>Shipping</span><span><?= $shipping ? '₹'.$shipping : '<span style="color:var(--success)">FREE</span>' ?></span></div>
    <?php if ($discount > 0): ?>
    <div class="summary-row"><span>Discount</span><span class="green">−₹<?= number_format($discount) ?></span></div>
    <?php endif; ?>
    <div class="summary-row total"><span>Total</span><span>₹<?= number_format($total) ?></span></div>

    <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top:8px;text-align:center;">
      Proceed to Checkout →
    </a>
    <a href="shop.php" class="btn btn-outline btn-block" style="margin-top:10px;text-align:center;">
      Continue Shopping
    </a>

    <p style="margin-top:16px;font-size:12px;color:var(--gray-500);text-align:center;">
      🔒 Secure checkout &nbsp;|&nbsp; Free returns &nbsp;|&nbsp; Genuine products
    </p>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
<script>
function updateCartQty(pid, delta, currentQty) {
  const newQty = Math.max(1, currentQty + delta);
  fetch('/NextTrendBag/cart_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=update&product_id=${pid}&qty=${newQty}`
  }).then(r => r.json()).then(d => {
    if (d.success) location.reload();
  });
}
function removeFromCart(pid) {
  if (!confirm('Remove this item from cart?')) return;
  fetch('/NextTrendBag/cart_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=remove&product_id=${pid}`
  }).then(r => r.json()).then(d => {
    if (d.success) location.reload();
  });
}
</script>
</body>
</html>
