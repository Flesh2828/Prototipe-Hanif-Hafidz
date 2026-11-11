-- =====================================================================
-- SKEMA BASIS DATA KOPIKITA (MySQL / MariaDB Compatible & SQLite)
-- Berdasarkan Dokumen PRD KopiKita (Kelompok 9 - Three Musketeers)
-- Mencakup F01 - F06 Multi-Outlet, BOM, Resep, Stok Mutasi, Transaksi
-- =====================================================================

CREATE TABLE IF NOT EXISTS outlets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    outlet_id INTEGER,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(30) NOT NULL, -- 'owner', 'manager', 'cashier', 'barista', 'warehouse', 'accountant'
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS ingredients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sku VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL, -- 'gram', 'ml', 'pcs', dll.
    cost_per_unit DECIMAL(12, 4) NOT NULL DEFAULT 0, -- Harga beli per unit (misal per gram)
    min_stock_alert DECIMAL(12, 2) NOT NULL DEFAULT 100, -- Ambang batas stok kritis
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS outlet_stocks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    outlet_id INTEGER NOT NULL,
    ingredient_id INTEGER NOT NULL,
    current_stock DECIMAL(12, 2) NOT NULL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(outlet_id, ingredient_id),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inventory_mutations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    outlet_id INTEGER NOT NULL,
    ingredient_id INTEGER NOT NULL,
    mutation_type VARCHAR(20) NOT NULL, -- 'IN' (Supplier), 'OUT_SALE' (BOM POS), 'ADJUSTMENT', 'WASTE'
    quantity DECIMAL(12, 2) NOT NULL,
    reference_id VARCHAR(50), -- No invoice transaksi atau restok
    notes TEXT,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(12, 2) NOT NULL, -- Harga jual
    image_url TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS product_recipes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    ingredient_id INTEGER NOT NULL,
    dosage DECIMAL(12, 4) NOT NULL, -- Takaran per porsi (sesuai unit ingredient)
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    outlet_id INTEGER NOT NULL,
    cashier_id INTEGER NOT NULL,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(100) DEFAULT 'Pelanggan Walk-in',
    payment_method VARCHAR(30) NOT NULL, -- 'cash', 'qris_sandbox'
    total_amount DECIMAL(12, 2) NOT NULL,
    total_hpp DECIMAL(12, 2) NOT NULL DEFAULT 0,
    cash_given DECIMAL(12, 2) DEFAULT 0,
    change_due DECIMAL(12, 2) DEFAULT 0,
    order_status VARCHAR(20) DEFAULT 'completed', -- 'completed', 'cancelled'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (cashier_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    unit_hpp DECIMAL(12, 4) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    subtotal_hpp DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

