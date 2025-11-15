<?php
$pdo = new PDO("sqlite:../db/catalog.sqlite");

$pdo->exec("DROP TABLE IF EXISTS products");

$pdo->exec("CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    category TEXT,
    price INTEGER,
    discount INTEGER,
    stock INTEGER,
    delivery TEXT,
    image TEXT
)");

$pdo->exec("INSERT INTO products (name, category, price, discount, stock, delivery, image) VALUES
('Ноутбук Dell Latitude 5520', 'Электроника', 78000, 9, 15, '3–7 дней', 'images/dell-laptop.jpg'),
('Монитор Dell 24\" FHD', 'Электроника', 13500, 10, 12, '3–7 дней', 'images/dell-monitor.jpg'),
('Принтер HP LaserJet Pro M404dn', 'Офис', 23000, 5, 5, '3–7 дней', 'images/hp-printer.jpg')");

echo "База данных пересоздана и заполнена!";
