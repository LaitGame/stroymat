<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Неизвестная ошибка'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $_POST['action'] ?? $input['action'] ?? '';

try {
    if (!isAuthenticated()) {
        throw new Exception('Требуется авторизация');
    }

    $userId = getCurrentUserId();

    switch ($action) {
        case 'add':
            $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);
            $quantity = (int)($input['quantity'] ?? $_POST['quantity'] ?? 1);

            if ($productId <= 0) {
                throw new Exception('Неверный ID товара');
            }

            // Проверяем существование товара
            $product = fetchOne("SELECT id, price, stock FROM products WHERE id = ?", [$productId]);
            if (!$product) {
                throw new Exception('Товар не найден');
            }

            // Проверяем наличие на складе
            if ($product['stock'] <= 0) {
                throw new Exception('Товара нет в наличии');
            }

            // Проверяем, есть ли уже товар в корзине
            $cartItem = fetchOne("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId]);

            if ($cartItem) {
                // Обновляем количество
                $newQuantity = $cartItem['quantity'] + $quantity;
                if ($newQuantity > $product['stock']) {
                    throw new Exception('Недостаточно товара на складе');
                }

                executeQuery("UPDATE cart SET quantity = ? WHERE id = ?", [$newQuantity, $cartItem['id']]);
            } else {
                // Добавляем новый товар
                executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", [$userId, $productId, $quantity]);
            }

            $response = [
                'success' => true,
                'message' => 'Товар добавлен в корзину',
                'cart_count' => getCartCount($userId)
            ];
            break;

        case 'remove':
            $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);

            if ($productId <= 0) {
                throw new Exception('Неверный ID товара');
            }

            $deleted = executeQuery("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId])->rowCount();

            if ($deleted) {
                $response = [
                    'success' => true,
                    'message' => 'Товар удален из корзины',
                    'cart_count' => getCartCount($userId)
                ];
            } else {
                throw new Exception('Товар не найден в корзине');
            }
            break;

        case 'update':
            $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);
            $quantity = (int)($input['quantity'] ?? $_POST['quantity'] ?? 1);

            if ($productId <= 0) {
                throw new Exception('Неверный ID товара');
            }

            if ($quantity <= 0) {
                throw new Exception('Количество должно быть больше 0');
            }

            // Проверяем наличие товара на складе
            $product = fetchOne("SELECT stock FROM products WHERE id = ?", [$productId]);
            if (!$product) {
                throw new Exception('Товар не найден');
            }

            if ($quantity > $product['stock']) {
                throw new Exception('Недостаточно товара на складе');
            }

            $updated = executeQuery("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?", [$quantity, $userId, $productId])->rowCount();

            if ($updated) {
                $response = [
                    'success' => true,
                    'message' => 'Количество обновлено',
                    'cart_count' => getCartCount($userId)
                ];
            } else {
                throw new Exception('Товар не найден в корзине');
            }
            break;

        case 'get':
            $cartItems = fetchAll("
                SELECT p.id, p.name, p.price, p.image, c.quantity, (p.price * c.quantity) as total 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ", [$userId]);

            $total = array_sum(array_column($cartItems, 'total'));

            $response = [
                'success' => true,
                'items' => $cartItems,
                'total' => $total,
                'cart_count' => getCartCount($userId)
            ];
            break;

        case 'checkout':
            if (empty($_POST['cart'])) {
                throw new Exception('Корзина пуста');
            }

            $cart = json_decode($_POST['cart'], true);
            $total = floatval($_POST['total']);

            // Проверяем, что корзина не пуста
            if (empty($cart)) {
                throw new Exception('Корзина пуста');
            }

            // Начинаем транзакцию
            $pdo->beginTransaction();

            try {
                // Создаем заказ
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'new')");
                $stmt->execute([$userId, $total]);
                $orderId = $pdo->lastInsertId();

                // Добавляем товары заказа и проверяем наличие
                foreach ($cart as $item) {
                    // Проверяем доступное количество
                    $product = fetchOne("SELECT stock FROM products WHERE id = ? FOR UPDATE", [$item['id']]);
                    if (!$product || $product['stock'] < $item['quantity']) {
                        throw new Exception('Товара "' . $item['name'] . '" недостаточно на складе');
                    }

                    // Добавляем в заказ
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);

                    // Уменьшаем количество на складе
                    executeQuery("UPDATE products SET stock = stock - ? WHERE id = ?", [$item['quantity'], $item['id']]);
                }

                // Очищаем корзину
                executeQuery("DELETE FROM cart WHERE user_id = ?", [$userId]);

                $pdo->commit();

                $response = [
                    'success' => true,
                    'order_id' => $orderId,
                    'message' => 'Заказ успешно оформлен',
                    'cart_count' => 0
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        default:
            throw new Exception('Неизвестное действие');
    }
} catch (PDOException $e) {
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
    error_log("Database error in cart_handler: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

/**
 * Получает количество товаров в корзине пользователя
 */
function getCartCount($userId) {
    $count = fetchOne("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?", [$userId]);
    return (int)($count['count'] ?? 0);
}
?>