<?php
if (session_status()===PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once '../db.php';
$msg = '';
// Add coupon
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add'])) {
    $code    = strtoupper(trim($_POST['code']??''));
    $pct     = (float)($_POST['discount_percent']??10);
    $maxuses = (int)($_POST['max_uses']??100);
    $expires = $_POST['expires_at'] ?: null;
    if ($code) {
        try {
            $pdo->prepare("INSERT INTO coupons (code,discount_percent,max_uses,expires_at) VALUES (?,?,?,?)")->execute([$code,$pct,$maxuses,$expires]);
            $msg = "✅ Coupon <strong>$code</strong> added!";
        } catch(Exception $e) { $msg = '❌ Coupon code already exists.'; }
    }
}
// Delete
if (isset($_GET['delete'])) { $pdo->prepare("DELETE FROM coupons WHERE id=?")->execute([(int)$_GET['delete']]); header('Location: coupons.php?deleted=1'); exit; }
// Toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $cur = $pdo->prepare("SELECT is_active FROM coupons WHERE id=?"); $cur->execute([$id]);
    $pdo->prepare("UPDATE coupons SET is_active=? WHERE id=?")->execute([!$cur->fetchColumn(), $id]);
    header('Location: coupons.php'); exit;
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coupons – NTB Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
  <?php include 'sidebar.php'; ?>
  <div class="main-wrap">
    <div class="topbar">
      <span class="topbar-title">🎟️ Coupons Management</span>
      <div class="topbar-right"><span class="admin-badge">Admin</span></div>
    </div>
    <div class="page-body">
      <?php if ($msg): ?><div class="flash <?= str_contains($msg,'✅')?'flash-success':'flash-error' ?>"><?= $msg ?></div><?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?><div class="flash flash-success">✅ Coupon deleted.</div><?php endif; ?>

      <!-- Add Form -->
      <div class="card" style="margin-bottom:24px;">
        <div class="card-header"><h3>➕ Create Coupon</h3></div>
        <div class="card-body" style="padding:24px;">
          <form method="POST">
            <input type="hidden" name="add" value="1">
            <div class="form-grid">
              <div class="form-group"><label>Coupon Code *</label><input type="text" name="code" required placeholder="e.g. SUMMER20" style="text-transform:uppercase;"></div>
              <div class="form-group"><label>Discount %</label><input type="number" name="discount_percent" min="1" max="100" value="10"></div>
              <div class="form-group"><label>Max Uses</label><input type="number" name="max_uses" value="100"></div>
              <div class="form-group"><label>Expires On</label><input type="date" name="expires_at"></div>
            </div>
            <button type="submit" class="btn btn-primary">Create Coupon</button>
          </form>
        </div>
      </div>

      <!-- Coupons Table -->
      <div class="card">
        <div class="card-header"><h3>All Coupons (<?= count($coupons) ?>)</h3></div>
        <div class="card-body">
          <table>
            <thead><tr><th>Code</th><th>Discount</th><th>Used / Max</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($coupons as $c): ?>
              <tr>
                <td style="font-weight:700;font-family:monospace;font-size:15px;letter-spacing:.06em;"><?= htmlspecialchars($c['code']) ?></td>
                <td><span style="font-size:1.1rem;font-weight:700;color:var(--accent2);"><?= $c['discount_percent'] ?>%</span></td>
                <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;height:6px;background:var(--surface2);border-radius:3px;overflow:hidden;">
                      <div style="height:100%;width:<?= min(100, round($c['used']/$c['max_uses']*100)) ?>%;background:var(--accent);border-radius:3px;"></div>
                    </div>
                    <span style="font-size:13px;color:var(--muted);"><?= $c['used'] ?> / <?= $c['max_uses'] ?></span>
                  </div>
                </td>
                <td style="color:var(--muted);font-size:13px;"><?= $c['expires_at'] ? date('d M Y', strtotime($c['expires_at'])) : '—' ?></td>
                <td>
                  <?php if ($c['is_active']): ?>
                    <span style="background:rgba(34,197,94,.15);color:var(--success);padding:4px 12px;border-radius:100px;font-size:12px;font-weight:600;">Active</span>
                  <?php else: ?>
                    <span style="background:rgba(239,68,68,.15);color:var(--danger);padding:4px 12px;border-radius:100px;font-size:12px;font-weight:600;">Disabled</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="coupons.php?toggle=<?= $c['id'] ?>" class="btn btn-outline btn-sm"><?= $c['is_active']?'Disable':'Enable' ?></a>
                  <a href="coupons.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete coupon <?= $c['code'] ?>?')" style="margin-left:6px;">Del</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($coupons)): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">No coupons created yet.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
