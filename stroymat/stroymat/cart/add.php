<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    header('Location: /products.php');
    exit;
}

$product_id = (int)$_GET['product_id'];
$user_id = $_SESSION['user_id'];

// Проверяем, есть ли уже товар в корзине
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$item = $stmt->fetch();

if ($item) {
    // Если есть - увеличиваем количество
    $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
    $stmt->execute([$item['id']]);
} else {
    // Если нет - добавляем новый
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
}

header('Location: /cart/');
exit;
?>