<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// // Получаем товары в корзине с информацией о продуктах
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.image, c.quantity, (p.price * c.quantity) as total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

// // Расчет общей суммы
$total = array_sum(array_column($items, 'total'));  // Суммируем все значения 'total'
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Ваша корзина</h2>
    
    <?php if (empty($items)): ?>
        <div class="alert alert-info">Ваша корзина пуста</div>
        <a href="/products.php" class="btn btn-primary">Перейти к товарам</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <img src="/assets/images/<?= htmlspecialchars($item['image']) ?>" width="50" class="me-2">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td><?= htmlspecialchars($item['price']) ?> руб.</td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['total']) ?> руб.</td>
                            <td>
                                <a href="/cart/remove.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Итого</th>
                        <th><?= number_format($total, 2) ?> руб.</th>
                        <th>
                            <a href="/checkout.php" class="btn btn-success">Оформить заказ</a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>