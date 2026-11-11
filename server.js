const express = require('express');
const cors = require('cors');
const path = require('path');
const db = require('./database/db');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Helper to compute live HPP for a product based on its BOM and current ingredient purchase cost
function calculateProductHpp(productId) {
  const recipeItems = db.prepare(`
    SELECT r.dosage, i.cost_per_unit 
    FROM product_recipes r
    JOIN ingredients i ON r.ingredient_id = i.id
    WHERE r.product_id = ?
  `).all(productId);

  let hpp = 0;
  for (const item of recipeItems) {
    hpp += (item.dosage * item.cost_per_unit);
  }
  return Math.round(hpp);
}

// ==========================================
// 1. AUTH & RBAC (F01)
// ==========================================
app.post('/api/auth/login', (req, res) => {
  const { username, password } = req.body;
  if (!username || !password) {
    return res.status(400).json({ error: 'Username dan password wajib diisi' });
  }

  const user = db.prepare(`
    SELECT u.id, u.username, u.full_name, u.role, u.outlet_id, o.name as outlet_name
    FROM users u
    LEFT JOIN outlets o ON u.outlet_id = o.id
    WHERE u.username = ? AND u.password_hash = ? AND u.is_active = 1
  `).get(username, password);

  if (!user) {
    return res.status(401).json({ error: 'Username atau password salah!' });
  }

  res.json({
    message: 'Login berhasil',
    user
  });
});

// ==========================================
// 2. MASTER DATA & OUTLETS
// ==========================================
app.get('/api/outlets', (req, res) => {
  const outlets = db.prepare('SELECT * FROM outlets WHERE is_active = 1').all();
  res.json(outlets);
});

// Menu with dynamic HPP calculation & Margin
app.get('/api/products', (req, res) => {
  const products = db.prepare(`
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
  `).all();

  const productsWithHpp = products.map(p => {
    const dynamicHpp = calculateProductHpp(p.id);
    const grossMargin = p.price - dynamicHpp;
    const marginPct = p.price > 0 ? ((grossMargin / p.price) * 100).toFixed(1) : 0;
    return {
      ...p,
      dynamic_hpp: dynamicHpp,
      gross_margin: grossMargin,
      margin_pct: Number(marginPct)
    };
  });

  res.json(productsWithHpp);
});

// Detail BOM / Recipe for a product
app.get('/api/products/:id/recipe', (req, res) => {
  const { id } = req.params;
  const recipes = db.prepare(`
    SELECT r.id, r.dosage, i.id as ingredient_id, i.name as ingredient_name, i.unit, i.cost_per_unit,
           (r.dosage * i.cost_per_unit) as subtotal_cost
    FROM product_recipes r
    JOIN ingredients i ON r.ingredient_id = i.id
    WHERE r.product_id = ?
  `).all(id);

  const product = db.prepare('SELECT * FROM products WHERE id = ?').get(id);
  res.json({ product, recipes, total_hpp: calculateProductHpp(id) });
});

// ==========================================
// 3. INVENTORY, BOM & CRITICAL ALERTS (F04, F05)
// ==========================================
app.get('/api/inventory', (req, res) => {
  const outletId = req.query.outlet_id;

  let query = `
    SELECT 
      i.id as ingredient_id, 
      i.sku, 
      i.name as ingredient_name, 
      i.unit, 
      i.cost_per_unit, 
      i.min_stock_alert,
      os.outlet_id,
      o.name as outlet_name,
      COALESCE(os.current_stock, 0) as current_stock,
      CASE 
        WHEN COALESCE(os.current_stock, 0) <= i.min_stock_alert THEN 1 
        ELSE 0 
      END as is_critical
    FROM ingredients i
    CROSS JOIN outlets o
    LEFT JOIN outlet_stocks os ON os.ingredient_id = i.id AND os.outlet_id = o.id
    WHERE o.is_active = 1
  `;

  const params = [];
  if (outletId) {
    query += ' AND o.id = ?';
    params.push(outletId);
  }

  query += ' ORDER BY is_critical DESC, i.name ASC';

  const inventory = db.prepare(query).all(...params);
  res.json(inventory);
});

// Critical alerts only
app.get('/api/inventory/critical-alerts', (req, res) => {
  const outletId = req.query.outlet_id;
  let query = `
    SELECT 
      i.sku, i.name as ingredient_name, i.unit, i.min_stock_alert,
      os.outlet_id, o.name as outlet_name, os.current_stock
    FROM outlet_stocks os
    JOIN ingredients i ON os.ingredient_id = i.id
    JOIN outlets o ON os.outlet_id = o.id
    WHERE os.current_stock <= i.min_stock_alert
  `;
  const params = [];
  if (outletId) {
    query += ' AND os.outlet_id = ?';
    params.push(outletId);
  }
  const alerts = db.prepare(query).all(...params);
  res.json(alerts);
});

// Mutasi Stok Masuk / Restock (F04)
app.post('/api/inventory/restock', (req, res) => {
  const { outlet_id, ingredient_id, quantity, user_id, notes, new_cost_per_unit } = req.body;
  if (!outlet_id || !ingredient_id || !quantity) {
    return res.status(400).json({ error: 'Data restok belum lengkap' });
  }

  try {
    // 1. Update ingredient unit price if supplied (Dynamic HPP auto adjustment!)
    if (new_cost_per_unit && new_cost_per_unit > 0) {
      db.prepare('UPDATE ingredients SET cost_per_unit = ? WHERE id = ?').run(new_cost_per_unit, ingredient_id);
    }

    // 2. Update or insert current stock
    const existing = db.prepare('SELECT current_stock FROM outlet_stocks WHERE outlet_id = ? AND ingredient_id = ?').get(outlet_id, ingredient_id);
    if (existing) {
      db.prepare('UPDATE outlet_stocks SET current_stock = current_stock + ?, updated_at = CURRENT_TIMESTAMP WHERE outlet_id = ? AND ingredient_id = ?')
        .run(quantity, outlet_id, ingredient_id);
    } else {
      db.prepare('INSERT INTO outlet_stocks (outlet_id, ingredient_id, current_stock) VALUES (?, ?, ?)')
        .run(outlet_id, ingredient_id, quantity);
    }

    // 3. Record mutation
    db.prepare(`
      INSERT INTO inventory_mutations (outlet_id, ingredient_id, mutation_type, quantity, reference_id, notes, created_by)
      VALUES (?, ?, 'IN', ?, ?, ?, ?)
    `).run(outlet_id, ingredient_id, quantity, 'RESTOCK-' + Date.now(), notes || 'Restok barang masuk', user_id || 1);

    res.json({ message: 'Stok berhasil ditambahkan dan HPP otomatis diperbarui' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ==========================================
// 4. POS TRANSACTION & AUTO-DEDUCTION (F02, F03, F04)
// ==========================================
app.post('/api/pos/checkout', (req, res) => {
  const startTime = Date.now();
  const { outlet_id, cashier_id, customer_name, payment_method, cash_given, items } = req.body;

  if (!outlet_id || !items || !items.length) {
    return res.status(400).json({ error: 'Item pesanan atau outlet tidak valid' });
  }

  // Pre-calculate totals & Check BOM stock sufficiency
  let totalAmount = 0;
  let totalHpp = 0;
  const stockDeductionMap = {}; // ingredient_id -> total deduction quantity

  const preparedItems = [];
  for (const item of items) {
    const product = db.prepare('SELECT * FROM products WHERE id = ?').get(item.product_id);
    if (!product) {
      return res.status(400).json({ error: `Produk ID ${item.product_id} tidak ditemukan` });
    }

    const unitHpp = calculateProductHpp(product.id);
    const subtotal = product.price * item.quantity;
    const subtotalHpp = unitHpp * item.quantity;

    totalAmount += subtotal;
    totalHpp += subtotalHpp;

    preparedItems.push({
      product_id: product.id,
      name: product.name,
      quantity: item.quantity,
      unit_price: product.price,
      unit_hpp: unitHpp,
      subtotal,
      subtotal_hpp: subtotalHpp
    });

    // Check recipe for stock deduction
    const recipes = db.prepare('SELECT ingredient_id, dosage FROM product_recipes WHERE product_id = ?').all(product.id);
    for (const r of recipes) {
      const needed = r.dosage * item.quantity;
      stockDeductionMap[r.ingredient_id] = (stockDeductionMap[r.ingredient_id] || 0) + needed;
    }
  }

  // Check if stock is sufficient
  for (const [ingredientId, neededQty] of Object.entries(stockDeductionMap)) {
    const stockRow = db.prepare('SELECT current_stock FROM outlet_stocks WHERE outlet_id = ? AND ingredient_id = ?')
      .get(outlet_id, ingredientId);
    const available = stockRow ? stockRow.current_stock : 0;
    if (available < neededQty) {
      const ing = db.prepare('SELECT name, unit FROM ingredients WHERE id = ?').get(ingredientId);
      return res.status(400).json({
        error: `Stok tidak mencukupi untuk bahan: ${ing ? ing.name : ingredientId}. Dibutuhkan: ${neededQty} ${ing.unit}, Tersedia: ${available} ${ing.unit}`
      });
    }
  }

  const invoiceNo = 'KPK-' + outlet_id + '-' + Date.now().toString().slice(-8);
  const changeDue = payment_method === 'cash' ? Math.max(0, (cash_given || 0) - totalAmount) : 0;

  try {
    // 1. Create Order
    const orderResult = db.prepare(`
      INSERT INTO orders (outlet_id, cashier_id, invoice_no, customer_name, payment_method, total_amount, total_hpp, cash_given, change_due, order_status)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')
    `).run(outlet_id, cashier_id || 1, invoiceNo, customer_name || 'Pelanggan Walk-in', payment_method, totalAmount, totalHpp, cash_given || totalAmount, changeDue);

    const orderId = orderResult.lastInsertRowid;

    // 2. Insert Order Items
    const insertItemStmt = db.prepare(`
      INSERT INTO order_items (order_id, product_id, quantity, unit_price, unit_hpp, subtotal, subtotal_hpp)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `);

    for (const pi of preparedItems) {
      insertItemStmt.run(orderId, pi.product_id, pi.quantity, pi.unit_price, pi.unit_hpp, pi.subtotal, pi.subtotal_hpp);
    }

    // 3. Auto-deduct stock based on BOM & Log mutation
    const updateStockStmt = db.prepare(`
      UPDATE outlet_stocks 
      SET current_stock = current_stock - ?, updated_at = CURRENT_TIMESTAMP
      WHERE outlet_id = ? AND ingredient_id = ?
    `);

    const logMutationStmt = db.prepare(`
      INSERT INTO inventory_mutations (outlet_id, ingredient_id, mutation_type, quantity, reference_id, notes, created_by)
      VALUES (?, ?, 'OUT_SALE', ?, ?, ?, ?)
    `);

    for (const [ingredientId, deductedQty] of Object.entries(stockDeductionMap)) {
      updateStockStmt.run(deductedQty, outlet_id, ingredientId);
      logMutationStmt.run(outlet_id, ingredientId, deductedQty, invoiceNo, `POS Auto-deduct BOM order #${invoiceNo}`, cashier_id || 1);
    }

    // Check newly triggered critical stock alerts
    const criticalAlertsTriggered = db.prepare(`
      SELECT i.name, i.unit, os.current_stock, i.min_stock_alert
      FROM outlet_stocks os
      JOIN ingredients i ON os.ingredient_id = i.id
      WHERE os.outlet_id = ? AND os.current_stock <= i.min_stock_alert
    `).all(outlet_id);

    const responseDurationMs = Date.now() - startTime;

    res.json({
      message: 'Transaksi berhasil disimpan dan stok otomatis terpotong!',
      invoice_no: invoiceNo,
      order_id: orderId,
      total_amount: totalAmount,
      total_hpp: totalHpp,
      gross_profit: totalAmount - totalHpp,
      change_due: changeDue,
      payment_method,
      items: preparedItems,
      response_duration_ms: responseDurationMs, // Memenuhi target SLA < 1.5s
      critical_alerts: criticalAlertsTriggered
    });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ==========================================
// 5. DASHBOARD LABA-RUGI & OMZET (F06)
// ==========================================
app.get('/api/dashboard/stats', (req, res) => {
  const { outlet_id, date_from, date_to } = req.query;

  let whereClause = "WHERE o.order_status = 'completed'";
  const params = [];

  if (outlet_id && outlet_id !== 'all') {
    whereClause += ' AND o.outlet_id = ?';
    params.push(outlet_id);
  }

  // Summary Metrics (Total Omzet, Total HPP, Laba Kotor, Jumlah Transaksi)
  const summary = db.prepare(`
    SELECT 
      COUNT(o.id) as total_transactions,
      COALESCE(SUM(o.total_amount), 0) as total_omzet,
      COALESCE(SUM(o.total_hpp), 0) as total_hpp,
      (COALESCE(SUM(o.total_amount), 0) - COALESCE(SUM(o.total_hpp), 0)) as gross_profit
    FROM orders o
    ${whereClause}
  `).get(...params);

  // Per Outlet Performance (untuk Owner multi-outlet)
  const outletPerformance = db.prepare(`
    SELECT 
      ot.id as outlet_id,
      ot.name as outlet_name,
      COUNT(o.id) as transactions_count,
      COALESCE(SUM(o.total_amount), 0) as total_omzet,
      COALESCE(SUM(o.total_hpp), 0) as total_hpp,
      (COALESCE(SUM(o.total_amount), 0) - COALESCE(SUM(o.total_hpp), 0)) as gross_profit
    FROM outlets ot
    LEFT JOIN orders o ON o.outlet_id = ot.id AND o.order_status = 'completed'
    GROUP BY ot.id, ot.name
  `).all();

  // Top Selling Menu
  let topMenuQuery = `
    SELECT 
      p.name,
      SUM(oi.quantity) as total_sold,
      SUM(oi.subtotal) as total_revenue,
      SUM(oi.subtotal_hpp) as total_hpp,
      (SUM(oi.subtotal) - SUM(oi.subtotal_hpp)) as total_profit
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    ${whereClause}
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
    LIMIT 5
  `;
  const topMenus = db.prepare(topMenuQuery).all(...params);

  // Recent Orders (5 latest)
  let recentOrdersQuery = `
    SELECT 
      o.id, o.invoice_no, o.customer_name, o.payment_method, o.total_amount, o.total_hpp,
      (o.total_amount - o.total_hpp) as gross_profit, o.created_at, ot.name as outlet_name
    FROM orders o
    JOIN outlets ot ON o.outlet_id = ot.id
    ${whereClause}
    ORDER BY o.id DESC
    LIMIT 10
  `;
  const recentOrders = db.prepare(recentOrdersQuery).all(...params);

  const marginPct = summary.total_omzet > 0 
    ? ((summary.gross_profit / summary.total_omzet) * 100).toFixed(1) 
    : 0;

  res.json({
    summary: {
      ...summary,
      margin_pct: Number(marginPct)
    },
    outlet_performance: outletPerformance,
    top_menus: topMenus,
    recent_orders: recentOrders
  });
});

// Start Server
app.listen(PORT, () => {
  console.log(`☕ KopiKita Server running at http://localhost:${PORT}`);
});

