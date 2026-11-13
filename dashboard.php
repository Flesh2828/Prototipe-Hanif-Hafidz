<?php
/**
 * Dashboard Laba-Rugi & Konsolidasi Multi-Outlet (F06)
 * KopiKita - PHP Native 8.x & MySQL XAMPP
 * Pure Semantic HTML5 & CSS3
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
  <title>KopiKita - Dashboard Laba-Rugi & Konsolidasi (F06)</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-shell" style="overflow-y:auto;">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <main class="page-container">
    <!-- Filter Bar Ribbon -->
    <div class="data-card" style="margin-bottom:16px;">
      <div class="data-card__header" style="padding:12px 20px;">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
          <span style="font-weight:700; color:var(--color-text-primary);">📍 Filter Cabang Outlet:</span>
          <select id="dashboardOutletFilter" class="form-control-native" style="width:auto; min-width:260px;" onchange="loadDashboardStats()">
            <option value="all">Konsolidasi Seluruh Cabang (Owner View)</option>
            <?php foreach ($outlets as $o): ?>
              <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="filter-pill" onclick="loadDashboardStats()">
          🔄 Segarkan Data
        </button>
      </div>
    </div>

    <!-- KPI Metric Cards (Bento Architecture) -->
    <div class="kpi-grid">
      <div class="kpi-card kpi-card--primary">
        <span class="kpi-label">Total Omzet Penjualan</span>
        <div class="kpi-value" id="dashOmzet">Rp 0</div>
        <span class="kpi-sub">📈 Real-time Multi-Outlet MySQL</span>
      </div>

      <div class="kpi-card kpi-card--danger">
        <span class="kpi-label">Total HPP (Biaya Bahan Baku)</span>
        <div class="kpi-value" id="dashHpp" style="color:var(--color-danger);">Rp 0</div>
        <span class="kpi-sub">🧮 Terkalkulasi otomatis dari BOM</span>
      </div>

      <div class="kpi-card kpi-card--success">
        <span class="kpi-label">Laba Kotor (Gross Profit)</span>
        <div class="kpi-value" id="dashProfit" style="color:var(--color-success);">Rp 0</div>
        <span class="kpi-sub" id="dashMarginPct">Margin: 0%</span>
      </div>

      <div class="kpi-card kpi-card--dark">
        <span class="kpi-label">Total Pesanan Selesai</span>
        <div class="kpi-value" id="dashTransactions">0 Order</div>
        <span class="kpi-sub">⚡ SLA Respon POS &le; 1,5s tercapai</span>
      </div>
    </div>

    <!-- Performance Comparison & Recent Orders Grid -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(480px, 1fr)); gap:20px;">
      <!-- Tabel Performa Per Cabang -->
      <section class="data-card">
        <header class="data-card__header">
          <h3>📊 Performa Omzet & Laba Per Cabang</h3>
        </header>
        <div class="table-responsive">
          <table class="native-table">
            <thead>
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
      </section>

      <!-- Tabel 10 Transaksi Terakhir -->
      <section class="data-card">
        <header class="data-card__header">
          <h3>🕒 10 Transaksi Penjualan Terakhir</h3>
        </header>
        <div class="table-responsive">
          <table class="native-table">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Outlet</th>
                <th>Metode</th>
                <th>Total</th>
                <th>Laba</th>
              </tr>
            </thead>
            <tbody id="recentOrdersTableBody">
              <!-- Dynamic via app.js -->
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>

  <script>
    const currentPhpUser = <?= json_encode($currentUser) ?>;
  </script>
  <script src="public/app.js"></script>
</body>
</html>
