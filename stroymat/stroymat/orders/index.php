<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Получаем заказы пользователя
$stmt = $pdo->prepare("
    SELECT id, order_date, total_amount, status 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-5">
    <h2>Мои заказы</h2>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">У вас пока нет заказов</div>
        <a href="/products.php" class="btn btn-primary">Перейти к товарам</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> руб.</td>
                            <td>
                                <span class="badge 
                                    <?= $order['status'] === 'completed' ? 'bg-success' : 
                                       ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning') ?>">
                                    <?= $order['status'] === 'pending' ? 'В обработке' : 
                                       ($order['status'] === 'processing' ? 'В процессе' : 
                                       ($order['status'] === 'completed' ? 'Завершен' : 'Отменен')) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">Подробнее</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>