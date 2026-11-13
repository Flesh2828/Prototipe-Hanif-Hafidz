// ==========================================
// STATE & CONFIG (KopiKita Design System)
// ==========================================
let currentUser = (typeof currentPhpUser !== 'undefined' && currentPhpUser) ? currentPhpUser : {
  id: 1,
  username: 'owner',
  full_name: 'Hafidz Taufiqurrahman (Owner)',
  role: 'owner',
  outlet_id: 1,
  outlet_name: 'KopiKita Kaliurang (Pusat)'
};

let allProducts = [];
let allIngredients = (typeof initialIngredientsData !== 'undefined' && initialIngredientsData) ? initialIngredientsData : [];
let allCategories = (typeof initialCategoriesData !== 'undefined' && initialCategoriesData) ? initialCategoriesData : [];
let cart = []; // { product, quantity }
let lastCheckoutResult = null;

const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
};

const escapeHtml = (str) => {
  if (!str) return '';
  return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
};

// ==========================================
// NATIVE MODAL SYSTEM (No Bootstrap JS Required)
// ==========================================
window.openModal = function(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
};

window.closeModal = function(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.remove('show');
    document.body.style.overflow = '';
  }
};

// Polyfill window.bootstrap.Modal for backwards compatibility
window.bootstrap = window.bootstrap || {};
window.bootstrap.Modal = class {
  constructor(el) {
    this.el = typeof el === 'string' ? document.getElementById(el) : el;
  }
  show() {
    if (this.el) {
      this.el.classList.add('show');
      document.body.style.overflow = 'hidden';
    }
  }
  hide() {
    if (this.el) {
      this.el.classList.remove('show');
      document.body.style.overflow = '';
    }
  }
  static getInstance(el) {
    const element = typeof el === 'string' ? document.getElementById(el) : el;
    return new window.bootstrap.Modal(element);
  }
  static getOrCreateInstance(el) {
    const element = typeof el === 'string' ? document.getElementById(el) : el;
    return new window.bootstrap.Modal(element);
  }
};

/**
 * Helper untuk menentukan URL API PHP backend yang dinamis & kompatibel di XAMPP
 */
function apiUrl(path) {
  let basePath = window.location.pathname;
  if (basePath.includes('/public/')) {
    basePath = basePath.substring(0, basePath.indexOf('/public/')) + '/';
  } else if (basePath.endsWith('.php') || basePath.endsWith('.html')) {
    basePath = basePath.substring(0, basePath.lastIndexOf('/') + 1);
  } else if (!basePath.endsWith('/')) {
    basePath = basePath + '/';
  }

  let clean = path.startsWith('/') ? path.substring(1) : path;

  // 1. Recipe Detail: api/products/:id/recipe -> api/recipes.php?id=:id
  const recipeMatch = clean.match(/^api\/products\/(\d+)\/recipe$/);
  if (recipeMatch) {
    return `${basePath}api/recipes.php?id=${recipeMatch[1]}`;
  }

  // 2. POS Checkout: api/pos/checkout -> api/pos.php
  if (clean === 'api/pos/checkout') {
    return `${basePath}api/pos.php`;
  }

  // 3. Inventory Critical Alerts: api/inventory/critical-alerts
  if (clean.startsWith('api/inventory/critical-alerts')) {
    const query = clean.includes('?') ? '&' + clean.split('?')[1] : '';
    return `${basePath}api/inventory.php?action=critical-alerts${query}`;
  }

  // 4. Inventory Actions
  if (clean.startsWith('api/inventory?action=')) {
    return `${basePath}${clean.replace('api/inventory?', 'api/inventory.php?')}`;
  }
  if (clean === 'api/inventory/restock') {
    return `${basePath}api/inventory.php?action=restock`;
  }

  // 5. Inventory: api/inventory -> api/inventory.php
  if (clean.startsWith('api/inventory')) {
    const query = clean.includes('?') ? '?' + clean.split('?')[1] : '';
    return `${basePath}api/inventory.php${query}`;
  }

  // 6. Products Actions: api/products?action=... -> api/products.php?action=...
  if (clean.startsWith('api/products?action=')) {
    return `${basePath}${clean.replace('api/products?', 'api/products.php?')}`;
  }
  if (clean.startsWith('api/products')) {
    const query = clean.includes('?') ? '?' + clean.split('?')[1] : '';
    return `${basePath}api/products.php${query}`;
  }

  // 7. Dashboard: api/dashboard/stats -> api/dashboard.php
  if (clean.startsWith('api/dashboard/stats')) {
    const query = clean.includes('?') ? '?' + clean.split('?')[1] : '';
    return `${basePath}api/dashboard.php${query}`;
  }

  // 8. Auth: api/auth/login -> api/auth.php?action=login
  if (clean === 'api/auth/login') {
    return `${basePath}api/auth.php?action=login`;
  }

  return `${basePath}${clean}`;
}

// ==========================================
// INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  if (typeof currentPhpUser !== 'undefined' && currentPhpUser) {
    currentUser = currentPhpUser;
  }
  updateUserDisplay();
  loadCategories();
  loadProducts();
  loadDashboardStats();
  loadInventory();
  loadCriticalAlerts();
});

// Load Categories
async function loadCategories() {
  try {
    const res = await fetch(apiUrl('api/categories.php'));
    if (res.ok) {
      allCategories = await res.json();
    }
  } catch (e) {
    console.warn('Gagal memuat kategori:', e);
  }
}

// Switch Tab (untuk Single Page mode di public/index.html)
function showTab(tabId) {
  const panes = document.querySelectorAll('.tab-pane-content');
  if (panes.length > 0) {
    panes.forEach(el => el.style.display = 'none');
    const target = document.getElementById(tabId);
    if (target) target.style.display = 'block';

    document.querySelectorAll('#navTabs .nav-link').forEach(btn => btn.classList.remove('active'));
    const activeBtn = Array.from(document.querySelectorAll('#navTabs .nav-link')).find(btn => btn.getAttribute('onclick')?.includes(tabId));
    if (activeBtn) activeBtn.classList.add('active');
  }

  if (tabId === 'dashboardTab') loadDashboardStats();
  if (tabId === 'inventoryTab') loadInventory();
  if (tabId === 'recipeTab') loadProducts();
  if (tabId === 'alertsTab') loadCriticalAlerts();
}

// User RBAC Switcher Simulation (F01)
async function switchUserRole(role, name, outletId, outletName) {
  currentUser = {
    id: role === 'owner' ? 1 : (role === 'manager' ? 2 : (role === 'cashier' ? 3 : (role === 'barista' ? 4 : (role === 'warehouse' ? 5 : 6)))),
    username: role,
    full_name: name,
    role: role,
    outlet_id: outletId || 1,
    outlet_name: outletName || 'KopiKita Kaliurang (Pusat)'
  };

  try {
    await fetch(apiUrl(`api/auth.php?action=switch&role=${role}`));
  } catch (e) {
    console.warn('Switch session synced locally:', e);
  }

  updateUserDisplay();
  closeModal('userSwitchModal');

  loadDashboardStats();
  loadInventory();
  loadCriticalAlerts();
  loadProducts();

  alert(`Peran berhasil dialihkan ke: ${name} (${role.toUpperCase()})\nOutlet Aktif: ${currentUser.outlet_name}`);
}

function updateUserDisplay() {
  const roleBadge = document.getElementById('currentRoleBadge');
  if (roleBadge) roleBadge.textContent = `Role: ${currentUser.role.toUpperCase()}`;

  const outletDisp = document.getElementById('currentOutletDisplay');
  if (outletDisp) outletDisp.textContent = currentUser.outlet_name;

  // RBAC permissions check
  const btnRestock = document.querySelector('[data-bs-target="#restockModal"], [data-modal-target="restockModal"]');
  if (btnRestock) {
    if (['cashier', 'barista', 'accountant'].includes(currentUser.role)) {
      btnRestock.style.display = 'none';
    } else {
      btnRestock.style.display = 'inline-block';
    }
  }

  const btnCheckout = document.getElementById('btnCheckout');
  if (btnCheckout) {
    if (['accountant', 'barista'].includes(currentUser.role)) {
      btnCheckout.disabled = true;
      btnCheckout.title = 'Peran Anda tidak diizinkan melakukan transaksi checkout POS';
    } else {
      btnCheckout.disabled = false;
      btnCheckout.title = '';
    }
  }
}

// ==========================================
// F02: POS KASIR & MENU SELECTION
// ==========================================
async function loadProducts() {
  try {
    const res = await fetch(apiUrl('api/products.php'));
    allProducts = await res.json();
    renderMenuGrid(allProducts);
    renderRecipeTable(allProducts);
  } catch (err) {
    console.error('Gagal mengambil data produk:', err);
  }
}

function renderMenuGrid(products) {
  const grid = document.getElementById('menuGrid');
  if (!grid) return;
  grid.innerHTML = '';

  products.forEach(p => {
    const card = document.createElement('article');
    card.className = 'menu-card';
    card.onclick = () => addToCart(p.id);

    const iconMap = {
      'Coffee Espresso Based': '☕',
      'Manual Brew': '🧪',
      'Non-Coffee & Mocktails': '🍹',
      'Pastry & Snacks': '🥐'
    };
    const icon = iconMap[p.category_name] || '☕';

    card.innerHTML = `
      <div class="menu-card__visual">
        <span class="category-badge-pill">${p.category_name || 'Menu'}</span>
        <span class="icon">${icon}</span>
        <span class="sku-code">${p.code}</span>
      </div>
      <div class="menu-card__body">
        <div>
          <h3 class="menu-card__title">${p.name}</h3>
          <p class="menu-card__desc">${p.description || ''}</p>
        </div>
        <div class="menu-card__footer">
          <div class="menu-card__price-row">
            <span class="menu-card__price">${formatRupiah(p.price)}</span>
            <span class="menu-card__margin-badge" title="Estimasi Margin Laba Kotor">+${p.margin_pct}%</span>
          </div>
          <button class="btn-add-item" onclick="event.stopPropagation(); addToCart(${p.id})">
            + Tambah
          </button>
        </div>
      </div>
    `;
    grid.appendChild(card);
  });
}

function filterMenu() {
  const searchEl = document.getElementById('searchMenuInput');
  const catEl = document.getElementById('categoryFilter');
  if (!searchEl) return;

  const query = searchEl.value.toLowerCase();
  const category = catEl ? catEl.value : 'all';

  const filtered = allProducts.filter(p => {
    const matchQuery = p.name.toLowerCase().includes(query) || p.code.toLowerCase().includes(query);
    const matchCat = (category === 'all' || p.category_name === category);
    return matchQuery && matchCat;
  });

  renderMenuGrid(filtered);
}

// Cart operations
function addToCart(productId) {
  const product = allProducts.find(p => p.id === productId);
  if (!product) return;

  const existing = cart.find(item => item.product.id === productId);
  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({ product, quantity: 1 });
  }

  renderCart();
}

function updateCartQty(productId, delta) {
  const item = cart.find(i => i.product.id === productId);
  if (!item) return;

  item.quantity += delta;
  if (item.quantity <= 0) {
    cart = cart.filter(i => i.product.id !== productId);
  }
  renderCart();
}

function clearCart() {
  cart = [];
  renderCart();
}

function renderCart() {
  const listEl = document.getElementById('cartItemsList');
  if (!listEl) return;

  if (cart.length === 0) {
    listEl.innerHTML = `
      <div class="cart-empty-state">
        <span class="icon">🛒</span>
        <p>Keranjang masih kosong</p>
        <small style="font-size:11px; color:var(--color-text-muted);">Pilih menu dari katalog di sebelah kiri</small>
      </div>`;
    const totalEl = document.getElementById('cartTotalAmount');
    if (totalEl) totalEl.textContent = 'Rp 0';
    const hppEl = document.getElementById('cartTotalHpp');
    if (hppEl) hppEl.textContent = 'Rp 0';
    const marginEl = document.getElementById('cartEstimatedMargin');
    if (marginEl) marginEl.textContent = 'Rp 0 (0%)';
    calculateChange();
    return;
  }

  let totalAmount = 0;
  let totalHpp = 0;

  listEl.innerHTML = '';
  cart.forEach(item => {
    const subtotal = item.product.price * item.quantity;
    const subtotalHpp = item.product.dynamic_hpp * item.quantity;
    totalAmount += subtotal;
    totalHpp += subtotalHpp;

    const row = document.createElement('div');
    row.className = 'cart-item-row';
    row.innerHTML = `
      <div class="cart-item-info">
        <div class="title">${item.product.name}</div>
        <div class="sub">${formatRupiah(item.product.price)} &bull; HPP: ${formatRupiah(item.product.dynamic_hpp)}</div>
      </div>
      <div class="cart-item-stepper">
        <button class="stepper-btn" onclick="updateCartQty(${item.product.id}, -1)">&minus;</button>
        <span class="stepper-qty">${item.quantity}</span>
        <button class="stepper-btn" onclick="updateCartQty(${item.product.id}, 1)">&#43;</button>
      </div>
      <div class="cart-item-subtotal">
        ${formatRupiah(subtotal)}
      </div>
    `;
    listEl.appendChild(row);
  });

  const grossProfit = totalAmount - totalHpp;
  const marginPct = totalAmount > 0 ? ((grossProfit / totalAmount) * 100).toFixed(1) : 0;

  const totalEl = document.getElementById('cartTotalAmount');
  if (totalEl) totalEl.textContent = formatRupiah(totalAmount);
  const hppEl = document.getElementById('cartTotalHpp');
  if (hppEl) hppEl.textContent = formatRupiah(totalHpp);
  const marginEl = document.getElementById('cartEstimatedMargin');
  if (marginEl) marginEl.textContent = `${formatRupiah(grossProfit)} (${marginPct}%)`;

  calculateChange();
}

function calculateChange() {
  const totalAmountEl = document.getElementById('cartTotalAmount');
  if (!totalAmountEl) return;
  const totalStr = totalAmountEl.textContent.replace(/[^\d]/g, '');
  const totalAmount = parseInt(totalStr, 10) || 0;
  const cashGivenInput = document.getElementById('cashGivenInput');
  const cashGiven = cashGivenInput ? (parseFloat(cashGivenInput.value) || 0) : 0;
  const change = Math.max(0, cashGiven - totalAmount);
  const changeDueText = document.getElementById('changeDueText');
  if (changeDueText) changeDueText.textContent = formatRupiah(change);
}

// Checkout POS process (F02, F03, F04)
async function processCheckout() {
  if (cart.length === 0) {
    alert('Keranjang belanja masih kosong!');
    return;
  }

  const paymentMethodEl = document.querySelector('input[name="paymentMethod"]:checked');
  const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'cash';
  const customerNameEl = document.getElementById('custNameInput');
  const customerName = (customerNameEl && customerNameEl.value) ? customerNameEl.value : 'Pelanggan Walk-in';
  const cashGivenInput = document.getElementById('cashGivenInput');
  const cashGiven = cashGivenInput ? (parseFloat(cashGivenInput.value) || 0) : 0;

  const payload = {
    outlet_id: currentUser.outlet_id || 1,
    cashier_id: currentUser.id || 1,
    customer_name: customerName,
    payment_method: paymentMethod,
    cash_given: cashGiven,
    items: cart.map(i => ({ product_id: i.product.id, quantity: i.quantity }))
  };

  const btn = document.getElementById('btnCheckout');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `⏳ Menyimpan ke MySQL...`;
  }

  try {
    const res = await fetch(apiUrl('api/pos/checkout'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (!res.ok) {
      alert(`Gagal transaksi: ${data.error}`);
      return;
    }

    lastCheckoutResult = data;
    showReceiptModal(data);

    // Refresh state
    cart = [];
    renderCart();
    loadDashboardStats();
    loadInventory();
    loadCriticalAlerts();

    if (data.critical_alerts && data.critical_alerts.length > 0) {
      console.warn('⚠️ Alert Stok Kritis Terpicu:', data.critical_alerts);
    }
  } catch (err) {
    alert(`Terjadi kesalahan server: ${err.message}`);
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `✅ Simpan Transaksi & Potong Stok`;
    }
  }
}

// Show ESC/POS Receipt Modal
function showReceiptModal(data) {
  const outNameEl = document.getElementById('receiptOutletName');
  if (outNameEl) outNameEl.textContent = currentUser.outlet_name;

  const invEl = document.getElementById('receiptInvoiceNo');
  if (invEl) invEl.textContent = data.invoice_no;

  const dateEl = document.getElementById('receiptDate');
  if (dateEl) dateEl.textContent = new Date().toLocaleString('id-ID');

  const cashierEl = document.getElementById('receiptCashier');
  if (cashierEl) cashierEl.textContent = currentUser.full_name;

  const custEl = document.getElementById('receiptCustomer');
  const custInput = document.getElementById('custNameInput');
  if (custEl && custInput) custEl.textContent = custInput.value;

  const table = document.getElementById('receiptItemsTable');
  if (table) {
    table.innerHTML = '';
    data.items.forEach(it => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="padding:2px 0;">${it.name} <br> <small style="color:#666;">${it.quantity} x ${formatRupiah(it.unit_price)}</small></td>
        <td style="text-align:right; font-weight:700; vertical-align:middle; padding:2px 0;">${formatRupiah(it.subtotal)}</td>
      `;
      table.appendChild(tr);
    });
  }

  const totEl = document.getElementById('receiptTotalAmount');
  if (totEl) totEl.textContent = formatRupiah(data.total_amount);

  const methEl = document.getElementById('receiptPaymentMethod');
  if (methEl) methEl.textContent = data.payment_method === 'cash' ? 'Cash' : 'QRIS Sandbox';

  const cashGivenInput = document.getElementById('cashGivenInput');
  const cashGivenVal = cashGivenInput ? parseFloat(cashGivenInput.value) : data.total_amount;
  const cashGivenEl = document.getElementById('receiptCashGiven');
  if (cashGivenEl) cashGivenEl.textContent = formatRupiah(data.payment_method === 'cash' ? (cashGivenVal || data.total_amount) : data.total_amount);

  const changeEl = document.getElementById('receiptChangeDue');
  if (changeEl) changeEl.textContent = formatRupiah(data.change_due);

  const hppEl = document.getElementById('receiptTotalHpp');
  if (hppEl) hppEl.textContent = formatRupiah(data.total_hpp);

  const profitEl = document.getElementById('receiptGrossProfit');
  if (profitEl) profitEl.textContent = formatRupiah(data.gross_profit);

  openModal('receiptModal');
}

function printReceipt() {
  window.print();
}

// ==========================================
// F06: DASHBOARD LABA-RUGI & OMZET
// ==========================================
async function loadDashboardStats() {
  const filterEl = document.getElementById('dashboardOutletFilter');
  const outletFilter = filterEl ? filterEl.value : 'all';

  try {
    const res = await fetch(apiUrl(`api/dashboard/stats?outlet_id=${outletFilter}`));
    const data = await res.json();

    const omzetEl = document.getElementById('dashOmzet');
    if (omzetEl) omzetEl.textContent = formatRupiah(data.summary.total_omzet);

    const hppEl = document.getElementById('dashHpp');
    if (hppEl) hppEl.textContent = formatRupiah(data.summary.total_hpp);

    const profitEl = document.getElementById('dashProfit');
    if (profitEl) profitEl.textContent = formatRupiah(data.summary.gross_profit);

    const marginEl = document.getElementById('dashMarginPct');
    if (marginEl) marginEl.textContent = `Margin Kotor: ${data.summary.margin_pct}%`;

    const txEl = document.getElementById('dashTransactions');
    if (txEl) txEl.textContent = `${data.summary.total_transactions} Order`;

    const outTbody = document.getElementById('outletPerformanceTableBody');
    if (outTbody && data.outlet_performance) {
      outTbody.innerHTML = '';
      data.outlet_performance.forEach(op => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${op.outlet_name}</td>
          <td>${op.transactions_count}</td>
          <td style="font-weight:700; color:var(--color-primary);">${formatRupiah(op.total_omzet)}</td>
          <td style="color:var(--color-danger);">${formatRupiah(op.total_hpp)}</td>
          <td style="font-weight:700; color:var(--color-success);">${formatRupiah(op.gross_profit)}</td>
        `;
        outTbody.appendChild(tr);
      });
    }

    const recTbody = document.getElementById('recentOrdersTableBody');
    if (recTbody && data.recent_orders) {
      recTbody.innerHTML = '';
      if (data.recent_orders.length === 0) {
        recTbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--color-text-muted);">Belum ada transaksi</td></tr>`;
      } else {
        data.recent_orders.forEach(ro => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><code>${ro.invoice_no}</code></td>
            <td>${ro.outlet_name}</td>
            <td><span class="status-pill">${ro.payment_method}</span></td>
            <td style="font-weight:700;">${formatRupiah(ro.total_amount)}</td>
            <td style="color:var(--color-success); font-weight:600;">+${formatRupiah(ro.gross_profit)}</td>
          `;
          recTbody.appendChild(tr);
        });
      }
    }
  } catch (err) {
    console.error('Gagal mengambil data dashboard:', err);
  }
}

// ==========================================
// F03: RESEP & HPP DINAMIS CRUD (TABLE & MODALS)
// ==========================================
function renderRecipeTable(products) {
  const tbody = document.getElementById('recipeMenuTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const isManagerOrOwner = ['owner', 'manager'].includes(currentUser.role);

  products.forEach(p => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><code>${p.code}</code></td>
      <td style="font-weight:700;">${p.name}</td>
      <td><span class="status-pill">${p.category_name || '-'}</span></td>
      <td style="font-weight:700; color:var(--color-primary);">${formatRupiah(p.price)}</td>
      <td style="font-weight:700; color:var(--color-danger);">${formatRupiah(p.dynamic_hpp)}</td>
      <td><span class="menu-card__margin-badge">+${formatRupiah(p.gross_margin)} (${p.margin_pct}%)</span></td>
      <td>
        <div style="display:flex; gap:6px; align-items:center;">
          <button class="filter-pill" style="padding:4px 10px;" onclick="showRecipeDetail(${p.id})" title="Lihat Takaran BOM">
            👁️ Detail
          </button>
          ${isManagerOrOwner ? `
            <button class="filter-pill" style="padding:4px 10px; color:var(--color-primary);" onclick="openEditMenuModal(${p.id})" title="Edit Menu & Resep">
              ✏️ Edit
            </button>
            <button class="filter-pill" style="padding:4px 10px; color:var(--color-danger);" onclick="deleteMenu(${p.id}, '${escapeHtml(p.name)}')" title="Hapus/Arsipkan Menu">
              🗑️
            </button>
          ` : ''}
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// View Detail Modal
async function showRecipeDetail(productId) {
  try {
    const res = await fetch(apiUrl(`api/products.php?id=${productId}`));
    const data = await res.json();

    const titleEl = document.getElementById('recipeModalTitle');
    if (titleEl) titleEl.textContent = `BOM: ${data.product.name} (${data.product.code})`;

    const tbody = document.getElementById('recipeModalBody');
    if (tbody) {
      tbody.innerHTML = '';
      if (!data.recipes || data.recipes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--color-text-muted);">Belum ada resep bahan baku</td></tr>`;
      } else {
        data.recipes.forEach(r => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><strong>${r.ingredient_name}</strong> <small style="color:var(--color-text-muted);">(${r.ingredient_sku || ''})</small></td>
            <td>${r.dosage} ${r.unit}</td>
            <td>${formatRupiah(r.cost_per_unit)} / ${r.unit}</td>
            <td style="font-weight:700;">${formatRupiah(r.subtotal_cost)}</td>
          `;
          tbody.appendChild(tr);
        });
      }
    }

    const totHppEl = document.getElementById('recipeModalTotalHpp');
    if (totHppEl) totHppEl.textContent = formatRupiah(data.total_hpp);

    openModal('recipeModal');
  } catch (err) {
    alert('Gagal mengambil data BOM: ' + err.message);
  }
}

// Open Modal Tambah Menu Baru
function openCreateMenuModal() {
  document.getElementById('menuFormId').value = '0';
  document.getElementById('menuFormModalTitle').textContent = '➕ Tambah Menu Baru & Resep BOM';
  document.getElementById('menuFormName').value = '';
  document.getElementById('menuFormCode').value = '';
  document.getElementById('menuFormPrice').value = '';
  document.getElementById('menuFormDesc').value = '';

  const container = document.getElementById('menuFormRecipeContainer');
  if (container) {
    container.innerHTML = '';
    // Tambah 1 baris bahan default
    addRecipeIngredientRow();
  }

  recalculateModalHpp();
  openModal('menuFormModal');
}

// Open Modal Edit Menu
async function openEditMenuModal(productId) {
  try {
    const res = await fetch(apiUrl(`api/products.php?id=${productId}`));
    const data = await res.json();

    document.getElementById('menuFormId').value = data.product.id;
    document.getElementById('menuFormModalTitle').textContent = `✏️ Edit Menu: ${data.product.name}`;
    document.getElementById('menuFormName').value = data.product.name;
    document.getElementById('menuFormCode').value = data.product.code;
    document.getElementById('menuFormPrice').value = Math.round(data.product.price);
    document.getElementById('menuFormDesc').value = data.product.description || '';
    if (document.getElementById('menuFormCategory') && data.product.category_id) {
      document.getElementById('menuFormCategory').value = data.product.category_id;
    }

    const container = document.getElementById('menuFormRecipeContainer');
    if (container) {
      container.innerHTML = '';
      if (data.recipes && data.recipes.length > 0) {
        data.recipes.forEach(r => {
          addRecipeIngredientRow(r.ingredient_id, r.dosage);
        });
      } else {
        addRecipeIngredientRow();
      }
    }

    recalculateModalHpp();
    openModal('menuFormModal');
  } catch (err) {
    alert('Gagal mengambil data menu untuk diedit: ' + err.message);
  }
}

// Dynamic Recipe Row Builder
function addRecipeIngredientRow(selectedIngId = '', dosage = '') {
  const container = document.getElementById('menuFormRecipeContainer');
  if (!container) return;

  const row = document.createElement('div');
  row.className = 'recipe-row';
  row.style.cssText = 'display:flex; align-items:center; gap:8px; background:var(--color-surface); padding:8px 10px; border-radius:var(--radius-md); border:1px solid var(--color-border-subtle);';

  // Build ingredient select options
  let optionsHtml = '<option value="">-- Pilih Bahan Baku --</option>';
  allIngredients.forEach(ing => {
    const isSel = String(ing.ingredient_id) === String(selectedIngId) ? 'selected' : '';
    optionsHtml += `<option value="${ing.ingredient_id}" data-unit="${ing.unit}" data-cost="${ing.cost_per_unit}" ${isSel}>${ing.ingredient_name} (${formatRupiah(ing.cost_per_unit)}/${ing.unit})</option>`;
  });

  row.innerHTML = `
    <select class="form-control-native recipe-row-ing" style="flex:2;" onchange="onRecipeIngredientChange(this)" required>
      ${optionsHtml}
    </select>
    <div style="display:flex; align-items:center; gap:4px; flex:1;">
      <input type="number" class="form-control-native recipe-row-dosage" placeholder="Takaran" step="any" min="0" value="${dosage}" style="width:100%;" oninput="recalculateModalHpp()" required>
      <span class="recipe-row-unit" style="font-size:11px; font-weight:700; color:var(--color-text-tertiary); min-width:35px;">unit</span>
    </div>
    <div class="recipe-row-subtotal" style="font-size:12px; font-weight:700; color:var(--color-primary); min-width:75px; text-align:right;">
      Rp 0
    </div>
    <button type="button" class="btn-clear-cart" style="font-size:14px; padding:4px;" onclick="this.closest('.recipe-row').remove(); recalculateModalHpp();" title="Hapus bahan ini">
      🗑️
    </button>
  `;

  container.appendChild(row);

  // Trigger unit update
  const selectEl = row.querySelector('.recipe-row-ing');
  if (selectEl) {
    onRecipeIngredientChange(selectEl);
  }
}

function onRecipeIngredientChange(selectEl) {
  const row = selectEl.closest('.recipe-row');
  if (!row) return;

  const selectedOpt = selectEl.options[selectEl.selectedIndex];
  const unit = selectedOpt ? (selectedOpt.dataset.unit || 'unit') : 'unit';
  const unitSpan = row.querySelector('.recipe-row-unit');
  if (unitSpan) unitSpan.textContent = unit;

  recalculateModalHpp();
}

function recalculateModalHpp() {
  const rows = document.querySelectorAll('#menuFormRecipeContainer .recipe-row');
  let totalHpp = 0;

  rows.forEach(row => {
    const selectEl = row.querySelector('.recipe-row-ing');
    const dosageInput = row.querySelector('.recipe-row-dosage');
    const subtotalEl = row.querySelector('.recipe-row-subtotal');

    const selectedOpt = selectEl ? selectEl.options[selectEl.selectedIndex] : null;
    const cost = selectedOpt ? parseFloat(selectedOpt.dataset.cost || 0) : 0;
    const dosage = dosageInput ? parseFloat(dosageInput.value || 0) : 0;
    const subtotal = cost * dosage;

    totalHpp += subtotal;
    if (subtotalEl) {
      subtotalEl.textContent = formatRupiah(subtotal);
    }
  });

  totalHpp = Math.round(totalHpp);
  const liveHppEl = document.getElementById('menuFormLiveHpp');
  if (liveHppEl) liveHppEl.textContent = formatRupiah(totalHpp);

  const priceInput = document.getElementById('menuFormPrice');
  const price = priceInput ? parseFloat(priceInput.value || 0) : 0;
  const grossMargin = price - totalHpp;
  const marginPct = price > 0 ? ((grossMargin / price) * 100).toFixed(1) : 0;

  const liveMarginEl = document.getElementById('menuFormLiveMargin');
  if (liveMarginEl) {
    liveMarginEl.textContent = `${formatRupiah(grossMargin)} (${marginPct}%)`;
    liveMarginEl.style.color = grossMargin >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
  }
}

// Submit Create / Edit Menu
async function submitMenuForm() {
  const id = parseInt(document.getElementById('menuFormId').value || '0', 10);
  const name = document.getElementById('menuFormName').value.trim();
  const categoryId = document.getElementById('menuFormCategory').value;
  const code = document.getElementById('menuFormCode').value.trim();
  const price = parseFloat(document.getElementById('menuFormPrice').value || '0');
  const description = document.getElementById('menuFormDesc').value.trim();

  if (!name) {
    alert('Nama menu wajib diisi!');
    return;
  }

  // Collect recipe items
  const recipes = [];
  const rows = document.querySelectorAll('#menuFormRecipeContainer .recipe-row');
  rows.forEach(row => {
    const ingId = parseInt(row.querySelector('.recipe-row-ing').value || '0', 10);
    const dosage = parseFloat(row.querySelector('.recipe-row-dosage').value || '0');
    if (ingId > 0 && dosage > 0) {
      recipes.push({ ingredient_id: ingId, dosage: dosage });
    }
  });

  const action = id > 0 ? 'update' : 'create';
  const payload = {
    id: id,
    name: name,
    category_id: categoryId,
    code: code,
    price: price,
    description: description,
    recipes: recipes
  };

  try {
    const res = await fetch(apiUrl(`api/products.php?action=${action}`), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (!res.ok) {
      alert(`Gagal: ${data.error}`);
      return;
    }

    alert('✅ ' + data.message);
    closeModal('menuFormModal');

    // Reload products & menu grid
    await loadProducts();
  } catch (err) {
    alert('Terjadi kesalahan server: ' + err.message);
  }
}

// Delete Menu
async function deleteMenu(productId, menuName) {
  if (!confirm(`Apakah Anda yakin ingin menghapus/menonaktifkan menu "${menuName}"?`)) {
    return;
  }

  try {
    const res = await fetch(apiUrl('api/products.php?action=delete'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId })
    });

    const data = await res.json();
    if (!res.ok) {
      alert(`Gagal: ${data.error}`);
      return;
    }

    alert('✅ ' + data.message);
    await loadProducts();
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

// ==========================================
// F04 & F05: INVENTORI CRUD (TABLE & MODALS)
// ==========================================
async function loadInventory() {
  const filterEl = document.getElementById('inventoryOutletFilter');
  const outletId = filterEl ? filterEl.value : (currentUser.outlet_id || 1);

  try {
    const res = await fetch(apiUrl(`api/inventory.php?outlet_id=${outletId}`));
    allIngredients = await res.json();

    const tbody = document.getElementById('inventoryTableBody');
    if (tbody) {
      tbody.innerHTML = '';
      const isWarehouseOrManager = ['owner', 'manager', 'warehouse'].includes(currentUser.role);
      const isManagerOrOwner = ['owner', 'manager'].includes(currentUser.role);

      allIngredients.forEach(ing => {
        const isCritical = parseInt(ing.is_critical, 10) === 1;
        const tr = document.createElement('tr');
        if (isCritical) tr.style.backgroundColor = 'var(--color-danger-bg)';

        tr.innerHTML = `
          <td><code>${ing.sku}</code></td>
          <td style="font-weight:600;">${ing.ingredient_name}</td>
          <td>${ing.unit}</td>
          <td>${formatRupiah(ing.cost_per_unit)} / ${ing.unit}</td>
          <td style="font-weight:700; color:${isCritical ? 'var(--color-danger)' : 'var(--color-text-primary)'};">${ing.current_stock} ${ing.unit}</td>
          <td style="color:var(--color-text-muted);">${ing.min_stock_alert} ${ing.unit}</td>
          <td>
            ${isCritical 
              ? '<span class="status-pill" style="background:var(--color-danger-bg); color:var(--color-danger); border-color:var(--color-danger-border);">⚠️ STOK KRITIS</span>' 
              : '<span class="status-pill" style="background:var(--color-success-bg); color:var(--color-success); border-color:var(--color-success-border);">Aman</span>'}
          </td>
          <td>
            <div style="display:flex; gap:6px; align-items:center;">
              ${isWarehouseOrManager ? `
                <button class="filter-pill" style="padding:4px 10px; color:var(--color-success);" onclick="openRestockForIngredient(${ing.ingredient_id})" title="Catat Restok">
                  📥 Restok
                </button>
                <button class="filter-pill" style="padding:4px 10px; color:var(--color-primary);" onclick="openEditIngredientModal(${ing.ingredient_id})" title="Edit Bahan & Opname">
                  ✏️ Edit
                </button>
              ` : ''}
              ${isManagerOrOwner ? `
                <button class="filter-pill" style="padding:4px 10px; color:var(--color-danger);" onclick="deleteIngredient(${ing.ingredient_id}, '${escapeHtml(ing.ingredient_name)}')" title="Hapus Bahan Baku">
                  🗑️
                </button>
              ` : ''}
            </div>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    populateRestockSelect();
  } catch (err) {
    console.error('Gagal mengambil inventori:', err);
  }
}

// Modal Tambah Bahan Baku Baru
function openCreateIngredientModal() {
  const form = document.getElementById('createIngredientForm');
  if (form) form.reset();
  openModal('createIngredientModal');
}

async function submitCreateIngredient() {
  const name = document.getElementById('newIngName').value.trim();
  const sku = document.getElementById('newIngSku').value.trim();
  const unit = document.getElementById('newIngUnit').value;
  const cost = parseFloat(document.getElementById('newIngCost').value || '0');
  const minAlert = parseFloat(document.getElementById('newIngMinAlert').value || '100');
  const initialStock = parseFloat(document.getElementById('newIngInitialStock').value || '0');
  const filterEl = document.getElementById('inventoryOutletFilter');
  const outletId = filterEl ? parseInt(filterEl.value, 10) : currentUser.outlet_id;

  if (!name) {
    alert('Nama bahan baku wajib diisi!');
    return;
  }

  try {
    const res = await fetch(apiUrl('api/inventory.php?action=create'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name,
        sku: sku,
        unit: unit,
        cost_per_unit: cost,
        min_stock_alert: minAlert,
        initial_stock: initialStock,
        outlet_id: outletId,
        user_id: currentUser.id
      })
    });

    const data = await res.json();
    if (!res.ok) {
      alert(`Gagal: ${data.error}`);
      return;
    }

    alert('✅ ' + data.message);
    closeModal('createIngredientModal');

    await loadInventory();
    await loadProducts();
    await loadCriticalAlerts();
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

// Modal Edit Bahan Baku & Opname
function openEditIngredientModal(ingId) {
  const ing = allIngredients.find(i => parseInt(i.ingredient_id, 10) === parseInt(ingId, 10));
  if (!ing) return;

  document.getElementById('editIngId').value = ing.ingredient_id;
  document.getElementById('editIngName').value = ing.ingredient_name;
  document.getElementById('editIngSku').value = ing.sku;
  document.getElementById('editIngUnit').value = ing.unit;
  document.getElementById('editIngCost').value = Math.round(ing.cost_per_unit);
  document.getElementById('editIngMinAlert').value = Math.round(ing.min_stock_alert);
  document.getElementById('editIngCurrentStock').value = ing.current_stock;
  document.getElementById('editIngNotes').value = '';

  openModal('editIngredientModal');
}

async function submitEditIngredient() {
  const id = parseInt(document.getElementById('editIngId').value, 10);
  const name = document.getElementById('editIngName').value.trim();
  const sku = document.getElementById('editIngSku').value.trim();
  const unit = document.getElementById('editIngUnit').value.trim();
  const cost = parseFloat(document.getElementById('editIngCost').value || '0');
  const minAlert = parseFloat(document.getElementById('editIngMinAlert').value || '0');
  const currentStock = parseFloat(document.getElementById('editIngCurrentStock').value || '0');
  const notes = document.getElementById('editIngNotes').value.trim();
  const filterEl = document.getElementById('inventoryOutletFilter');
  const outletId = filterEl ? parseInt(filterEl.value, 10) : currentUser.outlet_id;

  if (!name || !unit) {
    alert('Nama dan satuan bahan baku wajib diisi!');
    return;
  }

  try {
    const res = await fetch(apiUrl('api/inventory.php?action=update'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id: id,
        name: name,
        sku: sku,
        unit: unit,
        cost_per_unit: cost,
        min_stock_alert: minAlert,
        outlet_id: outletId,
        current_stock: currentStock,
        adjustment_notes: notes || 'Penyesuaian stok opname',
        user_id: currentUser.id
      })
    });

    const data = await res.json();
    if (!res.ok) {
      alert(`Gagal: ${data.error}`);
      return;
    }

    alert('✅ ' + data.message);
    closeModal('editIngredientModal');

    await loadInventory();
    await loadProducts();
    await loadCriticalAlerts();
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

// Delete Bahan Baku
async function deleteIngredient(ingId, ingName) {
  if (!confirm(`Hapus bahan baku "${ingName}" dari sistem?\n(Perhatian: Hanya dapat dihapus jika tidak sedang digunakan di resep menu)`)) {
    return;
  }

  try {
    const res = await fetch(apiUrl('api/inventory.php?action=delete'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: ingId })
    });

    const data = await res.json();
    if (!res.ok) {
      alert(`Gagal: ${data.error}`);
      return;
    }

    alert('✅ ' + data.message);
    await loadInventory();
    await loadProducts();
    await loadCriticalAlerts();
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

function openRestockForIngredient(ingId) {
  const select = document.getElementById('restockIngredientSelect');
  if (select) {
    select.value = ingId;
    onRestockIngredientChange();
  }
  openModal('restockModal');
}

function populateRestockSelect() {
  const select = document.getElementById('restockIngredientSelect');
  if (!select) return;
  select.innerHTML = '';

  const uniqueIngs = [];
  const map = new Map();
  for (const item of allIngredients) {
    if(!map.has(item.ingredient_id)){
      map.set(item.ingredient_id, true);
      uniqueIngs.push(item);
    }
  }

  uniqueIngs.forEach(ing => {
    const opt = document.createElement('option');
    opt.value = ing.ingredient_id;
    opt.textContent = `${ing.ingredient_name} (${ing.sku}) - Satuan: ${ing.unit}`;
    opt.dataset.unit = ing.unit;
    opt.dataset.cost = ing.cost_per_unit;
    select.appendChild(opt);
  });

  onRestockIngredientChange();
}

function onRestockIngredientChange() {
  const select = document.getElementById('restockIngredientSelect');
  if (!select) return;
  const selectedOpt = select.options[select.selectedIndex];
  if (selectedOpt) {
    const unitLabel = document.getElementById('restockUnitLabel');
    if (unitLabel) unitLabel.textContent = selectedOpt.dataset.unit || 'unit';
    const costInput = document.getElementById('restockNewCostInput');
    if (costInput) costInput.placeholder = `Saat ini: ${formatRupiah(selectedOpt.dataset.cost)}`;
  }
}

async function submitRestock() {
  const outSelect = document.getElementById('restockOutletSelect');
  const ingSelect = document.getElementById('restockIngredientSelect');
  const qtyInput = document.getElementById('restockQtyInput');
  const costInput = document.getElementById('restockNewCostInput');
  const notesInput = document.getElementById('restockNotesInput');

  const outletId = outSelect ? outSelect.value : 1;
  const ingredientId = ingSelect ? ingSelect.value : 1;
  const quantity = qtyInput ? parseFloat(qtyInput.value) : 0;
  const newCost = (costInput && costInput.value) ? parseFloat(costInput.value) : null;
  const notes = notesInput ? notesInput.value : '';

  if (!quantity || quantity <= 0) {
    alert('Harap masukkan jumlah restok yang valid!');
    return;
  }

  try {
    const res = await fetch(apiUrl('api/inventory.php?action=restock'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        outlet_id: outletId,
        ingredient_id: ingredientId,
        quantity: quantity,
        new_cost_per_unit: newCost,
        notes: notes,
        user_id: currentUser.id
      })
    });

    const data = await res.json();
    if (!res.ok) {
      alert(`Gagal: ${data.error}`);
      return;
    }

    alert('✅ ' + data.message);
    closeModal('restockModal');

    if (qtyInput) qtyInput.value = '';
    if (costInput) costInput.value = '';
    if (notesInput) notesInput.value = '';

    await loadInventory();
    await loadProducts();
    await loadCriticalAlerts();
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

// Critical Alerts Tab (F05)
async function loadCriticalAlerts() {
  try {
    const res = await fetch(apiUrl('api/inventory.php?action=critical-alerts'));
    const alerts = await res.json();

    const badge = document.getElementById('alertBadgeCount');
    if (badge) badge.textContent = alerts.length;

    const container = document.getElementById('alertsContainer');
    if (!container) return;

    if (alerts.length === 0) {
      container.innerHTML = `
        <div style="text-align:center; padding:40px 20px; color:var(--color-success);">
          <div style="font-size:42px; margin-bottom:12px;">✅</div>
          <h3>Semua Stok Bahan Baku Aman</h3>
          <p style="color:var(--color-text-muted); font-size:13px;">Tidak ada bahan baku yang berada di bawah ambang batas minimum alert.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <div style="background:var(--color-warning-bg); border:1px solid var(--color-warning-border); border-radius:var(--radius-md); padding:12px 16px; margin-bottom:16px; font-size:13px; color:var(--color-warning);">
        🔔 Terdapat <strong>${alerts.length}</strong> bahan baku yang memerlukan pengadaan restok segera!
      </div>
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="background:var(--color-surface); border-bottom:1px solid var(--color-border); text-align:left;">
            <th style="padding:10px;">Cabang Outlet</th>
            <th style="padding:10px;">SKU</th>
            <th style="padding:10px;">Bahan Baku</th>
            <th style="padding:10px;">Sisa Stok</th>
            <th style="padding:10px;">Batas Minimum</th>
            <th style="padding:10px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          ${alerts.map(a => `
            <tr style="border-bottom:1px solid var(--color-border);">
              <td style="padding:10px; font-weight:700;">${a.outlet_name}</td>
              <td style="padding:10px;"><code>${a.sku}</code></td>
              <td style="padding:10px; color:var(--color-danger); font-weight:600;">${a.ingredient_name}</td>
              <td style="padding:10px; color:var(--color-danger); font-weight:700;">${a.current_stock} ${a.unit}</td>
              <td style="padding:10px;">${a.min_stock_alert} ${a.unit}</td>
              <td style="padding:10px;">
                <button class="filter-pill" onclick="handleAlertRestock('${a.outlet_id}', '${a.ingredient_id}')">
                  📥 Restok
                </button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  } catch (err) {
    console.error('Gagal mengambil data alert:', err);
  }
}

function handleAlertRestock(outletId, ingredientId) {
  const filter = document.getElementById('inventoryOutletFilter');
  if (filter) filter.value = outletId;
  loadInventory();
  if (ingredientId) {
    openRestockForIngredient(ingredientId);
  } else {
    openModal('restockModal');
  }
}
