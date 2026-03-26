<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_orders.php'); exit;
}
$userId  = (int)$_SESSION['user_id'];
$orderId = (int)($_POST['order_id'] ?? 0);

$st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? AND status='Pending'");
$st->execute([$orderId, $userId]);
if ($st->fetch()) {
    // Restore stock
    $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id=?");
    $items->execute([$orderId]);
    foreach ($items->fetchAll() as $item) {
        $pdo->prepare("UPDATE products SET stock=stock+? WHERE id=?")->execute([$item['quantity'], $item['product_id']]);
    }
    $pdo->prepare("UPDATE orders SET status='Cancelled' WHERE id=?")->execute([$orderId]);
}
header("Location: view_order.php?id=$orderId&cancelled=1");
exit;
