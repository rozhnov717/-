<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO("sqlite:../../../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = (int)($_POST['priority'] ?? 2);
    $count_goods = (int)($_POST['count_goods'] ?? 0);
    $have_goods  = (int)($_POST['have_goods_in_catalog'] ?? 0);
    $goods_date  = $_POST['goods_date'] ?? null;
    $date        = time();
    $status      = 0;

    session_start();
    $id_creator = $_SESSION['worker_id'] ?? 0;

    $id_goods = 0;
    if ($have_goods === 1) {
        $stmt = $pdo->prepare('SELECT id FROM goods WHERE name = ? LIMIT 1');
        $stmt->execute([$title]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $id_goods = (int)$row['id'];
        } else {
            echo json_encode(['success' => false, 'error' => 'Товар не найден в каталоге']);
            exit;
        }
    }

    if ($title !== '' && $count_goods > 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO order_warehouse 
            (title, description, priority, status, date, id_goods, id_creator, count_goods, have_goods_in_catalog) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $ok = $stmt->execute([
            $title, $description, $priority, $status, $date,
            $id_goods, $id_creator, $count_goods, $have_goods
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
