<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST allowed"]);
    exit;
}
$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';
try {
    $pdo = new PDO("sqlite:../db/catalog1.sqlite");
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE login = :login AND password = :password");
    $stmt->execute([':login' => $login, ':password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo json_encode(["success" => true, "role" => "customer", "user" => $user]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM warker WHERE login = :login AND password = :password");
    $stmt->execute([':login' => $login, ':password' => $password]);
    $worker = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($worker) {
        echo json_encode(["success" => true, "role" => "worker", "user" => $worker]);
        exit;
    }
    echo json_encode(["error" => "Неверный логин или пароль"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "DB error: " . $e->getMessage()]);
}
