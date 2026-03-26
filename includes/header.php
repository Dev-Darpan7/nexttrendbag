<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';
$cartCount     = getCartCount($pdo);
$wishlistCount = getWishlistCount($pdo);
$currentPage   = $currentPage ?? basename($_SERVER['PHP_SELF'], '.php');
$depth         = $depth ?? 0;
$root = str_repeat('../', $depth);

if (!function_exists('navActive')) {
    function navActive(string $page, string $current): string {
        return $page === $current ? ' active' : '';
    }
}
?>
<header>
  <nav class="navbar">
    <div class="navbar-inner">
      <a href="<?= $root ?>index.php" class="nav-logo">
        <div class="logo-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <span class="brand-name">NextTrendBag</span>
      </a>

      <div class="nav-links">
        <a href="<?= $root ?>index.php" class="<?= navActive('index', $currentPage) ?>">Home</a>
        <a href="<?= $root ?>shop.php" class="<?= navActive('shop', $currentPage) ?>">Shop</a>
        <a href="<?= $root ?>wishlist.php" class="<?= navActive('wishlist', $currentPage) ?>">Wishlist</a>
        <a href="<?= $root ?>about.php" class="<?= navActive('about', $currentPage) ?>">About</a>
        <a href="<?= $root ?>contact.php" class="<?= navActive('contact', $currentPage) ?>">Contact</a>
      </div>

      <div class="nav-icons">
        <button class="nav-icon-btn" onclick="toggleSearch()" title="Search">
          <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </button>

        <a href="<?= $root ?>wishlist.php" class="nav-icon-btn" title="Wishlist" style="position:relative;">
          <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          <?php if ($wishlistCount > 0): ?><span class="badge-count"><?= $wishlistCount ?></span><?php endif; ?>
        </a>

        <a href="<?= $root ?>cart.php" class="nav-icon-btn" title="Cart" style="position:relative;">
          <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <span class="badge-count" id="cartCount" <?= $cartCount < 1 ? 'style="display:none;"' : '' ?>><?= $cartCount ?></span>
        </a>

        <div class="profile-dropdown">
          <button class="nav-icon-btn" title="Account" onclick="document.getElementById('profileMenu').classList.toggle('open')">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </button>
          <div class="profile-menu" id="profileMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="<?= $root ?>profile.php">👤 My Profile</a>
              <a href="<?= $root ?>my_orders.php">📦 My Orders</a>
              <a href="<?= $root ?>wishlist.php">❤️ Wishlist</a>
              <div class="menu-divider"></div>
              <form method="POST" action="<?= $root ?>logout.php">
                <button type="submit" style="background:none;border:none;width:100%;text-align:left;padding:10px 16px;font-size:13px;color:var(--danger);cursor:pointer;font-family:inherit;">🚪 Logout</button>
              </form>
            <?php else: ?>
              <a href="<?= $root ?>login.php">🔑 Login</a>
              <a href="<?= $root ?>register.php">📝 Register</a>
            <?php endif; ?>
          </div>
        </div>

        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </div>

    <div class="search-bar" id="searchBar">
      <div class="search-inner">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <form action="<?= $root ?>shop.php" method="GET" style="flex:1;">
          <input type="text" name="search" placeholder="Search for bags, backpacks, handbags…" id="searchInput" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:100%;background:none;border:none;outline:none;font-size:16px;color:var(--gray-900);">
        </form>
      </div>
    </div>

    <div class="mobile-menu" id="mobileMenu">
      <a href="<?= $root ?>index.php">Home</a>
      <a href="<?= $root ?>shop.php">Shop</a>
      <a href="<?= $root ?>wishlist.php">Wishlist</a>
      <a href="<?= $root ?>about.php">About</a>
      <a href="<?= $root ?>contact.php">Contact</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $root ?>profile.php">My Profile</a>
        <a href="<?= $root ?>my_orders.php">My Orders</a>
        <a href="<?= $root ?>logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= $root ?>login.php">Login</a>
        <a href="<?= $root ?>register.php">Register</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
<main>
