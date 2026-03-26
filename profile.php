<?php
session_start();
require_once 'db.php';
$depth = 0;
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];
$st = $pdo->prepare("SELECT * FROM users WHERE id=?"); $st->execute([$userId]);
$user = $st->fetch();
$recentOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 3");
$recentOrders->execute([$userId]); $orders = $recentOrders->fetchAll();

$pageTitle = 'My Profile – NextTrendBag';
$cartCount = getCartCount($pdo); $wishlistCount = getWishlistCount($pdo); $currentPage = 'profile';
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

<div class="breadcrumb"><a href="index.php">Home</a> / <span>My Profile</span></div>

<div class="profile-layout">
  <!-- Sidebar -->
  <div class="profile-sidebar">
    <div class="profile-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
    <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
    <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
    <nav class="profile-nav">
      <a href="profile.php" class="active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>My Profile
      </a>
      <a href="edit_profile.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit Profile
      </a>
      <a href="my_orders.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>My Orders
      </a>
      <a href="wishlist.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>Wishlist
      </a>
      <a href="change_password.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>Change Password
      </a>
    </nav>
  </div>

  <!-- Content -->
  <div class="profile-content">
    <h2 style="margin-bottom:24px;">Profile Details</h2>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
      <?php
      $fields = [
        ['Name',    $user['name']],
        ['Email',   $user['email']],
        ['Phone',   $user['phone'] ?: '—'],
        ['City',    $user['city']  ?: '—'],
        ['State',   $user['state'] ?: '—'],
        ['PIN Code',$user['pincode'] ?: '—'],
      ];
      foreach ($fields as [$label,$value]): ?>
      <div style="background:var(--beige-50);border-radius:var(--radius);padding:16px 18px;border:1px solid var(--beige-200);">
        <div style="font-size:11px;color:var(--gray-500);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px;"><?= $label ?></div>
        <div style="font-weight:600;font-size:15px;color:var(--gray-900);"><?= htmlspecialchars($value) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($user['address']): ?>
    <div style="background:var(--beige-50);border-radius:var(--radius);padding:16px 18px;border:1px solid var(--beige-200);margin-bottom:20px;">
      <div style="font-size:11px;color:var(--gray-500);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px;">Address</div>
      <div style="font-weight:600;font-size:15px;color:var(--gray-900);"><?= htmlspecialchars($user['address']) ?></div>
    </div>
    <?php endif; ?>

    <a href="edit_profile.php" class="btn btn-primary" style="margin-bottom:36px;">✏️ Edit Profile</a>

    <!-- Recent Orders -->
    <div style="margin-top:8px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h3>Recent Orders</h3>
        <a href="my_orders.php" style="font-size:13px;color:var(--brown);font-weight:600;">View all →</a>
      </div>
      <?php if ($orders): foreach ($orders as $o): ?>
      <div style="display:flex;align-items:center;gap:12px;padding:16px;background:var(--beige-50);border:1px solid var(--beige-200);border-radius:var(--radius);margin-bottom:10px;">
        <div style="flex:1;">
          <div style="font-weight:700;color:var(--brown);font-size:14px;">#NTB-<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></div>
          <div style="font-size:12px;color:var(--gray-500);margin-top:2px;"><?= date('d M Y', strtotime($o['created_at'])) ?></div>
        </div>
        <span class="status-badge status-<?= $o['status'] ?>"><?= $o['status'] ?></span>
        <span style="font-weight:700;font-size:15px;">₹<?= number_format($o['total']) ?></span>
        <a href="view_order.php?id=<?= $o['id'] ?>" class="btn btn-outline-brown btn-sm">View</a>
      </div>
      <?php endforeach; else: ?>
      <div style="text-align:center;padding:40px 20px;background:var(--beige-50);border-radius:var(--radius);border:1px dashed var(--beige-300);">
        <div style="font-size:2.5rem;margin-bottom:12px;">📦</div>
        <p style="color:var(--gray-500);margin-bottom:16px;">You haven't placed any orders yet.</p>
        <a href="shop.php" class="btn btn-primary">Start Shopping</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
