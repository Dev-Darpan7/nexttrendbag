<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexttrendbag');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:40px;color:#c0392b;background:#fdf2f2;border:1px solid #e74c3c;border-radius:8px;max-width:500px;margin:50px auto;">
        <strong>Database Connection Error</strong><br><br>' . htmlspecialchars($e->getMessage()) . '<br><br>
        Please make sure XAMPP MySQL is running and the database <strong>nexttrendbag</strong> exists.
    </div>');
}

// ── Helper: get cart count for current user ──────────────────────────────────
function getCartCount(PDO $pdo): int {
    if (!isset($_SESSION['user_id'])) return 0;
    $st = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?");
    $st->execute([$_SESSION['user_id']]);
    return (int)$st->fetchColumn();
}

// ── Helper: get wishlist count for current user ──────────────────────────────
function getWishlistCount(PDO $pdo): int {
    if (!isset($_SESSION['user_id'])) return 0;
    $st = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id=?");
    $st->execute([$_SESSION['user_id']]);
    return (int)$st->fetchColumn();
}
