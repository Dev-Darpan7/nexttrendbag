<?php
if (session_status()===PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once '../db.php';

// Update order status
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['order_id'])) {
    $pdo->prepare("UPDATE orders SET status=? WHERE id=?")
        ->execute([($_POST['status']??'Pending'), (int)$_POST['order_id']]);
    header('Location: orders.php?updated=1'); exit;
}

$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? "WHERE status=:s" : "";
$stmt = $pdo->prepare("SELECT o.*, u.name as customer FROM orders o JOIN users u ON o.user_id=u.id $where ORDER BY o.created_at DESC");
if ($status_filter) $stmt->bindParam(':s', $status_filter);
$stmt->execute();
$orders = $stmt->fetchAll();
$statuses = ['Pending','Processing','Shipped','Delivered','Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders – NTB Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
  <?php include 'sidebar.php'; ?>
  <div class="main-wrap">
    <div class="topbar">
      <span class="topbar-title">📦 Orders Management</span>
      <div class="topbar-right"><span class="admin-badge">Admin</span></div>
    </div>
    <div class="page-body">
      <?php if (isset($_GET['updated'])): ?><div class="flash flash-success">✅ Order status updated!</div><?php endif; ?>

      <!-- Status filter tabs -->
      <div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;">
        <a href="orders.php" class="btn <?= !$status_filter?'btn-primary':'btn-outline' ?> btn-sm">All</a>
        <?php foreach ($statuses as $s): ?>
        <a href="orders.php?status=<?= $s ?>" class="btn <?= $status_filter===$s?'btn-primary':'btn-outline' ?> btn-sm"><?= $s ?></a>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-header">
          <h3><?= $status_filter ?: 'All' ?> Orders (<?= count($orders) ?>)</h3>
        </div>
        <div class="card-body">
          <table>
            <thead><tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Date</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
              <tr>
                <td><a href="../view_order.php?id=<?= $o['id'] ?>" target="_blank" style="color:var(--accent2);font-weight:700;">#NTB-<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></a></td>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($o['customer']) ?></div>
                  <div style="font-size:12px;color:var(--muted);"><?= htmlspecialchars($o['ship_city']) ?></div>
                </td>
                <td style="font-weight:700;">₹<?= number_format($o['total']) ?></td>
                <td style="text-transform:uppercase;font-size:12px;font-weight:600;"><?= $o['payment_method'] ?></td>
                <td style="color:var(--muted);font-size:13px;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><span class="badge badge-<?= $o['status'] ?>"><?= $o['status'] ?></span></td>
                <td>
                  <form method="POST" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <select name="status" style="padding:6px 10px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:13px;outline:none;">
                      <?php foreach ($statuses as $s): ?>
                      <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($orders)): ?>
              <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">No orders found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
