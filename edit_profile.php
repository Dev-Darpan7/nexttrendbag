<?php
session_start();
require_once 'db.php';
$depth = 0;
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];
$st = $pdo->prepare("SELECT * FROM users WHERE id=?"); $st->execute([$userId]); $user = $st->fetch();

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    if (!$name) { $error = 'Name is required.'; }
    else {
        $pdo->prepare("UPDATE users SET name=?,phone=?,address=?,city=?,state=?,pincode=? WHERE id=?")
            ->execute([$name,$phone,$address,$city,$state,$pincode,$userId]);
        $_SESSION['user_name'] = $name;
        $success = 'Profile updated successfully!';
        $st->execute([$userId]); $user = $st->fetch();
    }
}
$pageTitle = 'Edit Profile – NextTrendBag';
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

<div class="breadcrumb"><a href="index.php">Home</a> / <a href="profile.php">Profile</a> / <span>Edit</span></div>

<div class="profile-layout">
  <div class="profile-sidebar">
    <div class="profile-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
    <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
    <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
    <nav class="profile-nav">
      <a href="profile.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>My Profile</a>
      <a href="edit_profile.php" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit Profile</a>
      <a href="my_orders.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>My Orders</a>
      <a href="wishlist.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>Wishlist</a>
      <a href="change_password.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>Change Password</a>
    </nav>
  </div>

  <div class="profile-content">
    <h2 style="margin-bottom:24px;">Edit Profile</h2>
    <?php if ($success): ?><div class="flash flash-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>">
      </div>
      <div class="form-group">
        <label>Email (read-only)</label>
        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:var(--beige-50);color:var(--gray-500);">
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="+91 98765 43210">
      </div>
      <div class="form-group">
        <label>Street Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address']??'') ?>" placeholder="House no., Street, Area">
      </div>
      <div class="form-grid-2">
        <div class="form-group"><label>City</label><input type="text" name="city" value="<?= htmlspecialchars($user['city']??'') ?>"></div>
        <div class="form-group"><label>State</label><input type="text" name="state" value="<?= htmlspecialchars($user['state']??'') ?>"></div>
      </div>
      <div class="form-group">
        <label>PIN Code</label>
        <input type="text" name="pincode" value="<?= htmlspecialchars($user['pincode']??'') ?>" placeholder="6-digit PIN">
      </div>
      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
        <a href="profile.php" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
