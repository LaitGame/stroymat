<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../includes/db.php';
include '../includes/auth.php';

// Перенаправляем если уже авторизован
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        // Проверка учетных данных
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();    // Получаем данные пользователя
        
        // Проверка пароля
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];    // Устанавливаем сессию
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            header('Location: /');  // ← Редирект после успешного входа
            exit;
        } else {
            $error = "Неверное имя пользователя или пароль";
        }
    } else {
        $error = "Все поля обязательны для заполнения";
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">Вход в систему</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Имя пользователя</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Войти</button>
            </form>
            
            <div class="mt-3 text-center">
                <p>Нет аккаунта? <a href="/auth/register.php">Зарегистрируйтесь</a></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>