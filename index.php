<?php
$products = [];

try {
    $pdo = new PDO("sqlite:catalog.sqlite");
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Ошибка подключения к SQLite: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Каталог товаров</title>
  <style>
    body { font-family: Arial; margin: 20px; }
    select { padding: 5px; margin-bottom: 20px; }
    .card-container { display: flex; gap: 20px; flex-wrap: wrap; justify-content: space-between; }
    .card { border: 1px solid #ccc; padding: 15px; width: 30%; box-sizing: border-box; border-radius: 5px; background: #f9f9f9; }
    .card img { max-width: 100%; height: auto; margin-bottom: 10px; }
    .discount { color: red; }
  </style>
</head>
<body>

  <h1>Каталог товаров</h1>
  <p>Товары доступны по договорным условиям для вашей организации.</p>

  <label for="category">Выберите категорию:</label>
  <select id="category" onchange="filterCards()">
    <option value="Все">Все категории</option>
    <option value="Электроника">Электроника</option>
    <option value="Офис">Офис</option>
  </select>

  <div class="card-container" id="cards">
    <?php foreach ($products as $product): ?>
      <div class="card" data-category="<?= $product['category'] ?>">
        <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
        <h3><?= $product['name'] ?></h3>
        <p>Категория: <?= $product['category'] ?></p>
        <p>Цена: <?= $product['price'] ?> ₽ <span class="discount">–<?= $product['discount'] ?>%</span></p>
        <p>В наличии: <?= $product['stock'] ?> шт</p>
        <p>Доставка: <?= $product['delivery'] ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <script>
    function filterCards() {
      const selected = document.getElementById('category').value;
      const cards = document.querySelectorAll('.card');
      cards.forEach(card => {
        const category = card.getAttribute('data-category');
        card.style.display = (selected === 'Все' || category === selected) ? 'block' : 'none';
      });
    }
  </script>

</body>
</html>
