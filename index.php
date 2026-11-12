<?php
/**
 * Halaman Utama & Autentikasi RBAC (F01)
 * Sesuai PRD KopiKita Bagian 7.1 (PHP Native 8.x & MySQL XAMPP)
 */
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

// Handle Switch Role Instant (Simulasi PRD)
if (isset($_GET['switch_role'])) {
    $role = $_GET['switch_role'];
    $user = switch_user_by_role($role);
    if ($user) {
        $success = "Berhasil beralih ke peran: {$user['full_name']} (" . strtoupper($role) . ")";
    } else {
        $error = "Peran {$role} tidak ditemukan.";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (session_id()) {
        session_destroy();
    }
    header('Location: index.php');
    exit;
}

// Handle Form Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $user = login_user($username, $password);
        if ($user) {
            // Redirect sesuai peran utama
            if (in_array($user['role'], ['cashier'])) {
                header('Location: pos.php');
            } elseif (in_array($user['role'], ['warehouse'])) {
                header('Location: inventory.php');
            } else {
                header('Location: pos.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah! Coba gunakan username: owner, manager1, kasir1, dll.';
        }
    } else {
        $error = 'Silakan isi username dan password.';
    }
}

init_default_session();
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiKita - Autentikasi RBAC & Portal Masuk (F01)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body class="bg-light">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="container py-4">
    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">
      <!-- Kolom Kiri: Sesi Aktif & Form Login -->
      <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge-fill text-warning"></i> Sesi Pengguna Aktif</h5>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle bg-warning p-3 text-dark me-3">
                <i class="bi bi-person-fill fs-2"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0"><?= htmlspecialchars($currentUser['full_name']) ?></h5>
                <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($currentUser['role']) ?></span>
                <span class="badge bg-secondary"><?= htmlspecialchars($currentUser['outlet_name']) ?></span>
              </div>
            </div>

            <div class="d-grid gap-2">
              <a href="pos.php" class="btn btn-primary fw-bold">
                <i class="bi bi-calculator"></i> Buka Antarmuka POS Kasir (F02)
              </a>
              <a href="dashboard.php" class="btn btn-outline-dark">
                <i class="bi bi-graph-up-arrow"></i> Buka Dashboard Laba-Rugi (F06)
              </a>
              <a href="inventory.php" class="btn btn-outline-secondary">
                <i class="bi bi-box-seam"></i> Buka Inventori & Stok Kritis (F04/F05)
              </a>
            </div>
          </div>
        </div>

        <!-- Form Login Manual -->
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-box-arrow-in-right text-primary"></i> Login Manual Pengguna</h6>
          </div>
          <div class="card-body">
            <form method="POST" action="index.php">
              <div class="mb-3">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Contoh: owner, kasir1, gudang1" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" value="password123" required>
                <small class="text-muted">Password default pengujian: <code>password123</code></small>
              </div>
              <button type="submit" class="btn btn-dark w-100 fw-bold">
                <i class="bi bi-box-arrow-in-right"></i> Masuk Sistem
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Simulasi 6 Peran Pengguna & RBAC Matrix -->
      <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-lightning-charge-fill text-warning"></i> 1-Click Simulasi 6 Peran Pengguna</h5>
            <span class="badge bg-success">F01 PRD Compliant</span>
          </div>
          <div class="card-body">
            <p class="small text-muted mb-3">Klik peran di bawah untuk menguji pembatasan wewenang (RBAC) secara instan:</p>
            <div class="row g-2">
              <div class="col-sm-6">
                <a href="index.php?switch_role=owner" class="btn btn-outline-primary text-start w-100 p-2">
                  <div class="fw-bold"><i class="bi bi-person-check-fill"></i> 1. Owner (Hafidz)</div>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Akses penuh seluruh outlet & analisa laba</small>
                </a>
              </div>
              <div class="col-sm-6">
                <a href="index.php?switch_role=manager" class="btn btn-outline-success text-start w-100 p-2">
                  <div class="fw-bold"><i class="bi bi-briefcase-fill"></i> 2. Manajer (Hanif)</div>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Kelola cabang Kaliurang, BOM & stok</small>
                </a>
              </div>
              <div class="col-sm-6">
                <a href="index.php?switch_role=cashier" class="btn btn-outline-warning text-dark text-start w-100 p-2">
                  <div class="fw-bold"><i class="bi bi-calculator"></i> 3. Kasir (Dzaky)</div>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Akses POS transaksi cabang Kaliurang</small>
                </a>
              </div>
              <div class="col-sm-6">
                <a href="index.php?switch_role=barista" class="btn btn-outline-info text-start w-100 p-2">
                  <div class="fw-bold"><i class="bi bi-cup-hot-fill"></i> 4. Barista (Rafie)</div>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Melihat panduan takaran BOM resep</small>
                </a>
              </div>
              <div class="col-sm-6">
                <a href="index.php?switch_role=warehouse" class="btn btn-outline-secondary text-start w-100 p-2">
                  <div class="fw-bold"><i class="bi bi-boxes"></i> 5. Gudang (Imad)</div>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Akses mutasi stok & restok supplier</small>
                </a>
              </div>
              <div class="col-sm-6">
                <a href="index.php?switch_role=accountant" class="btn btn-outline-danger text-start w-100 p-2">
                  <div class="fw-bold"><i class="bi bi-file-earmark-bar-graph"></i> 6. Auditor (Siti)</div>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Audit laporan keuangan read-only</small>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Matriks RBAC PRD -->
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white py-3 fw-bold">
            <i class="bi bi-shield-lock-fill text-primary"></i> Matriks Hak Akses RBAC (PRD KopiKita)
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle mb-0 text-center">
                <thead class="table-light">
                  <tr>
                    <th class="text-start">Peran Pengguna</th>
                    <th>POS Kasir</th>
                    <th>Inventori & Restok</th>
                    <th>Resep BOM & HPP</th>
                    <th>Dashboard Laba-Rugi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-start fw-bold">Owner</td>
                    <td class="text-success"><i class="bi bi-check-circle-fill"></i></td>
                    <td class="text-success"><i class="bi bi-check-circle-fill"></i></td>
                    <td class="text-success"><i class="bi bi-check-circle-fill"></i></td>
                    <td class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Konsolidasi</td>
                  </tr>
                  <tr>
                    <td class="text-start fw-bold">Manajer Outlet</td>
                    <td class="text-success"><i class="bi bi-check-circle-fill"></i></td>
                    <td class="text-success"><i class="bi bi-check-circle-fill"></i></td>
                    <td class="text-success"><i class="bi bi-check-circle-fill"></i></td>
                    <td class="text-primary">Cabang Sendiri</td>
                  </tr>
                  <tr>
                    <td class="text-start fw-bold">Kasir</td>
                    <td class="text-success fw-bold text-primary"><i class="bi bi-check-circle-fill"></i> Akses Utama</td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                  </tr>
                  <tr>
                    <td class="text-start fw-bold">Barista</td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                    <td class="text-warning">Lihat Stok</td>
                    <td class="text-warning">Lihat Takaran</td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                  </tr>
                  <tr>
                    <td class="text-start fw-bold">Staf Gudang</td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                    <td class="text-success fw-bold text-primary"><i class="bi bi-check-circle-fill"></i> Kelola Mutasi</td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i></td>
                  </tr>
                  <tr>
                    <td class="text-start fw-bold">Akuntan / Auditor</td>
                    <td class="text-secondary">Read-only</td>
                    <td class="text-secondary">Read-only</td>
                    <td class="text-secondary">Read-only</td>
                    <td class="text-success fw-bold text-success"><i class="bi bi-check-circle-fill"></i> Audit</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

