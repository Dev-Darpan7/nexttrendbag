<?php
session_start();
require_once 'db.php';
$depth = 0;

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Get cart items
$st = $pdo->prepare("SELECT c.product_id, c.quantity, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?");
$st->execute([$userId]);
$cartItems = $st->fetchAll();

if (empty($cartItems)) { header('Location: cart.php'); exit; }

// Totals
$subtotal    = array_sum(array_map(fn($i)=>$i['price']*$i['quantity'], $cartItems));
$shipping    = $subtotal > 999 ? 0 : 79;
$discountPct = (float)($_SESSION['coupon_discount'] ?? 0);
$discount    = round($subtotal * $discountPct / 100, 2);
$taxable     = $subtotal - $discount;
$tax         = round($taxable * 0.18, 2);
$total       = $taxable + $shipping + $tax;

// Pre-fill from profile
$st = $pdo->prepare("SELECT * FROM users WHERE id=?");
$st->execute([$userId]);
$user = $st->fetch();

$pageTitle = 'Checkout – NextTrendBag';
$cartCount     = getCartCount($pdo);
$wishlistCount = getWishlistCount($pdo);
$currentPage   = 'checkout';
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
  <a href="index.php">Home</a> / <a href="cart.php">Cart</a> / <span>Checkout</span>
</div>

<div style="max-width:1200px;margin:0 auto;padding:0 24px 16px;">
  <h1 style="font-size:2rem;">Checkout</h1>
</div>

<form method="POST" action="place_order.php" class="checkout-layout">

  <!-- Left: billing + shipping + payment -->
  <div>
    <!-- Billing -->
    <div class="form-card">
      <h3>Billing Details</h3>
      <div class="form-grid-2">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" required value="<?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>">
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?= htmlspecialchars(implode(' ', array_slice(explode(' ',$user['name']),1))) ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
      </div>
      <div class="form-group">
        <label>Phone Number *</label>
        <input type="tel" name="phone" required value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
      </div>
    </div>

    <!-- Shipping Address -->
    <div class="form-card">
      <h3>Shipping Address</h3>
      <div class="form-group">
        <label>Street Address *</label>
        <input type="text" name="address" required value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="House no., Street, Area">
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label>City *</label>
          <input type="text" name="city" required value="<?= htmlspecialchars($user['city'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>State *</label>
          <input type="text" name="state" required value="<?= htmlspecialchars($user['state'] ?? '') ?>">
        </div>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label>PIN Code *</label>
          <input type="text" name="pincode" required value="<?= htmlspecialchars($user['pincode'] ?? '') ?>" placeholder="6-digit PIN">
        </div>
        <div class="form-group">
          <label>Country</label>
          <select name="country">
            <option>India</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Payment -->
    <div class="form-card">
      <h3>Payment Method</h3>
      <div class="demo-notice">🎭 Demo Mode: No actual payment will be processed</div>
      <label class="payment-option">
        <input type="radio" name="payment_method" value="card" checked>
        <div class="payment-option-info">
          <p>Credit / Debit Card</p>
          <small>Visa, Mastercard, RuPay</small>
        </div>
        <div class="payment-icon" style="display:flex;gap:6px;">
          <span style="background:#1a1f71;color:white;font-size:11px;font-weight:700;padding:4px 8px;border-radius:4px;">VISA</span>
          <span style="background:#eb001b;color:white;font-size:11px;font-weight:700;padding:4px 8px;border-radius:4px;">MC</span>
        </div>
      </label>
      <label class="payment-option">
        <input type="radio" name="payment_method" value="upi">
        <div class="payment-option-info">
          <p>UPI</p>
          <small>Google Pay, PhonePe, Paytm</small>
        </div>
        <div class="payment-icon" style="background:#2d8741;color:white;font-size:11px;font-weight:700;padding:6px 10px;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;">UPI</div>
      </label>
      <label class="payment-option">
        <input type="radio" name="payment_method" value="cod">
        <div class="payment-option-info">
          <p>Cash on Delivery</p>
          <small>Pay when you receive</small>
        </div>
        <div class="payment-icon" style="font-size:24px;">💵</div>
      </label>
    </div>
  </div>

  <!-- Right: Order Summary -->
  <div>
    <div class="order-summary-box" style="position:sticky;top:86px;">
      <h3>Order Summary</h3>
      <div style="max-height:280px;overflow-y:auto;margin-bottom:16px;">
        <?php foreach ($cartItems as $item): ?>
        <div style="display:flex;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--beige-100);">
          <img src="<?= htmlspecialchars($item['image']) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:8px;" alt="">
          <div style="flex:1;">
            <p style="font-size:14px;font-weight:600;margin:0;"><?= htmlspecialchars($item['name']) ?></p>
            <p style="font-size:13px;color:var(--gray-500);margin:2px 0;">Qty: <?= $item['quantity'] ?></p>
          </div>
          <p style="font-weight:700;font-size:14px;">₹<?= number_format($item['price']*$item['quantity']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="summary-row"><span>Subtotal</span><span>₹<?= number_format($subtotal) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= $shipping ? '₹'.$shipping : '<span style="color:var(--success)">FREE</span>' ?></span></div>
      <?php if ($discount): ?>
      <div class="summary-row"><span>Coupon Discount</span><span class="green">−₹<?= number_format($discount) ?></span></div>
      <?php endif; ?>
      <div class="summary-row"><span>GST (18%)</span><span>₹<?= number_format($tax) ?></span></div>
      <div class="summary-row total"><span>Total</span><span>₹<?= number_format($total) ?></span></div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:12px;">
        🔒 Place Order
      </button>
      <p style="font-size:12px;color:var(--gray-500);text-align:center;margin-top:12px;">By placing your order you agree to our Terms & Privacy Policy.</p>
    </div>
  </div>

</form>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
