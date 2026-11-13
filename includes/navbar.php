<?php
/**
 * Shared Top Navigation Bar KopiKita (Pure Semantic HTML5 & CSS3)
 * Berdasarkan Figma "ui kopikita" TopNavBar Blueprint
 */
if (!isset($currentUser)) {
    $currentUser = current_user() ?? [
        'id' => 1,
        'username' => 'owner',
        'full_name' => 'Hafidz Taufiqurrahman (Owner)',
        'role' => 'owner',
        'outlet_id' => 1,
        'outlet_name' => 'KopiKita Kaliurang (Pusat)'
    ];
}

$activePage = basename($_SERVER['PHP_SELF']);

// Ambil jumlah alert stok kritis
try {
    $db = getDb();
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
?>
<!-- Top Navigation Header -->
<header class="top-navbar">
  <div class="top-navbar__left">
    <div class="brand-title">
      <span class="brand-badge">☕</span>
      <span>KopiKita <small>| Multi-Outlet System</small></span>
    </div>

    <nav class="top-nav-links" aria-label="Navigasi Halaman Utama">
      <a href="pos.php" class="top-nav-link <?= $activePage === 'pos.php' ? 'active' : '' ?>">POS Kasir</a>
      <a href="dashboard.php" class="top-nav-link <?= $activePage === 'dashboard.php' ? 'active' : '' ?>">Laba-Rugi</a>
      <a href="recipes.php" class="top-nav-link <?= $activePage === 'recipes.php' ? 'active' : '' ?>">Resep & BOM</a>
      <a href="inventory.php" class="top-nav-link <?= $activePage === 'inventory.php' ? 'active' : '' ?>">Inventori</a>
      <a href="index.php" class="top-nav-link <?= $activePage === 'index.php' ? 'active' : '' ?>">Portal RBAC</a>
    </nav>
  </div>

  <div class="top-navbar__right">
    <span class="status-pill">
      <span class="dot dot--online"></span> Online
    </span>

    <span class="status-pill">
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

<!-- MODAL: GANTI ROLE PENGGUNA (RBAC SIMULATOR) -->
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
