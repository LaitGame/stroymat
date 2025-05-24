<?php
// Проверяем, не активна ли уже сессия
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверка авторизации пользователя
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Алиас для совместимости с cart_handler.php
function isAuthenticated() {
    return isLoggedIn();
}

// Получение ID текущего пользователя
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Проверка админских прав
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Перенаправление неавторизованных пользователей
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /auth/login.php');
        exit;
    }
}

// Перенаправление авторизованных пользователей
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /');
        exit;
    }
}

// Защита от CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Проверка прав доступа к ресурсу
function checkPermission($requiredRole = null) {
    if (!isLoggedIn()) {
        redirectIfNotLoggedIn();
    }
    
    if ($requiredRole === 'admin' && !isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        die('Доступ запрещен');
    }
}

// Безопасный выход из системы
function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    header('Location: /auth/login.php');
    exit;
}

// Получение информации о текущем пользователе
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = ?");
        $stmt->execute([getCurrentUserId()]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Ошибка при получении данных пользователя: " . $e->getMessage());
        return null;
    }
}

// Проверка доступа к конкретному ресурсу
function hasAccessToResource($resourceOwnerId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return getCurrentUserId() == $resourceOwnerId || isAdmin();
}
?>