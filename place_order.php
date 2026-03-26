<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Fetch cart
$st = $pdo->prepare("SELECT c.product_id, c.quantity, p.price FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?");
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

// Sanitize POST
$fn      = htmlspecialchars(trim($_POST['first_name'] ?? ''));
$ln      = htmlspecialchars(trim($_POST['last_name'] ?? ''));
$email   = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone   = htmlspecialchars(trim($_POST['phone'] ?? ''));
$address = htmlspecialchars(trim($_POST['address'] ?? ''));
$city    = htmlspecialchars(trim($_POST['city'] ?? ''));
$state   = htmlspecialchars(trim($_POST['state'] ?? ''));
$pincode = htmlspecialchars(trim($_POST['pincode'] ?? ''));
$payment = in_array($_POST['payment_method']??'cod', ['card','upi','cod']) ? $_POST['payment_method'] : 'cod';
$coupon  = $_SESSION['applied_coupon'] ?? '';

try {
    $pdo->beginTransaction();

    // Insert order
    $st = $pdo->prepare("INSERT INTO orders
        (user_id, subtotal, shipping, discount, tax, total, status, payment_method, coupon_code,
         ship_name, ship_email, ship_phone, ship_address, ship_city, ship_state, ship_pincode)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $st->execute([
        $userId, $subtotal, $shipping, $discount, $tax, $total,
        'Pending', $payment, $coupon,
        "$fn $ln", $email, $phone, $address, $city, $state, $pincode
    ]);
    $orderId = $pdo->lastInsertId();

    // Insert order items
    $itemSt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
    foreach ($cartItems as $item) {
        $itemSt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
        // Decrement stock
        $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id=?")->execute([$item['quantity'], $item['product_id']]);
    }

    // Increment coupon usage
    if ($coupon) {
        $pdo->prepare("UPDATE coupons SET used=used+1 WHERE code=?")->execute([$coupon]);
    }

    // Clear cart
    $pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$userId]);

    // Clear session coupon
    unset($_SESSION['applied_coupon'], $_SESSION['coupon_discount']);


    $pdo->commit();
    
    // Simulate payment processing delay (3 seconds)
    // We render a loading screen and then redirect to order_success.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Processing Payment — NextTrendBag</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
  <style>
    body { background: var(--cream); height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
    .processing-card { text-align: center; background: var(--white); padding: 50px 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); max-width: 400px; width: 100%; border: 1px solid var(--beige-200); }
    .spinner { width: 50px; height: 50px; border: 4px solid var(--beige-200); border-top-color: var(--brown); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 24px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .processing-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--gray-900); margin-bottom: 8px; }
    .processing-desc { color: var(--gray-500); font-size: 14px; margin-bottom: 20px; }
    .payment-method-badge { display: inline-block; background: var(--beige-100); color: var(--brown); padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
  </style>
</head>
<body>
  <div class="processing-card">
    <div class="spinner"></div>
    <div class="processing-title">Processing Payment...</div>
    <div class="processing-desc">Please do not close or refresh this page.</div>
    <div class="payment-method-badge"><?= htmlspecialchars($payment) ?> Payment</div>
  </div>
  <script>
    // Redirect to success page after 3 seconds
    setTimeout(function() {
      window.location.href = "order_success.php?order_id=<?= $orderId ?>";
    }, 3000);
  </script>
</body>
</html>
<?php
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: checkout.php?error=1');
    exit;
}
