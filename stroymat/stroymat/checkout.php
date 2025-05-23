<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Перенаправляем если корзина пуста или пользователь не авторизован
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?redirect=/checkout.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем товары в корзине
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, c.quantity, (p.price * c.quantity) as total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: /cart/');
    exit;
}

// Общая сумма
$total_amount = array_sum(array_column($cart_items, 'total'));

// Получаем данные пользователя
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Обработка формы
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $payment = $_POST['payment'];
    $notes = trim($_POST['notes'] ?? '');

    // Валидация данных
    if (empty($name)) $errors[] = 'Укажите ваше имя';
    if (empty($phone)) $errors[] = 'Укажите телефон';
    if (empty($email)) $errors[] = 'Укажите email';
    if (empty($address)) $errors[] = 'Укажите адрес доставки';
    if (!in_array($payment, ['cash', 'card', 'online'])) $errors[] = 'Выберите способ оплаты';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();   // Начало транзакции

            // 1. Создаем заказ
            $order_stmt = $pdo->prepare("
                INSERT INTO orders 
                (user_id, total_amount, customer_name, customer_phone, customer_email, delivery_address, payment_method, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $order_stmt->execute([
                $user_id,
                $total_amount,
                $name,
                $phone,
                $email,
                $address,
                $payment,
                $notes
            ]);
            $order_id = $pdo->lastInsertId();   // Получаем ID созданного заказа

            // 2. Добавляем товары в заказ
            $item_stmt = $pdo->prepare("
                INSERT INTO order_items 
                (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($cart_items as $item) {
                $item_stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            // 3. Очищаем корзину
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

            $pdo->commit(); // Подтверждаем транзакцию

            // Перенаправляем на страницу заказа
            header("Location: /orders/view.php?id=$order_id");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();   // Откатываем при ошибке
            $errors[] = "Ошибка при оформлении заказа: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <h2>Оформление заказа</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">ФИО</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Телефон</label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Адрес доставки</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Способ оплаты</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment" id="cash" value="cash" checked>
                        <label class="form-check-label" for="cash">Наличными при получении</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment" id="card" value="card">
                        <label class="form-check-label" for="card">Картой при получении</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment" id="online" value="online">
                        <label class="form-check-label" for="online">Онлайн оплата</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Примечания к заказу</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg">Подтвердить заказ</button>
            </form>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ваш заказ</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></span>
                                <span><?= number_format($item['total'], 2) ?> руб.</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Итого:</span>
                        <span><?= number_format($total_amount, 2) ?> руб.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>