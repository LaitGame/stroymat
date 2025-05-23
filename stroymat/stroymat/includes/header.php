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
    <title>СтройМатериалы - интернет-магазин</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
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
                                <a href="/cart/" class="nav-link position-relative me-3">
                                    <i class="bi bi-cart3"></i>
                                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                                        <span class="cart-count"><?= $_SESSION['cart_count'] ?></span>
                                    <?php endif; ?>
                                </a>
                                
                                <div class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
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
        
        <style>
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