<?php
// Подключение настроек и БД
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров | СтройМатериалы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Каталог товаров</h1>
        
        <!-- Фильтры и сортировка -->
        <div class="row mb-4">
            <div class="col-md-4">
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Поиск..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit" class="btn btn-primary">Найти</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form method="GET">
                    <div class="input-group">
                        <input type="number" name="min_price" class="form-control" placeholder="От" 
                               value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                        <input type="number" name="max_price" class="form-control" placeholder="До" 
                               value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                        <button type="submit" class="btn btn-primary">Фильтр</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form method="GET" class="float-end">
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="">Сортировка</option>
                        <option value="price_asc" <?= ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>Цена (по возрастанию)</option>
                        <option value="price_desc" <?= ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>Цена (по убыванию)</option>
                        <option value="name_asc" <?= ($_GET['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>Название (А-Я)</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Список товаров -->
        <div class="row">
            <?php
            // Формируем SQL-запрос с фильтрами
            $sql = "SELECT * FROM products WHERE 1=1";
            $params = [];

            // Поиск по названию/описанию
            if (!empty($_GET['search'])) {
                $sql .= " AND (name LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $_GET['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // Фильтр по цене
            if (!empty($_GET['min_price'])) {
                $sql .= " AND price >= ?";
                $params[] = (float)$_GET['min_price'];
            }
            if (!empty($_GET['max_price'])) {
                $sql .= " AND price <= ?";
                $params[] = (float)$_GET['max_price'];
            }

            // Сортировка
            switch ($_GET['sort'] ?? '') {
                case 'price_asc':  $sql .= " ORDER BY price ASC"; break;
                case 'price_desc': $sql .= " ORDER BY price DESC"; break;
                case 'name_asc':   $sql .= " ORDER BY name ASC"; break;
                default:           $sql .= " ORDER BY id DESC"; // Сначала новые
            }

            // Выполняем запрос
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();

            // Вывод товаров
            foreach ($products as $product): 
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="/images/products/<?= htmlspecialchars($product['image']) ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                            <p class="text-success fw-bold"><?= number_format($product['price'], 2) ?> ₽</p>
                            <a href="/add_to_cart.php?id=<?= $product['id'] ?>" class="btn btn-primary">В корзину</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>