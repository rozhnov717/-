<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO("sqlite:../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Поддержка ?id=123 для получения одного заказа
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($id) {
        $stmt = $pdo->prepare('SELECT id, title, description, priority, status, date, comment, id_creator FROM "order" WHERE id = ?');
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['item' => $item ?: null], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Иначе — список всех заказов
    $stmt = $pdo->query('SELECT id, title, description, priority, status, date, comment, id_creator FROM "order" ORDER BY id DESC');
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['items' => $items ?: []], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
