// ==========================================
// STATE & CONFIG
// ==========================================
let currentUser = {
  id: 1,
  username: 'owner',
  full_name: 'Hafidz Taufiqurrahman',
  role: 'owner',
  outlet_id: 1,
  outlet_name: 'KopiKita Kaliurang'
};

let allProducts = [];
let allIngredients = [];
let cart = []; // { product, quantity }
let lastCheckoutResult = null;

const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
};

// ==========================================
// INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  updateUserDisplay();
  loadProducts();
  loadDashboardStats();
  loadInventory();
  loadCriticalAlerts();
});

// Switch Tab
function showTab(tabId) {
  document.querySelectorAll('.tab-pane-content').forEach(el => el.style.display = 'none');
  const target = document.getElementById(tabId);
  if (target) target.style.display = 'block';

  // Update active pill
  document.querySelectorAll('#navTabs .nav-link').forEach(btn => btn.classList.remove('active'));
  const activeBtn = Array.from(document.querySelectorAll('#navTabs .nav-link')).find(btn => btn.getAttribute('onclick').includes(tabId));
  if (activeBtn) activeBtn.classList.add('active');

  if (tabId === 'dashboardTab') loadDashboardStats();
  if (tabId === 'inventoryTab') loadInventory();
  if (tabId === 'recipeTab') loadProducts();
  if (tabId === 'alertsTab') loadCriticalAlerts();
}

// User RBAC Switcher Simulation (F01)
function switchUserRole(role, name, outletId, outletName) {
  currentUser = {
    id: role === 'owner' ? 1 : (role === 'manager' ? 2 : (role === 'cashier' ? 3 : 4)),
    username: role,
    full_name: name,
    role: role,
    outlet_id: outletId || 1,
    outlet_name: outletName
  };

  updateUserDisplay();
  const modalEl = document.getElementById('userSwitchModal');
  const modal = bootstrap.Modal.getInstance(modalEl);
  if (modal) modal.hide();

  // Reload data according to user outlet
  loadDashboardStats();
  loadInventory();
  loadCriticalAlerts();

  alert(`Peran berhasil dialihkan ke: ${name} (${role.toUpperCase()})\nOutlet Aktif: ${outletName}`);
}

function updateUserDisplay() {
  document.getElementById('currentRoleBadge').textContent = `Role: ${currentUser.role.toUpperCase()}`;
  document.getElementById('currentOutletDisplay').textContent = currentUser.outlet_name;

  // RBAC permissions check
  const btnRestock = document.querySelector('[data-bs-target="#restockModal"]');
  if (btnRestock) {
    if (['cashier', 'barista'].includes(currentUser.role)) {
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
    const res = await fetch('/api/products');
    allProducts = await res.json();
    renderMenuGrid(allProducts);
    renderRecipeTable(allProducts);
  } catch (err) {
    console.error('Gagal mengambil data produk:', err);
  }
}

function renderMenuGrid(products) {
  const grid = document.getElementById('menuGrid');
  grid.innerHTML = '';

  products.forEach(p => {
    const col = document.createElement('div');
    col.className = 'col';

    const iconMap = {
      'Coffee Espresso Based': 'bi-cup-hot',
      'Manual Brew': 'bi-filter-circle',
      'Non-Coffee & Mocktails': 'bi-cup-straw',
      'Pastry & Snacks': 'bi-egg-fried'
    };
    const iconClass = iconMap[p.category_name] || 'bi-cup';

    col.innerHTML = `
      <div class="card h-100 menu-card shadow-sm" onclick="addToCart(${p.id})">
        <div class="menu-img-placeholder">
          <i class="bi ${iconClass}"></i>
        </div>
        <div class="card-body p-2 d-flex flex-column justify-content-between">
          <div>
            <span class="badge bg-light text-dark border mb-1" style="font-size: 0.7rem;">${p.code}</span>
            <h6 class="card-title fw-bold mb-1 fs-6">${p.name}</h6>
            <p class="card-text text-muted small mb-2 text-truncate">${p.description || ''}</p>
          </div>
          <div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-bold text-primary">${formatRupiah(p.price)}</span>
              <span class="badge bg-success-subtle text-success" title="Estimasi Margin">
                +${p.margin_pct}%
              </span>
            </div>
            <button class="btn btn-outline-dark btn-sm w-100 py-1" onclick="event.stopPropagation(); addToCart(${p.id})">
              <i class="bi bi-plus-lg"></i> Tambah
            </button>
          </div>
        </div>
      </div>
    `;
    grid.appendChild(col);
  });
}

function filterMenu() {
  const query = document.getElementById('searchMenuInput').value.toLowerCase();
  const category = document.getElementById('categoryFilter').value;

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
  if (cart.length === 0) {
    listEl.innerHTML = `<p class="text-center text-muted my-4"><i class="bi bi-cart-x fs-1 d-block"></i>Keranjang masih kosong</p>`;
    document.getElementById('cartTotalAmount').textContent = 'Rp 0';
    document.getElementById('cartTotalHpp').textContent = 'Rp 0';
    document.getElementById('cartEstimatedMargin').textContent = 'Rp 0 (0%)';
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
    row.className = 'd-flex justify-content-between align-items-center mb-2 pb-2 border-bottom';
    row.innerHTML = `
      <div style="max-width: 60%;">
        <div class="fw-bold small text-truncate">${item.product.name}</div>
        <small class="text-muted">${formatRupiah(item.product.price)} &bull; HPP: ${formatRupiah(item.product.dynamic_hpp)}</small>
      </div>
      <div class="d-flex align-items-center gap-1">
        <button class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="updateCartQty(${item.product.id}, -1)">-</button>
        <span class="px-2 small fw-bold">${item.quantity}</span>
        <button class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="updateCartQty(${item.product.id}, 1)">+</button>
      </div>
      <div class="fw-bold small text-end" style="min-width: 75px;">
        ${formatRupiah(subtotal)}
      </div>
    `;
    listEl.appendChild(row);
  });

  const grossProfit = totalAmount - totalHpp;
  const marginPct = totalAmount > 0 ? ((grossProfit / totalAmount) * 100).toFixed(1) : 0;

  document.getElementById('cartTotalAmount').textContent = formatRupiah(totalAmount);
  document.getElementById('cartTotalHpp').textContent = formatRupiah(totalHpp);
  document.getElementById('cartEstimatedMargin').textContent = `${formatRupiah(grossProfit)} (${marginPct}%)`;

  calculateChange();
}

function togglePaymentMethod() {
  const method = document.querySelector('input[name="paymentMethod"]:checked').value;
  const cashGroup = document.getElementById('cashInputGroup');
  if (method === 'cash') {
    cashGroup.style.display = 'block';
  } else {
    cashGroup.style.display = 'none';
  }
}

function calculateChange() {
  const totalStr = document.getElementById('cartTotalAmount').textContent.replace(/[^\d]/g, '');
  const totalAmount = parseInt(totalStr, 10) || 0;
  const cashGiven = parseFloat(document.getElementById('cashGivenInput').value) || 0;
  const change = Math.max(0, cashGiven - totalAmount);
  document.getElementById('changeDueText').textContent = formatRupiah(change);
}

// Checkout POS process (F02, F03, F04)
async function processCheckout() {
  if (cart.length === 0) {
    alert('Keranjang belanja masih kosong!');
    return;
  }

  const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
  const customerName = document.getElementById('custNameInput').value || 'Pelanggan Walk-in';
  const cashGiven = parseFloat(document.getElementById('cashGivenInput').value) || 0;

  const payload = {
    outlet_id: currentUser.outlet_id || 1,
    cashier_id: currentUser.id || 1,
    customer_name: customerName,
    payment_method: paymentMethod,
    cash_given: cashGiven,
    items: cart.map(i => ({ product_id: i.product.id, quantity: i.quantity }))
  };

  const btn = document.getElementById('btnCheckout');
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Menyimpan...`;

  try {
    const res = await fetch('/api/pos/checkout', {
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

    // Check critical alert popup
    if (data.critical_alerts && data.critical_alerts.length > 0) {
      console.warn('⚠️ Alert Stok Kritis Terpicu:', data.critical_alerts);
    }
  } catch (err) {
    alert(`Terjadi kesalahan server: ${err.message}`);
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-check-circle-fill"></i> Simpan Transaksi & Potong Stok`;
  }
}

// Show ESC/POS Receipt Modal
function showReceiptModal(data) {
  document.getElementById('receiptOutletName').textContent = currentUser.outlet_name;
  document.getElementById('receiptInvoiceNo').textContent = data.invoice_no;
  document.getElementById('receiptDate').textContent = new Date().toLocaleString('id-ID');
  document.getElementById('receiptCashier').textContent = currentUser.full_name;
  document.getElementById('receiptCustomer').textContent = document.getElementById('custNameInput').value;

  const table = document.getElementById('receiptItemsTable');
  table.innerHTML = '';
  data.items.forEach(it => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${it.name} <br> <small class="text-muted">${it.quantity} x ${formatRupiah(it.unit_price)}</small></td>
      <td class="text-end fw-bold align-middle">${formatRupiah(it.subtotal)}</td>
    `;
    table.appendChild(tr);
  });

  document.getElementById('receiptTotalAmount').textContent = formatRupiah(data.total_amount);
  document.getElementById('receiptPaymentMethod').textContent = data.payment_method === 'cash' ? 'Cash' : 'QRIS Sandbox';
  document.getElementById('receiptCashGiven').textContent = formatRupiah(data.payment_method === 'cash' ? (parseFloat(document.getElementById('cashGivenInput').value) || data.total_amount) : data.total_amount);
  document.getElementById('receiptChangeDue').textContent = formatRupiah(data.change_due);
  document.getElementById('receiptTotalHpp').textContent = formatRupiah(data.total_hpp);
  document.getElementById('receiptGrossProfit').textContent = formatRupiah(data.gross_profit);

  const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
  modal.show();
}

function printReceipt() {
  window.print();
}

// ==========================================
// F06: DASHBOARD LABA-RUGI & OMZET
// ==========================================
async function loadDashboardStats() {
  const outletFilter = document.getElementById('dashboardOutletFilter').value;
  try {
    const res = await fetch(`/api/dashboard/stats?outlet_id=${outletFilter}`);
    const data = await res.json();

    document.getElementById('dashOmzet').textContent = formatRupiah(data.summary.total_omzet);
    document.getElementById('dashHpp').textContent = formatRupiah(data.summary.total_hpp);
    document.getElementById('dashProfit').textContent = formatRupiah(data.summary.gross_profit);
    document.getElementById('dashMarginPct').textContent = `Margin Kotor: ${data.summary.margin_pct}%`;
    document.getElementById('dashTransactions').textContent = `${data.summary.total_transactions} Order`;

    // Render Outlet Performance Table
    const outTbody = document.getElementById('outletPerformanceTableBody');
    outTbody.innerHTML = '';
    data.outlet_performance.forEach(op => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-semibold">${op.outlet_name}</td>
        <td><span class="badge bg-secondary">${op.transactions_count}</span></td>
        <td class="fw-bold text-primary">${formatRupiah(op.total_omzet)}</td>
        <td class="text-danger">${formatRupiah(op.total_hpp)}</td>
        <td class="fw-bold text-success">${formatRupiah(op.gross_profit)}</td>
      `;
      outTbody.appendChild(tr);
    });

    // Render Recent Orders Table
    const recTbody = document.getElementById('recentOrdersTableBody');
    recTbody.innerHTML = '';
    if (data.recent_orders.length === 0) {
      recTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada transaksi</td></tr>`;
    } else {
      data.recent_orders.forEach(ro => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><span class="badge bg-light text-dark border">${ro.invoice_no}</span></td>
          <td class="small">${ro.outlet_name}</td>
          <td><span class="badge ${ro.payment_method === 'cash' ? 'bg-success' : 'bg-primary'}">${ro.payment_method}</span></td>
          <td class="fw-bold">${formatRupiah(ro.total_amount)}</td>
          <td class="text-success small fw-semibold">+${formatRupiah(ro.gross_profit)}</td>
        `;
        recTbody.appendChild(tr);
      });
    }
  } catch (err) {
    console.error('Gagal mengambil data dashboard:', err);
  }
}

// ==========================================
// F03: RESEP & HPP DINAMIS TABLE
// ==========================================
function renderRecipeTable(products) {
  const tbody = document.getElementById('recipeMenuTableBody');
  tbody.innerHTML = '';

  products.forEach(p => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><span class="badge bg-light text-dark border">${p.code}</span></td>
      <td class="fw-bold">${p.name}</td>
      <td><span class="badge bg-secondary-subtle text-secondary">${p.category_name}</span></td>
      <td class="fw-bold text-primary">${formatRupiah(p.price)}</td>
      <td class="fw-bold text-danger">${formatRupiah(p.dynamic_hpp)}</td>
      <td><span class="badge bg-success">+${formatRupiah(p.gross_margin)} (${p.margin_pct}%)</span></td>
      <td>
        <button class="btn btn-outline-primary btn-sm py-0" onclick="showRecipeDetail(${p.id})">
          <i class="bi bi-eye"></i> Detail BOM
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

async function showRecipeDetail(productId) {
  try {
    const res = await fetch(`/api/products/${productId}/recipe`);
    const data = await res.json();

    document.getElementById('recipeModalTitle').textContent = `BOM: ${data.product.name} (${data.product.code})`;
    const tbody = document.getElementById('recipeModalBody');
    tbody.innerHTML = '';

    data.recipes.forEach(r => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-semibold">${r.ingredient_name}</td>
        <td>${r.dosage} ${r.unit}</td>
        <td>${formatRupiah(r.cost_per_unit)} / ${r.unit}</td>
        <td class="fw-bold">${formatRupiah(r.subtotal_cost)}</td>
      `;
      tbody.appendChild(tr);
    });

    document.getElementById('recipeModalTotalHpp').textContent = formatRupiah(data.total_hpp);
    const modal = new bootstrap.Modal(document.getElementById('recipeModal'));
    modal.show();
  } catch (err) {
    alert('Gagal mengambil data BOM: ' + err.message);
  }
}

// ==========================================
// F04 & F05: INVENTORI & CRITICAL ALERTS
// ==========================================
async function loadInventory() {
  const outletId = document.getElementById('inventoryOutletFilter').value;
  try {
    const res = await fetch(`/api/inventory?outlet_id=${outletId}`);
    allIngredients = await res.json();

    const tbody = document.getElementById('inventoryTableBody');
    tbody.innerHTML = '';

    allIngredients.forEach(ing => {
      const isCritical = ing.is_critical === 1;
      const tr = document.createElement('tr');
      if (isCritical) tr.className = 'table-danger';

      tr.innerHTML = `
        <td><span class="badge bg-light text-dark border">${ing.sku}</span></td>
        <td class="fw-semibold">${ing.ingredient_name}</td>
        <td>${ing.unit}</td>
        <td>${formatRupiah(ing.cost_per_unit)} / ${ing.unit}</td>
        <td class="fw-bold ${isCritical ? 'text-danger' : 'text-dark'}">${ing.current_stock} ${ing.unit}</td>
        <td class="text-muted">${ing.min_stock_alert} ${ing.unit}</td>
        <td>
          ${isCritical 
            ? '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> STOK KRITIS</span>' 
            : '<span class="badge bg-success">Aman</span>'}
        </td>
      `;
      tbody.appendChild(tr);
    });

    // Populate restock select
    populateRestockSelect();
  } catch (err) {
    console.error('Gagal mengambil inventori:', err);
  }
}

function populateRestockSelect() {
  const select = document.getElementById('restockIngredientSelect');
  select.innerHTML = '';
  // Deduplicate ingredients
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
  const selectedOpt = select.options[select.selectedIndex];
  if (selectedOpt) {
    document.getElementById('restockUnitLabel').textContent = selectedOpt.dataset.unit || 'unit';
    document.getElementById('restockNewCostInput').placeholder = `Saat ini: ${formatRupiah(selectedOpt.dataset.cost)}`;
  }
}

async function submitRestock() {
  const outletId = document.getElementById('restockOutletSelect').value;
  const ingredientId = document.getElementById('restockIngredientSelect').value;
  const quantity = parseFloat(document.getElementById('restockQtyInput').value);
  const newCost = parseFloat(document.getElementById('restockNewCostInput').value) || null;
  const notes = document.getElementById('restockNotesInput').value;

  if (!quantity || quantity <= 0) {
    alert('Harap masukkan jumlah restok yang valid!');
    return;
  }

  try {
    const res = await fetch('/api/inventory/restock', {
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
    const modal = bootstrap.Modal.getInstance(document.getElementById('restockModal'));
    if (modal) modal.hide();

    // Reset inputs
    document.getElementById('restockQtyInput').value = '';
    document.getElementById('restockNewCostInput').value = '';
    document.getElementById('restockNotesInput').value = '';

    // Refresh state
    loadInventory();
    loadProducts();
    loadCriticalAlerts();
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

// Critical Alerts Tab (F05)
async function loadCriticalAlerts() {
  try {
    const res = await fetch('/api/inventory/critical-alerts');
    const alerts = await res.json();

    document.getElementById('alertBadgeCount').textContent = alerts.length;

    const container = document.getElementById('alertsContainer');
    if (alerts.length === 0) {
      container.innerHTML = `
        <div class="text-center py-5 text-success">
          <i class="bi bi-check-circle-fill display-3 d-block mb-3"></i>
          <h5>Semua Stok Bahan Baku Dalam Kondisi Aman!</h5>
          <p class="text-muted">Tidak ada bahan baku yang berada di bawah ambang batas minimum alert.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <div class="alert alert-warning mb-3">
        <i class="bi bi-bell-fill"></i> Terdapat <strong>${alerts.length}</strong> bahan baku yang memerlukan pengadaan restok segera!
      </div>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-danger">
            <tr>
              <th>Cabang Outlet</th>
              <th>SKU</th>
              <th>Bahan Baku</th>
              <th>Sisa Stok Saat Ini</th>
              <th>Ambang Batas Minimum</th>
              <th>Aksi Penanganan</th>
            </tr>
          </thead>
          <tbody>
            ${alerts.map(a => `
              <tr>
                <td class="fw-bold">${a.outlet_name}</td>
                <td><span class="badge bg-light text-dark border">${a.sku}</span></td>
                <td class="fw-semibold text-danger">${a.ingredient_name}</td>
                <td class="fw-bold text-danger">${a.current_stock} ${a.unit}</td>
                <td>${a.min_stock_alert} ${a.unit}</td>
                <td>
                  <button class="btn btn-primary btn-sm py-0" onclick="showTab('inventoryTab'); document.getElementById('inventoryOutletFilter').value = '${a.outlet_id}'; loadInventory();">
                    <i class="bi bi-box-arrow-in-down"></i> Restok Sekarang
                  </button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
  } catch (err) {
    console.error('Gagal mengambil data alert:', err);
  }
}

