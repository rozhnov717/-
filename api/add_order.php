<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("sqlite:../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем данные из формы
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = (int) ($_POST['priority'] ?? 2); // 1=низкий, 2=средний, 3=высокий
    $status = 0; // 0 = new
    $date = time(); // UNIX timestamp
    $comment = $_POST['comment'] ?? '';
    $id_creator = (int) ($_POST['id_creator'] ?? 0);
    $count_goods = (int) ($_POST['count_goods'] ?? 0);

    if (!$title || !$description || $count_goods <= 0) {
        echo json_encode(['success' => false, 'error' => 'Заполните все поля и укажите количество товара']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO "order" (title, description, priority, status, date, comment, id_creator, count_goods) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $success = $stmt->execute([$title, $description, $priority, $status, $date, $comment, $id_creator, $count_goods]);

    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
