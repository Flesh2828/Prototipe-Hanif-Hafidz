<?php
/**
 * API Transaksi Kasir POS & Pemotongan Stok Otomatis BOM (F02, F03, F04)
 * Menjamin target SLA PRD <= 1.5 detik
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$startTime = microtime(true);
$db = getDb();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metode request tidak diizinkan. Gunakan POST.']);
    exit;
}

// Ambil input JSON
$inputRaw = file_get_contents('php://input');
$body = json_decode($inputRaw, true);

if (!$body) {
    $body = $_POST;
}

$outletId = isset($body['outlet_id']) ? (int)$body['outlet_id'] : 1;
$cashierId = isset($body['cashier_id']) ? (int)$body['cashier_id'] : 1;
$customerName = !empty($body['customer_name']) ? trim($body['customer_name']) : 'Pelanggan Walk-in';
$paymentMethod = !empty($body['payment_method']) ? trim($body['payment_method']) : 'cash';
$cashGiven = isset($body['cash_given']) ? (float)$body['cash_given'] : 0.0;
$items = isset($body['items']) && is_array($body['items']) ? $body['items'] : [];

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'Keranjang transaksi kosong. Pilih minimal satu menu.']);
    exit;
}

// 1. Pra-kalkulasi Item, HPP, Subtotal & Agregasi Takaran BOM
$totalAmount = 0.0;
$totalHpp = 0.0;
$stockDeductionMap = []; // ingredient_id => total kuantitas pengurangan
$preparedItems = [];

$stmtMenu = $db->prepare('SELECT id, code, name, price FROM menus WHERE id = ? AND is_active = 1');
$stmtBom = $db->prepare('SELECT ingredient_id, dosage FROM recipes_bom WHERE menu_id = ?');

foreach ($items as $item) {
    $menuId = (int)($item['product_id'] ?? $item['menu_id'] ?? 0);
    $qty = (int)($item['quantity'] ?? 0);

    if ($menuId <= 0 || $qty <= 0) {
        continue;
    }

    $stmtMenu->execute([$menuId]);
    $product = $stmtMenu->fetch();
    if (!$product) {
        http_response_code(400);
        echo json_encode(['error' => "Menu ID {$menuId} tidak ditemukan atau non-aktif"]);
        exit;
    }

    $unitHpp = calculateProductHpp($menuId, $db);
    $unitPrice = (float)$product['price'];
    $subtotal = $unitPrice * $qty;
    $subtotalHpp = $unitHpp * $qty;

    $totalAmount += $subtotal;
    $totalHpp += $subtotalHpp;

    $preparedItems[] = [
        'product_id' => $menuId,
        'name' => $product['name'],
        'quantity' => $qty,
        'unit_price' => $unitPrice,
        'unit_hpp' => $unitHpp,
        'subtotal' => $subtotal,
        'subtotal_hpp' => $subtotalHpp,
    ];

    // Petakan kebutuhan bahan baku sesuai BOM
    $stmtBom->execute([$menuId]);
    $bomItems = $stmtBom->fetchAll();
    foreach ($bomItems as $b) {
        $ingId = (int)$b['ingredient_id'];
        $needed = (float)$b['dosage'] * $qty;
        $stockDeductionMap[$ingId] = ($stockDeductionMap[$ingId] ?? 0.0) + $needed;
    }
}

if (empty($preparedItems)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data item belanja tidak valid.']);
    exit;
}

// 2. Verifikasi Ketersediaan Stok Bahan Baku (F04 Atomic Check)
$stmtCheckStock = $db->prepare('
    SELECT current_stock 
    FROM outlet_stocks 
    WHERE outlet_id = ? AND ingredient_id = ?
');
$stmtIngInfo = $db->prepare('SELECT name, unit FROM ingredients WHERE id = ?');

foreach ($stockDeductionMap as $ingId => $neededQty) {
    $stmtCheckStock->execute([$outletId, $ingId]);
    $stockRow = $stmtCheckStock->fetch();
    $available = $stockRow ? (float)$stockRow['current_stock'] : 0.0;

    if ($available < $neededQty) {
        $stmtIngInfo->execute([$ingId]);
        $ing = $stmtIngInfo->fetch();
        $ingName = $ing['name'] ?? "Bahan #{$ingId}";
        $ingUnit = $ing['unit'] ?? 'unit';

        http_response_code(400);
        echo json_encode([
            'error' => "Stok bahan baku tidak mencukupi untuk: {$ingName}. Dibutuhkan: {$neededQty} {$ingUnit}, Tersedia: {$available} {$ingUnit}"
        ]);
        exit;
    }
}

// 3. Generate No. Invoice & Hitung Kembalian
$invoiceNo = 'KPK-' . sprintf('%02d', $outletId) . '-' . date('Ymd') . '-' . substr((string)microtime(false), 2, 4);
$changeDue = $paymentMethod === 'cash' ? max(0.0, $cashGiven - $totalAmount) : 0.0;

try {
    $db->beginTransaction();

    // 4. Simpan Transaksi Induk
    $stmtInsertTrans = $db->prepare('
        INSERT INTO transactions (outlet_id, cashier_id, invoice_no, customer_name, payment_method, total_amount, total_hpp, cash_given, change_due, transaction_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "completed")
    ');
    $stmtInsertTrans->execute([
        $outletId,
        $cashierId,
        $invoiceNo,
        $customerName,
        $paymentMethod,
        $totalAmount,
        $totalHpp,
        $paymentMethod === 'cash' ? ($cashGiven ?: $totalAmount) : $totalAmount,
        $changeDue
    ]);
    $transactionId = (int)$db->lastInsertId();

    // 5. Simpan Rincian Item Transaksi
    $stmtInsertItem = $db->prepare('
        INSERT INTO transaction_items (transaction_id, menu_id, quantity, unit_price, unit_hpp, subtotal, subtotal_hpp)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    foreach ($preparedItems as $pi) {
        $stmtInsertItem->execute([
            $transactionId,
            $pi['product_id'],
            $pi['quantity'],
            $pi['unit_price'],
            $pi['unit_hpp'],
            $pi['subtotal'],
            $pi['subtotal_hpp']
        ]);
    }

    // 6. Pemotongan Stok Otomatis Berdasarkan BOM (F04) & Catat Mutasi
    $stmtUpdateStock = $db->prepare('
        UPDATE outlet_stocks 
        SET current_stock = current_stock - ?, updated_at = CURRENT_TIMESTAMP
        WHERE outlet_id = ? AND ingredient_id = ?
    ');

    $stmtLogMutation = $db->prepare('
        INSERT INTO inventory_mutations (outlet_id, ingredient_id, mutation_type, quantity, reference_id, notes, created_by)
        VALUES (?, ?, "OUT_SALE", ?, ?, ?, ?)
    ');

    foreach ($stockDeductionMap as $ingId => $deductedQty) {
        $stmtUpdateStock->execute([$deductedQty, $outletId, $ingId]);
        $stmtLogMutation->execute([
            $outletId,
            $ingId,
            $deductedQty,
            $invoiceNo,
            "Auto-deduct BOM penjualan order #{$invoiceNo}",
            $cashierId
        ]);
    }

    $db->commit();

    // 7. Ambil Deteksi Stok Kritis Terpicu (F05)
    $stmtAlerts = $db->prepare('
        SELECT i.name, i.unit, os.current_stock, i.min_stock_alert
        FROM outlet_stocks os
        JOIN ingredients i ON os.ingredient_id = i.id
        WHERE os.outlet_id = ? AND os.current_stock <= i.min_stock_alert
    ');
    $stmtAlerts->execute([$outletId]);
    $criticalAlerts = $stmtAlerts->fetchAll();

    $durationMs = (int) round((microtime(true) - $startTime) * 1000);

    echo json_encode([
        'message' => 'Transaksi berhasil disimpan dan stok otomatis terpotong!',
        'invoice_no' => $invoiceNo,
        'order_id' => $transactionId,
        'transaction_id' => $transactionId,
        'total_amount' => $totalAmount,
        'total_hpp' => $totalHpp,
        'gross_profit' => $totalAmount - $totalHpp,
        'change_due' => $changeDue,
        'payment_method' => $paymentMethod,
        'items' => $preparedItems,
        'response_duration_ms' => $durationMs,
        'critical_alerts' => $criticalAlerts
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memproses transaksi: ' . $e->getMessage()]);
}

