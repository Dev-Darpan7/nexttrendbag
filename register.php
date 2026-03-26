<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once 'db.php';

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check duplicate email
        $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $st = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $st->execute([$name, $email, $phone, $hash]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['is_admin']  = 0;
            header('Location: index.php?welcome=1');
            exit;
        }
    }
}
$pageTitle = 'Create Account – NextTrendBag';
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
  <div class="auth-card" style="max-width:520px;">
    <div class="auth-logo">
      <a href="index.php" style="display:inline-flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:var(--brown);border-radius:50%;display:flex;align-items:center;justify-content:center;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <span class="brand-name">NextTrendBag</span>
      </a>
    </div>

    <h2 class="auth-title">Create account</h2>
    <p class="auth-subtitle">Join thousands of happy shoppers</p>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="form-group">
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name" required placeholder="Your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" id="email" name="email" required placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="phone">Phone</label>
          <input type="tel" id="phone" name="phone" placeholder="+91 ..." value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="password">Password *</label>
          <input type="password" id="password" name="password" required placeholder="Min. 6 characters">
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>
</div>
<script src="bag.js"></script>
</body>
</html>
