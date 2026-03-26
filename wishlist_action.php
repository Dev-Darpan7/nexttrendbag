<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'redirect'=>'login.php']);
    exit;
}

$userId    = (int)$_SESSION['user_id'];
$productId = (int)($_POST['product_id'] ?? 0);

// Check if already in wishlist
$st = $pdo->prepare("SELECT id FROM wishlist WHERE user_id=? AND product_id=?");
$st->execute([$userId, $productId]);
if ($st->fetch()) {
    $pdo->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?")->execute([$userId, $productId]);
    echo json_encode(['success'=>true,'message'=>'Removed from wishlist','in_wishlist'=>false]);
} else {
    $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?,?)")->execute([$userId, $productId]);
    echo json_encode(['success'=>true,'message'=>'Added to wishlist ❤️','in_wishlist'=>true]);
}
