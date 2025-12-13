<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO("sqlite:../../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $category = isset($_GET['category']) ? trim($_GET['category']) : null;
    $search   = isset($_GET['search']) ? trim($_GET['search']) : null;

    $query = "SELECT * FROM goods WHERE 1=1";
    $params = [];

    if (!empty($category) && strtolower($category) !== strtolower('Все категории')) {
        $query .= " AND LOWER(category) = LOWER(:category)";
        $params[':category'] = $category;
    }

    if (!empty($search)) {
        $query .= " AND (LOWER(name) LIKE :search OR LOWER(description) LIKE :search)";
        $params[':search'] = '%' . strtolower($search) . '%';
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "count" => count($products),
        "items" => $products
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB error: " . $e->getMessage()]);
}
