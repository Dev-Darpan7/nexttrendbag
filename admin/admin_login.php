<?php
session_start();
if (isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
require_once '../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    $st = $pdo->prepare("SELECT id,name,password FROM users WHERE email=? AND is_admin=1");
    $st->execute([$email]);
    $admin = $st->fetch();
    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: index.php'); exit;
    } else { $error = 'Invalid admin credentials.'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login – NextTrendBag</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-brand">
    <div class="icon"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></div>
    <h1>NextTrendBag</h1>
    <p>Admin Control Panel</p>
  </div>
  <div class="card">
    <h2>Admin Login</h2>
    <p class="sub">Sign in with your admin credentials</p>
    <?php if ($error): ?><div class="flash flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="fg"><label>Email Address</label><input type="email" name="email" required placeholder="admin@nexttrendbag.com" value="<?= htmlspecialchars($_POST['email']??'') ?>"></div>
      <div class="fg"><label>Password</label><input type="password" name="password" required placeholder="Your password"></div>
      <button type="submit" class="btn">Sign in to Admin</button>
    </form>
  </div>
  <div class="back-link"><a href="../index.php">← Back to Store</a></div>
</div>
</body>
</html>
