<?php
/**
 * Konfigurasi Koneksi Basis Data MySQL (PDO)
 * Sesuai Standar XAMPP Apache & PRD KopiKita Bagian 7.1
 */

$host     = 'localhost';
$dbname   = 'kopikita';
$user     = 'root';
$password = '';

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    if (php_sapi_name() !== 'cli' && (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['error' => 'Koneksi database MySQL gagal: ' . $e->getMessage()]);
        exit;
    }
    die('Koneksi database MySQL gagal: ' . htmlspecialchars($e->getMessage()));
}

/**
 * Helper untuk mendapatkan instance PDO
 */
function getDb(): PDO {
    global $pdo;
    return $pdo;
}

/**
 * Menghitung HPP Dinamis per Porsi Produk/Menu berdasarkan BOM dan harga beli bahan baku saat ini (F03)
 */
function calculateProductHpp(int $menuId, ?PDO $db = null): int {
    if (!$db) {
        $db = getDb();
    }
    $stmt = $db->prepare('
        SELECT r.dosage, i.cost_per_unit 
        FROM recipes_bom r
        JOIN ingredients i ON r.ingredient_id = i.id
        WHERE r.menu_id = ?
    ');
    $stmt->execute([$menuId]);
    $items = $stmt->fetchAll();

    $hpp = 0.0;
    foreach ($items as $item) {
        $hpp += ((float)$item['dosage'] * (float)$item['cost_per_unit']);
    }
    return (int) round($hpp);
}

