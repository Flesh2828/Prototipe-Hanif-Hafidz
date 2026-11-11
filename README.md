# KopiKita — Coffee Shop Multi-Outlet POS & Management System

> **Acuan Dokumen:** Product Requirements Document (PRD) KopiKita — PMBOK 8 & PABW  
> **Tim:** Kelompok 9 (Three Musketeers) — Informatika FTI UII  
> **Anggota Tim:** Hafidz Taufiqurrahman (PM), Hanif Hanan (Dev 1), Dzaky (Dev 2), Rafie (QA), Imad (Analyst)

---

## 🌟 Ringkasan Implementasi Prototipe MVP

Prototipe ini mengimplementasikan seluruh cakupan fungsional **Must Have (F01–F06)** sesuai dengan spesifikasi PRD KopiKita:

1. **F01 — Autentikasi & Role-Based Access Control (RBAC):**
   - Mendukung 6 peran pengguna: *Owner, Manajer Outlet, Kasir, Barista, Staf Gudang, Akuntan/Auditor*.
   - Filter akses berbasis `outlet_id` penugasan dan pembatasan wewenang (misal: Kasir fokus POS, Gudang fokus mutasi inventori, Auditor *read-only*).

2. **F02 — POS Kasir Multi-Outlet:**
   - Katalog menu dengan pencarian & filter kategori.
   - Keranjang transaksi real-time dengan kalkulasi estimasi margin laba & HPP langsung.
   - Metode pembayaran: **Tunai (Cash)** dengan kalkulator uang kembali & **QRIS Sandbox**.
   - Simulasi cetak struk nota belanja format thermal **ESC/POS** (dengan fitur `window.print()` / cetak langsung).
   - Target performa respon simpan transaksi POS tercapai **29 ms** (jauh melampaui SLA PRD &le; 1,5 detik).

3. **F03 — Resep & Bill of Materials (BOM) & HPP Dinamis:**
   - Pemetaan takaran bahan baku per porsi (gramasi, ml, pcs).
   - Perhitungan HPP otomatis secara dinamis ketika harga beli supplier berubah.

4. **F04 — Inventori & Auto-Deduction:**
   - Pemotongan stok bahan baku otomatis secara atomik berdasarkan takaran BOM saat pesanan diselesaikan di POS.
   - Fitur pencatatan barang masuk (Restock) dengan opsi memperbarui harga beli supplier.

5. **F05 — Critical Stock Alert:**
   - Deteksi otomatis ketika stok bahan baku di cabang berada di bawah ambang batas minimum.
   - Badge notifikasi merah dan tabel alert penanganan restok.

6. **F06 — Dashboard Laba-Rugi & Konsolidasi Multi-Outlet:**
   - Ringkasan Omzet Harian, Total HPP, Laba Kotor (*Gross Profit*), dan Persentase Margin.
   - Tabel performa penjualan antar-cabang (Konsolidasi Owner View).
   - Daftar 10 transaksi terakhir dan menu terlaris.

---

## 🚀 Cara Menjalankan Aplikasi

Aplikasi berjalan menggunakan runtime **Node.js (v24+)** dengan built-in SQLite engine (`node:sqlite`) tanpa memerlukan instalasi database server eksternal tambahan.

### 1. Menjalankan Server
Buka terminal pada direktori proyek dan jalankan:
```bash
node server.js
```

### 2. Membuka Aplikasi di Browser
Buka browser dan akses alamat:
```
http://localhost:3000
```

---

## 🗄️ Struktur Proyek

```
Prototipe-Hanif-Hafidz/
├── database/
│   ├── schema.sql         # Skema DDL SQL lengkap (MySQL/MariaDB & SQLite compatible)
│   ├── db.js              # Inisialisasi basis data dan data benih (seeders)
│   └── kopikita.db        # File database SQLite
├── public/
│   ├── index.html         # Frontend Single Page Dashboard & POS
│   ├── style.css          # Styling kustom & format struk ESC/POS
│   └── app.js             # Logika interaktif frontend (POS, BOM, Mutasi, Alert, RBAC)
├── server.js              # REST API backend (Express, F01-F06 endpoints)
├── package.json           # Dependensi Node.js
└── README.md              # Dokumentasi teknis proyek
```

---

## 👥 Simulasi Akun Pengguna (RBAC)

Anda dapat berganti peran secara langsung melalui tombol **"Ganti Role"** di pojok kanan atas aplikasi:
- **Owner:** Hafidz (Akses penuh seluruh cabang)
- **Manajer:** Hanif (Akses kelola cabang Kaliurang)
- **Kasir:** Dzaky (Akses POS transaksi cabang Kaliurang)
- **Barista:** Rafie (Akses melihat resep & antrean)
- **Gudang:** Imad (Akses mutasi stok & restok)
- **Auditor:** Siti (Akses laporan & audit keuangan *read-only*)
