<?php
if (session_status()===PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once '../db.php';
$users = $pdo->query("SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total),0) as total_spent FROM users u LEFT JOIN orders o ON u.id=o.user_id WHERE u.is_admin=0 GROUP BY u.id ORDER BY u.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customers – NTB Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
  <?php include 'sidebar.php'; ?>
  <div class="main-wrap">
    <div class="topbar">
      <span class="topbar-title">👥 Customers</span>
      <div class="topbar-right"><span class="admin-badge">Admin</span></div>
    </div>
    <div class="page-body">
      <div class="card">
        <div class="card-header"><h3>All Customers (<?= count($users) ?>)</h3></div>
        <div class="card-body">
          <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Joined</th></tr></thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--accent);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;"><?= strtoupper(substr($u['name'],0,1)) ?></div>
                    <span style="font-weight:600;"><?= htmlspecialchars($u['name']) ?></span>
                  </div>
                </td>
                <td style="color:var(--muted);"><?= htmlspecialchars($u['email']) ?></td>
                <td style="color:var(--muted);"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                <td style="font-weight:600;text-align:center;"><?= $u['order_count'] ?></td>
                <td style="font-weight:700;color:var(--accent2);">₹<?= number_format($u['total_spent']) ?></td>
                <td style="color:var(--muted);font-size:13px;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($users)): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">No customers yet.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
