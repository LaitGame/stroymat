<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'СтройМатериалы - интернет-магазин' ?></title>
    
    <!-- Основные CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        /* Основные стили шапки */
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .search-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Стили корзины */
        .cart-icon-wrapper {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .cart-icon {
            background: white;
            padding: 10px 15px;
            border-radius: 50px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .cart-icon:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        
        .cart-icon .bi-cart3 {
            font-size: 1.5rem;
        }
        
        .cart-icon .badge {
            font-size: 0.8rem;
            position: absolute;
            top: -5px;
            right: -5px;
        }
        
        .cart-modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow-y: auto;
        }
        
        .cart-modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 25px;
            width: 90%;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 4px;
        }
        
        .cart-item-info {
            flex-grow: 1;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .cart-item-price {
            font-weight: bold;
            min-width: 100px;
            text-align: right;
        }
        
        .added-to-cart-message {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1100;
            opacity: 1;
            transition: opacity 0.5s ease;
            padding: 15px 25px;
            background: #28a745;
            color: white;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        /* Стили для поиска */
        .search-suggestions {
            position: absolute;
            z-index: 1000;
            width: 100%;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
            max-height: 300px;
            overflow-y: auto;
        }
        .suggestion-item {
            display: block;
            padding: 8px 15px;
            color: #333;
            text-decoration: none;
        }
        .suggestion-item:hover {
            background-color: #f8f9fa;
        }
        .input-group {
            position: relative;
        }
    </style>
</head>
<body>
    <header class="bg-light shadow-sm">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="/">
                        <i class="bi bi-house-gear"></i> СтройМатериалы
                    </a>
                    
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="navbarContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="/products.php">
                                    <i class="bi bi-list"></i> Каталог
                                </a>
                            </li>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/orders/">
                                        <i class="bi bi-bag-check"></i> Мои заказы
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        <!-- Поиск -->
                        <div class="search-container me-3">
                            <form action="/products.php" method="get" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Поиск товаров..." 
                                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="d-flex">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                            <li><a class="dropdown-item" href="/admin/"><i class="bi bi-gear"></i> Админ-панель</a></li>
                                        <?php endif; ?>
                                        <li><a class="dropdown-item" href="/profile.php"><i class="bi bi-person"></i> Профиль</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Выйти</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <a href="/auth/login.php" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-box-arrow-in-right"></i> Войти
                                </a>
                                <a href="/auth/register.php" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Регистрация
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Иконка корзины -->
    <div class="cart-icon-wrapper">
        <div id="cart-icon" class="cart-icon">
            <i class="bi bi-cart3"></i>
            <span id="cart-count" class="badge bg-danger">
                <?php
                try {
                    if (isset($_SESSION['user_id'])) {
                        require_once 'includes/db.php';
                        $count = fetchOne("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
                        echo $count['count'] ?? 0;
                    } else {
                        echo 0;
                    }
                } catch (Exception $e) {
                    echo 0;
                }
                ?>
            </span>
        </div>
    </div>
    
    <!-- Модальное окно корзины -->
    <div id="cart-modal" class="cart-modal">
        <div class="cart-modal-content">
            <button type="button" class="btn-close cart-close" aria-label="Close" style="float: right;"></button>
            <h3 class="mb-4"><i class="bi bi-cart-check"></i> Ваша корзина</h3>
            <div id="cart-items" class="mb-3"></div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Итого: <span id="cart-total-price">0</span> руб.</h5>
                <button id="checkout-btn" class="btn btn-primary">
                    <i class="bi bi-credit-card"></i> Оформить заказ
                </button>
            </div>
        </div>
    </div>
    
    <main class="container py-4">
        <!-- Здесь будет подключаться контент страниц -->
        
        <!-- Скрипт для подсказок поиска -->
        <script>
        $(document).ready(function() {
            const searchInput = $('input[name="search"]');
            const suggestionsContainer = $('<div class="search-suggestions"></div>').insertAfter(searchInput).hide();
            
            searchInput.on('input', function() {
                const term = $(this).val().trim();
                if (term.length > 2) {
                    $.get('/search_suggestions.php', { term: term }, function(data) {
                        if (data.length > 0) {
                            suggestionsContainer.empty();
                            $.each(data, function(index, product) {
                                $('<a href="/product.php?id=' + product.id + '" class="suggestion-item">' + 
                                  product.name + '</a>').appendTo(suggestionsContainer);
                            });
                            suggestionsContainer.show();
                        } else {
                            suggestionsContainer.hide();
                        }
                    }).fail(function() {
                        suggestionsContainer.hide();
                    });
                } else {
                    suggestionsContainer.hide();
                }
            });
            
            // Скрываем подсказки при клике вне поля
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.input-group').length) {
                    suggestionsContainer.hide();
                }
            });
        });
        </script>
    </main>
    
    <!-- Подключаем скрипт корзины -->
    <script src="/assets/js/cart.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>