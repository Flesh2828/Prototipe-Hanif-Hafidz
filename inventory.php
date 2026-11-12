<?php
/**
 * Modul Inventori, Mutasi Stok & Critical Stock Alert (F04, F05)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 */
require_once __DIR__ . '/includes/auth.php';
init_default_session();
$currentUser = current_user();

$db = getDb();
$stmtOutlets = $db->query('SELECT * FROM outlets WHERE is_active = 1 ORDER BY id ASC');
$outlets = $stmtOutlets->fetchAll();

$isAllowedRestock = !in_array($currentUser['role'], ['cashier', 'barista', 'accountant']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Inventori & Stok Kritis (F04/F05)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body class="bg-light">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="container-fluid pb-4">
    <!-- Nav tabs internal Inventori vs Alert -->
    <ul class="nav nav-tabs mb-3" id="inventoryTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active fw-bold" id="inv-list-tab" data-bs-toggle="tab" data-bs-target="#invListPane" type="button">
          <i class="bi bi-boxes text-primary"></i> F04: Stok Bahan Baku
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fw-bold text-danger" id="inv-alert-tab" data-bs-toggle="tab" data-bs-target="#invAlertPane" type="button">
          <i class="bi bi-exclamation-octagon-fill"></i> F05: Peringatan Stok Kritis
        </button>
      </li>
    </ul>

    <div class="tab-content" id="inventoryTabContent">
      <!-- PANE 1: STOK BAHAN BAKU (F04) -->
      <div class="tab-pane fade show active" id="invListPane" role="tabpanel">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h5 class="mb-0 fw-bold"><i class="bi bi-boxes text-info"></i> Manajemen Stok Bahan Baku Multi-Outlet</h5>
              <small class="text-muted">Stok berkurang otomatis saat POS checkout dan bertambah saat restok supplier</small>
            </div>
            <div class="d-flex gap-2">
              <select id="inventoryOutletFilter" class="form-select form-select-sm" onchange="loadInventory()">
                <?php foreach ($outlets as $o): ?>
                  <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($isAllowedRestock): ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#restockModal">
                  <i class="bi bi-plus-circle"></i> Catat Barang Masuk (Restock)
                </button>
              <?php else: ?>
                <button class="btn btn-secondary btn-sm" disabled title="Peran Anda tidak memiliki wewenang restok">
                  <i class="bi bi-lock-fill"></i> Restok Terkunci (RBAC)
                </button>
              <?php endif; ?>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>SKU</th>
                    <th>Nama Bahan Baku</th>
                    <th>Satuan</th>
                    <th>Harga Beli / Unit</th>
                    <th>Stok Saat Ini</th>
                    <th>Batas Minimum (Alert)</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="inventoryTableBody">
                  <!-- Dynamic via app.js -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- PANE 2: PERINGATAN STOK KRITIS (F05) -->
      <div class="tab-pane fade" id="invAlertPane" role="tabpanel">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0 fw-bold"><i class="bi bi-exclamation-octagon-fill"></i> Critical Stock Alert System</h5>
              <small class="text-white-50">Daftar bahan baku yang berada di bawah atau menyentuh ambang batas minimum</small>
            </div>
            <button class="btn btn-outline-light btn-sm" onclick="loadCriticalAlerts()"><i class="bi bi-arrow-clockwise"></i> Periksa Stok</button>
          </div>
          <div class="card-body" id="alertsContainer">
            <!-- Dynamic alerts via app.js -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: RESTOCK INVENTORY -->
  <div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">Catat Barang Masuk (Restock Bahan Baku)</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="restockForm" onsubmit="event.preventDefault(); submitRestock();">
            <div class="mb-3">
              <label class="form-label small fw-bold">Outlet Cabang Penerima</label>
              <select id="restockOutletSelect" class="form-select form-select-sm">
                <?php foreach ($outlets as $o): ?>
                  <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Pilih Bahan Baku</label>
              <select id="restockIngredientSelect" class="form-select form-select-sm" onchange="onRestockIngredientChange()">
                <!-- Dynamic via app.js -->
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Jumlah Masuk (<span id="restockUnitLabel">gram</span>)</label>
              <input type="number" id="restockQtyInput" class="form-control form-control-sm" placeholder="Contoh: 1000" step="any" required>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Perbarui Harga Beli per Unit (Opsional)</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">Rp</span>
                <input type="number" id="restockNewCostInput" class="form-control" placeholder="Biarkan kosong bila tidak berubah" step="any">
              </div>
              <small class="text-muted">Jika diubah, HPP seluruh menu yang memakai bahan ini akan otomatis terkalkulasi ulang!</small>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Catatan / No. Faktur Supplier</label>
              <input type="text" id="restockNotesInput" class="form-control form-control-sm" placeholder="Contoh: Faktur Supplier Kopi Gayo #9921">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary btn-sm" onclick="submitRestock()">Simpan Restok</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const currentPhpUser = <?= json_encode($currentUser) ?>;
    // Otomatis buka tab alert jika ada hash #alertsTab di URL
    document.addEventListener('DOMContentLoaded', () => {
      if (window.location.hash === '#alertsTab') {
        const alertTabBtn = document.getElementById('inv-alert-tab');
        if (alertTabBtn) {
          bootstrap.Tab.getOrCreateInstance(alertTabBtn).show();
        }
      }
    });
  </script>
  <script src="public/app.js"></script>
</body>
</html>

