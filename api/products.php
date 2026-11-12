<?php
/**
 * API Katalog Menu & Resep HPP Dinamis (F02, F03)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Jika meminta detail resep produk tertentu: /api/products.php?id=1 atau /api/recipes.php
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
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
        SELECT r.id, r.dosage, i.id as ingredient_id, i.name as ingredient_name, i.unit, i.cost_per_unit,
               (r.dosage * i.cost_per_unit) as subtotal_cost
        FROM recipes_bom r
        JOIN ingredients i ON r.ingredient_id = i.id
        WHERE r.menu_id = ?
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

// Ambil seluruh katalog menu aktif beserta kalkulasi HPP & margin dinamis
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
