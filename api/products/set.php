<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Подключение к базе
    $pdo = new PDO("sqlite:../../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем данные из POST
    $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $price    = isset($_POST['price']) ? (int)$_POST['price'] : 0;
    $discount = isset($_POST['discount']) ? (int)$_POST['discount'] : 0;
    $stock    = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $delivery = isset($_POST['delivery']) ? trim($_POST['delivery']) : '';
    $image    = isset($_POST['image']) ? trim($_POST['image']) : '';

    // Проверка обязательных полей
    if (empty($name) || empty($category) || $price <= 0 || $stock < 0 || empty($delivery) || empty($image)) {
        echo json_encode([
            "success" => false,
            "error"   => "Заполните все поля корректно"
        ]);
        exit;
    }

    // SQL для вставки
    $stmt = $pdo->prepare(
        "INSERT INTO goods (name, category, price, discount, stock, delivery, image)
         VALUES (:name, :category, :price, :discount, :stock, :delivery, :image)"
    );

    $ok = $stmt->execute([
        ':name'     => $name,
        ':category' => $category,
        ':price'    => $price,
        ':discount' => $discount,
        ':stock'    => $stock,
        ':delivery' => $delivery,
        ':image'    => $image
    ]);

    echo json_encode([
        "success" => $ok
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "DB error: " . $e->getMessage()
    ]);
}
