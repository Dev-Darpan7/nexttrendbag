<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $st = $pdo->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
        $st->execute([$email]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin']  = $user['is_admin'];
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
$pageTitle = 'Login – NextTrendBag';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php" style="display:inline-flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:var(--brown);border-radius:50%;display:flex;align-items:center;justify-content:center;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <span class="brand-name">NextTrendBag</span>
      </a>
    </div>

    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-subtitle">Sign in to your account to continue shopping</p>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Your password">
      </div>
      <div style="text-align:right;margin-bottom:20px;">
        <a href="forgot_password.php" style="font-size:13px;color:var(--brown);">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="register.php">Create one</a>
    </div>
  </div>
</div>
<script src="bag.js"></script>
</body>
</html>
