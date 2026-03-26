<?php
session_start();
require_once 'db.php';
$depth = 0;

$success = $error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$email || !$password) { $error = 'Please fill all fields.'; }
    elseif ($password !== $confirm) { $error = 'Passwords do not match.'; }
    elseif (strlen($password) < 6) { $error = 'Password must be at least 6 characters.'; }
    else {
        $st = $pdo->prepare("SELECT id FROM users WHERE email=?"); $st->execute([$email]);
        if (!$st->fetch()) { $error = 'No account found with that email.'; }
        else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password=? WHERE email=?")->execute([$hash,$email]);
            $success = 'Password updated! You can now login.';
        }
    }
}
$pageTitle = 'Forgot Password – NextTrendBag';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php" style="display:inline-flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:var(--brown);border-radius:50%;display:flex;align-items:center;justify-content:center;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></div>
        <span class="brand-name">NextTrendBag</span>
      </a>
    </div>
    <h2 class="auth-title">Reset Password</h2>
    <p class="auth-subtitle">Enter your email and a new password below</p>
    <?php if ($success): ?><div class="flash flash-success"><?= $success ?> <a href="login.php" style="color:var(--brown);font-weight:700;">Login →</a></div>
    <?php elseif ($error): ?><div class="flash flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!$success): ?>
    <form method="POST">
      <div class="form-group"><label>Email Address</label><input type="email" name="email" required placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email']??'') ?>"></div>
      <div class="form-group"><label>New Password</label><input type="password" name="password" required placeholder="At least 6 characters"></div>
      <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required placeholder="Repeat password"></div>
      <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
    </form>
    <?php endif; ?>
    <div class="auth-footer"><a href="login.php">← Back to Login</a></div>
  </div>
</div>
<script src="bag.js"></script>
</body>
</html>
