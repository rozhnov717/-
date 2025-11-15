<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    $pdo = new PDO("sqlite:../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = (int)($_POST['priority'] ?? 2);
    $comment = trim($_POST['comment'] ?? '');
    $count_goods = (int)($_POST['count_goods'] ?? 0);
    $id_creator = (int)($_POST['id_creator'] ?? 0);
    $date = time();
    $status = 0;

    if ($title !== '' && $description !== '' && $count_goods >= 0) {
        $stmt = $pdo->prepare('INSERT INTO "order" (title, description, priority, comment, count_goods, id_creator, date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $ok = $stmt->execute([$title, $description, $priority, $comment, $count_goods, $id_creator, $date, $status]);
        ob_end_clean();
        echo json_encode(['success' => $ok], JSON_UNESCAPED_UNICODE);
        exit;
    }

    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Неверные данные'], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
