<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = new PDO("sqlite:catalog1.sqlite");

    $stmt = $pdo->prepare("INSERT INTO products (name, category, price, discount, stock, delivery, image)
        VALUES (:name, :category, :price, :discount, :stock, :delivery, :image)");

    $stmt->execute([
        ':name' => $_POST['name'],
        ':category' => $_POST['category'],
        ':price' => $_POST['price'],
        ':discount' => $_POST['discount'],
        ':stock' => $_POST['stock'],
        ':delivery' => $_POST['delivery'],
        ':image' => $_POST['image']
    ]);

    echo "✅ Товар добавлен!";
}
?>

<form method="POST">
  <h2>Добавить товар</h2>
  <input name="name" placeholder="Название"><br>
  <input name="category" placeholder="Категория"><br>
  <input name="price" type="number" placeholder="Цена"><br>
  <input name="discount" type="number" placeholder="Скидка %"><br>
  <input name="stock" type="number" placeholder="В наличии"><br>
  <input name="delivery" placeholder="Доставка"><br>
  <input name="image" placeholder="Путь к картинке"><br>
  <button type="submit">Добавить</button>
</form>
