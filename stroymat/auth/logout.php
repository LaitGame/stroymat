<?php
// Старый код с добавлением проверки CSRF-токена
session_start();

// Проверяем, был ли отправлен CSRF-токен (только если это POST-запрос)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Логируем попытку CSRF-атаки
        error_log('Возможная CSRF-атака: неверный токен при выходе из системы');
        http_response_code(403);
        die('Ошибка безопасности: неверный токен');
    }
}

// Очищаем данные корзины, если они есть в сессии
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

// Очищаем все данные сессии
$_SESSION = array();

// Если нужно уничтожить куки сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную страницу
header("Location: /");
exit();
?>