<?php
session_start();
require_once 'db.php';
$depth = 0;

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$orderId = (int)($_GET['order_id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$st->execute([$orderId, (int)$_SESSION['user_id']]);
$order = $st->fetch();
if (!$order) { header('Location: my_orders.php'); exit; }

$pageTitle = 'Order Confirmed – NextTrendBag';
$cartCount = $wishlistCount = 0; $currentPage = '';
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

<div class="success-page">
  <div class="success-icon">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
  </div>
  <h1 style="font-size:2.2rem;margin-bottom:12px;">Order Placed Successfully!</h1>
  <p style="color:var(--gray-500);font-size:17px;margin-bottom:24px;">
    Thank you for your purchase! Your order has been confirmed and will be shipped soon.
  </p>

  <div class="order-num-box">
    <small>Order Number</small>
    <strong>#NTB-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
  </div>

  <p style="color:var(--gray-500);margin-bottom:32px;">
    Shipping to: <strong><?= htmlspecialchars($order['ship_name']) ?></strong>,
    <?= htmlspecialchars($order['ship_city']) ?>, <?= htmlspecialchars($order['ship_state']) ?>
  </p>

  <div style="background:var(--beige-50);border-radius:var(--radius);padding:20px;margin-bottom:32px;display:grid;grid-template-columns:1fr 1fr;gap:16px;text-align:left;">
    <div>
      <small style="color:var(--gray-500);">Payment Method</small>
      <p style="font-weight:600;margin:4px 0;"><?= strtoupper($order['payment_method']) ?></p>
    </div>
    <div>
      <small style="color:var(--gray-500);">Order Total</small>
      <p style="font-weight:700;font-size:1.2rem;color:var(--brown);margin:4px 0;">₹<?= number_format($order['total']) ?></p>
    </div>
    <div>
      <small style="color:var(--gray-500);">Status</small>
      <p style="margin:4px 0;"><span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span></p>
    </div>
    <div>
      <small style="color:var(--gray-500);">Order Date</small>
      <p style="font-weight:600;margin:4px 0;"><?= date('d M Y', strtotime($order['created_at'])) ?></p>
    </div>
  </div>

  <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
    <a href="my_orders.php" class="btn btn-primary">Track My Order</a>
    <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
