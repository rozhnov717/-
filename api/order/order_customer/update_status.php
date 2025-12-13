<?php
header('Content-Type: application/json; charset=utf-8');
ob_start(); 

try {
    $pdo = new PDO("sqlite:../../../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $status = isset($_GET['status']) ? (int)$_GET['status'] : -1;

    if ($id > 0 && in_array($status, [0, 1, 2, 3])) {
        $stmt = $pdo->prepare('UPDATE "order_customer" SET status = ? WHERE id = ?');
        $success = $stmt->execute([$status, $id]);
        ob_end_clean();
        echo json_encode(['success' => $success], JSON_UNESCAPED_UNICODE);
    } else {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Неверные параметры'], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
