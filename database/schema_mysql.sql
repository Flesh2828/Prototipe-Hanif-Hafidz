-- =====================================================================
-- SKEMA BASIS DATA KOPIKITA (MySQL 8.x / MariaDB Murni - XAMPP)
-- Berdasarkan Dokumen PRD KopiKita (Kelompok 9 - Three Musketeers)
-- Bagian 7.1: PHP Native 8.x & MySQL via PDO
-- Mencakup F01 - F06 Multi-Outlet, BOM, Resep, Stok Mutasi, Transaksi POS
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `kopikita` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `kopikita`;

-- Matikan foreign key check sementara untuk pembersihan tabel lama bila ada
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS `order_items`;
DROP VIEW IF EXISTS `orders`;
DROP VIEW IF EXISTS `product_recipes`;
DROP VIEW IF EXISTS `products`;

DROP TABLE IF EXISTS `transaction_items`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `inventory_mutations`;
DROP TABLE IF EXISTS `outlet_stocks`;
DROP TABLE IF EXISTS `recipes_bom`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `outlets`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. TABEL OUTLETS (Multi-Cabang)
CREATE TABLE `outlets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `address` TEXT,
    `phone` VARCHAR(20),
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABEL USERS (6 Peran Pengguna Sesuai RBAC F01)
-- Peran: 'owner', 'manager', 'cashier', 'barista', 'warehouse', 'accountant'
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `outlet_id` INT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('owner', 'manager', 'cashier', 'barista', 'warehouse', 'accountant') NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_users_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABEL CATEGORIES (Kategori Menu)
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABEL MENUS (Katalog Produk/Menu Jual)
CREATE TABLE `menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NULL,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_menus_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABEL INGREDIENTS (Bahan Baku Inventori)
CREATE TABLE `ingredients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sku` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `unit` VARCHAR(20) NOT NULL COMMENT 'gram, ml, pcs, dll.',
    `cost_per_unit` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000 COMMENT 'Harga beli per unit untuk kalkulasi HPP dinamis',
    `min_stock_alert` DECIMAL(12, 2) NOT NULL DEFAULT 100.00 COMMENT 'Ambang batas stok kritis (F05)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABEL RECIPES_BOM (Bill of Materials Resep Menu - F03)
CREATE TABLE `recipes_bom` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `menu_id` INT NOT NULL,
    `ingredient_id` INT NOT NULL,
    `dosage` DECIMAL(12, 4) NOT NULL COMMENT 'Takaran bahan per porsi',
    CONSTRAINT `fk_bom_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_bom_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABEL OUTLET_STOCKS (Stok Bahan Baku Riil Tiap Cabang - F04)
CREATE TABLE `outlet_stocks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `outlet_id` INT NOT NULL,
    `ingredient_id` INT NOT NULL,
    `current_stock` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_outlet_ingredient` (`outlet_id`, `ingredient_id`),
    CONSTRAINT `fk_stocks_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_stocks_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABEL INVENTORY_MUTATIONS (Riwayat Mutasi Stok Masuk/Keluar)
CREATE TABLE `inventory_mutations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `outlet_id` INT NOT NULL,
    `ingredient_id` INT NOT NULL,
    `mutation_type` ENUM('IN', 'OUT_SALE', 'ADJUSTMENT', 'WASTE') NOT NULL,
    `quantity` DECIMAL(12, 2) NOT NULL,
    `reference_id` VARCHAR(50) DEFAULT NULL COMMENT 'No. Invoice atau No. PO Restok',
    `notes` TEXT,
    `created_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mutations_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets`(`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_mutations_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_mutations_user` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABEL TRANSACTIONS (Pencatatan Transaksi Penjualan POS - F02, F06)
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `outlet_id` INT NOT NULL,
    `cashier_id` INT NOT NULL,
    `invoice_no` VARCHAR(50) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) DEFAULT 'Pelanggan Walk-in',
    `payment_method` VARCHAR(30) NOT NULL COMMENT 'cash, qris_sandbox',
    `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `total_hpp` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cash_given` DECIMAL(12, 2) DEFAULT 0.00,
    `change_due` DECIMAL(12, 2) DEFAULT 0.00,
    `transaction_status` VARCHAR(20) NOT NULL DEFAULT 'completed' COMMENT 'completed, cancelled',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_trans_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets`(`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_trans_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABEL TRANSACTION_ITEMS (Rincian Item Belanja per Transaksi)
CREATE TABLE `transaction_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT NOT NULL,
    `menu_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(12, 2) NOT NULL,
    `unit_hpp` DECIMAL(12, 4) NOT NULL,
    `subtotal` DECIMAL(12, 2) NOT NULL,
    `subtotal_hpp` DECIMAL(12, 2) NOT NULL,
    CONSTRAINT `fk_items_trans` FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- VIEW UNTUK KOMPATIBILITAS QUERY (ALIASED ACCESS)
-- =====================================================================
CREATE OR REPLACE VIEW `products` AS 
    SELECT `id`, `category_id`, `code`, `name`, `description`, `price`, `image_url`, `is_active`, `created_at` 
    FROM `menus`;

CREATE OR REPLACE VIEW `product_recipes` AS 
    SELECT `id`, `menu_id` AS `product_id`, `ingredient_id`, `dosage` 
    FROM `recipes_bom`;

CREATE OR REPLACE VIEW `orders` AS 
    SELECT `id`, `outlet_id`, `cashier_id`, `invoice_no`, `customer_name`, `payment_method`, `total_amount`, `total_hpp`, `cash_given`, `change_due`, `transaction_status` AS `order_status`, `created_at` 
    FROM `transactions`;

CREATE OR REPLACE VIEW `order_items` AS 
    SELECT `id`, `transaction_id` AS `order_id`, `menu_id` AS `product_id`, `quantity`, `unit_price`, `unit_hpp`, `subtotal`, `subtotal_hpp` 
    FROM `transaction_items`;

-- =====================================================================
-- DATA DUMMY / INITIAL SEED DATA
-- =====================================================================

-- 1. Outlets
INSERT INTO `outlets` (`id`, `code`, `name`, `address`, `phone`, `is_active`) VALUES
(1, 'OUT-01', 'KopiKita Kaliurang (Pusat)', 'Jl. Kaliurang KM 14.5, Sleman, Yogyakarta', '081234567890', 1),
(2, 'OUT-02', 'KopiKita Gejayan', 'Jl. Gejayan No. 45, Sleman, Yogyakarta', '081298765432', 1),
(3, 'OUT-03', 'KopiKita Malioboro', 'Jl. Malioboro No. 12, Kota Yogyakarta', '081311223344', 1);

-- 2. Users (6 Peran Pengguna)
-- Password plain: password123 (hash kompatibel password_hash / bcrypt)
INSERT INTO `users` (`id`, `outlet_id`, `username`, `password_hash`, `full_name`, `role`, `is_active`) VALUES
(1, NULL, 'owner', '$2y$10$eE0m1W8Yg9dfl2kZ5LdO3uK75N1tYg7eWfBqI0F5W1Bsm4xXk1aK2', 'Hafidz Taufiqurrahman (Owner)', 'owner', 1),
(2, 1, 'manager1', '$2y$10$eE0m1W8Yg9dfl2kZ5LdO3uK75N1tYg7eWfBqI0F5W1Bsm4xXk1aK2', 'Hanif Hanan (Manager KopiKita 01)', 'manager', 1),
(3, 1, 'kasir1', '$2y$10$eE0m1W8Yg9dfl2kZ5LdO3uK75N1tYg7eWfBqI0F5W1Bsm4xXk1aK2', 'Dzaky Kasir (Outlet Kaliurang)', 'cashier', 1),
(4, 1, 'barista1', '$2y$10$eE0m1W8Yg9dfl2kZ5LdO3uK75N1tYg7eWfBqI0F5W1Bsm4xXk1aK2', 'Rafie Barista (Outlet Kaliurang)', 'barista', 1),
(5, 1, 'gudang1', '$2y$10$eE0m1W8Yg9dfl2kZ5LdO3uK75N1tYg7eWfBqI0F5W1Bsm4xXk1aK2', 'Imad Gudang (Staf Inventori)', 'warehouse', 1),
(6, NULL, 'auditor', '$2y$10$eE0m1W8Yg9dfl2kZ5LdO3uK75N1tYg7eWfBqI0F5W1Bsm4xXk1aK2', 'Siti Akuntan & Auditor', 'accountant', 1);

-- 3. Categories
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Coffee Espresso Based', 'Olahan kopi mesin espresso'),
(2, 'Manual Brew', 'Kopi seduh manual V60, Aeropress, Japanese'),
(3, 'Non-Coffee & Mocktails', 'Minuman segar teh, susu, dan buah'),
(4, 'Pastry & Snacks', 'Camilan pendamping kopi');

-- 4. Ingredients (Bahan Baku)
INSERT INTO `ingredients` (`id`, `sku`, `name`, `unit`, `cost_per_unit`, `min_stock_alert`) VALUES
(1, 'ING-01', 'Biji Kopi House Blend (Arabica-Robusta)', 'gram', 220.0000, 500.00),
(2, 'ING-02', 'Biji Kopi Single Origin Gayo', 'gram', 350.0000, 300.00),
(3, 'ING-03', 'Susu Segar UHT Fresh Milk', 'ml', 20.0000, 2000.00),
(4, 'ING-04', 'Sirup Gula Aren Asli', 'ml', 40.0000, 500.00),
(5, 'ING-05', 'Sirup Karamel Monin', 'ml', 90.0000, 250.00),
(6, 'ING-06', 'Cup Paper 12oz & Lid', 'pcs', 750.0000, 50.00),
(7, 'ING-07', 'Cup Plastic Cold 16oz + Straw', 'pcs', 850.0000, 50.00),
(8, 'ING-08', 'Croissant Butter Mentega', 'pcs', 9000.0000, 10.00);

-- 5. Outlet Stocks (Stok Awal Cabang 1, 2, 3)
INSERT INTO `outlet_stocks` (`outlet_id`, `ingredient_id`, `current_stock`) VALUES
(1, 1, 3000.00),
(1, 2, 1500.00),
(1, 3, 10000.00),
(1, 4, 3000.00),
(1, 5, 1000.00),
(1, 6, 200.00),
(1, 7, 250.00),
(1, 8, 25.00),
(2, 1, 3000.00),
(2, 2, 1500.00),
(2, 3, 10000.00),
(2, 4, 3000.00),
(2, 5, 1000.00),
(2, 6, 200.00),
(2, 7, 250.00),
(2, 8, 25.00),
(3, 1, 3000.00),
(3, 2, 1500.00),
(3, 3, 10000.00),
(3, 4, 3000.00),
(3, 5, 1000.00),
(3, 6, 200.00),
(3, 7, 250.00),
(3, 8, 25.00);

-- 6. Menus
INSERT INTO `menus` (`id`, `category_id`, `code`, `name`, `description`, `price`, `image_url`, `is_active`) VALUES
(1, 1, 'PRD-01', 'Kopi Susu Gula Aren (Es)', 'Signature espresso dengan susu segar dan aren murni khas KopiKita', 20000.00, 'kopsus-aren.jpg', 1),
(2, 1, 'PRD-02', 'Caffe Latte (Hot)', 'Double shot espresso dipadu silky steamed milk', 24000.00, 'latte.jpg', 1),
(3, 1, 'PRD-03', 'Caramel Macchiato (Es)', 'Espresso creamy dengan saus vanilla dan drizzle karamel', 28000.00, 'caramel-macchiato.jpg', 1),
(4, 1, 'PRD-04', 'Americano / Long Black', 'Double shot espresso dicairkan air murni panas/dingin', 18000.00, 'americano.jpg', 1),
(5, 2, 'PRD-05', 'Manual Brew V60 Gayo', 'Filter coffee dengan notes fruity dan aroma floral khas Aceh Gayo', 26000.00, 'v60.jpg', 1),
(6, 4, 'PRD-06', 'Butter Croissant Premium', 'Pastry renyah wangi butter Prancis hangat', 18000.00, 'croissant.jpg', 1);

-- 7. Recipes BOM (Bill of Materials)
-- Menu 1: Kopsus Aren (18g kopi house, 120ml susu, 30ml aren, 1 cup cold) -> HPP Rp 8.410
INSERT INTO `recipes_bom` (`menu_id`, `ingredient_id`, `dosage`) VALUES
(1, 1, 18.0000),
(1, 3, 120.0000),
(1, 4, 30.0000),
(1, 7, 1.0000);

-- Menu 2: Caffe Latte Hot (18g kopi house, 180ml susu, 1 cup hot) -> HPP Rp 8.310
INSERT INTO `recipes_bom` (`menu_id`, `ingredient_id`, `dosage`) VALUES
(2, 1, 18.0000),
(2, 3, 180.0000),
(2, 6, 1.0000);

-- Menu 3: Caramel Macchiato (18g kopi house, 130ml susu, 25ml karamel, 1 cup cold) -> HPP Rp 9.660
INSERT INTO `recipes_bom` (`menu_id`, `ingredient_id`, `dosage`) VALUES
(3, 1, 18.0000),
(3, 3, 130.0000),
(3, 5, 25.0000),
(3, 7, 1.0000);

-- Menu 4: Americano (18g kopi house, 1 cup cold) -> HPP Rp 4.810
INSERT INTO `recipes_bom` (`menu_id`, `ingredient_id`, `dosage`) VALUES
(4, 1, 18.0000),
(4, 7, 1.0000);

-- Menu 5: V60 Gayo (15g single origin Gayo, 1 cup paper) -> HPP Rp 6.000
INSERT INTO `recipes_bom` (`menu_id`, `ingredient_id`, `dosage`) VALUES
(5, 2, 15.0000),
(5, 6, 1.0000);

-- Menu 6: Croissant (1 pcs croissant mentega) -> HPP Rp 9.000
INSERT INTO `recipes_bom` (`menu_id`, `ingredient_id`, `dosage`) VALUES
(6, 8, 1.0000);

-- 8. Sample Transactions (Data Penjualan Awal untuk Dashboard F06)
INSERT INTO `transactions` (`id`, `outlet_id`, `cashier_id`, `invoice_no`, `customer_name`, `payment_method`, `total_amount`, `total_hpp`, `cash_given`, `change_due`, `transaction_status`, `created_at`) VALUES
(1, 1, 3, 'KPK-01-20261101', 'Budi Santoso', 'cash', 44000.00, 16720.00, 50000.00, 6000.00, 'completed', '2026-11-11 10:15:00'),
(2, 1, 3, 'KPK-01-20261102', 'Siti Rahma', 'qris_sandbox', 28000.00, 9660.00, 28000.00, 0.00, 'completed', '2026-11-11 11:30:00'),
(3, 2, 3, 'KPK-02-20261101', 'Ahmad Fadil', 'cash', 38000.00, 15000.00, 50000.00, 12000.00, 'completed', '2026-11-11 12:45:00');

-- 9. Sample Transaction Items
INSERT INTO `transaction_items` (`transaction_id`, `menu_id`, `quantity`, `unit_price`, `unit_hpp`, `subtotal`, `subtotal_hpp`) VALUES
(1, 1, 1, 20000.00, 8410.0000, 20000.00, 8410.00),
(1, 2, 1, 24000.00, 8310.0000, 24000.00, 8310.00),
(2, 3, 1, 28000.00, 9660.0000, 28000.00, 9660.00),
(3, 1, 1, 20000.00, 8410.0000, 20000.00, 8410.00),
(3, 6, 1, 18000.00, 9000.0000, 18000.00, 9000.00);

