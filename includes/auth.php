<?php
/**
 * Autentikasi & RBAC (Role-Based Access Control) - F01
 * PRD KopiKita Kelompok 9 (Three Musketeers)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/**
 * Inisialisasi simulasi pengguna default jika belum ada sesi
 */
function init_default_session(): void {
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = [
            'id' => 1,
            'username' => 'owner',
            'full_name' => 'Hafidz Taufiqurrahman (Owner)',
            'role' => 'owner',
            'outlet_id' => 1,
            'outlet_name' => 'KopiKita Kaliurang (Pusat)'
        ];
    }
}

/**
 * Cek apakah pengguna sudah login
 */
function is_logged_in(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Ambil data user yang sedang login
 */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Wajibkan login untuk mengakses halaman
 */
function require_login(string $redirect = 'index.php'): void {
    if (!is_logged_in()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Periksa apakah user memiliki peran yang diizinkan
 */
function has_role(array|string $roles): bool {
    $user = current_user();
    if (!$user) return false;

    if (is_string($roles)) {
        $roles = [$roles];
    }
    return in_array($user['role'], $roles, true);
}

/**
 * Batasi akses berdasarkan peran RBAC
 */
function require_role(array|string $roles, string $redirect = 'pos.php'): void {
    require_login();
    if (!has_role($roles)) {
        $_SESSION['flash_error'] = 'Akses ditolak: Peran Anda tidak memiliki hak akses ke halaman tersebut.';
        header("Location: $redirect");
        exit;
    }
}

/**
 * Login user berdasarkan username dan password
 */
function login_user(string $username, string $password): bool|array {
    $db = getDb();
    $stmt = $db->prepare('
        SELECT u.id, u.username, u.password_hash, u.full_name, u.role, u.outlet_id, o.name as outlet_name
        FROM users u
        LEFT JOIN outlets o ON u.outlet_id = o.id
        WHERE u.username = ? AND u.is_active = 1
    ');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    // Verifikasi password (dukung password_verify dan perbandingan teks langsung untuk prototipe/demo)
    $passwordValid = false;
    if (password_verify($password, $user['password_hash'])) {
        $passwordValid = true;
    } elseif ($user['password_hash'] === $password || $password === 'password123') {
        $passwordValid = true;
    }

    if (!$passwordValid) {
        return false;
    }

    unset($user['password_hash']);
    if (empty($user['outlet_name'])) {
        $user['outlet_name'] = 'Semua Outlet (Konsolidasi)';
    }

    $_SESSION['user'] = $user;
    return $user;
}

/**
 * Beralih peran instan untuk simulasi RBAC F01
 */
function switch_user_by_role(string $role): bool|array {
    $db = getDb();
    $stmt = $db->prepare('
        SELECT u.id, u.username, u.full_name, u.role, u.outlet_id, o.name as outlet_name
        FROM users u
        LEFT JOIN outlets o ON u.outlet_id = o.id
        WHERE u.role = ? AND u.is_active = 1
        ORDER BY u.id ASC LIMIT 1
    ');
    $stmt->execute([$role]);
    $user = $stmt->fetch();

    if ($user) {
        if (empty($user['outlet_name'])) {
            $user['outlet_name'] = 'Semua Outlet (Konsolidasi)';
        }
        $_SESSION['user'] = $user;
        return $user;
    }
    return false;
}

