<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/db.php';

// Получаем параметры поиска и фильтрации
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Подготавливаем SQL запрос
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1";

$params = [];

// Добавляем условия поиска
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

// Добавляем фильтр по категории
if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}

// Сортировка
$sql .= " ORDER BY p.name ASC";

// Пагинация
$per_page = 9; // Количество товаров на странице
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Получаем общее количество товаров
$count_sql = str_replace('SELECT p.*, c.name as category_name', 'SELECT COUNT(*) as total', $sql);
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetch()['total'];
$total_pages = ceil($total_items / $per_page);

// Добавляем лимит в основной запрос
$sql .= " LIMIT $offset, $per_page";

// Получаем товары
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Получаем список категорий для фильтра
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Фильтр по категориям -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Категории</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item <?= $category_id == 0 ? 'active' : '' ?>">
                            <a href="?<?= http_build_query(array_merge($_GET, ['category' => 0, 'page' => 1])) ?>">
                                Все категории
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li class="list-group-item <?= $category_id == $category['id'] ? 'active' : '' ?>">
                                <a href="?<?= http_build_query(array_merge($_GET, ['category' => $category['id'], 'page' => 1])) ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <?php if (!empty($search)): ?>
                        Результаты поиска "<?= htmlspecialchars($search) ?>"
                        <small class="text-muted">Найдено: <?= $total_items ?></small>
                    <?php elseif ($category_id > 0): ?>
                        Категория: <?= htmlspecialchars($categories[array_search($category_id, array_column($categories, 'id'))]['name']) ?>
                        <small class="text-muted">Товаров: <?= $total_items ?></small>
                    <?php else: ?>
                        Все товары
                        <small class="text-muted">Всего: <?= $total_items ?></small>
                    <?php endif; ?>
                </h2>
                
                <!-- Кнопка сброса фильтров -->
                <?php if (!empty($search) || $category_id > 0): ?>
                    <a href="/products.php" class="btn btn-outline-secondary">Сбросить фильтры</a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="alert alert-info">Товары не найдены. Попробуйте изменить параметры поиска.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
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
                
                <!-- Пагинация -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <!-- Кнопка "Назад" -->
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                                   aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <!-- Номера страниц -->
                            <?php 
                            // Показываем ограниченное количество страниц вокруг текущей
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                    <a class="page-link" 
                                       href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; 
                            
                            if ($end < $total_pages) {
                                if ($end < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <!-- Кнопка "Вперед" -->
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                                   aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>