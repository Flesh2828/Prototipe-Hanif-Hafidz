const { DatabaseSync } = require('node:sqlite');
const path = require('path');
const fs = require('fs');

const dbPath = path.join(__dirname, 'kopikita.db');
const db = new DatabaseSync(dbPath);

// Enable foreign keys
db.exec('PRAGMA foreign_keys = ON;');

function initDatabase() {
  const schemaSql = fs.readFileSync(path.join(__dirname, 'schema.sql'), 'utf-8');
  db.exec(schemaSql);
  seedInitialData();
}

function seedInitialData() {
  const checkOutlets = db.prepare('SELECT COUNT(*) as count FROM outlets').get();
  if (checkOutlets.count > 0) return;

  console.log('🌱 Seeding initial data for KopiKita...');

  // 1. Outlets
  const insertOutlet = db.prepare('INSERT INTO outlets (code, name, address, phone) VALUES (?, ?, ?, ?)');
  insertOutlet.run('OUT-01', 'KopiKita Kaliurang (Pusat)', 'Jl. Kaliurang KM 14.5, Sleman, Yogyakarta', '081234567890');
  insertOutlet.run('OUT-02', 'KopiKita Gejayan', 'Jl. Gejayan No. 45, Sleman, Yogyakarta', '081298765432');
  insertOutlet.run('OUT-03', 'KopiKita Malioboro', 'Jl. Malioboro No. 12, Kota Yogyakarta', '081311223344');

  // 2. Users (Owner, Manager, Cashier, Barista, Warehouse, Accountant)
  // Simple plain-text/hash simulation for prototype (password: password123)
  const insertUser = db.prepare('INSERT INTO users (outlet_id, username, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)');
  insertUser.run(null, 'owner', 'password123', 'Hafidz Taufiqurrahman (Owner)', 'owner');
  insertUser.run(1, 'manager1', 'password123', 'Hanif Hanan (Manager KopiKita 01)', 'manager');
  insertUser.run(1, 'kasir1', 'password123', 'Dzaky Kasir (Outlet Kaliurang)', 'cashier');
  insertUser.run(1, 'barista1', 'password123', 'Rafie Barista (Outlet Kaliurang)', 'barista');
  insertUser.run(1, 'gudang1', 'password123', 'Imad Gudang (Staf Inventori)', 'warehouse');
  insertUser.run(null, 'auditor', 'password123', 'Siti Akuntan & Auditor', 'accountant');

  // 3. Categories
  const insertCategory = db.prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
  insertCategory.run('Coffee Espresso Based', 'Olahan kopi mesin espresso');
  insertCategory.run('Manual Brew', 'Kopi seduh manual V60, Aeropress, Japanese');
  insertCategory.run('Non-Coffee & Mocktails', 'Minuman segar teh, susu, dan buah');
  insertCategory.run('Pastry & Snacks', 'Camilan pendamping kopi');

  // 4. Ingredients (Bahan Baku)
  // Biaya dalam Rupiah per gram/ml/pcs
  const insertIngredient = db.prepare('INSERT INTO ingredients (sku, name, unit, cost_per_unit, min_stock_alert) VALUES (?, ?, ?, ?, ?)');
  insertIngredient.run('ING-01', 'Biji Kopi House Blend (Arabica-Robusta)', 'gram', 220, 500); // Rp 220 / gram (Rp 220.000/kg)
  insertIngredient.run('ING-02', 'Biji Kopi Single Origin Gayo', 'gram', 350, 300); // Rp 350 / gram
  insertIngredient.run('ING-03', 'Susu Segar UHT Fresh Milk', 'ml', 20, 2000); // Rp 20 / ml (Rp 20.000/liter)
  insertIngredient.run('ING-04', 'Sirup Gula Aren Asli', 'ml', 40, 500); // Rp 40 / ml (Rp 40.000/liter)
  insertIngredient.run('ING-05', 'Sirup Karamel Monin', 'ml', 90, 250); // Rp 90 / ml
  insertIngredient.run('ING-06', 'Cup Paper 12oz & Lid', 'pcs', 750, 50); // Rp 750 / pcs
  insertIngredient.run('ING-07', 'Cup Plastic Cold 16oz + Straw', 'pcs', 850, 50); // Rp 850 / pcs
  insertIngredient.run('ING-08', 'Croissant Butter Mentega', 'pcs', 9000, 10); // Rp 9.000 / pcs

  // 5. Initial Outlet Stocks (Beri stok awal untuk Outlet 1, 2, 3)
  const insertStock = db.prepare('INSERT INTO outlet_stocks (outlet_id, ingredient_id, current_stock) VALUES (?, ?, ?)');
  const outlets = [1, 2, 3];
  outlets.forEach(outId => {
    insertStock.run(outId, 1, 3000); // 3000 gram biji kopi house blend
    insertStock.run(outId, 2, 1500); // 1500 gram single origin
    insertStock.run(outId, 3, 10000); // 10.000 ml susu (10 liter)
    insertStock.run(outId, 4, 3000); // 3.000 ml aren
    insertStock.run(outId, 5, 1000); // 1000 ml karamel
    insertStock.run(outId, 6, 200);  // 200 hot cups
    insertStock.run(outId, 7, 250);  // 250 cold cups
    insertStock.run(outId, 8, 25);   // 25 pcs croissant
  });

  // 6. Products (Menu Jual)
  const insertProduct = db.prepare('INSERT INTO products (category_id, code, name, description, price, image_url) VALUES (?, ?, ?, ?, ?, ?)');
  insertProduct.run(1, 'PRD-01', 'Kopi Susu Gula Aren (Es)', 'Signature espresso dengan susu segar dan aren murni khas KopiKita', 20000, 'kopsus-aren.jpg');
  insertProduct.run(1, 'PRD-02', 'Caffe Latte (Hot)', 'Double shot espresso dipadu silky steamed milk', 24000, 'latte.jpg');
  insertProduct.run(1, 'PRD-03', 'Caramel Macchiato (Es)', 'Espresso creamy dengan saus vanilla dan drizzle karamel', 28000, 'caramel-macchiato.jpg');
  insertProduct.run(1, 'PRD-04', 'Americano / Long Black', 'Double shot espresso dicairkan air murni panas/dingin', 18000, 'americano.jpg');
  insertProduct.run(2, 'PRD-05', 'Manual Brew V60 Gayo', 'Filter coffee dengan notes fruity dan aroma floral khas Aceh Gayo', 26000, 'v60.jpg');
  insertProduct.run(4, 'PRD-06', 'Butter Croissant Premium', 'Pastry renyah wangi butter Prancis hangat', 18000, 'croissant.jpg');

  // 7. BOM / Recipes (Resep Takaran Bahan per Porsi)
  const insertRecipe = db.prepare('INSERT INTO product_recipes (product_id, ingredient_id, dosage) VALUES (?, ?, ?)');
  
  // PRD-01: Kopsus Aren (18g kopi house, 120ml susu, 30ml aren, 1 cup cold)
  insertRecipe.run(1, 1, 18);  // 18g biji kopi (Rp 3.960)
  insertRecipe.run(1, 3, 120); // 120ml susu (Rp 2.400)
  insertRecipe.run(1, 4, 30);  // 30ml aren (Rp 1.200)
  insertRecipe.run(1, 7, 1);   // 1 cold cup (Rp 850)
  // Total HPP Kopsus Aren: ~Rp 8.410

  // PRD-02: Caffe Latte Hot (18g kopi house, 180ml susu, 1 cup hot)
  insertRecipe.run(2, 1, 18);  // 18g biji kopi (Rp 3.960)
  insertRecipe.run(2, 3, 180); // 180ml susu (Rp 3.600)
  insertRecipe.run(2, 6, 1);   // 1 hot cup (Rp 750)
  // Total HPP Latte Hot: ~Rp 8.310

  // PRD-03: Caramel Macchiato (18g kopi house, 130ml susu, 25ml karamel, 1 cup cold)
  insertRecipe.run(3, 1, 18);  // 18g biji kopi (Rp 3.960)
  insertRecipe.run(3, 3, 130); // 130ml susu (Rp 2.600)
  insertRecipe.run(3, 5, 25);  // 25ml karamel (Rp 2.250)
  insertRecipe.run(3, 7, 1);   // 1 cold cup (Rp 850)
  // Total HPP Caramel Macchiato: ~Rp 9.660

  // PRD-04: Americano (18g kopi house, 1 cup cold/hot)
  insertRecipe.run(4, 1, 18);  // 18g biji kopi (Rp 3.960)
  insertRecipe.run(4, 7, 1);   // 1 cold cup (Rp 850)
  // Total HPP Americano: ~Rp 4.810

  // PRD-05: V60 Gayo (15g single origin Gayo, 1 cup paper)
  insertRecipe.run(5, 2, 15);  // 15g single origin (Rp 5.250)
  insertRecipe.run(5, 6, 1);   // 1 cup paper (Rp 750)
  // Total HPP V60: ~Rp 6.000

  // PRD-06: Croissant (1 pcs croissant mentega)
  insertRecipe.run(6, 8, 1);   // 1 pcs croissant (Rp 9.000)
  // Total HPP Croissant: Rp 9.000

  console.log('✅ Seeding completed successfully!');
}

initDatabase();

module.exports = db;

