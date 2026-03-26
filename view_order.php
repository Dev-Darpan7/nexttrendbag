<?php
session_start();
require_once 'db.php';
$depth = 0;

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId  = (int)$_SESSION['user_id'];
$orderId = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$st->execute([$orderId, $userId]);
$order = $st->fetch();
if (!$order) { header('Location: my_orders.php'); exit; }

$st2 = $pdo->prepare("SELECT oi.*, p.name, p.image, p.category FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
$st2->execute([$orderId]);
$items = $st2->fetchAll();

$pageTitle = 'Order #NTB-'.str_pad($orderId,6,'0',STR_PAD_LEFT).' – NextTrendBag';
$cartCount = getCartCount($pdo); $wishlistCount = getWishlistCount($pdo);
$currentPage = '';
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
<div class="breadcrumb">
  <a href="index.php">Home</a> / <a href="my_orders.php">My Orders</a> /
  <span>#NTB-<?= str_pad($orderId,6,'0',STR_PAD_LEFT) ?></span>
</div>
<div style="max-width:900px;margin:0 auto;padding:24px 24px 60px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
    <h1 style="font-size:1.6rem;">Order #NTB-<?= str_pad($orderId,6,'0',STR_PAD_LEFT) ?></h1>
    <span class="status-badge status-<?= $order['status'] ?>" style="font-size:14px;padding:6px 16px;"><?= $order['status'] ?></span>
  </div>

  <!-- Order Items -->
  <div class="form-card" style="background:white;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);padding:24px;margin-bottom:20px;">
    <h3 style="margin-bottom:20px;">Items Ordered</h3>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:var(--beige-50);">
          <th style="padding:12px 16px;text-align:left;font-size:13px;font-weight:600;color:var(--gray-500);">Product</th>
          <th style="padding:12px 16px;text-align:center;font-size:13px;font-weight:600;color:var(--gray-500);">Qty</th>
          <th style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:var(--gray-500);">Unit Price</th>
          <th style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:var(--gray-500);">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr style="border-bottom:1px solid var(--beige-100);">
          <td style="padding:14px 16px;">
            <div style="display:flex;align-items:center;gap:12px;">
              <img src="<?= htmlspecialchars($item['image']) ?>" style="width:52px;height:52px;object-fit:cover;border-radius:8px;" alt="">
              <div>
                <div style="font-weight:600;font-size:15px;"><?= htmlspecialchars($item['name']) ?></div>
                <div style="font-size:12px;color:var(--gray-500);"><?= ucfirst($item['category']) ?></div>
              </div>
            </div>
          </td>
          <td style="padding:14px 16px;text-align:center;font-weight:600;"><?= $item['quantity'] ?></td>
          <td style="padding:14px 16px;text-align:right;">₹<?= number_format($item['price']) ?></td>
          <td style="padding:14px 16px;text-align:right;font-weight:700;">₹<?= number_format($item['price']*$item['quantity']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Grid: Shipping + Summary -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    <!-- Shipping -->
    <div style="background:white;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);padding:24px;">
      <h3 style="margin-bottom:16px;">Shipping Address</h3>
      <p style="font-weight:600;margin:0;"><?= htmlspecialchars($order['ship_name']) ?></p>
      <p style="margin:4px 0;"><?= htmlspecialchars($order['ship_address']) ?></p>
      <p style="margin:4px 0;"><?= htmlspecialchars($order['ship_city'].', '.$order['ship_state'].' – '.$order['ship_pincode']) ?></p>
      <p style="margin:4px 0;">📞 <?= htmlspecialchars($order['ship_phone']) ?></p>
      <p style="margin:4px 0;">✉️ <?= htmlspecialchars($order['ship_email']) ?></p>
    </div>
    <!-- Payment Summary -->
    <div style="background:white;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);padding:24px;">
      <h3 style="margin-bottom:16px;">Payment Summary</h3>
      <div class="summary-row"><span>Subtotal</span><span>₹<?= number_format($order['subtotal']) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= $order['shipping']>0 ? '₹'.number_format($order['shipping']) : 'FREE' ?></span></div>
      <?php if ($order['discount']>0): ?>
      <div class="summary-row"><span>Discount</span><span style="color:var(--success);">−₹<?= number_format($order['discount']) ?></span></div>
      <?php endif; ?>
      <div class="summary-row"><span>GST (18%)</span><span>₹<?= number_format($order['tax']) ?></span></div>
      <div class="summary-row total"><span>Total</span><span>₹<?= number_format($order['total']) ?></span></div>
      <p style="margin-top:12px;font-size:13px;color:var(--gray-500);">Payment: <strong><?= strtoupper($order['payment_method']) ?></strong></p>
      <?php if ($order['coupon_code']): ?>
      <p style="font-size:13px;color:var(--success);">Coupon: <?= htmlspecialchars($order['coupon_code']) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div style="display:flex;gap:12px;">
    <a href="my_orders.php" class="btn btn-outline">← Back to Orders</a>
    <?php if ($order['status']==='Pending'): ?>
    <form method="POST" action="cancel_order.php" onsubmit="return confirm('Cancel this order?')">
      <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
      <button type="submit" class="btn btn-danger">Cancel Order</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
