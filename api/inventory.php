<?php
/**
 * API Inventori Bahan Baku, Restok & Peringatan Stok Kritis (F04, F05)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

// ==========================================
// 1. GET CRITICAL ALERTS ONLY (F05)
// ==========================================
if ($method === 'GET' && $action === 'critical-alerts') {
    $outletId = isset($_GET['outlet_id']) && is_numeric($_GET['outlet_id']) ? (int)$_GET['outlet_id'] : null;

    $sql = '
        SELECT 
            i.id as ingredient_id,
            i.sku, 
            i.name as ingredient_name, 
            i.unit, 
            i.min_stock_alert,
            os.outlet_id, 
            o.name as outlet_name, 
            COALESCE(os.current_stock, 0) as current_stock
        FROM outlet_stocks os
        JOIN ingredients i ON os.ingredient_id = i.id
        JOIN outlets o ON os.outlet_id = o.id
        WHERE os.current_stock <= i.min_stock_alert
    ';
    $params = [];
    if ($outletId) {
        $sql .= ' AND os.outlet_id = ?';
        $params[] = $outletId;
    }
    $sql .= ' ORDER BY (i.min_stock_alert - os.current_stock) DESC, i.name ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());
    exit;
}

// ==========================================
// 2. GET INVENTORY LIST (F04)
// ==========================================
if ($method === 'GET') {
    $outletId = isset($_GET['outlet_id']) && is_numeric($_GET['outlet_id']) ? (int)$_GET['outlet_id'] : null;

    $sql = '
        SELECT 
            i.id as ingredient_id, 
            i.sku, 
            i.name as ingredient_name, 
            i.unit, 
            i.cost_per_unit, 
            i.min_stock_alert,
            o.id as outlet_id,
            o.name as outlet_name,
            COALESCE(os.current_stock, 0) as current_stock,
            CASE 
                WHEN COALESCE(os.current_stock, 0) <= i.min_stock_alert THEN 1 
                ELSE 0 
            END as is_critical
        FROM ingredients i
        CROSS JOIN outlets o
        LEFT JOIN outlet_stocks os ON os.ingredient_id = i.id AND os.outlet_id = o.id
        WHERE o.is_active = 1
    ';
    $params = [];
    if ($outletId) {
        $sql .= ' AND o.id = ?';
        $params[] = $outletId;
    }
    $sql .= ' ORDER BY is_critical DESC, i.name ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());
    exit;
}

// ==========================================
// 3. POST RESTOCK (Pencatatan Barang Masuk - F04)
// ==========================================
if ($method === 'POST') {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $outletId = isset($body['outlet_id']) ? (int)$body['outlet_id'] : 0;
    $ingredientId = isset($body['ingredient_id']) ? (int)$body['ingredient_id'] : 0;
    $quantity = isset($body['quantity']) ? (float)$body['quantity'] : 0.0;
    $newCost = !empty($body['new_cost_per_unit']) ? (float)$body['new_cost_per_unit'] : null;
    $notes = !empty($body['notes']) ? trim($body['notes']) : 'Restok barang masuk';
    $userId = isset($body['user_id']) ? (int)$body['user_id'] : 1;

    if ($outletId <= 0 || $ingredientId <= 0 || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Data restok belum lengkap atau kuantitas tidak valid.']);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Perbarui harga beli supplier jika ada input baru (HPP Dinamis langsung terupdate)
        if ($newCost && $newCost > 0) {
            $stmtUpdateCost = $db->prepare('UPDATE ingredients SET cost_per_unit = ? WHERE id = ?');
            $stmtUpdateCost->execute([$newCost, $ingredientId]);
        }

        // 2. Tambahkan stok cabang
        $stmtStock = $db->prepare('
            INSERT INTO outlet_stocks (outlet_id, ingredient_id, current_stock)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                current_stock = current_stock + VALUES(current_stock),
                updated_at = CURRENT_TIMESTAMP
        ');
        $stmtStock->execute([$outletId, $ingredientId, $quantity]);

        // 3. Catat riwayat mutasi
        $refId = 'RESTOCK-' . date('Ymd') . '-' . time();
        $stmtMut = $db->prepare('
            INSERT INTO inventory_mutations (outlet_id, ingredient_id, mutation_type, quantity, reference_id, notes, created_by)
            VALUES (?, ?, "IN", ?, ?, ?, ?)
        ');
        $stmtMut->execute([$outletId, $ingredientId, $quantity, $refId, $notes, $userId]);

        $db->commit();

        echo json_encode([
            'message' => 'Stok bahan baku berhasil ditambahkan dan HPP otomatis terkalkulasi ulang!',
            'reference_id' => $refId
        ]);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mencatat restok: ' . $e->getMessage()]);
    }
    exit;
}
