
<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST allowed"]);
    exit;
}

try {
    $pdo = new PDO("sqlite:../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $required = ['name','category','price','discount','stock','delivery','image'];
    foreach ($required as $key) {
        if (!isset($_POST[$key])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing field: $key"]);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO products (name, category, price, discount, stock, delivery, image)
        VALUES (:name, :category, :price, :discount, :stock, :delivery, :image)
    ");

    $stmt->execute([
        ':name'     => trim($_POST['name']),
        ':category' => trim($_POST['category']),
        ':price'    => (int)$_POST['price'],
        ':discount' => (int)$_POST['discount'],
        ':stock'    => (int)$_POST['stock'],
        ':delivery' => trim($_POST['delivery']),
        ':image'    => trim($_POST['image'])
    ]);

    echo json_encode(["success" => true, "message" => "✅ Товар добавлен!"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB error: " . $e->getMessage()]);
}
