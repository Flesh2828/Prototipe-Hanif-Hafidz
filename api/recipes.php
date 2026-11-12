<?php
/**
 * API Resep BOM (F03)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter ID menu wajib diisi']);
    exit;
}

$db = getDb();

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
    echo json_encode(['error' => 'Menu tidak ditemukan']);
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

