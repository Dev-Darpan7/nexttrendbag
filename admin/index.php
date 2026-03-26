<?php
if (session_status()===PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once '../db.php';
// Stats
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status!='Cancelled'")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin=0")->fetchColumn();
$recentOrders  = $pdo->query("SELECT o.*, u.name as customer FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();
$lowStock      = $pdo->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – NTB Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
  <?php include 'sidebar.php'; /* already included above but reload renders */ ?>
  <div class="main-wrap">
    <div class="topbar">
      <span class="topbar-title">📊 Dashboard</span>
      <div class="topbar-right">
        <span style="font-size:13px;color:var(--muted);"><?= date('d M Y, h:i A') ?></span>
        <span class="admin-badge">Admin</span>
      </div>
    </div>
    <div class="page-body">

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon brown">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div><div class="stat-num">₹<?= number_format($totalRevenue) ?></div><div class="stat-label">Total Revenue</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <div><div class="stat-num"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          </div>
          <div><div class="stat-num"><?= $totalProducts ?></div><div class="stat-label">Products</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
          </div>
          <div><div class="stat-num"><?= $totalUsers ?></div><div class="stat-label">Customers</div></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
        <!-- Recent Orders -->
        <div class="card">
          <div class="card-header">
            <h3>Recent Orders</h3>
            <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
          </div>
          <div class="card-body">
            <table>
              <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                  <td><a href="orders.php?view=<?= $o['id'] ?>" style="color:var(--accent2);font-weight:600;">#NTB-<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></a></td>
                  <td><?= htmlspecialchars($o['customer']) ?></td>
                  <td style="font-weight:700;">₹<?= number_format($o['total']) ?></td>
                  <td><span class="badge badge-<?= $o['status'] ?>"><?= $o['status'] ?></span></td>
                  <td style="color:var(--muted);"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Low Stock -->
        <div class="card">
          <div class="card-header">
            <h3>⚠️ Low Stock</h3>
            <a href="products.php" class="btn btn-outline btn-sm">Manage</a>
          </div>
          <div class="card-body">
            <?php foreach ($lowStock as $p): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border);">
              <img src="../<?= htmlspecialchars($p['image']) ?>" class="prod-thumb" alt="">
              <div style="flex:1;">
                <div style="font-size:14px;font-weight:500;"><?= htmlspecialchars(substr($p['name'],0,30)) ?>…</div>
                <div style="font-size:12px;color:var(--muted);">₹<?= number_format($p['price']) ?></div>
              </div>
              <span style="font-weight:700;color:<?= $p['stock']<5?'var(--danger)':'var(--warning)' ?>;"><?= $p['stock'] ?> left</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($lowStock)): ?><p style="padding:20px;color:var(--muted);font-size:14px;">✅ All products have sufficient stock.</p><?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
