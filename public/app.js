let currentPage = 'catalog';
let currentPriorityFilter = '';
let currentStatusFilter = '';

let selectedCategory = 'Все категории';
let searchQuery = '';

const categories = ['Все категории', 'Электроника', 'Офисная техника', 'Инструменты', 'Материалы'];

const faqData = [
  { id: "1", question: "Как оформить заказ в каталоге товаров?", answer: "Для оформления заказа выберите нужные товары..." },
  { id: "2", question: "Какие способы оплаты доступны?", answer: "Мы принимаем безналичную оплату..." },
  { id: "3", question: "Сколько времени занимает доставка?", answer: "Стандартная доставка занимает 1-3 рабочих дня..." },
  { id: "4", question: "Как отследить статус моей заявки?", answer: "Статус заявки можно отследить в разделе 'Заявки'..." },
  { id: "5", question: "Предоставляете ли вы гарантию на товары?", answer: "Да, на все товары предоставляется официальная гарантия..." }
];

const recentOrders = [
  { id: "ORD-2025-001", date: "2025-01-15", client: "ООО Техносфера", amount: "₽125,000", status: "completed" },
  { id: "ORD-2025-002", date: "2025-01-14", client: "АО Прогресс", amount: "₽87,500", status: "processing" },
  { id: "ORD-2025-003", date: "2025-01-13", client: "ИП Иванов", amount: "₽45,200", status: "pending" },
  { id: "ORD-2025-004", date: "2025-01-12", client: "ООО Инновации", amount: "₽156,800", status: "completed" }
];

// ------------------------ Navigation ------------------------
function showPage(pageId) {
  // Скрываем все страницы
  document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));

  // Показываем нужную страницу, если она существует
  const targetPage = document.getElementById(`${pageId}-page`);
  if (targetPage) {
    targetPage.classList.add('active');
  } else {
    console.warn(`Страница "${pageId}-page" не найдена`);
  }

  // Сброс навигации
  document.querySelectorAll('.nav-item').forEach(item => {
    item.classList.remove('active', 'bg-green-500', 'text-white');
    item.classList.add('text-gray-700', 'hover:bg-gray-100');
  });

  // Подсветка активной вкладки
  const activeNavItem = document.querySelector(`.nav-item[data-page="${pageId}"]`);
  if (activeNavItem) {
    activeNavItem.classList.add('active', 'bg-green-500', 'text-white');
    activeNavItem.classList.remove('text-gray-700', 'hover:bg-gray-100');
  } else {
    console.warn(`Навигационный элемент data-page="${pageId}" не найден`);
  }

  // Обновляем текущую страницу
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

// ------------------------ Catalog ------------------------
async function fetchProducts() {
  const params = new URLSearchParams();
  if (selectedCategory && selectedCategory !== 'Все категории') params.set('category', selectedCategory);
  if (searchQuery) params.set('search', searchQuery);

  const res = await fetch('/Procure_Flow/api/products.php?' + params.toString());
  const data = await res.json();
  renderProducts(data.items || []);
  document.getElementById('products-count').textContent = data.count || 0;
}

function renderCategoriesBar() {
  const bar = document.getElementById('categories-bar');
  bar.innerHTML = categories.map(cat => {
    const isActive = selectedCategory === cat;
    const classes = isActive ? 'bg-green-500 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50';
    return `<button data-cat="${cat}" class="px-4 py-2 rounded-lg ${classes}">${cat}</button>`;
  }).join('');
  bar.querySelectorAll('button').forEach(btn => {
    btn.addEventListener('click', () => {
      selectedCategory = btn.dataset.cat;
      fetchProducts();
    });
  });
}

function renderProducts(products) {
  const grid = document.getElementById('products-grid');
  const noProducts = document.getElementById('no-products');

  if (!products || products.length === 0) {
    grid.innerHTML = '';
    noProducts.classList.remove('hidden');
    return;
  }
  noProducts.classList.add('hidden');

  grid.innerHTML = products.map(product => `
    <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
      <div class="relative">
        <div class="aspect-square bg-gray-100 overflow-hidden">
          <img src="${product.image}" alt="${escapeHtml(product.name)}" class="w-full h-full object-cover">
        </div>
        ${product.discount > 0 ? `<span class="absolute top-3 right-3 bg-red-500 text-white text-xs px-2 py-1 rounded-full">–${product.discount}%</span>` : ''}
      </div>
      <div class="p-4">
        <h3 class="font-medium text-gray-900 mb-2">${escapeHtml(product.name)}</h3>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-500">${escapeHtml(product.category)}</span>
          <span class="font-semibold text-lg">${product.price} ₽</span>
        </div>
        <p class="text-sm text-gray-400 mt-2">В наличии: ${product.stock} шт</p>
        <p class="text-sm text-gray-400">Доставка: ${escapeHtml(product.delivery || '')}</p>
      </div>
    </div>
  `).join('');
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
// ------------------------ Add Product ------------------------
async function addProduct(formEl) {
  const formData = new FormData(formEl);
 const res = await fetch('/Procure_Flow/api/add_products.php', {
  method: 'POST',
  body: formData
});

  const data = await res.json();
  document.getElementById('add-result').textContent = data.message || data.error || '';
  await fetchProducts();
}

// ------------------------ Orders ------------------------
let orders = [];

async function addOrderToDB(title, description, priority, comment = '', count_goods = 0, id_creator = 0) {
  const formData = new FormData();
  formData.append('title', title);
  formData.append('description', description);
  formData.append('priority', priority);
  formData.append('comment', comment);
  formData.append('count_goods', count_goods);
  formData.append('id_creator', id_creator);

const res = await fetch('/Procure_Flow/api/add_order.php', {
  method: 'POST',
  body: formData
});


  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (data.success) {
      document.getElementById('order-modal').classList.add('hidden');

      await fetchOrders();
    } else {
      alert(data.error || 'Ошибка при создании заказа');
    }
  } catch (e) {
    alert('Ошибка: сервер вернул невалидный JSON');
    console.error('Ответ сервера:', text);
  }
}

async function fetchOrders() {
  const res = await fetch('/Procure_Flow/api/get_order.php');
  const text = await res.text();
  try {
    const data = JSON.parse(text);
    const rawItems = Array.isArray(data) ? data : (data.items || []);

    // Приводим типы к числам
    const items = rawItems.map(o => ({
      ...o,
      id: Number(o.id),
      status: Number(o.status),
      priority: Number(o.priority),
      date: Number(o.date),
      count_goods: Number(o.count_goods || 0)
    }));

    renderOrders(items);
  } catch (e) {
    console.error('Невалидный JSON:', text);
    alert('Ошибка: сервер вернул невалидный JSON');
  }
}
function formatUnixDate(unix) {
  const date = new Date(unix * 1000);
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function getStatusColor(status) {
  switch (status) {
    case 0: return 'bg-blue-100 text-blue-600';      // Новый
    case 1: return 'bg-yellow-100 text-yellow-600';   // В работе
    case 2: return 'bg-green-100 text-green-600';     // Завершён
    case 3: return 'bg-gray-100 text-gray-500';       // Отменён
    default: return 'bg-gray-100 text-gray-400';
  }
}



// ------------------------ Events ------------------------
document.addEventListener('DOMContentLoaded', () => {
  // Навигация
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
      const page = this.dataset.page; showPage(page); closeSidebar();
    });
  });
  document.getElementById('sidebar-toggle').addEventListener('click', toggleSidebar);
  document.getElementById('sidebar-close').addEventListener('click', closeSidebar);
  document.getElementById('sidebar-overlay').addEventListener('click', closeSidebar);

  // Каталог
  renderCategoriesBar();
  document.getElementById('search-input').addEventListener('input', e => {
    searchQuery = e.target.value.trim().toLowerCase();
    fetchProducts();
  });
  fetchProducts();

  // Добавление товара
  document.getElementById('addForm').addEventListener('submit', async e => {
    e.preventDefault();
    await addProduct(e.target);
    e.target.reset();
  });

  // Заказы
  fetchOrders();

  // Открытие модалки заказа
document.getElementById('create-order-btn').addEventListener('click', () => {
  openCreateOrderModal(); // вызывает функцию, которая открывает модалку и очищает поля
});


  // Закрытие модалки
 document.getElementById('close-modal-btn').addEventListener('click', closeOrderModal);
document.getElementById('cancel-order-btn').addEventListener('click', closeOrderModal);


  // Отправка заказа
 
document.getElementById('submit-order-btn').addEventListener('click', submitOrder);


  // FAQ
  renderFAQ();
  document.getElementById('faq-search').addEventListener('input', filterFAQ);
  document.addEventListener('click', e => {
    if (e.target.closest('.faq-trigger')) {
      const trigger = e.target.closest('.faq-trigger');
      const content = trigger.nextElementSibling;
      const icon = trigger.querySelector('svg');
      if (content.classList.contains('hidden')) {
        content.classList.remove('hidden'); icon.style.transform = 'rotate(180deg)';
      } else {
        content.classList.add('hidden'); icon.style.transform = 'rotate(0deg)';
      }
    }
  });

  // Отчёты
  renderRecentOrders();
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
});
// Auth modal logic
document.querySelector('#sidebar .flex.items-center.gap-3.p-3').addEventListener('click', () => {
  document.getElementById('auth-modal').classList.add('active');
});

document.getElementById('auth-close').addEventListener('click', () => {
  document.getElementById('auth-modal').classList.remove('active');
});

let isRegistering = false;
document.getElementById('auth-toggle').addEventListener('click', () => {
  isRegistering = !isRegistering;
  document.getElementById('auth-title').textContent = isRegistering ? 'Регистрация' : 'Вход';
  document.getElementById('extra-fields').classList.toggle('hidden');
  document.getElementById('auth-toggle').textContent = isRegistering ? 'Уже есть аккаунт? Войти' : 'Нет аккаунта? Зарегистрироваться';
});

document.getElementById('auth-form').addEventListener('submit', async e => {
  e.preventDefault();
  const formData = new FormData(e.target);
 const url = isRegistering ? '/Procure_Flow/api/register.php' : '/Procure_Flow/api/login.php';

  const res = await fetch(url, { method: 'POST', body: formData });
  const data = await res.json();
  document.getElementById('auth-result').textContent = data.message || data.error || '';
  if (data.success && data.user) {
    localStorage.setItem('role', data.role);
    localStorage.setItem('user', JSON.stringify(data.user));
    document.getElementById('auth-modal').classList.remove('active');
    applyRoleUI(data.role);
    document.getElementById('profile-login').textContent = data.user.login;
    document.getElementById('profile-email').textContent = data.user.email;
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const savedUser = localStorage.getItem('user');
  if (savedUser) {
    const user = JSON.parse(savedUser);
    document.getElementById('profile-login').textContent = user.login;
    document.getElementById('profile-email').textContent = user.email;
  }
  const savedRole = localStorage.getItem('role');
  if (savedRole) applyRoleUI(savedRole);
});

function applyRoleUI(role) {
  const addTab = document.getElementById('nav-add');
  const reportsTab = document.getElementById('nav-reports');
  if (role === 'worker') {
    addTab.style.display = '';
    reportsTab.style.display = '';
  } else {
    addTab.style.display = 'none';
    reportsTab.style.display = 'none';
  }
}
document.addEventListener('DOMContentLoaded', () => {
  fetchOrders(); // загрузка заказов сразу при старте
});

async function checkLowStock() {
  try {
    const res = await fetch('/Procure_Flow/api/check_stock.php');
    const data = await res.json();
    if (Array.isArray(data.items) && data.items.length > 0) {
      const messages = data.items.map(p => 
        `Товар "${escapeHtml(p.name)}" заканчивается (осталось ${p.stock} шт). Необходимо заказать новую партию.`
      );
      alert(messages.join('\n'));
    }
  } catch (e) {
    console.error('Ошибка при проверке остатков:', e);
  }
}

function formatUnixDate(timestamp) {
  const date = new Date(timestamp * 1000);
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
}

function updateOrderCounters(items) {
  const newCount       = items.filter(o => o.status === 0).length;
  const progressCount  = items.filter(o => o.status === 1).length;
  const completedCount = items.filter(o => o.status === 2).length;
  const totalCount     = items.length;

  document.getElementById('requests-new').textContent = newCount;
  document.getElementById('requests-progress').textContent = progressCount;
  document.getElementById('requests-completed').textContent = completedCount;
  document.getElementById('requests-total').textContent = totalCount;
}


function renderOrders(items) {
  const container = document.getElementById('orders-list');

  // применяем фильтры
  let filtered = items.filter(order => {
    const priorityOk = !currentPriorityFilter || order.priority == currentPriorityFilter;
    const statusOk = currentStatusFilter === '' || order.status == currentStatusFilter;
    return priorityOk && statusOk;
  });

  if (!filtered || filtered.length === 0) {
    container.innerHTML = '<p class="text-gray-500">Заявок не найдено</p>';
    updateOrderCounters(items); // считаем по всем заявкам
    return;
  }

  updateOrderCounters(items); // считаем по всем заявкам

  const priorityLabels = {1: "Низкий", 2: "Средний", 3: "Высокий"};
  const statusLabels = {0: "Новый", 1: "В работе", 2: "Завершён", 3: "Отменён"};

  container.innerHTML = filtered.map(order => {
    const formattedDate = formatUnixDate(order.date);
    const statusClass = getStatusColor(order.status);

    // отладка: выводим статус каждой заявки
    console.log(`Заявка ID ${order.id}: статус =`, order.status);

    return `
      <div class="border border-gray-200 rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow duration-200 ${statusClass}">
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-lg font-semibold text-gray-800">${escapeHtml(order.title)}</h3>
          <span class="text-xs px-2 py-1 rounded-full bg-white border ${statusClass}">
            ${statusLabels[order.status] || '—'}
          </span>
        </div>
        <p class="text-sm text-gray-600 mb-1">${escapeHtml(order.description)}</p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm text-gray-700 mt-2">
          <div>Приоритет: <span class="font-medium">${priorityLabels[order.priority] || '—'}</span></div>
          <div>Комментарий: <span class="font-medium">${escapeHtml(order.comment || '—')}</span></div>
          <div>Количество: <span class="font-medium">${order.count_goods || '—'} шт</span></div>
        </div>
        <p class="text-xs text-gray-500 mt-3">Создан: ${formattedDate} | ID: ${order.id}</p>
        <div class="mt-3 flex gap-3">
          ${Number(order.status) === 0 
            ? `<button onclick="startOrder(${order.id})" class="text-sm text-blue-600 hover:underline">В работу</button>` 
            : ''}
          ${Number(order.status) === 1 
            ? `<button onclick="markOrderCompleted(${order.id})" class="text-sm text-green-600 hover:underline">Завершить</button>` 
            : ''}
          ${Number(order.status) === 1 
            ? `<button onclick="cancelOrder(${order.id})" class="text-sm text-orange-600 hover:underline">Отменить</button>` 
            : ''}
          <button onclick='openEditOrderModal(${JSON.stringify(order)})' class="text-sm text-indigo-600 hover:underline">Редактировать</button>
          <button onclick="deleteOrder(${order.id})" class="text-sm text-red-600 hover:underline">Удалить</button>
        </div>
      </div>
    `;
  }).join('');
}






async function fetchOrders() {
  const res = await fetch('/Procure_Flow/api/get_order.php');
  const text = await res.text();
  try {
    const data = JSON.parse(text);
    const rawItems = Array.isArray(data) ? data : (data.items || []);

    // Нормализуем типы, чтобы фильтры и цвета работали корректно
    const items = rawItems.map(o => ({
      ...o,
      id: Number(o.id),
      status: Number(o.status),
      priority: Number(o.priority),
      date: Number(o.date),
      count_goods: Number(o.count_goods ?? 0)
    }));

    renderOrders(items);
  } catch (e) {
    console.error('Невалидный JSON:', text);
    alert('Ошибка: сервер вернул невалидный JSON');
  }
}


async function markOrderCompleted(id) {
const res = await fetch(`/Procure_Flow/api/update_order_status.php?id=${id}&status=2`);

  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (data.success) fetchOrders();
    else alert(data.error || 'Ошибка при обновлении');
  } catch (e) {
    alert('Ошибка: сервер вернул невалидный JSON');
  }
}

async function deleteOrder(id) {
  if (!confirm('Удалить заявку?')) return;
const res = await fetch(`/Procure_Flow/api/delete_order.php?id=${id}`, { method: 'DELETE' });

  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (data.success) fetchOrders();
    else alert(data.error || 'Ошибка при удалении');
  } catch (e) {
    alert('Ошибка: сервер вернул невалидный JSON');
  }
}
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
async function startOrder(id) {
  const res = await fetch(`/Procure_Flow/api/update_order_status.php?id=${id}&status=1`);
  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (data.success) {
      fetchOrders();
    } else {
      alert(data.error || 'Ошибка при переводе заявки в работу');
    }
  } catch (e) {
    alert('Ошибка: сервер вернул невалидный JSON');
  }
}

async function cancelOrder(id) {
  const res = await fetch(`/Procure_Flow/api/update_order_status.php?id=${id}&status=3`);
  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (data.success) {
      fetchOrders();
    } else {
      alert(data.error || 'Ошибка при отмене заявки');
    }
  } catch (e) {
    alert('Ошибка: сервер вернул невалидный JSON');
  }
}
let currentEditOrderId = null;

function openEditModal(id) {
  const order = orders.find(o => o.id === id);
  if (!order) return;

  currentEditOrderId = id;
  document.getElementById('edit-order-title').value = order.title;
  document.getElementById('edit-order-description').value = order.description;
  document.getElementById('edit-order-priority').value = order.priority;
  document.getElementById('edit-order-comment').value = order.comment || '';
  document.getElementById('edit-order-count').value = order.count_goods || 0;

  document.getElementById('edit-order-modal').classList.remove('hidden');
}

function closeOrderModal() {
  document.getElementById('order-modal').classList.add('hidden');
}


async function submitEditOrder() {
  const title = document.getElementById('edit-order-title').value;
  const description = document.getElementById('edit-order-description').value;
  const priority = document.getElementById('edit-order-priority').value;
  const comment = document.getElementById('edit-order-comment').value;
  const count = document.getElementById('edit-order-count').value;

  const formData = new FormData();
  formData.append('id', currentEditOrderId);
  formData.append('title', title);
  formData.append('description', description);
  formData.append('priority', priority);
  formData.append('comment', comment);
  formData.append('count_goods', count);

  const res = await fetch('/Procure_Flow/api/update_order.php', {
    method: 'POST',
    body: formData
  });

  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (data.success) {
      closeEditModal();
      fetchOrders();
    } else {
      alert(data.error || 'Ошибка при обновлении');
    }
  } catch (e) {
    alert('Ошибка: сервер вернул невалидный JSON');
    console.error('Ответ сервера:', text);
  }
}

function openCreateOrderModal() {
  document.getElementById('order-modal-title').textContent = 'Новый заказ';
  document.getElementById('order-id').value = '';
  document.getElementById('order-title').value = '';
  document.getElementById('order-description').value = '';
  document.getElementById('order-priority').value = '2';
  document.getElementById('order-comment').value = '';
  document.getElementById('order-count').value = '';
  document.getElementById('order-modal').classList.remove('hidden');
}


function openEditOrderModal(order) {
  document.getElementById('order-modal-title').textContent = 'Редактировать заказ';
  document.getElementById('order-id').value = order.id;
  document.getElementById('order-title').value = order.title;
  document.getElementById('order-description').value = order.description;
  document.getElementById('order-priority').value = order.priority;
  document.getElementById('order-comment').value = order.comment || '';
  document.getElementById('order-count').value = order.count_goods || 0;
  document.getElementById('order-modal').classList.remove('hidden');
}

function closeOrderModal() {
  document.getElementById('order-modal').classList.add('hidden');
}

async function submitOrder() {
  const id = document.getElementById('order-id').value;
  const title = document.getElementById('order-title').value;
  const description = document.getElementById('order-description').value;
  const priority = document.getElementById('order-priority').value;
  const comment = document.getElementById('order-comment').value;
  const count = document.getElementById('order-count').value;

  if (!title || !description || !count) {
    alert('Заполните все поля');
    return;
  }

  const formData = new FormData();
  formData.append('title', title);
  formData.append('description', description);
  formData.append('priority', priority);
  formData.append('comment', comment);
  formData.append('count_goods', count);

  let url = '/Procure_Flow/api/add_order.php';
  if (id) {
    formData.append('id', id);
    url = '/Procure_Flow/api/update_order.php';
  }

  const res = await fetch(url, { method: 'POST', body: formData });
  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (data.success) {
      closeOrderModal();
      fetchOrders();
    } else {
      alert(data.error || 'Ошибка при сохранении');
    }
  } catch (e) {
    alert('Ошибка: сервер вернул невалидный JSON');
    console.error('Ответ сервера:', text);
  }
}

// Привязка кнопок
document.getElementById('submit-order-btn').addEventListener('click', submitOrder);
document.getElementById('cancel-order-btn').addEventListener('click', closeOrderModal);
document.getElementById('close-modal-btn').addEventListener('click', closeOrderModal);











document.addEventListener('DOMContentLoaded', () => {
  checkLowStock();
  fetchOrders(); // загрузка заявок при старте
const addForm = document.getElementById('addForm');
if (addForm) {
  addForm.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('/Procure_Flow/api/add_products.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    document.getElementById('add-result').textContent = data.message || data.error || '';
    if (data.success) e.target.reset();
  });
}

  // Фильтр по приоритету
  document.getElementById('filter-priority').addEventListener('change', e => {
    currentPriorityFilter = e.target.value;
    fetchOrders();
  });

  // Фильтры по статусу
  document.getElementById('filter-status-new').addEventListener('click', () => {
    currentStatusFilter = 0; fetchOrders();
  });
  document.getElementById('filter-status-progress').addEventListener('click', () => {
    currentStatusFilter = 1; fetchOrders();
  });
  document.getElementById('filter-status-completed').addEventListener('click', () => {
    currentStatusFilter = 2; fetchOrders();
  });
  document.getElementById('filter-status-cancelled').addEventListener('click', () => {
    currentStatusFilter = 3; fetchOrders();
  });
  document.getElementById('filter-status-all').addEventListener('click', () => {
    currentStatusFilter = ''; fetchOrders();
  });
});


