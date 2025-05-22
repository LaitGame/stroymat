<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

redirectIfLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        
        $_SESSION['success'] = 'Регистрация успешна! Теперь вы можете войти.';
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $error = "Ошибка регистрации: " . $e->getMessage();
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="auth-form">
    <h2>Регистрация</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Имя пользователя</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
    </form>
    <p class="mt-3">Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>

<?php include '../includes/footer.php'; ?>