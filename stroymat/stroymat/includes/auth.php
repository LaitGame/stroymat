<?php
// Проверка авторизации пользователя
function isLoggedIn() {
    return isset($_SESSION['user_id']);    // Проверяем, установлен ли user_id в сессии
}
// Проверка админских прав
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'; // Проверка роли
}
// Перенаправление неавторизованных пользователей
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');    // Отправляем HTTP-заголовок для перенаправления
        exit;    // Немедленное прекращение выполнения скрипта
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /');
        exit;
    }
}
?>