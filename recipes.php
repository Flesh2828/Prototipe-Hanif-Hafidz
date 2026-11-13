<?php
/**
 * Resep & Bill of Materials (BOM) & HPP Dinamis (F03 CRUD)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 * Pure Semantic HTML5 & CSS3
 */
require_once __DIR__ . '/includes/auth.php';
init_default_session();
$currentUser = current_user();

$db = getDb();
$stmtCats = $db->query('SELECT * FROM categories ORDER BY id ASC');
$categories = $stmtCats->fetchAll();

$stmtIngs = $db->query('SELECT * FROM ingredients ORDER BY name ASC');
$ingredients = $stmtIngs->fetchAll();

$isManagerOrOwner = in_array($currentUser['role'], ['owner', 'manager']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Manajemen Menu & Resep BOM (F03 CRUD)</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-shell" style="overflow-y:auto;">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <main class="page-container">
    <section class="data-card">
      <header class="data-card__header" style="flex-wrap:wrap; gap:12px;">
        <div>
          <h2>📖 Manajemen Menu & Bill of Materials (BOM)</h2>
          <p style="font-size:12px; color:var(--color-text-muted); margin-top:2px;">
            Kelola katalog menu jual, harga, serta pemetaan gramasi bahan baku untuk kalkulasi HPP dinamis otomatis
          </p>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
          <?php if ($isManagerOrOwner): ?>
            <button class="btn-checkout-primary" style="padding:8px 16px; font-size:13px;" onclick="openCreateMenuModal()">
              ➕ Tambah Menu Baru
            </button>
          <?php else: ?>
            <button class="filter-pill" disabled style="opacity:0.5; cursor:not-allowed;" title="Hanya Manajer Outlet & Owner yang dapat menambah/mengubah menu">
              🔒 Tambah Menu Terkunci (RBAC)
            </button>
          <?php endif; ?>
        </div>
      </header>

      <div class="table-responsive">
        <table class="native-table">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama Menu</th>
              <th>Kategori</th>
              <th>Harga Jual</th>
              <th>HPP Otomatis</th>
              <th>Margin Kotor</th>
              <th>Aksi Pengelolaan</th>
            </tr>
          </thead>
          <tbody id="recipeMenuTableBody">
            <!-- Dynamic via app.js -->
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- ====================================================================
       MODAL: FORM TAMBAH / EDIT MENU BARU & RESEP BOM (F03 CRUD)
       ==================================================================== -->
  <div class="native-modal" id="menuFormModal">
    <div class="native-modal-backdrop" onclick="closeModal('menuFormModal')"></div>
    <div class="native-modal-dialog native-modal-dialog--lg">
      <header class="native-modal-header">
        <h3 id="menuFormModalTitle">➕ Tambah Menu Baru & Resep BOM</h3>
        <button class="btn-close-modal" onclick="closeModal('menuFormModal')">&times;</button>
      </header>
      <div class="native-modal-body">
        <form id="menuForm" onsubmit="event.preventDefault(); submitMenuForm();" style="display:flex; flex-direction:column; gap:16px;">
          <input type="hidden" id="menuFormId" value="0">

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
            <div class="form-group-custom">
              <label for="menuFormName">Nama Menu / Produk *</label>
              <input type="text" id="menuFormName" class="form-control-native" placeholder="Contoh: Kopi Susu Pandan Aren" required>
            </div>

            <div class="form-group-custom">
              <label for="menuFormCategory">Kategori Menu *</label>
              <select id="menuFormCategory" class="form-control-native" required>
                <?php foreach ($categories as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group-custom">
              <label for="menuFormCode">Kode Menu / SKU (Opsional)</label>
              <input type="text" id="menuFormCode" class="form-control-native" placeholder="Contoh: PRD-07 (Kosongkan utk auto-generate)">
            </div>

            <div class="form-group-custom">
              <label for="menuFormPrice">Harga Jual Konsumen (Rp) *</label>
              <input type="number" id="menuFormPrice" class="form-control-native" placeholder="Contoh: 22000" min="0" step="100" required oninput="recalculateModalHpp()">
            </div>
          </div>

          <div class="form-group-custom">
            <label for="menuFormDesc">Deskripsi Menu (Opsional)</label>
            <input type="text" id="menuFormDesc" class="form-control-native" placeholder="Contoh: Signature espresso dengan ekstrak daun pandan alami dan gula aren murni">
          </div>

          <!-- RESEP BOM BUILDER -->
          <div style="border-top:1px dashed var(--color-border); padding-top:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <div>
                <strong style="color:var(--color-primary-dark); font-size:13px;">📋 Komposisi Bahan Baku (Bill of Materials / BOM)</strong>
                <p style="font-size:11px; color:var(--color-text-muted);">Masukkan takaran bahan baku per porsi (gram, ml, atau pcs) untuk menghitung HPP otomatis</p>
              </div>
              <button type="button" class="filter-pill" style="color:var(--color-primary); font-weight:700;" onclick="addRecipeIngredientRow()">
                ➕ Tambah Bahan
              </button>
            </div>

            <div id="menuFormRecipeContainer" style="display:flex; flex-direction:column; gap:8px; max-height:220px; overflow-y:auto; padding-right:4px;">
              <!-- Dynamic rows via app.js -->
            </div>

            <!-- LIVE HPP & MARGIN ESTIMATOR -->
            <div style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:12px; margin-top:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
              <div>
                <div style="font-size:11px; text-transform:uppercase; color:var(--color-text-muted); font-weight:700;">Estimasi Total HPP per Porsi:</div>
                <div id="menuFormLiveHpp" style="font-size:18px; font-weight:800; color:var(--color-danger);">Rp 0</div>
              </div>
              <div style="text-align:right;">
                <div style="font-size:11px; text-transform:uppercase; color:var(--color-text-muted); font-weight:700;">Estimasi Margin Kotor:</div>
                <div id="menuFormLiveMargin" style="font-size:18px; font-weight:800; color:var(--color-success);">Rp 0 (0%)</div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <footer class="native-modal-footer">
        <button type="button" class="filter-pill" onclick="closeModal('menuFormModal')">Batal</button>
        <button type="button" class="btn-checkout-primary" style="padding:7px 16px; font-size:13px;" onclick="submitMenuForm()">
          💾 Simpan Menu & Resep
        </button>
      </footer>
    </div>
  </div>

  <!-- ====================================================================
       MODAL: DETAIL KOMPOSISI RESEP BOM (VIEW ONLY)
       ==================================================================== -->
  <div class="native-modal" id="recipeModal">
    <div class="native-modal-backdrop" onclick="closeModal('recipeModal')"></div>
    <div class="native-modal-dialog">
      <header class="native-modal-header">
        <h3 id="recipeModalTitle">Komposisi Resep & BOM</h3>
        <button class="btn-close-modal" onclick="closeModal('recipeModal')">&times;</button>
      </header>
      <div class="native-modal-body">
        <div class="table-responsive">
          <table class="native-table">
            <thead>
              <tr>
                <th>Bahan Baku</th>
                <th>Takaran Porsi</th>
                <th>Biaya Satuan</th>
                <th>Subtotal HPP</th>
              </tr>
            </thead>
            <tbody id="recipeModalBody">
              <!-- Dynamic via app.js -->
            </tbody>
            <tfoot>
              <tr style="background:var(--color-surface); font-weight:700;">
                <td colspan="3">Total Estimasi HPP per Porsi:</td>
                <td id="recipeModalTotalHpp" style="color:var(--color-danger); font-size:15px;">Rp 0</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <footer class="native-modal-footer">
        <button type="button" class="filter-pill" onclick="closeModal('recipeModal')">Tutup</button>
      </footer>
    </div>
  </div>

  <script>
    const currentPhpUser = <?= json_encode($currentUser) ?>;
    // Data bahan baku awal untuk select option di JS
    const initialIngredientsData = <?= json_encode($ingredients) ?>;
    const initialCategoriesData = <?= json_encode($categories) ?>;
  </script>
  <script src="public/app.js"></script>
</body>
</html>
