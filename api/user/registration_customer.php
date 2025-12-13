<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST allowed"]);
    exit;
}
$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
try {
    $pdo = new PDO("sqlite:../../db/catalog1.sqlite");
    $check = $pdo->prepare("SELECT id FROM customer WHERE login = :login UNION SELECT id FROM worker WHERE login = :login");
    $check->execute([':login' => $login]);
    if ($check->fetch()) {
        echo json_encode(["error" => "Логин уже занят"]);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO customer (login, password, email, telefone) VALUES (:login, :password, :email, :telefone)");
    $stmt->execute([
        ':login' => $login,
        ':password' => $password,
        ':email' => $email,
        ':telefone' => $telefone
    ]);
    echo json_encode(["success" => true, "message" => "Регистрация успешна"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "DB error: " . $e->getMessage()]);
}
