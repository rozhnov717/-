<?php
try {
    $pdo = new PDO("sqlite:../db/catalog1.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("INSERT INTO warker (id, login, password, telefon, email) 
                VALUES (1, 'kotak', '123456', '375338896568', 'dfdf@gmail.com')");

    echo "Работник добавлен!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
