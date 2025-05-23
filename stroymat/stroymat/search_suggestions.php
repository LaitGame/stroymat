<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if (empty($term) || strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, name, image 
        FROM products 
        WHERE name LIKE :term 
        OR description LIKE :term
        ORDER BY name
        LIMIT 5
    ");
    
    $searchTerm = '%' . $term . '%';
    $stmt->bindParam(':term', $searchTerm);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Форматируем результаты для autocomplete
    $suggestions = [];
    foreach ($results as $row) {
        $suggestions[] = [
            'id' => $row['id'],
            'label' => $row['name'],
            'value' => $row['name'],
            'image' => '/assets/images/' . $row['image']
        ];
    }
    
    echo json_encode($suggestions);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}