<?php
/**
 * Modul Inventori, Mutasi Stok & Critical Stock Alert (F04, F05 CRUD)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 * Pure Semantic HTML5 & CSS3
 */
require_once __DIR__ . '/includes/auth.php';
init_default_session();
$currentUser = current_user();

$db = getDb();
$stmtOutlets = $db->query('SELECT * FROM outlets WHERE is_active = 1 ORDER BY id ASC');
$outlets = $stmtOutlets->fetchAll();

$isWarehouseOrManager = in_array($currentUser['role'], ['owner', 'manager', 'warehouse']);
$isManagerOrOwner = in_array($currentUser['role'], ['owner', 'manager']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Manajemen Inventori & Stok Bahan Baku (F04/F05 CRUD)</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-shell" style="overflow-y:auto;">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <main class="page-container">
    <!-- Sub Nav Tabs & Outlet Filter Strip -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
      <nav class="category-filter-strip" aria-label="Tab Inventori">
        <button class="filter-pill active" id="tabBtnInv" onclick="switchInvPane('invListPane', this)">
          📦 F04: Stok Bahan Baku
        </button>
        <button class="filter-pill" id="tabBtnAlert" onclick="switchInvPane('invAlertPane', this)" style="color:var(--color-danger);">
          ⚠️ F05: Peringatan Stok Kritis
        </button>
      </nav>

      <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:6px;">
          <span style="font-size:12px; font-weight:700; color:var(--color-text-primary);">Cabang:</span>
          <select id="inventoryOutletFilter" class="form-control-native" style="width:auto; min-width:200px;" onchange="loadInventory()">
            <?php foreach ($outlets as $o): ?>
              <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if ($isWarehouseOrManager): ?>
          <button class="btn-checkout-primary" style="padding:7px 14px; font-size:12px;" onclick="openModal('createIngredientModal')">
            ➕ Tambah Bahan Baku
          </button>
          <button class="filter-pill" style="padding:7px 14px; font-size:12px; color:var(--color-primary); font-weight:700;" onclick="openModal('restockModal')">
            📥 Restok Barang Masuk
          </button>
        <?php else: ?>
          <button class="filter-pill" disabled style="opacity:0.5; cursor:not-allowed;" title="Hanya Staf Gudang, Manajer, atau Owner yang memiliki hak kelola inventori">
            🔒 Kelola Stok Terkunci (RBAC)
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- PANE 1: STOK BAHAN BAKU -->
    <section id="invListPane" class="data-card">
      <header class="data-card__header">
        <div>
          <h2>📦 Manajemen Stok Bahan Baku Multi-Outlet</h2>
          <p style="font-size:12px; color:var(--color-text-muted);">
            Stok berkurang otomatis saat POS checkout berdasarkan BOM dan bertambah saat restok/opname dicatat
          </p>
        </div>
      </header>
      <div class="table-responsive">
        <table class="native-table">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Nama Bahan Baku</th>
              <th>Satuan</th>
              <th>Harga Beli / Unit</th>
              <th>Stok Saat Ini</th>
              <th>Batas Min (Alert F05)</th>
              <th>Status</th>
              <th>Aksi Pengelolaan</th>
            </tr>
          </thead>
          <tbody id="inventoryTableBody">
            <!-- Dynamic via app.js -->
          </tbody>
        </table>
      </div>
    </section>

    <!-- PANE 2: PERINGATAN STOK KRITIS -->
    <section id="invAlertPane" class="data-card" style="display:none;">
      <header class="data-card__header" style="background-color:var(--color-danger-bg); border-color:var(--color-danger-border);">
        <h2 style="color:var(--color-danger);">⚠️ Critical Stock Alert System (F05)</h2>
        <button class="filter-pill" onclick="loadCriticalAlerts()">🔄 Periksa Stok</button>
      </header>
      <div style="padding:20px;" id="alertsContainer">
        <!-- Dynamic via app.js -->
      </div>
    </section>
  </main>

  <!-- ====================================================================
       MODAL 1: TAMBAH BAHAN BAKU BARU (F04 CRUD)
       ==================================================================== -->
  <div class="native-modal" id="createIngredientModal">
    <div class="native-modal-backdrop" onclick="closeModal('createIngredientModal')"></div>
    <div class="native-modal-dialog">
      <header class="native-modal-header">
        <h3>➕ Tambah Bahan Baku Baru</h3>
        <button class="btn-close-modal" onclick="closeModal('createIngredientModal')">&times;</button>
      </header>
      <div class="native-modal-body">
        <form id="createIngredientForm" onsubmit="event.preventDefault(); submitCreateIngredient();" style="display:flex; flex-direction:column; gap:12px;">
          <div class="form-group-custom">
            <label for="newIngName">Nama Bahan Baku *</label>
            <input type="text" id="newIngName" class="form-control-native" placeholder="Contoh: Biji Kopi Flores Bajawa" required>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="form-group-custom">
              <label for="newIngSku">Kode SKU (Opsional)</label>
              <input type="text" id="newIngSku" class="form-control-native" placeholder="ING-XX (Auto-generate)">
            </div>
            <div class="form-group-custom">
              <label for="newIngUnit">Satuan Takaran *</label>
              <select id="newIngUnit" class="form-control-native" required>
                <option value="gram">gram (Kopi, Bubuk)</option>
                <option value="ml">ml (Susu, Sirup, Cairan)</option>
                <option value="pcs">pcs (Cup, Lid, Pastry)</option>
                <option value="sachet">sachet</option>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="form-group-custom">
              <label for="newIngCost">Harga Beli Supplier / Satuan (Rp) *</label>
              <input type="number" id="newIngCost" class="form-control-native" placeholder="Contoh: 280" min="0" step="any" required>
            </div>
            <div class="form-group-custom">
              <label for="newIngMinAlert">Batas Minimum Alert (F05) *</label>
              <input type="number" id="newIngMinAlert" class="form-control-native" placeholder="Contoh: 500" min="0" step="any" required>
            </div>
          </div>

          <div class="form-group-custom">
            <label for="newIngInitialStock">Stok Awal di Cabang Terpilih</label>
            <input type="number" id="newIngInitialStock" class="form-control-native" placeholder="Contoh: 2000" min="0" step="any" value="1000">
            <small style="color:var(--color-text-muted); font-size:11px;">Stok awal akan dicatat sebagai saldo perdana cabang.</small>
          </div>
        </form>
      </div>
      <footer class="native-modal-footer">
        <button type="button" class="filter-pill" onclick="closeModal('createIngredientModal')">Batal</button>
        <button type="button" class="btn-checkout-primary" style="padding:6px 14px; font-size:12px;" onclick="submitCreateIngredient()">
          💾 Simpan Bahan Baku
        </button>
      </footer>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 2: EDIT BAHAN BAKU & KOREKSI STOK OPNAME (F04 CRUD)
       ==================================================================== -->
  <div class="native-modal" id="editIngredientModal">
    <div class="native-modal-backdrop" onclick="closeModal('editIngredientModal')"></div>
    <div class="native-modal-dialog">
      <header class="native-modal-header">
        <h3>✏️ Edit Bahan Baku & Penyesuaian Stok Opname</h3>
        <button class="btn-close-modal" onclick="closeModal('editIngredientModal')">&times;</button>
      </header>
      <div class="native-modal-body">
        <form id="editIngredientForm" onsubmit="event.preventDefault(); submitEditIngredient();" style="display:flex; flex-direction:column; gap:12px;">
          <input type="hidden" id="editIngId" value="0">

          <div class="form-group-custom">
            <label for="editIngName">Nama Bahan Baku *</label>
            <input type="text" id="editIngName" class="form-control-native" required>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="form-group-custom">
              <label for="editIngSku">Kode SKU</label>
              <input type="text" id="editIngSku" class="form-control-native" required>
            </div>
            <div class="form-group-custom">
              <label for="editIngUnit">Satuan Takaran *</label>
              <input type="text" id="editIngUnit" class="form-control-native" placeholder="gram, ml, pcs" required>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="form-group-custom">
              <label for="editIngCost">Harga Beli Supplier / Unit (Rp) *</label>
              <input type="number" id="editIngCost" class="form-control-native" min="0" step="any" required>
              <small style="color:var(--color-text-muted); font-size:10px;">Perubahan harga akan mengupdate HPP menu terkait!</small>
            </div>
            <div class="form-group-custom">
              <label for="editIngMinAlert">Batas Min Alert F05 *</label>
              <input type="number" id="editIngMinAlert" class="form-control-native" min="0" step="any" required>
              <small style="color:var(--color-text-muted); font-size:10px;">Pemicu badge merah saat stok &le; batas ini</small>
            </div>
          </div>

          <!-- KOREKSI STOK OPNAME -->
          <div style="background:var(--color-surface); border:1px dashed var(--color-border-accent); border-radius:var(--radius-md); padding:12px; margin-top:4px;">
            <strong style="color:var(--color-primary-dark); font-size:12px;">📋 Penyesuaian Fisik / Stock Opname (Cabang Aktif)</strong>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
              <div class="form-group-custom">
                <label for="editIngCurrentStock">Stok Fisik Riil Terkini</label>
                <input type="number" id="editIngCurrentStock" class="form-control-native" step="any">
              </div>
              <div class="form-group-custom">
                <label for="editIngNotes">Catatan Penyesuaian</label>
                <input type="text" id="editIngNotes" class="form-control-native" placeholder="Opname Rutin / Rusak / Dll">
              </div>
            </div>
          </div>
        </form>
      </div>
      <footer class="native-modal-footer">
        <button type="button" class="filter-pill" onclick="closeModal('editIngredientModal')">Batal</button>
        <button type="button" class="btn-checkout-primary" style="padding:6px 14px; font-size:12px;" onclick="submitEditIngredient()">
          💾 Simpan Perubahan
        </button>
      </footer>
    </div>
  </div>

  <!-- ====================================================================
       MODAL 3: RESTOCK INVENTORY (PENCATATAN BARANG MASUK)
       ==================================================================== -->
  <div class="native-modal" id="restockModal">
    <div class="native-modal-backdrop" onclick="closeModal('restockModal')"></div>
    <div class="native-modal-dialog">
      <header class="native-modal-header">
        <h3>📥 Catat Barang Masuk (Restock Bahan Baku)</h3>
        <button class="btn-close-modal" onclick="closeModal('restockModal')">&times;</button>
      </header>
      <div class="native-modal-body">
        <form id="restockForm" onsubmit="event.preventDefault(); submitRestock();" style="display:flex; flex-direction:column; gap:12px;">
          <div class="form-group-custom">
            <label for="restockOutletSelect">Outlet Cabang Penerima</label>
            <select id="restockOutletSelect" class="form-control-native">
              <?php foreach ($outlets as $o): ?>
                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group-custom">
            <label for="restockIngredientSelect">Pilih Bahan Baku</label>
            <select id="restockIngredientSelect" class="form-control-native" onchange="onRestockIngredientChange()">
              <!-- Dynamic via app.js -->
            </select>
          </div>

          <div class="form-group-custom">
            <label for="restockQtyInput">Jumlah Masuk (<span id="restockUnitLabel">gram</span>)</label>
            <input type="number" id="restockQtyInput" class="form-control-native" placeholder="Contoh: 1000" step="any" required>
          </div>

          <div class="form-group-custom">
            <label for="restockNewCostInput">Perbarui Harga Beli per Unit (Opsional)</label>
            <input type="number" id="restockNewCostInput" class="form-control-native" placeholder="Biarkan kosong bila tidak berubah" step="any">
            <small style="color:var(--color-text-muted); font-size:11px;">Jika diubah, HPP seluruh menu terkait akan otomatis terkalkulasi ulang!</small>
          </div>

          <div class="form-group-custom">
            <label for="restockNotesInput">Catatan / No. Faktur Supplier</label>
            <input type="text" id="restockNotesInput" class="form-control-native" placeholder="Contoh: Faktur Supplier Kopi Gayo #9921">
          </div>
        </form>
      </div>
      <footer class="native-modal-footer">
        <button type="button" class="filter-pill" onclick="closeModal('restockModal')">Batal</button>
        <button type="button" class="btn-checkout-primary" style="padding:6px 14px; font-size:12px;" onclick="submitRestock()">Simpan Restok</button>
      </footer>
    </div>
  </div>

  <script>
    const currentPhpUser = <?= json_encode($currentUser) ?>;

    function switchInvPane(paneId, btn) {
      document.querySelectorAll('.category-filter-strip .filter-pill').forEach(el => el.classList.remove('active'));
      btn.classList.add('active');

      document.getElementById('invListPane').style.display = paneId === 'invListPane' ? 'block' : 'none';
      document.getElementById('invAlertPane').style.display = paneId === 'invAlertPane' ? 'block' : 'none';

      if (paneId === 'invAlertPane') {
        loadCriticalAlerts();
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      if (window.location.hash === '#alertsTab') {
        const btn = document.getElementById('tabBtnAlert');
        if (btn) switchInvPane('invAlertPane', btn);
      }
    });
  </script>
  <script src="public/app.js"></script>
</body>
</html>
