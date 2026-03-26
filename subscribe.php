<?php
session_start();
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = filter_var($_POST['email']??'', FILTER_SANITIZE_EMAIL);
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try { $pdo->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)")->execute([$email]); } catch(Exception $e){}
    }
}
header('Location: index.php?subscribed=1'); exit;
