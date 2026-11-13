<?php
/**
 * API Inventori Bahan Baku, Restok, Stok Opname & Alert Kritis (F04, F05 CRUD)
 * Mendukung Tambah Bahan Baru, Update Harga Beli, Atur Min Alert, dan Penyesuaian Stok
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
            i.cost_per_unit,
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
// 3. CREATE BAHAN BAKU BARU (POST ?action=create)
// ==========================================
if ($method === 'POST' && ($action === 'create' || (isset($_POST['action']) && $_POST['action'] === 'create'))) {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $name = trim($body['name'] ?? '');
    $sku = trim($body['sku'] ?? '');
    $unit = trim($body['unit'] ?? 'gram');
    $costPerUnit = isset($body['cost_per_unit']) ? (float)$body['cost_per_unit'] : 0.0;
    $minStockAlert = isset($body['min_stock_alert']) ? (float)$body['min_stock_alert'] : 100.0;
    $initialStock = isset($body['initial_stock']) ? (float)$body['initial_stock'] : 0.0;
    $targetOutletId = !empty($body['outlet_id']) ? (int)$body['outlet_id'] : null;
    $userId = isset($body['user_id']) ? (int)$body['user_id'] : 1;

    if (empty($name) || empty($unit)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nama bahan baku dan satuan wajib diisi.']);
        exit;
    }

    try {
        $db->beginTransaction();

        // Auto-generate SKU jika belum ada
        if (empty($sku)) {
            $stmtCount = $db->query('SELECT COUNT(*) as cnt FROM ingredients');
            $cnt = (int)$stmtCount->fetch()['cnt'] + 1;
            $sku = 'ING-' . sprintf('%02d', $cnt);

            $stmtCheck = $db->prepare('SELECT id FROM ingredients WHERE sku = ?');
            $stmtCheck->execute([$sku]);
            if ($stmtCheck->fetch()) {
                $sku = 'ING-' . sprintf('%02d', $cnt) . '-' . time();
            }
        } else {
            $stmtCheck = $db->prepare('SELECT id FROM ingredients WHERE sku = ?');
            $stmtCheck->execute([$sku]);
            if ($stmtCheck->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => "SKU {$sku} sudah digunakan. Gunakan SKU lain."]);
                $db->rollBack();
                exit;
            }
        }

        // Simpan ke ingredients
        $stmtInsert = $db->prepare('
            INSERT INTO ingredients (sku, name, unit, cost_per_unit, min_stock_alert)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmtInsert->execute([$sku, $name, $unit, $costPerUnit, $minStockAlert]);
        $ingredientId = (int)$db->lastInsertId();

        // Buat entri stok untuk semua cabang aktif
        $stmtOutlets = $db->query('SELECT id FROM outlets WHERE is_active = 1');
        $outlets = $stmtOutlets->fetchAll();

        $stmtStock = $db->prepare('
            INSERT INTO outlet_stocks (outlet_id, ingredient_id, current_stock)
            VALUES (?, ?, ?)
        ');

        $stmtMut = $db->prepare('
            INSERT INTO inventory_mutations (outlet_id, ingredient_id, mutation_type, quantity, reference_id, notes, created_by)
            VALUES (?, ?, "IN", ?, ?, ?, ?)
        ');

        foreach ($outlets as $out) {
            $outId = (int)$out['id'];
            $stockForThisOutlet = ($targetOutletId === null || $targetOutletId === $outId) ? $initialStock : 0.0;
            $stmtStock->execute([$outId, $ingredientId, $stockForThisOutlet]);

            if ($stockForThisOutlet > 0) {
                $stmtMut->execute([
                    $outId,
                    $ingredientId,
                    $stockForThisOutlet,
                    'INIT-' . date('Ymd') . '-' . $ingredientId,
                    'Stok Awal Bahan Baku Baru',
                    $userId
                ]);
            }
        }

        $db->commit();

        echo json_encode([
            'message' => 'Bahan baku baru berhasil ditambahkan ke inventori seluruh cabang!',
            'ingredient_id' => $ingredientId,
            'sku' => $sku,
            'name' => $name
        ]);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menambahkan bahan baku: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 4. UPDATE BAHAN BAKU & EDIT/KOREKSI STOK OPNAME (POST ?action=update)
// ==========================================
if ($method === 'POST' && ($action === 'update' || (isset($_POST['action']) && $_POST['action'] === 'update'))) {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $id = (int)($body['id'] ?? $body['ingredient_id'] ?? 0);
    $name = trim($body['name'] ?? '');
    $sku = trim($body['sku'] ?? '');
    $unit = trim($body['unit'] ?? '');
    $costPerUnit = isset($body['cost_per_unit']) ? (float)$body['cost_per_unit'] : null;
    $minStockAlert = isset($body['min_stock_alert']) ? (float)$body['min_stock_alert'] : null;
    $outletId = !empty($body['outlet_id']) ? (int)$body['outlet_id'] : null;
    $currentStock = isset($body['current_stock']) ? (float)$body['current_stock'] : null;
    $adjustmentNotes = trim($body['adjustment_notes'] ?? 'Penyesuaian stok opname / edit bahan');
    $userId = isset($body['user_id']) ? (int)$body['user_id'] : 1;

    if ($id <= 0 || empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID dan nama bahan baku wajib diisi.']);
        exit;
    }

    try {
        $db->beginTransaction();

        // Cek SKU keunikan jika ada
        if (!empty($sku)) {
            $stmtCheck = $db->prepare('SELECT id FROM ingredients WHERE sku = ? AND id != ?');
            $stmtCheck->execute([$sku, $id]);
            if ($stmtCheck->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => "SKU {$sku} sudah digunakan oleh bahan lain."]);
                $db->rollBack();
                exit;
            }
        }

        // Update Tabel ingredients
        $sqlUp = 'UPDATE ingredients SET name = ?, unit = ?';
        $paramsUp = [$name, $unit];

        if (!empty($sku)) {
            $sqlUp .= ', sku = ?';
            $paramsUp[] = $sku;
        }
        if ($costPerUnit !== null) {
            $sqlUp .= ', cost_per_unit = ?';
            $paramsUp[] = $costPerUnit;
        }
        if ($minStockAlert !== null) {
            $sqlUp .= ', min_stock_alert = ?';
            $paramsUp[] = $minStockAlert;
        }
        $sqlUp .= ' WHERE id = ?';
        $paramsUp[] = $id;

        $stmtUp = $db->prepare($sqlUp);
        $stmtUp->execute($paramsUp);

        // Jika dilakukan koreksi stok fisik cabang (Stock Opname)
        if ($outletId && $currentStock !== null) {
            $stmtCurr = $db->prepare('SELECT current_stock FROM outlet_stocks WHERE outlet_id = ? AND ingredient_id = ?');
            $stmtCurr->execute([$outletId, $id]);
            $rowCurr = $stmtCurr->fetch();
            $oldStock = $rowCurr ? (float)$rowCurr['current_stock'] : 0.0;
            $diff = $currentStock - $oldStock;

            // Update outlet_stocks
            $stmtSetStock = $db->prepare('
                INSERT INTO outlet_stocks (outlet_id, ingredient_id, current_stock)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    current_stock = VALUES(current_stock),
                    updated_at = CURRENT_TIMESTAMP
            ');
            $stmtSetStock->execute([$outletId, $id, $currentStock]);

            // Catat mutasi penyesuaian jika ada selisih
            if (abs($diff) > 0.001) {
                $refId = 'ADJ-' . date('Ymd') . '-' . time();
                $mutType = $diff > 0 ? 'ADJUSTMENT' : 'WASTE';
                $stmtMut = $db->prepare('
                    INSERT INTO inventory_mutations (outlet_id, ingredient_id, mutation_type, quantity, reference_id, notes, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $stmtMut->execute([
                    $outletId,
                    $id,
                    $mutType,
                    abs($diff),
                    $refId,
                    $adjustmentNotes . " (Koreksi: {$oldStock} -> {$currentStock})",
                    $userId
                ]);
            }
        }

        $db->commit();
        echo json_encode(['message' => 'Data bahan baku & stok berhasil diperbarui!']);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memperbarui bahan baku: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 5. DELETE BAHAN BAKU (POST ?action=delete)
// ==========================================
if ($method === 'POST' && ($action === 'delete' || (isset($_POST['action']) && $_POST['action'] === 'delete'))) {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $id = (int)($body['id'] ?? $body['ingredient_id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID bahan baku tidak valid.']);
        exit;
    }

    try {
        $db->beginTransaction();

        // Cek apakah bahan baku digunakan di salah satu resep BOM
        $stmtCheckBom = $db->prepare('
            SELECT p.name 
            FROM recipes_bom r 
            JOIN menus p ON r.menu_id = p.id 
            WHERE r.ingredient_id = ?
        ');
        $stmtCheckBom->execute([$id]);
        $usedInMenus = $stmtCheckBom->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($usedInMenus)) {
            $menuList = implode(', ', array_slice($usedInMenus, 0, 3));
            if (count($usedInMenus) > 3) {
                $menuList .= ' dan lainnya';
            }
            http_response_code(400);
            echo json_encode([
                'error' => "Bahan baku tidak dapat dihapus karena masih digunakan dalam resep menu: {$menuList}. Hapus/edit resep menu terkait terlebih dahulu."
            ]);
            $db->rollBack();
            exit;
        }

        // Hapus stok & mutasi lalu bahan baku
        $stmtDelMut = $db->prepare('DELETE FROM inventory_mutations WHERE ingredient_id = ?');
        $stmtDelMut->execute([$id]);

        $stmtDelStock = $db->prepare('DELETE FROM outlet_stocks WHERE ingredient_id = ?');
        $stmtDelStock->execute([$id]);

        $stmtDel = $db->prepare('DELETE FROM ingredients WHERE id = ?');
        $stmtDel->execute([$id]);

        $db->commit();
        echo json_encode(['message' => 'Bahan baku berhasil dihapus dari sistem.']);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus bahan baku: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 6. POST RESTOCK (Pencatatan Barang Masuk - F04)
// ==========================================
if ($method === 'POST') {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $outletId = !empty($body['outlet_id']) ? (int)$body['outlet_id'] : ($_SESSION['outlet_id'] ?? 1);
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
