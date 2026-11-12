<?php
/**
 * Antarmuka Kasir POS Multi-Outlet (F02)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 */
require_once __DIR__ . '/includes/auth.php';
init_default_session();
$currentUser = current_user();

$db = getDb();
$stmtCats = $db->query('SELECT * FROM categories ORDER BY id ASC');
$categories = $stmtCats->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Kasir POS Multi-Outlet (F02)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body class="bg-light">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="container-fluid pb-4">
    <!-- Alert Flash Error if any -->
    <?php if (isset($_SESSION['flash_error'])): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="row g-3">
      <!-- Kolom Kiri: Menu Grid -->
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h5 class="mb-0 fw-bold"><i class="bi bi-grid-fill text-warning"></i> Menu Kopi & Makanan</h5>
              <small class="text-muted">Pilih produk untuk ditambahkan ke kasir</small>
            </div>
            <div class="d-flex gap-2">
              <input type="text" id="searchMenuInput" class="form-control form-control-sm" placeholder="Cari menu..." onkeyup="filterMenu()">
              <select id="categoryFilter" class="form-select form-select-sm" onchange="filterMenu()">
                <option value="all">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="card-body">
            <div class="row row-cols-2 row-cols-md-3 g-3" id="menuGrid">
              <!-- Diisi secara dinamis via JavaScript dari API MySQL -->
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Keranjang & Checkout -->
      <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
          <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fs-6 fw-bold"><i class="bi bi-cart3 text-warning"></i> Keranjang Transaksi</h5>
            <button class="btn btn-outline-danger btn-sm py-0" onclick="clearCart()"><i class="bi bi-trash"></i> Kosongkan</button>
          </div>
          <div class="card-body p-0">
            <!-- Item List -->
            <div id="cartItemsList" class="p-3" style="max-height: 280px; overflow-y: auto;">
              <p class="text-center text-muted my-4"><i class="bi bi-cart-x fs-1 d-block"></i>Keranjang masih kosong</p>
            </div>

            <!-- Cart Summary -->
            <div class="bg-light p-3 border-top">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Total HPP (Est. Biaya Bahan)</span>
                <span id="cartTotalHpp" class="fw-semibold text-danger">Rp 0</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Estimasi Margin Laba</span>
                <span id="cartEstimatedMargin" class="fw-semibold text-success">Rp 0 (0%)</span>
              </div>
              <hr class="my-2">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold fs-5">Total Pembayaran</span>
                <span id="cartTotalAmount" class="fw-bold fs-4 text-primary">Rp 0</span>
              </div>

              <!-- Payment Form -->
              <div class="mb-3">
                <label class="form-label small fw-bold">Nama Pelanggan (Opsional)</label>
                <input type="text" id="custNameInput" class="form-control form-control-sm" placeholder="Pelanggan Walk-in" value="Pelanggan Walk-in">
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Metode Pembayaran</label>
                <div class="btn-group w-100" role="group">
                  <input type="radio" class="btn-check" name="paymentMethod" id="payCash" value="cash" checked onchange="togglePaymentMethod()">
                  <label class="btn btn-outline-success btn-sm" for="payCash"><i class="bi bi-cash-stack"></i> Tunai (Cash)</label>

                  <input type="radio" class="btn-check" name="paymentMethod" id="payQris" value="qris_sandbox" onchange="togglePaymentMethod()">
                  <label class="btn btn-outline-primary btn-sm" for="payQris"><i class="bi bi-qr-code"></i> QRIS Sandbox</label>
                </div>
              </div>

              <div id="cashInputGroup" class="mb-3">
                <label class="form-label small fw-bold">Nominal Uang Diterima</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">Rp</span>
                  <input type="number" id="cashGivenInput" class="form-control" placeholder="0" oninput="calculateChange()">
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <small class="text-muted">Kembalian:</small>
                  <small id="changeDueText" class="fw-bold text-dark">Rp 0</small>
                </div>
              </div>

              <?php 
                $isAllowedCheckout = !in_array($currentUser['role'], ['barista', 'accountant']);
              ?>
              <button id="btnCheckout" class="btn btn-success w-100 py-2 fw-bold" onclick="processCheckout()" <?= !$isAllowedCheckout ? 'disabled title="Peran Anda tidak diizinkan melakukan transaksi checkout"' : '' ?>>
                <i class="bi bi-check-circle-fill"></i> Simpan Transaksi & Potong Stok (BOM)
              </button>
              <?php if (!$isAllowedCheckout): ?>
                <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small text-center">
                  <i class="bi bi-exclamation-triangle"></i> Peran <strong><?= strtoupper($currentUser['role']) ?></strong> bersifat Read-Only untuk POS.
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: STRUK INVOICE / PRINT RECEIPT ESC/POS SIMULATION -->
  <div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-body p-3">
          <div id="receiptPrintArea" class="receipt-box font-monospace text-center">
            <h5 class="fw-bold mb-0">KOPIKITA</h5>
            <small id="receiptOutletName"><?= htmlspecialchars($currentUser['outlet_name']) ?></small>
            <div class="border-top border-bottom border-dark border-dashed my-2 py-1 text-start small">
              <div>Inv: <span id="receiptInvoiceNo">KPK-01-000000</span></div>
              <div>Waktu: <span id="receiptDate"><?= date('d/m/Y H:i') ?></span></div>
              <div>Kasir: <span id="receiptCashier"><?= htmlspecialchars($currentUser['full_name']) ?></span></div>
              <div>Customer: <span id="receiptCustomer">Pelanggan</span></div>
            </div>
            <table class="table table-borderless table-sm small text-start mb-2" id="receiptItemsTable">
              <!-- Dynamic -->
            </table>
            <div class="border-top border-dark border-dashed pt-1 small text-start">
              <div class="d-flex justify-content-between">
                <span>Total:</span> <strong id="receiptTotalAmount">Rp 0</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span>Bayar (<span id="receiptPaymentMethod">Cash</span>):</span> <span id="receiptCashGiven">Rp 0</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Kembali:</span> <span id="receiptChangeDue">Rp 0</span>
              </div>
              <hr class="my-1 border-dashed">
              <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                <span>Total HPP Bahan:</span> <span id="receiptTotalHpp">Rp 0</span>
              </div>
              <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                <span>Laba Kotor Order:</span> <span id="receiptGrossProfit">Rp 0</span>
              </div>
            </div>
            <div class="mt-3 text-center small text-muted">
              Terima Kasih atas Kunjungan Anda!<br>
              <em>Kopi Nikmat, Transaksi Halal Berkah</em>
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-primary btn-sm" onclick="printReceipt()"><i class="bi bi-printer"></i> Cetak Struk (ESC/POS)</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Injeksi state sesi pengguna dari PHP
    const currentPhpUser = <?= json_encode($currentUser) ?>;
  </script>
  <script src="public/app.js"></script>
</body>
</html>

