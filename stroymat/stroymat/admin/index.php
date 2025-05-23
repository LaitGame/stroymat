<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}
include '../includes/header.php';
?>

<h2>Админ-панель</h2>
<ul>
    <li><a href="/admin/categories.php">Управление категориями</a></li>
    <li><a href="/admin/add_product.php">Добавить товар</a></li>
</ul>

<?php include '../includes/footer.php'; ?>