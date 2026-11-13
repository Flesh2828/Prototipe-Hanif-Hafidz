<?php
/**
 * Antarmuka Kasir POS Multi-Outlet (F02)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 * Desain Semantik HTML5 & Pure CSS3 (Figma: ui kopikita)
 */
require_once __DIR__ . '/includes/auth.php';
init_default_session();
$currentUser = current_user();

$db = getDb();
$stmtCats = $db->query('SELECT * FROM categories ORDER BY id ASC');
$categories = $stmtCats->fetchAll();

// Ambil jumlah alert stok kritis untuk badge notifikasi
try {
    $alertStmt = $db->query('
        SELECT COUNT(*) as alert_count 
        FROM outlet_stocks os 
        JOIN ingredients i ON os.ingredient_id = i.id 
        WHERE os.current_stock <= i.min_stock_alert
    ');
    $alertCount = (int)($alertStmt->fetch()['alert_count'] ?? 0);
} catch (Exception $e) {
    $alertCount = 0;
}

$isAllowedCheckout = !in_array($currentUser['role'], ['barista', 'accountant']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Kasir POS Multi-Outlet (F02)</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-shell">

  <!-- ====================================================================
       TOP NAV BAR (Shared Component Anchor - Figma 1280x56)
       ==================================================================== -->
  <header class="top-navbar">
    <div class="top-navbar__left">
      <div class="brand-title">
        <span class="brand-badge">☕</span>
        <span>KopiKita <small>| Multi-Outlet System</small></span>
      </div>

      <nav class="top-nav-links" aria-label="Navigasi Utama">
        <a href="pos.php" class="top-nav-link active">POS Kasir</a>
        <a href="dashboard.php" class="top-nav-link">Laba-Rugi</a>
        <a href="recipes.php" class="top-nav-link">Resep & BOM</a>
        <a href="inventory.php" class="top-nav-link">Inventori</a>
        <a href="index.php" class="top-nav-link">Portal RBAC</a>
      </nav>
    </div>

    <div class="top-navbar__right">
      <span class="status-pill" title="Koneksi Sistem">
        <span class="dot dot--online"></span> Online
      </span>

      <span class="status-pill" title="Nomor Kasir Aktif">
        Register #01
      </span>

      <a href="inventory.php#alertsTab" class="btn-bell" title="Peringatan Stok Kritis">
        🔔
        <span class="badge-count" id="alertBadgeCount"><?= $alertCount ?></span>
      </a>

      <div class="user-profile-badge" onclick="openModal('userSwitchModal')" title="Klik untuk ganti peran">
        <div class="user-avatar-circle">
          <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
        </div>
        <div style="line-height:1.2;">
          <div style="font-size:12px; font-weight:700;" id="currentRoleBadge">Role: <?= strtoupper($currentUser['role']) ?></div>
          <div style="font-size:10px; color:var(--color-accent-sand);" id="currentOutletDisplay"><?= htmlspecialchars($currentUser['outlet_name']) ?></div>
        </div>
      </div>

      <?php if (is_logged_in()): ?>
        <a href="index.php?logout=1" class="top-nav-link" style="color:var(--color-danger); padding:6px 8px;" title="Keluar">
          🚪 Keluar
        </a>
      <?php endif; ?>
    </div>
  </header>

  <!-- ====================================================================
       3-PANE APPLICATION WORKSPACE (Figma Frame Structure)
       ==================================================================== -->
  <div class="workspace-3pane">

    <!-- ------------------------------------------------------------------
         PANE 1: LEFT NAVIGATION RAIL (256px Fixed, Rich Espresso)
         ------------------------------------------------------------------ -->
    <aside class="nav-rail">
      <div>
        <div class="outlet-switcher-card">
          <div class="label">Cabang Aktif</div>
          <div class="outlet-name">
            📍 <?= htmlspecialchars($currentUser['outlet_name']) ?>
          </div>
          <span class="role-tag">Hak Akses: <?= strtoupper($currentUser['role']) ?></span>
        </div>

        <ul class="rail-menu-list">
          <li class="rail-menu-item active">
            <a href="pos.php"><span class="icon">🧾</span> POS & Fast Billing</a>
          </li>
          <li class="rail-menu-item">
            <a href="dashboard.php"><span class="icon">📊</span> Multi-Outlet P&L</a>
          </li>
          <li class="rail-menu-item">
            <a href="recipes.php"><span class="icon">📖</span> Resep & HPP Dinamis</a>
          </li>
          <li class="rail-menu-item">
            <a href="inventory.php"><span class="icon">📦</span> Live Inventori & Stok</a>
          </li>
          <li class="rail-menu-item">
            <a href="index.php"><span class="icon">🛡️</span> RBAC & Info Proyek</a>
          </li>
        </ul>
      </div>

      <div class="rail-footer-card">
        <div style="font-weight:700; color:var(--color-text-primary); margin-bottom:2px;">Shift Pagi (Aktif)</div>
        <div>Kasir: <?= htmlspecialchars($currentUser['full_name']) ?></div>
        <div style="font-size:10px; color:var(--color-text-tertiary); margin-top:4px;">SLA Simpan Transaksi &le; 1,5s</div>
        <button class="btn-switch-role" onclick="openModal('userSwitchModal')">
          🔄 Ganti Simulasi Role
        </button>
      </div>
    </aside>

    <!-- ------------------------------------------------------------------
         PANE 2: CENTRAL WORK CANVAS (Dynamic Menu & Live Stock HPP)
         ------------------------------------------------------------------ -->
    <main class="work-canvas">
      <section class="canvas-header-strip">
        <div class="canvas-header-strip__top">
          <div class="canvas-title-box">
            <h1>☕ Katalog Menu KopiKita</h1>
            <p>Pilih menu kopi atau camilan untuk ditambahkan ke keranjang kasir</p>
          </div>

          <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchMenuInput" placeholder="Cari nama menu / SKU..." onkeyup="filterMenu()">
          </div>
        </div>

        <!-- Hidden input for app.js filter synchronization -->
        <input type="hidden" id="categoryFilter" value="all">

        <nav class="category-filter-strip" aria-label="Filter Kategori">
          <button class="filter-pill active" onclick="selectCategoryFilter('all', this)">
            Semua Menu
          </button>
          <?php foreach ($categories as $cat): ?>
            <button class="filter-pill" onclick="selectCategoryFilter('<?= htmlspecialchars($cat['name']) ?>', this)">
              <?= htmlspecialchars($cat['name']) ?>
            </button>
          <?php endforeach; ?>
        </nav>
      </section>

      <section class="catalog-grid-wrap">
        <div class="catalog-grid" id="menuGrid">
          <!-- Diisi secara dinamis oleh public/app.js dari database MySQL -->
        </div>
      </section>
    </main>

    <!-- ------------------------------------------------------------------
         PANE 3: RIGHT SIDEBAR - ACTIVE CHECKOUT & ORDER LEDGER (380px Fixed)
         ------------------------------------------------------------------ -->
    <aside class="order-ledger">
      <header class="ledger-header">
        <h2>🛒 Keranjang Transaksi</h2>
        <button class="btn-clear-cart" onclick="clearCart()" title="Hapus semua item">
          🗑️ Kosongkan
        </button>
      </header>

      <!-- Cart Items List (Scrollable) -->
      <section class="cart-items-scroll" id="cartItemsList">
        <div class="cart-empty-state">
          <span class="icon">🛒</span>
          <p>Keranjang masih kosong</p>
        </div>
      </section>

      <!-- Financial Calculations & Checkout Form -->
      <footer class="ledger-checkout-box">
        <div class="calc-breakdown-row">
          <span>Total HPP (Est. Biaya Bahan)</span>
          <span id="cartTotalHpp" style="color:var(--color-danger); font-weight:700;">Rp 0</span>
        </div>
        <div class="calc-breakdown-row">
          <span>Estimasi Margin Laba</span>
          <span id="cartEstimatedMargin" style="color:var(--color-success); font-weight:700;">Rp 0 (0%)</span>
        </div>

        <div class="calc-breakdown-row total-row">
          <span class="total-label">Total Tagihan</span>
          <span class="total-value" id="cartTotalAmount">Rp 0</span>
        </div>

        <div class="form-group-custom">
          <label for="custNameInput">Nama Pelanggan (Opsional)</label>
          <input type="text" id="custNameInput" class="form-control-native" value="Pelanggan Walk-in" placeholder="Nama pemesan">
        </div>

        <div class="form-group-custom">
          <label>Metode Pembayaran</label>
          <div class="payment-toggle-grid">
            <button type="button" class="payment-toggle-btn active" id="btnPayCash" onclick="setPaymentMethod('cash')">
              💵 Tunai (Cash)
            </button>
            <button type="button" class="payment-toggle-btn" id="btnPayQris" onclick="setPaymentMethod('qris_sandbox')">
              📱 QRIS Sandbox
            </button>
          </div>
          <!-- Hidden radio inputs to maintain compatibility with app.js -->
          <input type="radio" name="paymentMethod" id="payCash" value="cash" checked style="display:none;">
          <input type="radio" name="paymentMethod" id="payQris" value="qris_sandbox" style="display:none;">
        </div>

        <div id="cashInputGroup" class="form-group-custom">
          <label for="cashGivenInput">Uang Diterima (Rp)</label>
          <input type="number" id="cashGivenInput" class="form-control-native" placeholder="0" oninput="calculateChange()">
          <div style="display:flex; justify-content:space-between; font-size:11px; margin-top:2px;">
            <span style="color:var(--color-text-muted);">Kembalian:</span>
            <span id="changeDueText" style="font-weight:700; color:var(--color-text-primary);">Rp 0</span>
          </div>
        </div>

        <button id="btnCheckout" class="btn-checkout-primary" onclick="processCheckout()" <?= !$isAllowedCheckout ? 'disabled title="Peran Anda tidak diizinkan melakukan transaksi checkout"' : '' ?>>
          ✅ Simpan Transaksi & Potong Stok
        </button>

        <?php if (!$isAllowedCheckout): ?>
          <div style="font-size:11px; color:var(--color-warning); text-align:center;">
            ⚠️ Peran <strong><?= strtoupper($currentUser['role']) ?></strong> bersifat Read-Only untuk POS.
          </div>
        <?php endif; ?>
      </footer>
    </aside>

  </div>

  <!-- ====================================================================
       MODAL: STRUK INVOICE / PRINT RECEIPT ESC/POS SIMULATION
       ==================================================================== -->
  <div class="native-modal" id="receiptModal">
    <div class="native-modal-backdrop" onclick="closeModal('receiptModal')"></div>
    <div class="native-modal-dialog native-modal-dialog--sm">
      <header class="native-modal-header">
        <h3>🧾 Nota Pembayaran (ESC/POS)</h3>
        <button class="btn-close-modal" onclick="closeModal('receiptModal')">&times;</button>
      </header>
      <div class="native-modal-body">
        <div id="receiptPrintArea" class="receipt-box">
          <div style="text-align:center; font-weight:700; font-size:14px;">KOPIKITA COFFEE</div>
          <div style="text-align:center; font-size:11px;" id="receiptOutletName"><?= htmlspecialchars($currentUser['outlet_name']) ?></div>
          <hr class="receipt-divider">
          <div>Inv : <span id="receiptInvoiceNo">KPK-01-000000</span></div>
          <div>Tgl : <span id="receiptDate"><?= date('d/m/Y H:i') ?></span></div>
          <div>Kas : <span id="receiptCashier"><?= htmlspecialchars($currentUser['full_name']) ?></span></div>
          <div>Plg : <span id="receiptCustomer">Pelanggan</span></div>
          <hr class="receipt-divider">
          <table style="width:100%; border-collapse:collapse; margin-bottom:8px;" id="receiptItemsTable">
            <!-- Dynamic via app.js -->
          </table>
          <hr class="receipt-divider">
          <div style="display:flex; justify-content:space-between;">
            <span>Total :</span> <strong id="receiptTotalAmount">Rp 0</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Bayar (<span id="receiptPaymentMethod">Cash</span>) :</span> <span id="receiptCashGiven">Rp 0</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Kembali :</span> <span id="receiptChangeDue">Rp 0</span>
          </div>
          <hr class="receipt-divider">
          <div style="display:flex; justify-content:space-between; color:#666; font-size:10px;">
            <span>Total HPP Bahan :</span> <span id="receiptTotalHpp">Rp 0</span>
          </div>
          <div style="display:flex; justify-content:space-between; color:#666; font-size:10px;">
            <span>Laba Kotor :</span> <span id="receiptGrossProfit">Rp 0</span>
          </div>
          <div style="text-align:center; margin-top:12px; font-size:11px;">
            Terima Kasih atas Kunjungan Anda!<br>
            <em>Kopi Nikmat, Transaksi Halal Berkah</em>
          </div>
        </div>
      </div>
      <footer class="native-modal-footer">
        <button type="button" class="filter-pill" onclick="closeModal('receiptModal')">Tutup</button>
        <button type="button" class="btn-checkout-primary" style="padding:6px 14px; font-size:12px;" onclick="printReceipt()">
          🖨️ Cetak Struk
        </button>
      </footer>
    </div>
  </div>

  <!-- ====================================================================
       MODAL: GANTI ROLE PENGGUNA (RBAC SIMULATOR)
       ==================================================================== -->
  <div class="native-modal" id="userSwitchModal">
    <div class="native-modal-backdrop" onclick="closeModal('userSwitchModal')"></div>
    <div class="native-modal-dialog">
      <header class="native-modal-header">
        <h3>👥 Pilih Simulasi Pengguna & Hak Akses (RBAC)</h3>
        <button class="btn-close-modal" onclick="closeModal('userSwitchModal')">&times;</button>
      </header>
      <div class="native-modal-body">
        <p style="font-size:12px; color:var(--color-text-tertiary); margin-bottom:12px;">
          Sesuai Dokumen PRD Bagian 3: Hak akses dibatasi berdasarkan peran dan Outlet ID penugasan.
        </p>
        <div style="display:flex; flex-direction:column; gap:8px;">
          <a href="index.php?switch_role=owner" class="outlet-switcher-card" style="display:block; margin-bottom:0; text-decoration:none;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <strong style="color:var(--color-primary-dark);">1. Owner / Pemilik Usaha (Hafidz)</strong>
              <span class="role-tag">Full Agregat</span>
            </div>
            <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:4px;">Akses penuh seluruh cabang, dashboard omzet, HPP, margin laba bersih.</div>
          </a>
          <a href="index.php?switch_role=manager" class="outlet-switcher-card" style="display:block; margin-bottom:0; text-decoration:none;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <strong style="color:var(--color-success);">2. Manajer Outlet (Hanif)</strong>
              <span class="role-tag" style="background:var(--color-success-bg); color:var(--color-success);">Cabang 1</span>
            </div>
            <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:4px;">Mengelola BOM resep, inventori cabang Kaliurang, restok dan staf.</div>
          </a>
          <a href="index.php?switch_role=cashier" class="outlet-switcher-card" style="display:block; margin-bottom:0; text-decoration:none;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <strong style="color:var(--color-accent);">3. Kasir (Dzaky)</strong>
              <span class="role-tag" style="background:var(--color-accent-light); color:var(--color-accent);">POS Kasir</span>
            </div>
            <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:4px;">Memasukkan pesanan kasir, pembayaran tunai/QRIS, cetak nota thermal.</div>
          </a>
          <a href="index.php?switch_role=barista" class="outlet-switcher-card" style="display:block; margin-bottom:0; text-decoration:none;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <strong style="color:var(--color-info);">4. Barista (Rafie)</strong>
              <span class="role-tag" style="background:var(--color-info-bg); color:var(--color-info);">Antrean & Resep</span>
            </div>
            <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:4px;">Melihat antrean pesanan dan panduan gramasi takaran resep BOM.</div>
          </a>
          <a href="index.php?switch_role=warehouse" class="outlet-switcher-card" style="display:block; margin-bottom:0; text-decoration:none;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <strong style="color:var(--color-text-primary);">5. Staf Gudang (Imad)</strong>
              <span class="role-tag">Inventori</span>
            </div>
            <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:4px;">Mencatat mutasi barang masuk, opname stok, dan alert stok kritis.</div>
          </a>
          <a href="index.php?switch_role=accountant" class="outlet-switcher-card" style="display:block; margin-bottom:0; text-decoration:none;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <strong style="color:var(--color-danger);">6. Akuntan / Auditor (Siti)</strong>
              <span class="role-tag" style="background:var(--color-danger-bg); color:var(--color-danger);">Read-Only</span>
            </div>
            <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:4px;">Audit omzet, HPP, margin laba tanpa izin mengubah data transaksi.</div>
          </a>
        </div>
      </div>
      <footer class="native-modal-footer">
        <button type="button" class="filter-pill" onclick="closeModal('userSwitchModal')">Tutup</button>
      </footer>
    </div>
  </div>

  <script>
    // State pengguna dari PHP
    const currentPhpUser = <?= json_encode($currentUser) ?>;

    // Helper Filter Kategori
    function selectCategoryFilter(categoryName, btn) {
      document.querySelectorAll('.category-filter-strip .filter-pill').forEach(el => el.classList.remove('active'));
      btn.classList.add('active');
      const catInput = document.getElementById('categoryFilter');
      if (catInput) {
        catInput.value = categoryName;
        filterMenu();
      }
    }

    // Helper Switch Payment Method
    function setPaymentMethod(method) {
      const btnCash = document.getElementById('btnPayCash');
      const btnQris = document.getElementById('btnPayQris');
      const radioCash = document.getElementById('payCash');
      const radioQris = document.getElementById('payQris');
      const cashGroup = document.getElementById('cashInputGroup');

      if (method === 'cash') {
        btnCash.classList.add('active');
        btnQris.classList.remove('active');
        radioCash.checked = true;
        cashGroup.style.display = 'flex';
      } else {
        btnQris.classList.add('active');
        btnCash.classList.remove('active');
        radioQris.checked = true;
        cashGroup.style.display = 'none';
      }
    }
  </script>
  <script src="public/app.js"></script>
</body>
</html>
