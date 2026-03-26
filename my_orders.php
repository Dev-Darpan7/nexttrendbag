<?php
session_start();
require_once 'db.php';
$depth = 0;

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$userId = (int)$_SESSION['user_id'];

$st = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$st->execute([$userId]);
$orders = $st->fetchAll();

$pageTitle = 'My Orders – NextTrendBag';
$cartCount = getCartCount($pdo); $wishlistCount = getWishlistCount($pdo);
$currentPage = 'my_orders';
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
<div class="breadcrumb"><a href="index.php">Home</a> / <span>My Orders</span></div>
<div style="max-width:900px;margin:0 auto;padding:24px 24px 60px;">
  <h1 style="font-size:1.8rem;margin-bottom:28px;">My Orders</h1>

  <?php if (empty($orders)): ?>
  <div style="text-align:center;padding:60px 20px;">
    <div style="font-size:60px;margin-bottom:20px;">📦</div>
    <h2 style="margin-bottom:12px;">No orders yet</h2>
    <p style="color:var(--gray-500);margin-bottom:28px;">You haven't placed any orders. Start shopping!</p>
    <a href="shop.php" class="btn btn-primary">Shop Now</a>
  </div>
  <?php else: ?>
  <?php foreach ($orders as $order): ?>
  <div class="order-card">
    <div class="order-card-header">
      <div>
        <div class="order-id">#NTB-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
        <div class="order-date"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
      </div>
      <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
      <div>
        <small style="color:var(--gray-500);">Payment</small>
        <p style="font-weight:600;margin:2px 0;text-transform:uppercase;"><?= $order['payment_method'] ?></p>
      </div>
      <div>
        <small style="color:var(--gray-500);">Ship to</small>
        <p style="font-weight:600;margin:2px 0;"><?= htmlspecialchars($order['ship_city'].', '.$order['ship_state']) ?></p>
      </div>
      <div>
        <small style="color:var(--gray-500);">Total</small>
        <p style="font-weight:700;font-size:1.1rem;color:var(--brown);margin:2px 0;">₹<?= number_format($order['total']) ?></p>
      </div>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="view_order.php?id=<?= $order['id'] ?>" class="btn btn-outline-brown btn-sm">View Details</a>
      <?php if ($order['status'] === 'Pending'): ?>
      <form method="POST" action="cancel_order.php" onsubmit="return confirm('Cancel this order?')">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <button type="submit" class="btn btn-danger btn-sm">Cancel Order</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
