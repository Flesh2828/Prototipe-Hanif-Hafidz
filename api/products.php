<?php
/**
 * API Katalog Menu & Resep HPP Dinamis (F02, F03 CRUD)
 * Mendukung Create, Read, Update, Delete Menu & Resep BOM
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

// ==========================================
// 1. DETAIL MENU & RESEP BOM (GET ?id=X)
// ==========================================
if ($method === 'GET' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = (int)$_GET['id'];
    
    $stmtMenu = $db->prepare('
        SELECT p.*, c.name as category_name
        FROM menus p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ');
    $stmtMenu->execute([$productId]);
    $product = $stmtMenu->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Produk tidak ditemukan']);
        exit;
    }

    $stmtBOM = $db->prepare('
        SELECT r.id, r.dosage, i.id as ingredient_id, i.sku as ingredient_sku, i.name as ingredient_name, i.unit, i.cost_per_unit,
               (r.dosage * i.cost_per_unit) as subtotal_cost
        FROM recipes_bom r
        JOIN ingredients i ON r.ingredient_id = i.id
        WHERE r.menu_id = ?
        ORDER BY r.id ASC
    ');
    $stmtBOM->execute([$productId]);
    $recipes = $stmtBOM->fetchAll();

    $totalHpp = calculateProductHpp($productId, $db);

    echo json_encode([
        'product' => $product,
        'recipes' => $recipes,
        'total_hpp' => $totalHpp
    ]);
    exit;
}

// ==========================================
// 2. CREATE MENU BARU BESERTA RESEP BOM (POST ?action=create)
// ==========================================
if ($method === 'POST' && ($action === 'create' || (isset($_POST['action']) && $_POST['action'] === 'create'))) {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $name = trim($body['name'] ?? '');
    $categoryId = !empty($body['category_id']) ? (int)$body['category_id'] : 1;
    $price = isset($body['price']) ? (float)$body['price'] : 0.0;
    $description = trim($body['description'] ?? '');
    $code = trim($body['code'] ?? '');
    $rawRecipes = $body['recipes'] ?? ($body['recipe_items'] ?? []);
    if (is_string($rawRecipes)) {
        $recipes = json_decode($rawRecipes, true) ?: [];
    } else if (is_array($rawRecipes)) {
        $recipes = $rawRecipes;
    } else {
        $recipes = [];
    }

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nama menu wajib diisi.']);
        exit;
    }

    if ($price < 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Harga jual tidak boleh negatif.']);
        exit;
    }

    try {
        $db->beginTransaction();

        // Auto-generate code jika kosong
        if (empty($code)) {
            $stmtCount = $db->query('SELECT COUNT(*) as cnt FROM menus');
            $cnt = (int)$stmtCount->fetch()['cnt'] + 1;
            $code = 'PRD-' . sprintf('%02d', $cnt);

            // Pastikan unique
            $stmtCheck = $db->prepare('SELECT id FROM menus WHERE code = ?');
            $stmtCheck->execute([$code]);
            if ($stmtCheck->fetch()) {
                $code = 'PRD-' . sprintf('%02d', $cnt) . '-' . time();
            }
        } else {
            // Cek keunikan kode
            $stmtCheck = $db->prepare('SELECT id FROM menus WHERE code = ?');
            $stmtCheck->execute([$code]);
            if ($stmtCheck->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => "Kode menu {$code} sudah digunakan. Gunakan kode lain."]);
                $db->rollBack();
                exit;
            }
        }

        // Simpan Menu
        $stmtInsert = $db->prepare('
            INSERT INTO menus (category_id, code, name, description, price, image_url, is_active)
            VALUES (?, ?, ?, ?, ?, "coffee-default.jpg", 1)
        ');
        $stmtInsert->execute([$categoryId, $code, $name, $description, $price]);
        $menuId = (int)$db->lastInsertId();

        // Simpan Komposisi Resep BOM
        if (!empty($recipes)) {
            $stmtRecipe = $db->prepare('
                INSERT INTO recipes_bom (menu_id, ingredient_id, dosage)
                VALUES (?, ?, ?)
            ');
            foreach ($recipes as $r) {
                $ingId = (int)($r['ingredient_id'] ?? 0);
                $dosage = (float)($r['dosage'] ?? 0);
                if ($ingId > 0 && $dosage > 0) {
                    $stmtRecipe->execute([$menuId, $ingId, $dosage]);
                }
            }
        }

        $db->commit();

        $newHpp = calculateProductHpp($menuId, $db);
        echo json_encode([
            'message' => 'Menu baru dan resep BOM berhasil ditambahkan!',
            'menu_id' => $menuId,
            'code' => $code,
            'name' => $name,
            'hpp' => $newHpp
        ]);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menambahkan menu: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 3. UPDATE MENU & RESEP BOM (POST ?action=update)
// ==========================================
if ($method === 'POST' && ($action === 'update' || (isset($_POST['action']) && $_POST['action'] === 'update'))) {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $id = (int)($body['id'] ?? 0);
    $name = trim($body['name'] ?? '');
    $categoryId = !empty($body['category_id']) ? (int)$body['category_id'] : 1;
    $price = isset($body['price']) ? (float)$body['price'] : 0.0;
    $description = trim($body['description'] ?? '');
    $code = trim($body['code'] ?? '');
    $rawRecipes = $body['recipes'] ?? ($body['recipe_items'] ?? []);
    if (is_string($rawRecipes)) {
        $recipes = json_decode($rawRecipes, true) ?: [];
    } else if (is_array($rawRecipes)) {
        $recipes = $rawRecipes;
    } else {
        $recipes = [];
    }

    if ($id <= 0 || empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID menu dan nama menu wajib diisi.']);
        exit;
    }

    try {
        $db->beginTransaction();

        // Cek keunikan kode jika diubah
        if (!empty($code)) {
            $stmtCheck = $db->prepare('SELECT id FROM menus WHERE code = ? AND id != ?');
            $stmtCheck->execute([$code, $id]);
            if ($stmtCheck->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => "Kode menu {$code} sudah digunakan oleh menu lain."]);
                $db->rollBack();
                exit;
            }
        }

        // Update Tabel Menus
        $stmtUpdate = $db->prepare('
            UPDATE menus 
            SET name = ?, category_id = ?, price = ?, description = ?' . (!empty($code) ? ', code = ?' : '') . '
            WHERE id = ?
        ');
        $params = [$name, $categoryId, $price, $description];
        if (!empty($code)) {
            $params[] = $code;
        }
        $params[] = $id;
        $stmtUpdate->execute($params);

        // Hapus resep BOM lama dan ganti dengan resep BOM yang baru
        $stmtDelBom = $db->prepare('DELETE FROM recipes_bom WHERE menu_id = ?');
        $stmtDelBom->execute([$id]);

        if (!empty($recipes)) {
            $stmtInsertBom = $db->prepare('
                INSERT INTO recipes_bom (menu_id, ingredient_id, dosage)
                VALUES (?, ?, ?)
            ');
            foreach ($recipes as $r) {
                $ingId = (int)($r['ingredient_id'] ?? 0);
                $dosage = (float)($r['dosage'] ?? 0);
                if ($ingId > 0 && $dosage > 0) {
                    $stmtInsertBom->execute([$id, $ingId, $dosage]);
                }
            }
        }

        $db->commit();

        $updatedHpp = calculateProductHpp($id, $db);
        echo json_encode([
            'message' => 'Menu dan resep BOM berhasil diperbarui!',
            'menu_id' => $id,
            'hpp' => $updatedHpp
        ]);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memperbarui menu: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 4. DELETE / DEACTIVATE MENU (POST ?action=delete)
// ==========================================
if ($method === 'POST' && ($action === 'delete' || (isset($_POST['action']) && $_POST['action'] === 'delete'))) {
    $inputRaw = file_get_contents('php://input');
    $body = json_decode($inputRaw, true);
    if (!$body) {
        $body = $_POST;
    }

    $id = (int)($body['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID menu tidak valid.']);
        exit;
    }

    try {
        $db->beginTransaction();

        // Cek apakah menu pernah digunakan dalam riwayat transaksi (transaction_items)
        $stmtCheckSold = $db->prepare('SELECT COUNT(*) as count FROM transaction_items WHERE menu_id = ?');
        $stmtCheckSold->execute([$id]);
        $hasSold = (int)$stmtCheckSold->fetch()['count'] > 0;

        if ($hasSold) {
            // Jika sudah ada histori transaksi, lakukan soft-delete (is_active = 0) agar audit data historis aman
            $stmtDeactivate = $db->prepare('UPDATE menus SET is_active = 0 WHERE id = ?');
            $stmtDeactivate->execute([$id]);
            $msg = 'Menu telah dinonaktifkan (diarsipkan) karena terdapat riwayat transaksi penjualan.';
        } else {
            // Jika belum pernah ada transaksi, hapus total beserta resep BOM-nya
            $stmtDelBom = $db->prepare('DELETE FROM recipes_bom WHERE menu_id = ?');
            $stmtDelBom->execute([$id]);

            $stmtDel = $db->prepare('DELETE FROM menus WHERE id = ?');
            $stmtDel->execute([$id]);
            $msg = 'Menu dan resep BOM berhasil dihapus permanen.';
        }

        $db->commit();
        echo json_encode(['message' => $msg]);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus menu: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 5. GET ALL MENUS WITH DYNAMIC HPP & MARGIN
// ==========================================
$stmt = $db->prepare('
    SELECT p.*, c.name as category_name
    FROM menus p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.id ASC
');
$stmt->execute();
$products = $stmt->fetchAll();

$result = [];
foreach ($products as $p) {
    $dynamicHpp = calculateProductHpp((int)$p['id'], $db);
    $price = (float)$p['price'];
    $grossMargin = $price - $dynamicHpp;
    $marginPct = $price > 0 ? round(($grossMargin / $price) * 100, 1) : 0;

    $p['dynamic_hpp'] = $dynamicHpp;
    $p['gross_margin'] = $grossMargin;
    $p['margin_pct'] = $marginPct;
    $result[] = $p;
}

echo json_encode($result);
