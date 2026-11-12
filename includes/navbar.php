<?php
/**
 * Shared Navbar KopiKita Multi-Outlet System
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

// Ambil jumlah alert stok kritis untuk badge
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
<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="pos.php">
      <span class="brand-badge"><i class="bi bi-cup-hot-fill text-warning"></i></span>
      <span>KopiKita <small class="text-secondary fw-normal fs-6">| Multi-Outlet System (PHP Native & MySQL)</small></span>
    </a>

    <div class="d-flex align-items-center gap-3 ms-auto">
      <!-- Role & Outlet Switcher -->
      <div class="d-flex align-items-center bg-secondary bg-opacity-25 px-3 py-1 rounded-pill">
        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
        <span id="currentOutletDisplay" class="fw-semibold text-white me-3"><?= htmlspecialchars($currentUser['outlet_name'] ?? 'Kaliurang') ?></span>
        <span class="badge bg-warning text-dark me-2" id="currentRoleBadge">Role: <?= strtoupper($currentUser['role'] ?? 'OWNER') ?></span>
        <button class="btn btn-sm btn-outline-light py-0 px-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#userSwitchModal">
          <i class="bi bi-person-gear"></i> Ganti Role
        </button>
      </div>

      <a href="inventory.php#alertsTab" class="btn btn-warning position-relative btn-sm">
        <i class="bi bi-bell-fill"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="alertBadgeCount"><?= $alertCount ?></span>
      </a>

      <?php if (is_logged_in()): ?>
        <a href="index.php?logout=1" class="btn btn-outline-danger btn-sm" title="Keluar">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Sub Nav Tabs (Modular Navigation) -->
<div class="container-fluid pt-3">
  <ul class="nav nav-pills mb-3 bg-white p-2 rounded shadow-sm gap-2" id="navTabs">
    <li class="nav-item">
      <a class="nav-link <?= $activePage === 'pos.php' ? 'active' : '' ?>" href="pos.php">
        <i class="bi bi-calculator"></i> F02: POS Kasir
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $activePage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
        <i class="bi bi-graph-up-arrow"></i> F06: Dashboard Laba-Rugi
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $activePage === 'recipes.php' ? 'active' : '' ?>" href="recipes.php">
        <i class="bi bi-journal-text"></i> F03: Resep & HPP Dinamis
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $activePage === 'inventory.php' ? 'active' : '' ?>" href="inventory.php">
        <i class="bi bi-box-seam"></i> F04/F05: Inventori & Stok Kritis
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $activePage === 'index.php' ? 'active' : '' ?>" href="index.php">
        <i class="bi bi-shield-check"></i> F01: RBAC & Portal Login
      </a>
    </li>
  </ul>
</div>

<!-- MODAL: GANTI ROLE PENGGUNA (RBAC SIMULATOR) -->
<div class="modal fade" id="userSwitchModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-people-fill"></i> Pilih Simulasi Pengguna & Hak Akses (RBAC)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted">Sesuai Dokumen PRD Bagian 3: Hak akses dibatasi berdasarkan peran dan Outlet ID penugasan.</p>
        <div class="list-group">
          <a href="index.php?switch_role=owner" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h6 class="mb-1 fw-bold text-primary">1. Owner / Pemilik Usaha (Hafidz)</h6>
              <span class="badge bg-primary">Full Agregat</span>
            </div>
            <small class="text-muted">Melihat dashboard seluruh outlet, omzet, HPP, margin laba bersih.</small>
          </a>
          <a href="index.php?switch_role=manager" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h6 class="mb-1 fw-bold text-success">2. Manajer Outlet (Hanif)</h6>
              <span class="badge bg-success">Cabang 1</span>
            </div>
            <small class="text-muted">Mengelola BOM, inventori cabang Kaliurang, restok dan staf.</small>
          </a>
          <a href="index.php?switch_role=cashier" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h6 class="mb-1 fw-bold text-warning text-dark">3. Kasir (Dzaky)</h6>
              <span class="badge bg-warning text-dark">POS Kasir</span>
            </div>
            <small class="text-muted">Memasukkan pesanan, pembayaran tunai/QRIS, cetak nota thermal.</small>
          </a>
          <a href="index.php?switch_role=barista" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h6 class="mb-1 fw-bold text-info">4. Barista (Rafie)</h6>
              <span class="badge bg-info">Antrean & Resep</span>
            </div>
            <small class="text-muted">Melihat antrean pesanan dan panduan gramasi resep.</small>
          </a>
          <a href="index.php?switch_role=warehouse" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h6 class="mb-1 fw-bold text-secondary">5. Staf Gudang (Imad)</h6>
              <span class="badge bg-secondary">Inventori</span>
            </div>
            <small class="text-muted">Mencatat mutasi barang masuk, opname, dan menindaklanjuti alert stok kritis.</small>
          </a>
          <a href="index.php?switch_role=accountant" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h6 class="mb-1 fw-bold text-danger">6. Akuntan / Auditor (Siti)</h6>
              <span class="badge bg-danger">Read-Only</span>
            </div>
            <small class="text-muted">Mengaudit omzet, HPP, margin laba tanpa izin mengubah data.</small>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

