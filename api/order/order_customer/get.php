<?php
header('Content-Type: application/json; charset=utf-8');
ob_start(); 

try {
    $pdo = new PDO("sqlite:../../../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($id) {
        $stmt = $pdo->prepare('SELECT id, id_creator, id_goods, date, count_goods, status, comment FROM "order_customer" WHERE id = ?');
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        ob_end_clean(); 
        echo json_encode(['item' => $item ?: null], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->query('SELECT id, id_creator, id_goods, date, count_goods, status, comment FROM "order_customer" ORDER BY id DESC');
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_end_clean();
    echo json_encode(['items' => $items ?: []], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
