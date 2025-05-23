<?php
header("Content-Type: application/json");
session_start();

// Очищаем сессию PHP
$_SESSION = array();
session_destroy();

// Возвращаем успешный ответ
echo json_encode(['status' => 'success']);
exit();
?>