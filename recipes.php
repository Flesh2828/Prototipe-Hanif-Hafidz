<?php
/**
 * Resep & Bill of Materials (BOM) & HPP Dinamis (F03)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 */
require_once __DIR__ . '/includes/auth.php';
init_default_session();
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Resep & BOM HPP Dinamis (F03)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body class="bg-light">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="container-fluid pb-4">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0 fw-bold"><i class="bi bi-journal-check text-success"></i> Bill of Materials (BOM) & Perhitungan HPP Dinamis</h5>
          <small class="text-muted">HPP otomatis terkalkulasi per porsi saat harga beli bahan baku supplier diperbarui</small>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Kode</th>
                <th>Nama Menu</th>
                <th>Kategori</th>
                <th>Harga Jual</th>
                <th>HPP Otomatis</th>
                <th>Margin Kotor</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="recipeMenuTableBody">
              <!-- Dynamic via app.js -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: RECIPE / BOM DETAIL -->
  <div class="modal fade" id="recipeModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold" id="recipeModalTitle">Komposisi Resep & BOM</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead class="table-light">
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
              <tfoot class="table-light">
                <tr>
                  <th colspan="3">Total Estimasi HPP per Porsi:</th>
                  <th id="recipeModalTotalHpp" class="text-danger">Rp 0</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const currentPhpUser = <?= json_encode($currentUser) ?>;
  </script>
  <script src="public/app.js"></script>
</body>
</html>

