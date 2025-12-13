<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO("sqlite:../../../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $count_goods = (int)($_POST['count_goods'] ?? 0);
    $id_goods    = (int)($_POST['id_goods'] ?? 0);
    $comment     = trim($_POST['comment'] ?? '');
    $date        = time();
    $status      = 0;

    session_start();
    $id_creator = 1;

    if ($id_goods > 0 && $count_goods > 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO order_customer 
            (id_creator, id_goods, date, count_goods, status, comment) 
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        $ok = $stmt->execute([
            $id_creator, $id_goods, $date, $count_goods, $status, $comment
        ]);

        echo json_encode(['success' => $ok], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Неверные данные'], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
