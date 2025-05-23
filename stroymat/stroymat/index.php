<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Получаем популярные товары
$popular_products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
    LIMIT 6
")->fetchAll();

// Получаем категории
$categories = $pdo->query("SELECT * FROM categories LIMIT 4")->fetchAll();

include 'includes/header.php';
?>

<!-- Hero-секция -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">Строительные материалы от проверенных поставщиков</h1>
                <p class="lead mb-4">Широкий ассортимент качественных материалов для строительства и ремонта по выгодным ценам</p>
                <a href="/products.php" class="btn btn-primary btn-lg">В каталог</a>
            </div>
            <div class="col-md-6">
                <img src="/assets/images/hero-image.jpg" alt="Строительные материалы" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Категории -->
<section class="categories-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Популярные категории</h2>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <a href="/products.php?category=<?= $category['id'] ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($category['name']) ?></h5>
                                <p class="text-muted"><?= rand(10, 50) ?> товаров</p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Популярные товары -->
<section class="products-section py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2>Популярные товары</h2>
            <a href="/products.php" class="btn btn-outline-primary">Все товары</a>
        </div>
        
        <div class="row">
            <?php foreach ($popular_products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="/assets/images/products/<?= htmlspecialchars($product['image']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(mb_substr($product['description'], 0, 100)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price fw-bold"><?= number_format($product['price'], 2) ?> руб.</span>
                                <a href="/product.php?id=<?= $product['id'] ?>" class="btn btn-primary">Подробнее</a>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <small class="text-muted">Категория: <?= htmlspecialchars($product['category_name']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Преимущества -->
<section class="advantages-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Почему выбирают нас</h2>
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <div class="advantages-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h5>Быстрая доставка</h5>
                <p>По городу в течение 24 часов</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="advantages-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <h5>Цены от производителя</h5>
                <p>Без наценок посредников</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="advantages-icon">
                    <i class="bi bi-award"></i>
                </div>
                <h5>Гарантия качества</h5>
                <p>Все товары сертифицированы</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="advantages-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <h5>Поддержка 24/7</h5>
                <p>Консультации по любым вопросам</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>