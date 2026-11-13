<?php
/**
 * Halaman Utama & Autentikasi RBAC (F01)
 * Sesuai PRD KopiKita Bagian 7.1 (PHP Native 8.x & MySQL XAMPP)
 * Pure Semantic HTML5 & CSS3 (Figma: ui kopikita)
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
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-shell" style="overflow-y:auto;">

  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <main class="page-container" style="max-width:1100px;">
    <?php if ($error): ?>
      <div class="native-alert native-alert--danger">
        <span>⚠️</span> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="native-alert native-alert--success">
        <span>✅</span> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:24px;">
      <!-- Kolom Kiri: Sesi Aktif & Form Login -->
      <section>
        <!-- Kartu Sesi Aktif -->
        <div class="data-card">
          <header class="data-card__header">
            <h3>👤 Sesi Pengguna Aktif</h3>
          </header>
          <div style="padding:20px;">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px;">
              <div class="user-avatar-circle" style="width:48px; height:48px; font-size:20px;">
                <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
              </div>
              <div>
                <h3 style="font-size:16px; font-weight:700; color:var(--color-text-primary); margin-bottom:2px;"><?= htmlspecialchars($currentUser['full_name']) ?></h3>
                <span class="status-pill" style="background:var(--color-primary-light); color:var(--color-primary-dark); border-color:transparent;">
                  Role: <?= strtoupper($currentUser['role']) ?>
                </span>
                <span class="status-pill" style="margin-left:4px;">
                  📍 <?= htmlspecialchars($currentUser['outlet_name']) ?>
                </span>
              </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
              <a href="pos.php" class="btn-checkout-primary" style="text-align:center; text-decoration:none;">
                🧾 Buka Antarmuka POS Kasir (F02)
              </a>
              <a href="dashboard.php" class="btn-add-item" style="padding:10px; text-align:center; text-decoration:none;">
                📊 Buka Dashboard Laba-Rugi (F06)
              </a>
              <a href="inventory.php" class="btn-add-item" style="padding:10px; text-align:center; text-decoration:none;">
                📦 Buka Inventori & Stok Kritis (F04/F05)
              </a>
            </div>
          </div>
        </div>

        <!-- Form Login Manual -->
        <div class="data-card">
          <header class="data-card__header">
            <h3>🔐 Login Manual Pengguna</h3>
          </header>
          <div style="padding:20px;">
            <form method="POST" action="index.php" style="display:flex; flex-direction:column; gap:14px;">
              <div class="form-group-custom">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control-native" placeholder="Contoh: owner, kasir1, gudang1" required>
              </div>

              <div class="form-group-custom">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control-native" value="password123" required>
                <small style="color:var(--color-text-muted); font-size:11px;">Password default pengujian: <code>password123</code></small>
              </div>

              <button type="submit" class="btn-checkout-primary" style="margin-top:6px;">
                🚪 Masuk ke Sistem
              </button>
            </form>
          </div>
        </div>
      </section>

      <!-- Kolom Kanan: 1-Click Simulasi 6 Peran & Matriks RBAC -->
      <section>
        <!-- 1-Click Role Switcher -->
        <div class="data-card">
          <header class="data-card__header">
            <h3>⚡ 1-Click Simulasi 6 Peran Pengguna</h3>
            <span class="status-pill" style="background:var(--color-success-bg); color:var(--color-success);">F01 RBAC</span>
          </header>
          <div style="padding:16px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <a href="index.php?switch_role=owner" class="outlet-switcher-card" style="margin-bottom:0; text-decoration:none;">
              <strong style="color:var(--color-primary-dark); font-size:13px;">1. Owner (Hafidz)</strong>
              <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:2px;">Akses penuh agregat seluruh cabang</div>
            </a>

            <a href="index.php?switch_role=manager" class="outlet-switcher-card" style="margin-bottom:0; text-decoration:none;">
              <strong style="color:var(--color-success); font-size:13px;">2. Manajer (Hanif)</strong>
              <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:2px;">Kelola cabang 1, BOM resep & stok</div>
            </a>

            <a href="index.php?switch_role=cashier" class="outlet-switcher-card" style="margin-bottom:0; text-decoration:none;">
              <strong style="color:var(--color-accent); font-size:13px;">3. Kasir (Dzaky)</strong>
              <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:2px;">Akses transaksi POS kasir</div>
            </a>

            <a href="index.php?switch_role=barista" class="outlet-switcher-card" style="margin-bottom:0; text-decoration:none;">
              <strong style="color:var(--color-info); font-size:13px;">4. Barista (Rafie)</strong>
              <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:2px;">Melihat panduan takaran resep</div>
            </a>

            <a href="index.php?switch_role=warehouse" class="outlet-switcher-card" style="margin-bottom:0; text-decoration:none;">
              <strong style="color:var(--color-text-primary); font-size:13px;">5. Gudang (Imad)</strong>
              <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:2px;">Akses mutasi stok & restok</div>
            </a>

            <a href="index.php?switch_role=accountant" class="outlet-switcher-card" style="margin-bottom:0; text-decoration:none;">
              <strong style="color:var(--color-danger); font-size:13px;">6. Auditor (Siti)</strong>
              <div style="font-size:11px; color:var(--color-text-tertiary); margin-top:2px;">Audit laporan keuangan read-only</div>
            </a>
          </div>
        </div>

        <!-- Matriks RBAC PRD -->
        <div class="data-card">
          <header class="data-card__header">
            <h3>🛡️ Matriks Hak Akses RBAC (PRD KopiKita)</h3>
          </header>
          <div class="table-responsive">
            <table class="native-table" style="text-align:center;">
              <thead>
                <tr>
                  <th style="text-align:left;">Peran Pengguna</th>
                  <th>POS</th>
                  <th>Inventori</th>
                  <th>Resep BOM</th>
                  <th>Laba-Rugi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="text-align:left; font-weight:700;">Owner</td>
                  <td>✅</td>
                  <td>✅</td>
                  <td>✅</td>
                  <td><span class="status-pill" style="background:var(--color-success-bg); color:var(--color-success);">Konsolidasi</span></td>
                </tr>
                <tr>
                  <td style="text-align:left; font-weight:700;">Manajer Outlet</td>
                  <td>✅</td>
                  <td>✅</td>
                  <td>✅</td>
                  <td><span class="status-pill">Cabang</span></td>
                </tr>
                <tr>
                  <td style="text-align:left; font-weight:700; color:var(--color-primary);">Kasir</td>
                  <td><strong style="color:var(--color-primary);">✅ Utama</strong></td>
                  <td>❌</td>
                  <td>❌</td>
                  <td>❌</td>
                </tr>
                <tr>
                  <td style="text-align:left; font-weight:700;">Barista</td>
                  <td>❌</td>
                  <td>👁️ Lihat</td>
                  <td>👁️ Lihat</td>
                  <td>❌</td>
                </tr>
                <tr>
                  <td style="text-align:left; font-weight:700;">Staf Gudang</td>
                  <td>❌</td>
                  <td><strong style="color:var(--color-success);">✅ Kelola</strong></td>
                  <td>❌</td>
                  <td>❌</td>
                </tr>
                <tr>
                  <td style="text-align:left; font-weight:700;">Akuntan / Auditor</td>
                  <td>👁️ Read-only</td>
                  <td>👁️ Read-only</td>
                  <td>👁️ Read-only</td>
                  <td><strong style="color:var(--color-success);">✅ Audit</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </main>

</body>
</html>
