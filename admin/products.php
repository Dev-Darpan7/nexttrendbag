<?php
if (session_status()===PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once '../db.php';

$msg = '';
// Delete
if (isset($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$pid]);
    header('Location: products.php?deleted=1'); exit;
}
// Add / Edit product
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? 'backpacks';
    $price    = (float)($_POST['price'] ?? 0);
    $origprice= (float)($_POST['original_price'] ?? 0) ?: null;
    $stock    = (int)($_POST['stock'] ?? 0);
    $rating   = (float)($_POST['rating'] ?? 4.5);
    $badge    = trim($_POST['badge'] ?? '') ?: null;
    $color    = trim($_POST['color'] ?? 'brown');
    $desc     = trim($_POST['description'] ?? '');
    $featured = isset($_POST['is_featured']) ? 1 : 0;

    // Handle image upload
    $image = $_POST['current_image'] ?? 'images/bag4.jpeg';
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = 'uploads/'.uniqid().'.'.$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../'.$fname);
        $image = $fname;
    }

    if ($id) {
        $pdo->prepare("UPDATE products SET name=?,category=?,price=?,original_price=?,image=?,stock=?,rating=?,description=?,badge=?,is_featured=?,color=? WHERE id=?")
            ->execute([$name,$category,$price,$origprice,$image,$stock,$rating,$desc,$badge,$featured,$color,$id]);
        $msg = '✅ Product updated!';
    } else {
        $pdo->prepare("INSERT INTO products (name,category,price,original_price,image,stock,rating,description,badge,is_featured,color) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$name,$category,$price,$origprice,$image,$stock,$rating,$desc,$badge,$featured,$color]);
        $msg = '✅ Product added!';
    }
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM products WHERE id=?"); $st->execute([(int)$_GET['edit']]);
    $editProduct = $st->fetch();
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products – NTB Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
  <?php include 'sidebar.php'; ?>
  <div class="main-wrap">
    <div class="topbar">
      <span class="topbar-title">🛍️ Products Management</span>
      <div class="topbar-right"><span class="admin-badge">Admin</span></div>
    </div>
    <div class="page-body">
      <?php if ($msg): ?><div class="flash flash-success"><?= $msg ?></div><?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?><div class="flash flash-success">✅ Product deleted.</div><?php endif; ?>

      <!-- Add / Edit Form -->
      <div class="card" style="margin-bottom:28px;">
        <div class="card-header"><h3><?= $editProduct ? '✏️ Edit Product' : '➕ Add New Product' ?></h3></div>
        <div class="card-body" style="padding:24px;">
          <form method="POST" enctype="multipart/form-data">
            <?php if ($editProduct): ?><input type="hidden" name="id" value="<?= $editProduct['id'] ?>"><input type="hidden" name="current_image" value="<?= htmlspecialchars($editProduct['image']) ?>"><?php endif; ?>
            <div class="form-grid">
              <div class="form-group"><label>Product Name *</label><input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name']??'') ?>"></div>
              <div class="form-group"><label>Category</label>
                <select name="category">
                  <?php foreach (['backpacks','handbags','laptop','travel'] as $c): ?>
                  <option value="<?= $c ?>" <?= ($editProduct['category']??'')===$c?'selected':'' ?>><?= ucfirst($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" step="0.01" required value="<?= $editProduct['price']??'' ?>"></div>
              <div class="form-group"><label>Original Price (₹)</label><input type="number" name="original_price" step="0.01" value="<?= $editProduct['original_price']??'' ?>"></div>
              <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock" value="<?= $editProduct['stock']??50 ?>"></div>
              <div class="form-group"><label>Rating (1-5)</label><input type="number" name="rating" step="0.1" min="1" max="5" value="<?= $editProduct['rating']??4.5 ?>"></div>
              <div class="form-group"><label>Badge (optional)</label><input type="text" name="badge" placeholder="e.g. Best Seller" value="<?= htmlspecialchars($editProduct['badge']??'') ?>"></div>
              <div class="form-group"><label>Color</label>
                <select name="color">
                  <?php foreach (['brown','black','beige','tan','navy'] as $c): ?>
                  <option value="<?= $c ?>" <?= ($editProduct['color']??'brown')===$c?'selected':'' ?>><?= ucfirst($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= htmlspecialchars($editProduct['description']??'') ?></textarea></div>
            <div class="form-group"><label>Product Image</label><input type="file" name="image" accept="image/*">
              <?php if ($editProduct && $editProduct['image']): ?><small style="color:var(--muted);display:block;margin-top:4px;">Current: <?= htmlspecialchars($editProduct['image']) ?></small><?php endif; ?>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:10px;">
              <input type="checkbox" name="is_featured" id="feat" value="1" <?= ($editProduct['is_featured']??0)?'checked':'' ?> style="width:auto;">
              <label for="feat" style="margin:0;">Featured Product</label>
            </div>
            <div style="display:flex;gap:12px;">
              <button type="submit" class="btn btn-primary"><?= $editProduct ? 'Update Product' : 'Add Product' ?></button>
              <?php if ($editProduct): ?><a href="products.php" class="btn btn-outline">Cancel</a><?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <!-- Products Table -->
      <div class="card">
        <div class="card-header"><h3>All Products (<?= count($products) ?>)</h3></div>
        <div class="card-body">
          <table>
            <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Rating</th><th>Featured</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($products as $p): ?>
              <tr>
                <td><img src="../<?= htmlspecialchars($p['image']) ?>" class="prod-thumb" onerror="this.style.display='none'" alt=""></td>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($p['name']) ?></div>
                  <?php if ($p['badge']): ?><span style="font-size:11px;background:rgba(139,69,19,.15);color:var(--accent2);padding:2px 8px;border-radius:100px;"><?= htmlspecialchars($p['badge']) ?></span><?php endif; ?>
                </td>
                <td style="text-transform:capitalize;"><?= $p['category'] ?></td>
                <td>
                  <div style="font-weight:700;">₹<?= number_format($p['price']) ?></div>
                  <?php if ($p['original_price']): ?><div style="font-size:12px;color:var(--muted);text-decoration:line-through;">₹<?= number_format($p['original_price']) ?></div><?php endif; ?>
                </td>
                <td><span style="font-weight:600;color:<?= $p['stock']<10?'var(--danger)':($p['stock']<20?'var(--warning)':'var(--success)') ?>;"><?= $p['stock'] ?></span></td>
                <td>⭐ <?= $p['rating'] ?></td>
                <td><?= $p['is_featured']?'<span style="color:var(--success);">✓ Yes</span>':'<span style="color:var(--muted);">No</span>' ?></td>
                <td>
                  <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                  <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')" style="margin-left:6px;">Del</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
