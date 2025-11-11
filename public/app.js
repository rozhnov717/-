// ------------------------ State ------------------------
let currentPage = 'catalog';
let selectedCategory = 'Все категории';
let searchQuery = '';

const categories = ['Все категории', 'Электроника', 'Офисная техника', 'Инструменты', 'Материалы'];

let requests = [
  { id: "1", title: "Поставка ноутбуков Dell", description: "Необходимо поставить 15 ноутбуков Dell Latitude для нового офиса", status: "in_progress", date: "2025-01-15", priority: "high" },
  { id: "2", title: "Ремонт принтера HP", description: "Принтер HP LaserJet не печатает, требуется диагностика", status: "new", date: "2025-01-10", priority: "medium" },
  { id: "3", title: "Установка ПО на рабочие места", description: "Установить Microsoft Office на 10 рабочих мест", status: "completed", date: "2025-01-05", priority: "low" }
];

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
  document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
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

function escapeHtml(str) {
  return String(str || '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}

// ------------------------ Add Product ------------------------
async function addProduct(formEl) {
  const formData = new FormData(formEl);
  const res = await fetch('../api/add_products.php', { method: 'POST', body: formData });
  const data = await res.json();
  document.getElementById('add-result').textContent = data.message || data.error || '';
  await fetchProducts();
}

// ------------------------ Requests ------------------------
function renderRequests() {
  const container = document.getElementById('requests-list');
  const statusColors = { new: "bg-blue-500", in_progress: "bg-yellow-500", completed: "bg-green-500", cancelled: "bg-red-500" };
  const statusLabels = { new: "Новая", in_progress: "В работе", completed: "Завершена", cancelled: "Отменена" };

  container.innerHTML = requests.map(request => `
    <div class="bg-white border border-gray-200 rounded-lg border-l-4 ${request.priority === 'high' ? 'border-l-red-500' : request.priority === 'medium' ? 'border-l-yellow-500' : 'border-l-green-500'}">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">${escapeHtml(request.title)}</h3>
          <div class="flex items-center gap-2">
            <span class="${statusColors[request.status]} text-white text-xs px-2 py-1 rounded-full">${statusLabels[request.status]}</span>
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
        <p class="text-gray-500 mb-4">${escapeHtml(request.description)}</p>
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

  // Counters
  document.getElementById('requests-total').textContent = requests.length;
  document.getElementById('requests-new').textContent = requests.filter(r => r.status === 'new').length;
  document.getElementById('requests-progress').textContent = requests.filter(r => r.status === 'in_progress').length;
  document.getElementById('requests-completed').textContent = requests.filter(r => r.status === 'completed').length;
}

function addRequest(title, description, priority) {
  const newRequest = {
    id: Date.now().toString(),
    title, description,
    status: "new",
    date: new Date().toISOString().split('T')[0],
    priority
  };
  requests.unshift(newRequest);
  renderRequests();
}

// ------------------------ FAQ ------------------------
function renderFAQ(faqToRender = faqData) {
  const container = document.getElementById('faq-list');
  container.innerHTML = faqToRender.map(item => `
    <div class="border border-gray-200 rounded-lg">
      <button class="faq-trigger w-full text-left p-4 hover:bg-gray-50 flex items-center justify-between" data-faq="${item.id}">
        <span class="font-medium">${escapeHtml(item.question)}</span>
        <svg class="h-4 w-4 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      <div class="faq-content hidden px-4 pb-4 text-gray-500">
        ${escapeHtml(item.answer)}
      </div>
    </div>
  `).join('');
}

function filterFAQ() {
  const q = document.getElementById('faq-search').value.toLowerCase();
  if (!q) return renderFAQ();
  const filtered = faqData.filter(item =>
    item.question.toLowerCase().includes(q) || item.answer.toLowerCase().includes(q)
  );
  renderFAQ(filtered);
}

// ------------------------ Reports ------------------------
function renderRecentOrders() {
  const container = document.getElementById('recent-orders');
  const statusColors = { completed: "bg-green-500", processing: "bg-yellow-500", pending: "bg-blue-500", cancelled: "bg-red-500" };
  const statusLabels = { completed: "Выполнен", processing: "В обработке", pending: "Ожидает", cancelled: "Отменен" };

  container.innerHTML = recentOrders.map(order => `
    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
      <div>
        <div class="font-medium">${order.id}</div>
        <div class="text-sm text-gray-500">${order.client}</div>
      </div>
      <div class="text-right">
        <div class="font-medium">${order.amount}</div>
        <div class="flex items-center gap-2">
          <span class="${statusColors[order.status]} text-white text-xs px-2 py-1 rounded-full">${statusLabels[order.status]}</span>
          <span class="text-xs text-gray-500">${order.date}</span>
        </div>
      </div>
    </div>
  `).join('');
}

// ------------------------ Charts (simple canvas) ------------------------
function createSimpleChart(canvasId, data, type = 'line') {
  const canvas = document.getElementById(canvasId);
  const ctx = canvas.getContext('2d');
  const width = canvas.width, height = canvas.height;

  ctx.clearRect(0, 0, width, height);
  ctx.strokeStyle = '#22c55e'; ctx.fillStyle = '#22c55e'; ctx.lineWidth = 2;

  const padding = 40;
  const chartWidth = width - padding * 2;
  const chartHeight = height - padding * 2;
  const maxValue = Math.max(...data.map(d => d.value));
  const stepX = chartWidth / (data.length - 1);

  // Axes
  ctx.strokeStyle = '#e5e7eb'; ctx.lineWidth = 1;
  ctx.beginPath(); ctx.moveTo(padding, padding); ctx.lineTo(padding, height - padding); ctx.stroke();
  ctx.beginPath(); ctx.moveTo(padding, height - padding); ctx.lineTo(width - padding, height - padding); ctx.stroke();

  // Data
  ctx.strokeStyle = '#22c55e'; ctx.fillStyle = '#22c55e'; ctx.lineWidth = 2;
  if (type === 'line') {
    ctx.beginPath();
    data.forEach((point, i) => {
      const x = padding + i * stepX;
      const y = height - padding - (point.value / maxValue) * chartHeight;
      if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    });
    ctx.stroke();
    data.forEach((point, i) => {
      const x = padding + i * stepX;
      const y = height - padding - (point.value / maxValue) * chartHeight;
      ctx.beginPath(); ctx.arc(x, y, 4, 0, Math.PI * 2); ctx.fill();
    });
  } else {
    const barWidth = stepX * 0.6;
    data.forEach((point, i) => {
      const x = padding + i * stepX - barWidth / 2;
      const barHeight = (point.value / maxValue) * chartHeight;
      const y = height - padding - barHeight;
      ctx.fillRect(x, y, barWidth, barHeight);
    });
  }

  ctx.fillStyle = '#6b7280'; ctx.font = '12px sans-serif'; ctx.textAlign = 'center';
  data.forEach((point, i) => {
    const x = padding + i * stepX;
    ctx.fillText(point.label, x, height - 10);
  });
}

// ------------------------ Events ------------------------
document.addEventListener('DOMContentLoaded', () => {
  // Nav
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
      const page = this.dataset.page; showPage(page); closeSidebar();
    });
  });
  document.getElementById('sidebar-toggle').addEventListener('click', toggleSidebar);
  document.getElementById('sidebar-close').addEventListener('click', closeSidebar);
  document.getElementById('sidebar-overlay').addEventListener('click', closeSidebar);

  // Catalog
  renderCategoriesBar();
  document.getElementById('search-input').addEventListener('input', e => {
    searchQuery = e.target.value.trim().toLowerCase();
    fetchProducts();
  });
  fetchProducts();

  // Add product
  document.getElementById('addForm').addEventListener('submit', async e => {
    e.preventDefault();
    await addProduct(e.target);
    e.target.reset();
  });

  // Requests
  renderRequests();
  document.getElementById('create-request-btn').addEventListener('click', () => {
    document.getElementById('create-request-modal').classList.add('active');
  });
  document.getElementById('close-modal-btn').addEventListener('click', () => {
    document.getElementById('create-request-modal').classList.remove('active');
  });
  document.getElementById('cancel-request-btn').addEventListener('click', () => {
    document.getElementById('create-request-modal').classList.remove('active');
  });
  document.getElementById('submit-request-btn').addEventListener('click', () => {
    const title = document.getElementById('request-title').value;
    const description = document.getElementById('request-description').value;
    const priority = document.getElementById('request-priority').value;
    if (title && description) {
      addRequest(title, description, priority);
      document.getElementById('request-title').value = '';
      document.getElementById('request-description').value = '';
      document.getElementById('request-priority').value = 'medium';
      document.getElementById('create-request-modal').classList.remove('active');
    }
  });

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

  // Reports
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
  const url = isRegistering ? '../api/register.php' : '../api/login.php';

  const res = await fetch(url, { method: 'POST', body: formData });
  const data = await res.json();
  document.getElementById('auth-result').textContent = data.message || data.error || '';

  if (data.success) {
    document.getElementById('auth-modal').classList.remove('active');
    // Можно сохранить user info в localStorage или показать приветствие
  }
});
