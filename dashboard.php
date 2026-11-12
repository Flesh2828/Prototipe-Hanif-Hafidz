<?php
/**
 * Dashboard Laba-Rugi & Konsolidasi Multi-Outlet (F06)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 */
require_once __DIR__ . '/includes/auth.php';
init_default_session();
$currentUser = current_user();

$db = getDb();
$stmtOutlets = $db->query('SELECT * FROM outlets WHERE is_active = 1 ORDER BY id ASC');
$outlets = $stmtOutlets->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Dashboard Laba-Rugi & Konsolidasi Multi-Outlet (F06)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body class="bg-light">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="container-fluid pb-4">
    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="fw-bold"><i class="bi bi-funnel-fill text-primary"></i> Filter Outlet:</span>
          <select id="dashboardOutletFilter" class="form-select form-select-sm" onchange="loadDashboardStats()">
            <option value="all">Konsolidasi Seluruh Cabang (Owner View)</option>
            <?php foreach ($outlets as $o): ?>
              <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <button class="btn btn-sm btn-outline-secondary" onclick="loadDashboardStats()"><i class="bi bi-arrow-clockwise"></i> Segarkan Data</button>
        </div>
      </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white p-3">
          <small class="text-white-50 text-uppercase fw-bold">Total Omzet Penjualan</small>
          <h3 class="fw-bold my-1" id="dashOmzet">Rp 0</h3>
          <small class="text-white-50"><i class="bi bi-graph-up"></i> Real-time multi-outlet MySQL</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white p-3">
          <small class="text-white-50 text-uppercase fw-bold">Total HPP (Biaya Bahan)</small>
          <h3 class="fw-bold my-1" id="dashHpp">Rp 0</h3>
          <small class="text-white-50"><i class="bi bi-calculator"></i> Berdasarkan BOM otomatis</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white p-3">
          <small class="text-white-50 text-uppercase fw-bold">Laba Kotor (Gross Profit)</small>
          <h3 class="fw-bold my-1" id="dashProfit">Rp 0</h3>
          <small class="text-white-50"><span id="dashMarginPct">Margin: 0%</span></small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-dark text-white p-3">
          <small class="text-white-50 text-uppercase fw-bold">Total Transaksi</small>
          <h3 class="fw-bold my-1" id="dashTransactions">0 Order</h3>
          <small class="text-white-50">SLA Kasir &le; 1,5s tercapai</small>
        </div>
      </div>
    </div>

    <!-- Performance Comparison & Recent Orders -->
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-bar-chart-fill text-primary"></i> Performa Omzet & Laba Per Cabang
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Cabang Outlet</th>
                    <th>Pesanan</th>
                    <th>Omzet</th>
                    <th>HPP</th>
                    <th>Laba Kotor</th>
                  </tr>
                </thead>
                <tbody id="outletPerformanceTableBody">
                  <!-- Dynamic via app.js -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-clock-history text-secondary"></i> 10 Transaksi Penjualan Terakhir
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No. Invoice</th>
                    <th>Outlet</th>
                    <th>Pembayaran</th>
                    <th>Omzet</th>
                    <th>Laba</th>
                  </tr>
                </thead>
                <tbody id="recentOrdersTableBody">
                  <!-- Dynamic via app.js -->
                </tbody>
              </table>
            </div>
          </div>
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

