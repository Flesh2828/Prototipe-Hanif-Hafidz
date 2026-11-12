# KopiKita — Coffee Shop Multi-Outlet POS & Management System

> **Acuan Dokumen:** Product Requirements Document (PRD) KopiKita — PMBOK 8 & PABW  
> **Bagian 7.1 Tech Stack:** PHP Native 8.x & MySQL Database (via PDO) pada lingkungan Apache XAMPP  
> **Tim:** Kelompok 9 (Three Musketeers) — Informatika FTI UII  
> **Anggota Tim:** Hafidz Taufiqurrahman (PM), Hanif Hanan (Dev 1), Dzaky (Dev 2), Rafie (QA), Imad (Analyst)

---

## 🌟 Ringkasan Implementasi Prototipe MVP

Prototipe ini mengimplementasikan seluruh cakupan fungsional **Must Have (F01–F06)** sesuai dengan spesifikasi PRD KopiKita:

1. **F01 — Autentikasi & Role-Based Access Control (RBAC):**
   - Mendukung 6 peran pengguna: *Owner, Manajer Outlet, Kasir, Barista, Staf Gudang, Akuntan/Auditor*.
   - Filter akses berbasis `outlet_id` penugasan dan pembatasan wewenang (misal: Kasir fokus POS, Gudang fokus mutasi inventori, Barista melihat resep/antrean, Auditor *read-only*).
   - Fitur 1-click simulasi pergantian peran secara instan untuk pengujian RBAC pada antarmuka.

2. **F02 — POS Kasir Multi-Outlet:**
   - Katalog menu dengan pencarian & filter kategori terintegrasi data riil MySQL.
   - Keranjang transaksi real-time dengan kalkulasi estimasi margin laba & HPP langsung.
   - Metode pembayaran: **Tunai (Cash)** dengan kalkulator uang kembali & **QRIS Sandbox**.
   - Simulasi cetak struk nota belanja format thermal **ESC/POS** (dengan fitur `window.print()` / cetak langsung).
   - Target performa respon simpan transaksi POS tercapai **11–29 ms** (jauh melampaui SLA PRD &le; 1,5 detik).

3. **F03 — Resep & Bill of Materials (BOM) & HPP Dinamis:**
   - Pemetaan takaran bahan baku per porsi (gramasi, ml, pcs) pada tabel `recipes_bom`.
   - Perhitungan HPP otomatis secara dinamis ketika harga beli supplier diperbarui saat restok.

4. **F04 — Inventori & Auto-Deduction:**
   - Pemotongan stok bahan baku otomatis secara atomik berdasarkan takaran BOM saat pesanan diselesaikan di POS.
   - Fitur pencatatan barang masuk (Restock) dengan opsi memperbarui harga beli supplier dan pencatatan riwayat di `inventory_mutations`.

5. **F05 — Critical Stock Alert:**
   - Deteksi otomatis ketika stok bahan baku di cabang berada di bawah ambang batas minimum (`current_stock <= min_stock_alert`).
   - Badge notifikasi merah real-time dan tabel alert penanganan restok.

6. **F06 — Dashboard Laba-Rugi & Konsolidasi Multi-Outlet:**
   - Ringkasan Omzet Harian, Total HPP, Laba Kotor (*Gross Profit*), dan Persentase Margin.
   - Tabel performa penjualan antar-cabang (Konsolidasi Owner View).
   - Daftar 10 transaksi terakhir dan menu terlaris.

---

## 🚀 Cara Menjalankan Aplikasi di XAMPP

Aplikasi berjalan pada **PHP Native 8.x** dan **MySQL / MariaDB** menggunakan web server Apache bawaan XAMPP.

### 1. Letakkan Folder Proyek di XAMPP htdocs
Pastikan folder proyek berada pada:
```
D:\XAMPP\htdocs\Prototipe-Hanif-Hafidz
```
*(atau `C:\xampp\htdocs\Prototipe-Hanif-Hafidz` sesuai letak instalasi XAMPP Anda)*

### 2. Jalankan Apache & MySQL
Buka **XAMPP Control Panel**, lalu klik tombol **Start** pada:
- **Apache**
- **MySQL**

### 3. Impor Basis Data MySQL
Impor skrip database `database/schema_mysql.sql` melalui **phpMyAdmin** atau terminal:
- Buka browser ke: `http://localhost/phpmyadmin`
- Klik menu **Import**, pilih file: `database/schema_mysql.sql`, lalu klik **Import**.
- Atau jalankan lewat command line:
  ```bash
  mysql -u root -e "source d:/XAMPP/htdocs/Prototipe-Hanif-Hafidz/database/schema_mysql.sql"
  ```

### 4. Membuka Aplikasi di Browser
Buka browser dan akses alamat:
```
http://localhost/Prototipe-Hanif-Hafidz/
```
Anda dapat langsung mengakses halaman modular:
- **Portal Login & RBAC Matrix (F01):** `http://localhost/Prototipe-Hanif-Hafidz/index.php`
- **Antarmuka Kasir POS (F02):** `http://localhost/Prototipe-Hanif-Hafidz/pos.php`
- **Dashboard Laba-Rugi Multi-Outlet (F06):** `http://localhost/Prototipe-Hanif-Hafidz/dashboard.php`
- **Resep & HPP Dinamis BOM (F03):** `http://localhost/Prototipe-Hanif-Hafidz/recipes.php`
- **Inventori & Peringatan Stok Kritis (F04/F05):** `http://localhost/Prototipe-Hanif-Hafidz/inventory.php`

---

## 🗄️ Struktur Proyek (PHP Native & MySQL)

```
Prototipe-Hanif-Hafidz/
├── config/
│   └── db.php                 # Konfigurasi koneksi MySQL via PDO standar XAMPP (root@localhost)
├── database/
│   ├── schema_mysql.sql       # Skema DDL MySQL murni & data seed (outlets, users, menus, BOM, dll.)
│   └── schema.sql             # Skema DDL cadangan
├── includes/
│   ├── auth.php               # Logika autentikasi session & helper RBAC (F01)
│   └── navbar.php             # Komponen navigasi atas & switcher role dinamis
├── api/
│   ├── auth.php               # REST endpoint autentikasi & switch role
│   ├── pos.php                # REST endpoint transaksi checkout, potong stok BOM & hitung SLA
│   ├── products.php           # REST endpoint katalog menu & kalkulasi dynamic HPP
│   ├── recipes.php            # REST endpoint komposisi resep BOM
│   ├── inventory.php          # REST endpoint stok, restok supplier & alert stok kritis
│   ├── dashboard.php          # REST endpoint analitik omzet, laba kotor & performa outlet
│   └── outlets.php            # REST endpoint daftar cabang outlet aktif
├── actions/
│   ├── checkout.php           # Action handler untuk checkout transaksi kasir POS
│   └── restock.php            # Action handler untuk restok bahan baku inventori
├── public/
│   ├── index.html             # Antarmuka SPA terintegrasi
│   ├── style.css              # Styling kustom, kartu menu & struk ESC/POS
│   └── app.js                 # Handler interaktif frontend terhubung ke REST API PHP
├── index.php                  # Halaman utama login RBAC & portal proyek (F01)
├── pos.php                    # Halaman antarmuka kasir POS (F02)
├── dashboard.php              # Halaman dashboard laba-rugi & konsolidasi multi-cabang (F06)
├── recipes.php                # Halaman panduan resep & BOM HPP dinamis (F03)
├── inventory.php              # Halaman manajemen stok & alert kritis (F04, F05)
├── .htaccess                  # Konfigurasi routing URL Apache XAMPP
└── README.md                  # Dokumentasi teknis proyek
```

---

## 👥 Simulasi Akun Pengguna (RBAC F01)

Sistem menyediakan 6 akun pengguna bawaan yang dapat langsung diuji:
- **Owner (Hafidz):** username `owner` (Akses penuh konsolidasi seluruh cabang)
- **Manajer (Hanif):** username `manager1` (Akses kelola cabang Kaliurang, stok & BOM)
- **Kasir (Dzaky):** username `kasir1` (Akses POS transaksi cabang Kaliurang)
- **Barista (Rafie):** username `barista1` (Akses antrean & panduan takaran resep)
- **Gudang (Imad):** username `gudang1` (Akses mutasi stok & restok supplier)
- **Auditor (Siti):** username `auditor` (Akses laporan laba-rugi *read-only*)

*Password default untuk semua akun:* `password123`
