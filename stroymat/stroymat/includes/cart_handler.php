<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Неизвестная ошибка'];

try {
    if ($_POST['action'] === 'checkout' && !empty($_POST['cart'])) {
        $cart = json_decode($_POST['cart'], true);
        $total = floatval($_POST['total']);
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Создаем заказ
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'new')");
        $stmt->execute([$userId, $total]);
        $orderId = $pdo->lastInsertId();
        
        // Добавляем товары заказа
        foreach ($cart as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        }
        
        $response = [
            'success' => true,
            'order_id' => $orderId,
            'message' => 'Заказ успешно оформлен'
        ];
    }
} catch (PDOException $e) {
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
}

echo json_encode($response);