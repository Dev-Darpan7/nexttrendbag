<?php
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$currentAdmin = basename($_SERVER['PHP_SELF'],'.php');

if (!function_exists('adminNav')) {
    function adminNav(string $page, string $current): string {
        return $page===$current ? ' active' : '';
    }
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></div>
    <span>NTB Admin</span>
  </div>
  <div class="sidebar-label">Main Menu</div>
  <nav class="sidebar-nav">
    <a href="index.php" class="<?= adminNav('index',$currentAdmin) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="products.php" class="<?= adminNav('products',$currentAdmin) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      Products
    </a>
    <a href="orders.php" class="<?= adminNav('orders',$currentAdmin) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      Orders
    </a>
    <a href="users.php" class="<?= adminNav('users',$currentAdmin) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
      Customers
    </a>
    <a href="coupons.php" class="<?= adminNav('coupons',$currentAdmin) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
      Coupons
    </a>
  </nav>
  <div class="sidebar-label">Store</div>
  <nav class="sidebar-nav">
    <a href="../index.php" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
      View Store
    </a>
  </nav>
  <div class="sidebar-footer">
    <p style="font-size:13px;color:var(--muted);margin-bottom:8px;">Logged in as <strong style="color:var(--text);"><?= htmlspecialchars($adminName) ?></strong></p>
    <a href="logout.php">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      Logout
    </a>
  </div>
</aside>
