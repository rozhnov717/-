<?php
// Подключение к базе (замени параметры на свои)
$host = "localhost";
$dbname = "procureflow";   // имя твоей базы
$user = "root";            // логин MySQL
$pass = "";                // пароль MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL для создания таблицы
    $sql = "
    CREATE TABLE IF NOT EXISTS requests (
      id INT AUTO_INCREMENT PRIMARY KEY,
      title VARCHAR(255) NOT NULL,
      description TEXT NOT NULL,
      priority ENUM('low','medium','high') DEFAULT 'medium',
      status ENUM('new','in_progress','completed','cancelled') DEFAULT 'new',
      date DATE NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";

    $pdo->exec($sql);
    echo "Таблица 'requests' успешно создана или уже существует.";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
