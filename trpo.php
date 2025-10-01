<?php
$products = [];
$productCount = 0;
$selectedCategory = $_GET['category'] ?? 'Все категории';
$searchQuery = $_GET['search'] ?? '';

try {
    $pdo = new PDO("sqlite:catalog1.sqlite");

    $query = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if ($selectedCategory !== 'Все категории') {
        $query .= " AND LOWER(category) = LOWER(:category)";
        $params[':category'] = strtolower($selectedCategory);
    }

    if (!empty($searchQuery)) {
        $query .= " AND (LOWER(name) LIKE :search OR LOWER(description) LIKE :search)";
        $params[':search'] = '%' . strtolower($searchQuery) . '%';
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $productCount = count($products);
    $stmt = $pdo->prepare("UPDATE products SET category = :category WHERE id = :id");
$stmt->execute([
    ':category' => 'Офисная техника',
    ':id' => 3
]);

} catch (PDOException $e) {
    echo "Ошибка подключения к SQLite: " . $e->getMessage();
}
?>



}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProcureFlow - Управление закупками</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
  background-image: url("img/money.jpg");
  background-size: cover;                 /* фото растягивается на весь экран */
  background-position: center;            /* выравнивание по центру */
  background-repeat: no-repeat;           /* без повторов */
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
}


        /* Custom CSS Variables */
        :root {
            --font-size: 16px;
            --background: #ffffff;
            --foreground: #1f2937;
            --card: #ffffff;
            --card-foreground: #1f2937;
            --primary: #22c55e;
            --primary-foreground: #ffffff;
            --secondary: #f3f4f6;
            --secondary-foreground: #1f2937;
            --muted: #f9fafb;
            --muted-foreground: #6b7280;
            --accent: #f3f4f6;
            --accent-foreground: #1f2937;
            --destructive: #ef4444;
            --destructive-foreground: #ffffff;
            --border: #e5e7eb;
            --input: #f3f4f6;
            --ring: #22c55e;
            --radius: 0.5rem;
        }

        .sidebar-transition {
            transition: transform 0.2s ease-in-out;
        }

        .page-content {
            display: none;
        }

        .page-content.active {
            display: block;
        }

        .modal {
            display: none;
        }

        .modal.active {
            display: flex;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .product-card img {
            transition: transform 0.2s ease-in-out;
        }

        /* Chart styles */
        .chart-bar {
            background: #22c55e;
            transition: height 0.3s ease;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Mobile sidebar overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white border-r border-gray-200 sidebar-transition transform -translate-x-full lg:translate-x-0 z-50">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold">ProcureFlow</h1>
                        <p class="text-xs text-gray-500">Управление закупками</p>
                    </div>
                </div>
                <button id="sidebar-close" class="lg:hidden p-1 hover:bg-gray-100 rounded">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4">
            <div class="space-y-2">
                <button class="nav-item active w-full flex items-center justify-start h-11 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600" data-page="catalog">
                    <svg class="h-4 w-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Каталог товаров
                </button>
                <button class="nav-item w-full flex items-center justify-start h-11 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100" data-page="requests">
                    <svg class="h-4 w-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Заявки
                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">3</span>
                </button>
                <button class="nav-item w-full flex items-center justify-start h-11 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100" data-page="help">
                    <svg class="h-4 w-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Помощь
                </button>
                <button class="nav-item w-full flex items-center justify-start h-11 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100" data-page="reports">
                    <svg class="h-4 w-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Отчеты
                </button>
            </div>
        </nav>

        <!-- User section -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
            <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 cursor-pointer">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium">Иван Петров</div>
                    <div class="text-xs text-gray-500">ivan@company.ru</div>
                </div>
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="lg:ml-64">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-4 py-3 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button id="sidebar-toggle" class="lg:hidden p-1 hover:bg-gray-100 rounded">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <p class="text-sm text-gray-500">Добро пожаловать в</p>
                        <h2 class="text-xl font-semibold">ProcureFlow</h2>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="p-2 hover:bg-gray-100 rounded">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 19.504C5.732 21.423 7.733 22 10 22c3.866 0 7-1.79 7-4s-3.134-4-7-4c-3.866 0-7 1.79-7 4zm0 0C4.134 17.79 7.268 16 11 16"/>
                        </svg>
                    </button>
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="p-4 lg:p-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:p-8 min-h-[calc(100vh-8rem)]">
                
                <!-- Catalog Page -->
                <div id="catalog-page" class="page-content active">
                    <div class="space-y-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-semibold">Каталог товаров</h1>
                                <p class="text-gray-500">Найдите и закажите необходимые товары</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="text-sm text-gray-500">Найдено товаров: <span id="products-count"><?= $productCount ?></span></span>



                            </div>
                        </div>

                        <!-- Search -->
                        <div class="relative max-w-md">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input id="search-input" type="text" placeholder="Поиск по названию или описанию..." class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <!-- Categories -->
                        <div class="flex flex-wrap gap-2">
                           <div class="flex flex-wrap gap-2">
<?php
$categories = ['Все категории', 'Электроника', 'Офисная техника', 'Инструменты', 'Материалы'];
foreach ($categories as $cat):
    $active = ($selectedCategory === $cat) ? 'bg-green-500 text-white' : 'border border-gray-300 text-gray-700';
?>
    <a href="?category=<?= urlencode($cat) ?>" class="px-4 py-2 rounded-lg hover:bg-gray-50 <?= $active ?>">
        <?= $cat ?>
    </a>
<?php endforeach; ?>
</div>

                        </div>

                        <!-- Products Grid -->
                        <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
<?php foreach ($products as $product): ?>
    <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
        <div class="relative">
            <div class="aspect-square bg-gray-100 overflow-hidden">
                <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="w-full h-full object-cover">
            </div>
            <?php if ($product['discount'] > 0): ?>
                <span class="absolute top-3 right-3 bg-red-500 text-white text-xs px-2 py-1 rounded-full">–<?= $product['discount'] ?>%</span>
            <?php endif; ?>
        </div>
        <div class="p-4">
            <h3 class="font-medium text-gray-900 mb-2"><?= htmlspecialchars($product['name']) ?></h3>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500"><?= htmlspecialchars($product['category']) ?></span>
                <span class="font-semibold text-lg"><?= $product['price'] ?> ₽</span>
            </div>
            <p class="text-sm text-gray-400 mt-2">В наличии: <?= $product['stock'] ?> шт</p>
            <p class="text-sm text-gray-400">Доставка: <?= $product['delivery'] ?></p>
        </div>
    </div>
<?php endforeach; ?>
</div>


                        <!-- No products message -->
                        <div id="no-products" class="text-center py-12 hidden">
                            <p class="text-gray-500">Товары не найдены</p>
                            <p class="text-sm text-gray-400">Попробуйте изменить критерии поиска</p>
                        </div>
                    </div>
                </div>

                <!-- Requests Page -->
                <div id="requests-page" class="page-content">
                    <div class="space-y-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-semibold">Заявки</h1>
                                <p class="text-gray-500">Управление заявками и запросами</p>
                            </div>
                            <button id="create-request-btn" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Создать заявку
                            </button>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="text-2xl font-bold text-blue-600">1</div>
                                <div class="text-sm text-gray-500">Новые заявки</div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="text-2xl font-bold text-yellow-600">1</div>
                                <div class="text-sm text-gray-500">В работе</div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="text-2xl font-bold text-green-600">1</div>
                                <div class="text-sm text-gray-500">Завершено</div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="text-2xl font-bold">3</div>
                                <div class="text-sm text-gray-500">Всего заявок</div>
                            </div>
                        </div>

                        <!-- Requests List -->
                        <div id="requests-list" class="space-y-4">
                            <!-- Requests will be dynamically inserted here -->
                        </div>
                    </div>
                </div>

                <!-- Help Page -->
                <div id="help-page" class="page-content">
                    <div class="space-y-8">
                        <!-- Header -->
                        <div>
                            <h1 class="text-2xl font-semibold">Центр помощи</h1>
                            <p class="text-gray-500">Найдите ответы на часто задаваемые вопросы или свяжитесь с нами</p>
                        </div>

                        <!-- Quick Search -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="relative max-w-md mx-auto">
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input id="faq-search" type="text" placeholder="Поиск по базе знаний..." class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- FAQ Section -->
                            <div class="lg:col-span-2 space-y-6">
                                <div>
                                    <h2 class="text-xl font-semibold mb-4">Часто задаваемые вопросы</h2>
                                    <div id="faq-list" class="space-y-2">
                                        <!-- FAQ items will be dynamically inserted here -->
                                    </div>
                                </div>

                                <!-- Contact Form -->
                                <div class="bg-white border border-gray-200 rounded-lg">
                                    <div class="p-6 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold">Не нашли ответ? Напишите нам</h3>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Имя</label>
                                                <input type="text" placeholder="Ваше имя" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Email</label>
                                                <input type="email" placeholder="your@email.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Тема обращения</label>
                                            <input type="text" placeholder="Кратко опишите проблему" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Сообщение</label>
                                            <textarea placeholder="Подробное описание вашего вопроса" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                                        </div>
                                        <button class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                                            Отправить сообщение
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="space-y-6">
                                <!-- Contact Info -->
                                <div class="bg-white border border-gray-200 rounded-lg">
                                    <div class="p-6 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold">Контактная информация</h3>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 bg-green-100 rounded-lg">
                                                <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium">Телефон поддержки</div>
                                                <div class="text-sm text-blue-600">+7 (495) 123-45-67</div>
                                                <div class="text-xs text-gray-500">Пн-Пт: 9:00-18:00</div>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 bg-green-100 rounded-lg">
                                                <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium">Email поддержки</div>
                                                <div class="text-sm text-blue-600">support@procureflow.ru</div>
                                                <div class="text-xs text-gray-500">Ответим в течение 2 часов</div>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 bg-green-100 rounded-lg">
                                                <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium">Онлайн чат</div>
                                                <div class="text-sm text-blue-600">Доступен на сайте</div>
                                                <div class="text-xs text-gray-500">Мгновенные ответы</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="bg-white border border-gray-200 rounded-lg">
                                    <div class="p-6 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold">Статус системы</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                            <span class="text-sm">Все системы работают нормально</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-2">
                                            Последнее обновление: сегодня в 14:30
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Page -->
                <div id="reports-page" class="page-content">
                    <div class="space-y-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-semibold">Отчеты и аналитика</h1>
                                <p class="text-gray-500">Анализ показателей и статистика заказов</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <select class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option>Последний месяц</option>
                                    <option>3 месяца</option>
                                    <option selected>6 месяцев</option>
                                    <option>Год</option>
                                </select>
                                <button class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Экспорт
                                </button>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Общий оборот</p>
                                        <p class="text-2xl font-bold">₽12,450,000</p>
                                    </div>
                                    <div class="p-3 bg-green-100 rounded-lg">
                                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center mt-4">
                                    <svg class="h-4 w-4 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span class="text-sm text-green-500">+12.5%</span>
                                    <span class="text-sm text-gray-500 ml-1">за период</span>
                                </div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Количество заказов</p>
                                        <p class="text-2xl font-bold">328</p>
                                    </div>
                                    <div class="p-3 bg-green-100 rounded-lg">
                                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center mt-4">
                                    <svg class="h-4 w-4 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span class="text-sm text-green-500">+8.2%</span>
                                    <span class="text-sm text-gray-500 ml-1">за период</span>
                                </div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Средний чек</p>
                                        <p class="text-2xl font-bold">₽37,957</p>
                                    </div>
                                    <div class="p-3 bg-green-100 rounded-lg">
                                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center mt-4">
                                    <svg class="h-4 w-4 mr-1 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                    </svg>
                                    <span class="text-sm text-red-500">-2.1%</span>
                                    <span class="text-sm text-gray-500 ml-1">за период</span>
                                </div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Время обработки</p>
                                        <p class="text-2xl font-bold">2.3 дня</p>
                                    </div>
                                    <div class="p-3 bg-green-100 rounded-lg">
                                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center mt-4">
                                    <svg class="h-4 w-4 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span class="text-sm text-green-500">-15.2%</span>
                                    <span class="text-sm text-gray-500 ml-1">за период</span>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Orders Chart -->
                            <div class="bg-white border border-gray-200 rounded-lg">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold">Динамика заказов</h3>
                                </div>
                                <div class="p-6">
                                    <div class="chart-container">
                                        <canvas id="ordersChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Revenue Chart -->
                            <div class="bg-white border border-gray-200 rounded-lg">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold">Выручка по месяцам</h3>
                                </div>
                                <div class="p-6">
                                    <div class="chart-container">
                                        <canvas id="revenueChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold">Последние заказы</h3>
                            </div>
                            <div class="p-6">
                                <div id="recent-orders" class="space-y-3">
                                    <!-- Recent orders will be dynamically inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Request Modal -->
    <div id="create-request-modal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-md w-full mx-4">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Новая заявка</h3>
                    <button id="close-modal-btn" class="p-1 hover:bg-gray-100 rounded">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Название заявки</label>
                    <input id="request-title" type="text" placeholder="Введите название заявки" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Описание</label>
                    <textarea id="request-description" placeholder="Подробное описание заявки" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Приоритет</label>
                    <select id="request-priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="low">Низкий</option>
                        <option value="medium" selected>Средний</option>
                        <option value="high">Высокий</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-4">
                    <button id="submit-request-btn" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        Создать заявку
                    </button>
                    <button id="cancel-request-btn" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Отмена
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Application state
        let currentPage = 'catalog';
        let requests = [
            {
                id: "1",
                title: "Поставка ноутбуков Dell",
                description: "Необходимо поставить 15 ноутбуков Dell Latitude для нового офиса",
                status: "in_progress",
                date: "2025-01-15",
                priority: "high"
            },
            {
                id: "2",
                title: "Ремонт принтера HP",
                description: "Принтер HP LaserJet не печатает, требуется диагностика",
                status: "new",
                date: "2025-01-10",
                priority: "medium"
            },
            {
                id: "3",
                title: "Установка ПО на рабочие места",
                description: "Установить Microsoft Office на 10 рабочих мест",
                status: "completed",
                date: "2025-01-05",
                priority: "low"
            }
        ];

      /*  const products = [
            {
                id: "1",
                name: "Ноутбук Dell Latitude 5520",
                image: "https://images.unsplash.com/photo-1641430034785-47f6f91ab6cf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb2Rlcm4lMjBsYXB0b3AlMjBjb21wdXRlciUyMHRlY2hub2xvZ3l8ZW58MXx8fHwxNzU5MjgxNzAzfDA&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽89,990",
                category: "Ноутбуки",
                isNew: false
            },
            {
                id: "2", 
                name: "Монитор Dell 24\" FHD",
                image: "https://images.unsplash.com/photo-1641430034785-47f6f91ab6cf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb2Rlcm4lMjBsYXB0b3AlMjBjb21wdXRlciUyMHRlY2hub2xvZ3l8ZW58MXx8fHwxNzU5MjgxNzAzfDA&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽15,990",
                category: "Мониторы",
                isNew: true
            },
            {
                id: "3",
                name: "Принтер HP LaserJet Pro M404n",
                image: "https://images.unsplash.com/photo-1641430034785-47f6f91ab6cf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb2Rlcm4lMjBsYXB0b3AlMjBjb21wdXRlciUyMHRlY2hub2xvZ3l8ZW58MXx8fHwxNzU5MjgxNzAzfDA&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽28,990",
                category: "Принтеры",
                isNew: false
            },
            {
                id: "4",
                name: "Электропила Bosch PSB 650 RE",
                image: "https://images.unsplash.com/photo-1641430034785-47f6f91ab6cf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb2Rlcm4lMjBsYXB0b3AlMjBjb21wdXRlciUyMHRlY2hub2xvZ3l8ZW58MXx8fHwxNzU5MjgxNzAzfDA&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽12,490",
                category: "Инструменты",
                isNew: true
            },
            {
                id: "5",
                name: "Смартфон Samsung Galaxy S24",
                image: "https://images.unsplash.com/photo-1640936343842-268f9d87e764?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxzbWFydHBob25lJTIwbW9iaWxlJTIwZGV2aWNlfGVufDF8fHx8MTc1OTI4MDUyNnww&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽45,990",
                category: "Смартфоны",
                isNew: true
            },
            {
                id: "6",
                name: "Наушники AirPods Pro",
                image: "https://images.unsplash.com/photo-1632200004922-bc18602c79fc?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx3aXJlbGVzcyUyMGhlYWRwaG9uZXMlMjBhdWRpb3xlbnwxfHx8fDE3NTkyMTAyODZ8MA&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽19,990",
                category: "Аудио",
                isNew: false
            },
            {
                id: "7",
                name: "Планшет iPad Air",
                image: "https://images.unsplash.com/photo-1628591459313-a64214c5bfac?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx0YWJsZXQlMjBkZXZpY2UlMjBlbGVjdHJvbmljc3xlbnwxfHx8fDE3NTkyOTcyNjd8MA&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽52,990",
                category: "Планшеты",
                isNew: false
            },
            {
                id: "8",
                name: "Клавиатура механическая",
                image: "https://images.unsplash.com/photo-1641430034785-47f6f91ab6cf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb2Rlcm4lMjBsYXB0b3AlMjBjb21wdXRlciUyMHRlY2hub2xvZ3l8ZW58MXx8fHwxNzU5MjgxNzAzfDA&ixlib=rb-4.1.0&q=80&w=400",
                price: "₽8,990",
                category: "Аксессуары",
                isNew: true
            }
        ];*/

        const faqData = [
            {
                id: "1",
                question: "Как оформить заказ в каталоге товаров?",
                answer: "Для оформления заказа выберите нужные товары в каталоге, добавьте их в корзину и следуйте инструкциям на экране. Вы также можете создать заявку через раздел 'Заявки' для более сложных запросов."
            },
            {
                id: "2", 
                question: "Какие способы оплаты доступны?",
                answer: "Мы принимаем безналичную оплату по счету, банковские карты и электронные платежи. Для корпоративных клиентов доступна отсрочка платежа по договору."
            },
            {
                id: "3",
                question: "Сколько времени занимает доставка?",
                answer: "Стандартная доставка занимает 1-3 рабочих дня в пределах города и 3-7 дней в регионы. Для срочных заказов доступна экспресс-доставка в течение дня."
            },
            {
                id: "4",
                question: "Как отследить статус моей заявки?",
                answer: "Статус заявки можно отследить в разделе 'Заявки'. Там отображается текущий статус, дата создания и комментарии менеджера."
            },
            {
                id: "5",
                question: "Предоставляете ли вы гарантию на товары?",
                answer: "Да, на все товары предоставляется официальная гарантия производителя. Срок гарантии указан в описании каждого товара."
            }
        ];

        const recentOrders = [
            { id: "ORD-2025-001", date: "2025-01-15", client: "ООО Техносфера", amount: "₽125,000", status: "completed" },
            { id: "ORD-2025-002", date: "2025-01-14", client: "АО Прогресс", amount: "₽87,500", status: "processing" },
            { id: "ORD-2025-003", date: "2025-01-13", client: "ИП Иванов", amount: "₽45,200", status: "pending" },
            { id: "ORD-2025-004", date: "2025-01-12", client: "ООО Инновации", amount: "₽156,800", status: "completed" }
        ];

        // Utility functions
        function showPage(pageId) {
            document.querySelectorAll('.page-content').forEach(page => {
                page.classList.remove('active');
            });
            document.getElementById(pageId + '-page').classList.add('active');
            
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active', 'bg-green-500', 'text-white');
                item.classList.add('text-gray-700', 'hover:bg-gray-100');
            });
            
            const activeNavItem = document.querySelector(`.nav-item[data-page="${pageId}"]`);
            if (activeNavItem) {
                activeNavItem.classList.add('active', 'bg-green-500', 'text-white');
                activeNavItem.classList.remove('text-gray-700', 'hover:bg-gray-100');
            }
            
            currentPage = pageId;
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }

        // Product functions
        function renderProducts(productsToRender = products) {
            const grid = document.getElementById('products-grid');
            const noProducts = document.getElementById('no-products');
            const countElement = document.getElementById('products-count');
            
            if (productsToRender.length === 0) {
                grid.innerHTML = '';
                noProducts.classList.remove('hidden');
            } else {
                noProducts.classList.add('hidden');
                grid.innerHTML = productsToRender.map(product => `
                    <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="relative">
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                <img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover">
                            </div>
                            ${product.isNew ? '<span class="absolute top-3 right-3 bg-green-500 text-white text-xs px-2 py-1 rounded-full">Новинка</span>' : ''}
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 mb-2 line-clamp-2">${product.name}</h3>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">${product.category}</span>
                                <span class="font-semibold text-lg">${product.price}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            
            countElement.textContent = productsToRender.length;
        }

        function filterProducts() {
            const searchQuery = document.getElementById('search-input').value.toLowerCase();
            const selectedCategory = document.querySelector('.category-btn.active').dataset.category;
            
            let filtered = products;
            
            if (searchQuery) {
                filtered = filtered.filter(product => 
                    product.name.toLowerCase().includes(searchQuery)
                );
            }
            
            if (selectedCategory !== "Все категории") {
                filtered = filtered.filter(product => 
                    product.category.toLowerCase().includes(selectedCategory.toLowerCase())
                );
            }
            
            renderProducts(filtered);
        }

        // Request functions
        function renderRequests() {
            const container = document.getElementById('requests-list');
            const statusColors = {
                new: "bg-blue-500",
                in_progress: "bg-yellow-500", 
                completed: "bg-green-500",
                cancelled: "bg-red-500"
            };
            
            const statusLabels = {
                new: "Новая",
                in_progress: "В работе",
                completed: "Завершена",
                cancelled: "Отменена"
            };
            
            const priorityColors = {
                low: "border-l-green-500",
                medium: "border-l-yellow-500",
                high: "border-l-red-500"
            };
            
            container.innerHTML = requests.map(request => `
                <div class="bg-white border border-gray-200 rounded-lg border-l-4 ${priorityColors[request.priority]}">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">${request.title}</h3>
                            <div class="flex items-center gap-2">
                                <span class="${statusColors[request.status]} text-white text-xs px-2 py-1 rounded-full">
                                    ${statusLabels[request.status]}
                                </span>
                                <div class="flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    ${request.date}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-500 mb-4">${request.description}</p>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Подробнее
                            </button>
                            <button class="px-3 py-1 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Комментарии
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function addRequest(title, description, priority) {
            const newRequest = {
                id: Date.now().toString(),
                title: title,
                description: description,
                status: "new",
                date: new Date().toISOString().split('T')[0],
                priority: priority
            };
            
            requests.unshift(newRequest);
            renderRequests();
        }

        // FAQ functions
        function renderFAQ(faqToRender = faqData) {
            const container = document.getElementById('faq-list');
            
            container.innerHTML = faqToRender.map(item => `
                <div class="border border-gray-200 rounded-lg">
                    <button class="faq-trigger w-full text-left p-4 hover:bg-gray-50 flex items-center justify-between" data-faq="${item.id}">
                        <span class="font-medium">${item.question}</span>
                        <svg class="h-4 w-4 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="faq-content hidden px-4 pb-4 text-gray-500">
                        ${item.answer}
                    </div>
                </div>
            `).join('');
        }

        function filterFAQ() {
            const searchQuery = document.getElementById('faq-search').value.toLowerCase();
            
            if (!searchQuery) {
                renderFAQ();
                return;
            }
            
            const filtered = faqData.filter(item =>
                item.question.toLowerCase().includes(searchQuery) ||
                item.answer.toLowerCase().includes(searchQuery)
            );
            
            renderFAQ(filtered);
        }

        // Reports functions
        function renderRecentOrders() {
            const container = document.getElementById('recent-orders');
            const statusColors = {
                completed: "bg-green-500",
                processing: "bg-yellow-500",
                pending: "bg-blue-500",
                cancelled: "bg-red-500"
            };
            
            const statusLabels = {
                completed: "Выполнен",
                processing: "В обработке", 
                pending: "Ожидает",
                cancelled: "Отменен"
            };
            
            container.innerHTML = recentOrders.map(order => `
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div>
                        <div class="font-medium">${order.id}</div>
                        <div class="text-sm text-gray-500">${order.client}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium">${order.amount}</div>
                        <div class="flex items-center gap-2">
                            <span class="${statusColors[order.status]} text-white text-xs px-2 py-1 rounded-full">
                                ${statusLabels[order.status]}
                            </span>
                            <span class="text-xs text-gray-500">${order.date}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function createSimpleChart(canvasId, data, type = 'line') {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            const width = canvas.width;
            const height = canvas.height;
            
            // Clear canvas
            ctx.clearRect(0, 0, width, height);
            
            // Set styles
            ctx.strokeStyle = '#22c55e';
            ctx.fillStyle = '#22c55e';
            ctx.lineWidth = 2;
            
            // Calculate points
            const padding = 40;
            const chartWidth = width - padding * 2;
            const chartHeight = height - padding * 2;
            
            const maxValue = Math.max(...data.map(d => d.value));
            const stepX = chartWidth / (data.length - 1);
            
            // Draw axes
            ctx.strokeStyle = '#e5e7eb';
            ctx.lineWidth = 1;
            
            // Y axis
            ctx.beginPath();
            ctx.moveTo(padding, padding);
            ctx.lineTo(padding, height - padding);
            ctx.stroke();
            
            // X axis
            ctx.beginPath();
            ctx.moveTo(padding, height - padding);
            ctx.lineTo(width - padding, height - padding);
            ctx.stroke();
            
            // Draw data
            ctx.strokeStyle = '#22c55e';
            ctx.fillStyle = '#22c55e';
            ctx.lineWidth = 2;
            
            if (type === 'line') {
                // Draw line
                ctx.beginPath();
                data.forEach((point, index) => {
                    const x = padding + index * stepX;
                    const y = height - padding - (point.value / maxValue) * chartHeight;
                    
                    if (index === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                });
                ctx.stroke();
                
                // Draw points
                data.forEach((point, index) => {
                    const x = padding + index * stepX;
                    const y = height - padding - (point.value / maxValue) * chartHeight;
                    
                    ctx.beginPath();
                    ctx.arc(x, y, 4, 0, 2 * Math.PI);
                    ctx.fill();
                });
            } else if (type === 'bar') {
                // Draw bars
                const barWidth = stepX * 0.6;
                data.forEach((point, index) => {
                    const x = padding + index * stepX - barWidth / 2;
                    const barHeight = (point.value / maxValue) * chartHeight;
                    const y = height - padding - barHeight;
                    
                    ctx.fillRect(x, y, barWidth, barHeight);
                });
            }
            
            // Draw labels
            ctx.fillStyle = '#6b7280';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'center';
            
            data.forEach((point, index) => {
                const x = padding + index * stepX;
                ctx.fillText(point.label, x, height - 10);
            });
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    const page = this.dataset.page;
                    showPage(page);
                    closeSidebar();
                });
            });
            
            // Sidebar toggle
            document.getElementById('sidebar-toggle').addEventListener('click', toggleSidebar);
            document.getElementById('sidebar-close').addEventListener('click', closeSidebar);
            document.getElementById('sidebar-overlay').addEventListener('click', closeSidebar);
            
            // Product catalog
            document.getElementById('search-input').addEventListener('input', filterProducts);
            
    
                    
                    this.classList.add('active', 'bg-green-500', 'text-white');
                    this.classList.remove('border', 'border-gray-300', 'text-gray-700');
                    
                    filterProducts();
                });
           
            
            // Requests modal
            document.getElementById('create-request-btn').addEventListener('click', function() {
                document.getElementById('create-request-modal').classList.add('active');
            });
            
            document.getElementById('close-modal-btn').addEventListener('click', function() {
                document.getElementById('create-request-modal').classList.remove('active');
            });
            
            document.getElementById('cancel-request-btn').addEventListener('click', function() {
                document.getElementById('create-request-modal').classList.remove('active');
            });
            
            document.getElementById('submit-request-btn').addEventListener('click', function() {
                const title = document.getElementById('request-title').value;
                const description = document.getElementById('request-description').value;
                const priority = document.getElementById('request-priority').value;
                
                if (title && description) {
                    addRequest(title, description, priority);
                    
                    // Reset form
                    document.getElementById('request-title').value = '';
                    document.getElementById('request-description').value = '';
                    document.getElementById('request-priority').value = 'medium';
                    
                    // Close modal
                    document.getElementById('create-request-modal').classList.remove('active');
                }
            });
            
            // FAQ search
            document.getElementById('faq-search').addEventListener('input', filterFAQ);
            
            // FAQ toggle
            document.addEventListener('click', function(e) {
                if (e.target.closest('.faq-trigger')) {
                    const trigger = e.target.closest('.faq-trigger');
                    const content = trigger.nextElementSibling;
                    const icon = trigger.querySelector('svg');
                    
                    if (content.classList.contains('hidden')) {
                        content.classList.remove('hidden');
                        icon.style.transform = 'rotate(180deg)';
                    } else {
                        content.classList.add('hidden');
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            });
            
            // Initialize data
           // renderProducts();
            renderRequests();
            renderFAQ();
            renderRecentOrders();
            
            // Initialize charts
            setTimeout(() => {
                const ordersData = [
                    {label: 'Янв', value: 45},
                    {label: 'Фев', value: 52},
                    {label: 'Мар', value: 48},
                    {label: 'Апр', value: 61},
                    {label: 'Май', value: 55},
                    {label: 'Июн', value: 67}
                ];
                
                const revenueData = [
                    {label: 'Янв', value: 1250},
                    {label: 'Фев', value: 1680},
                    {label: 'Мар', value: 1420},
                    {label: 'Апр', value: 1890},
                    {label: 'Май', value: 1750},
                    {label: 'Июн', value: 2100}
                ];
                
                createSimpleChart('ordersChart', ordersData, 'line');
                createSimpleChart('revenueChart', revenueData, 'bar');
            }, 100);
    </script>
</body>
</html>
