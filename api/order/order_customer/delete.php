<?php
header('Content-Type: application/json; charset=utf-8');
try {
    $pdo = new PDO("sqlite:../../../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Некорректный id']); exit; }

    $stmt = $pdo->prepare('DELETE FROM "order_customer" WHERE id = ?');
    $ok = $stmt->execute([$id]);

    echo json_encode(['success' => $ok]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
