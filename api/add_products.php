<?php
header('Content-Type: application/json; charset=utf-8');
try {
    $pdo = new PDO("sqlite:../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = (int)($_POST['priority'] ?? 2);
    $comment = $_POST['comment'] ?? '';
    $count_goods = (int)($_POST['count_goods'] ?? 0);
    $id_creator = (int)($_POST['id_creator'] ?? 0);
    $date = time();
    $status = 0;

    if (!$title || !$description || $count_goods <= 0) {
        echo json_encode(['success' => false, 'error' => 'Заполните все поля']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO "order"(title, description, priority, status, date, comment, count_goods, id_creator) VALUES(?,?,?,?,?,?,?,?)');
    $ok = $stmt->execute([$title, $description, $priority, $status, $date, $comment, $count_goods, $id_creator]);

    echo json_encode(['success' => $ok]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
