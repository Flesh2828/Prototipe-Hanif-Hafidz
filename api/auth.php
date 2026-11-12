<?php
/**
 * API Autentikasi & RBAC (F01)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/auth.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'login' || ($method === 'POST' && empty($action))) {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username dan password wajib diisi']);
        exit;
    }

    $user = login_user($username, $password);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Username atau password salah!']);
        exit;
    }

    echo json_encode([
        'message' => 'Login berhasil',
        'user' => $user
    ]);
    exit;
}

if ($action === 'switch') {
    $role = $_GET['role'] ?? ($_POST['role'] ?? 'owner');
    $user = switch_user_by_role($role);
    if (!$user) {
        http_response_code(400);
        echo json_encode(['error' => "Peran {$role} tidak ditemukan"]);
        exit;
    }
    echo json_encode([
        'message' => 'Berhasil beralih peran',
        'user' => $user
    ]);
    exit;
}

if ($action === 'logout') {
    $_SESSION = [];
    if (session_id()) {
        session_destroy();
    }
    echo json_encode(['message' => 'Logout berhasil']);
    exit;
}

// Default: info user saat ini
$user = current_user();
if (!$user) {
    init_default_session();
    $user = current_user();
}
echo json_encode(['user' => $user]);

