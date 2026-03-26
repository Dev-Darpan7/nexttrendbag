<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'redirect'=>'login.php']);
    exit;
}

$userId    = (int)$_SESSION['user_id'];
$action    = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$qty       = max(1, (int)($_POST['qty'] ?? 1));

function cartCount(PDO $pdo, int $uid): int {
    $s = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?");
    $s->execute([$uid]);
    return (int)$s->fetchColumn();
}

if ($action === 'add') {
    // Verify product exists
    $s = $pdo->prepare("SELECT id, stock FROM products WHERE id=?");
    $s->execute([$productId]);
    $prod = $s->fetch();
    if (!$prod) { echo json_encode(['success'=>false,'message'=>'Product not found']); exit; }

    // Upsert
    $s = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    $s->execute([$userId, $productId, $qty, $qty]);
    echo json_encode(['success'=>true,'cart_count'=>cartCount($pdo,$userId)]);
    exit;
}

if ($action === 'remove') {
    $s = $pdo->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
    $s->execute([$userId, $productId]);
    echo json_encode(['success'=>true,'cart_count'=>cartCount($pdo,$userId)]);
    exit;
}

if ($action === 'update') {
    if ($qty < 1) {
        $s = $pdo->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
        $s->execute([$userId, $productId]);
    } else {
        $s = $pdo->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?");
        $s->execute([$qty, $userId, $productId]);
    }
    echo json_encode(['success'=>true,'cart_count'=>cartCount($pdo,$userId)]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid action']);
