<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /orders/');
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Получаем информацию о заказе
$order_stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND user_id = ?
");
$order_stmt->execute([$order_id, $user_id]);
$order = $order_stmt->fetch();

if (!$order) {
    header('Location: /orders/');
    exit;
}

// Получаем товары заказа
$items_stmt = $pdo->prepare("
    SELECT p.name, p.image, oi.quantity, oi.price, (oi.quantity * oi.price) as total
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-5">
    <h2>Заказ №<?= $order['id'] ?></h2>
    <p class="text-muted">Дата: <?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></p>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Товары в заказе</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($items as $item): ?>
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-2">
                                        <img src="/assets/images/<?= htmlspecialchars($item['image']) ?>" class="img-fluid">
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?= htmlspecialchars($item['name']) ?></h6>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <?= $item['quantity'] ?> шт.
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <?= number_format($item['total'], 2) ?> руб.
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Итого:</span>
                        <span><?= number_format($order['total_amount'], 2) ?> руб.</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Информация о заказе</h5>
                </div>
                <div class="card-body">
                    <h6>Статус:</h6>
                    <p>
                        <span class="badge 
                            <?= $order['status'] === 'completed' ? 'bg-success' : 
                               ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning') ?>">
                            <?= $order['status'] === 'pending' ? 'В обработке' : 
                               ($order['status'] === 'processing' ? 'В процессе' : 
                               ($order['status'] === 'completed' ? 'Завершен' : 'Отменен')) ?>
                        </span>
                    </p>
                    
                    <h6>Способ оплаты:</h6>
                    <p>
                        <?= $order['payment_method'] === 'cash' ? 'Наличными при получении' : 
                           ($order['payment_method'] === 'card' ? 'Картой при получении' : 'Онлайн оплата') ?>
                    </p>
                    
                    <h6>Данные покупателя:</h6>
                    <p>
                        <?= htmlspecialchars($order['customer_name']) ?><br>
                        Телефон: <?= htmlspecialchars($order['customer_phone']) ?><br>
                        Email: <?= htmlspecialchars($order['customer_email']) ?>
                    </p>
                    
                    <h6>Адрес доставки:</h6>
                    <p><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
                    
                    <?php if (!empty($order['notes'])): ?>
                        <h6>Примечания:</h6>
                        <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <a href="/orders/" class="btn btn-secondary mt-3">Вернуться к списку заказов</a>
</div>

<?php include '../includes/footer.php'; ?>