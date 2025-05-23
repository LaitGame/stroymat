<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Проверяем ID товара
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /products.php');
    exit;
}

$product_id = (int)$_GET['id'];

// Получаем данные товара
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Если товар не найден
if (!$product) {
    header('Location: /products.php');
    exit;
}

// Получаем похожие товары
$similar_stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ?
    ORDER BY RAND() 
    LIMIT 4
");
$similar_stmt->execute([$product['category_id'], $product_id]);
$similar_products = $similar_stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Главная</a></li>
            <li class="breadcrumb-item"><a href="/products.php">Каталог</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Изображение товара -->
        <div class="col-md-6">
            <div class="card mb-4">
                <img src="/assets/images/products/<?= htmlspecialchars($product['image']) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     style="max-height: 500px; object-fit: contain;">
            </div>
        </div>
        
        <!-- Информация о товаре -->
        <div class="col-md-6">
            <h1 class="mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-secondary me-2"><?= htmlspecialchars($product['category_name']) ?></span>
                <div class="rating">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-half text-warning"></i>
                    <span class="ms-1">4.5</span>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="text-primary mb-3"><?= number_format($product['price'], 2) ?> руб.</h3>
                    
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <button class="btn btn-outline-secondary">-</button>
                            <span class="mx-2">1</span>
                            <button class="btn btn-outline-secondary">+</button>
                        </div>
                        <button class="btn btn-primary flex-grow-1">
                            <i class="bi bi-cart-plus"></i> В корзину
                        </button>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success">
                            <i class="bi bi-lightning"></i> Купить в 1 клик
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Характеристики -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Характеристики</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="40%">Категория</th>
                                <td><?= htmlspecialchars($product['category_name']) ?></td>
                            </tr>
                            <tr>
                                <th>Артикул</th>
                                <td>PRD-<?= $product['id'] ?></td>
                            </tr>
                            <tr>
                                <th>Вес</th>
                                <td>3.5 кг</td>
                            </tr>
                            <tr>
                                <th>Размеры</th>
                                <td>250x120x65 мм</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Описание -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Описание</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    
                    <?php if ($product['name'] == 'Кирпич строительный М-150'): ?>
                        <h6>Особенности:</h6>
                        <ul>
                            <li>Высокая прочность (М-150)</li>
                            <li>Морозостойкость F50</li>
                            <li>Низкое водопоглощение</li>
                            <li>Идеальная геометрия</li>
                        </ul>
                    <?php elseif ($product['name'] == 'Штукатурка гипсовая'): ?>
                        <h6>Преимущества:</h6>
                        <ul>
                            <li>Готовая смесь - просто добавь воды</li>
                            <li>Быстрое высыхание</li>
                            <li>Не дает усадки</li>
                            <li>Экологически чистый состав</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Похожие товары -->
    <?php if (!empty($similar_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Похожие товары</h3>
                <div class="row">
                    <?php foreach ($similar_products as $similar): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <img src="/assets/images/products/<?= htmlspecialchars($similar['image']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($similar['name']) ?>"
                                     style="height: 180px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($similar['name']) ?></h5>
                                    <p class="card-text text-primary fw-bold">
                                        <?= number_format($similar['price'], 2) ?> руб.
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="/product.php?id=<?= $similar['id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>