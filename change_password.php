<?php
session_start();
require_once 'db.php';
$depth = 0;
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];
$success = $error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $st = $pdo->prepare("SELECT password FROM users WHERE id=?"); $st->execute([$userId]);
    $hash = $st->fetchColumn();
    if (!password_verify($current, $hash)) { $error = 'Current password is incorrect.'; }
    elseif (strlen($new) < 6) { $error = 'New password must be at least 6 characters.'; }
    elseif ($new !== $confirm) { $error = 'New passwords do not match.'; }
    else { $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_BCRYPT), $userId]); $success = 'Password changed successfully!'; }
}
$pageTitle = 'Change Password – NextTrendBag';
$cartCount = getCartCount($pdo); $wishlistCount = getWishlistCount($pdo); $currentPage = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="breadcrumb"><a href="index.php">Home</a> / <a href="profile.php">Profile</a> / <span>Change Password</span></div>
<div class="profile-layout">
  <div class="profile-sidebar">
    <nav class="profile-nav">
      <a href="profile.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>My Profile</a>
      <a href="edit_profile.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit Profile</a>
      <a href="my_orders.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>My Orders</a>
      <a href="wishlist.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>Wishlist</a>
      <a href="change_password.php" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>Change Password</a>
    </nav>
  </div>
  <div class="profile-content">
    <h2>Change Password</h2>
    <?php if ($success): ?><div class="flash flash-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" style="max-width:420px;">
      <div class="form-group"><label>Current Password *</label><input type="password" name="current_password" required placeholder="Your current password"></div>
      <div class="form-group"><label>New Password *</label><input type="password" name="new_password" required placeholder="At least 6 characters"></div>
      <div class="form-group"><label>Confirm New Password *</label><input type="password" name="confirm_password" required placeholder="Repeat new password"></div>
      <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
