<?php
/**
 * API Dashboard Laba-Rugi, Omzet & Konsolidasi Multi-Outlet (F06)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDb();
$outletFilter = $_GET['outlet_id'] ?? 'all';

$whereClause = "WHERE t.transaction_status = 'completed'";
$params = [];

if ($outletFilter && $outletFilter !== 'all') {
    $whereClause .= ' AND t.outlet_id = ?';
    $params[] = (int)$outletFilter;
}

// 1. Ringkasan Metrik Finansial
$stmtSummary = $db->prepare("
    SELECT 
        COUNT(t.id) as total_transactions,
        COALESCE(SUM(t.total_amount), 0) as total_omzet,
        COALESCE(SUM(t.total_hpp), 0) as total_hpp,
        (COALESCE(SUM(t.total_amount), 0) - COALESCE(SUM(t.total_hpp), 0)) as gross_profit
    FROM transactions t
    $whereClause
");
$stmtSummary->execute($params);
$summary = $stmtSummary->fetch();

$totalOmzet = (float)($summary['total_omzet'] ?? 0);
$grossProfit = (float)($summary['gross_profit'] ?? 0);
$marginPct = $totalOmzet > 0 ? round(($grossProfit / $totalOmzet) * 100, 1) : 0;
$summary['margin_pct'] = $marginPct;

// 2. Performa Penjualan Antar-Cabang (Konsolidasi Owner View)
$stmtOutletPerf = $db->prepare("
    SELECT 
        ot.id as outlet_id,
        ot.name as outlet_name,
        COUNT(t.id) as transactions_count,
        COALESCE(SUM(t.total_amount), 0) as total_omzet,
        COALESCE(SUM(t.total_hpp), 0) as total_hpp,
        (COALESCE(SUM(t.total_amount), 0) - COALESCE(SUM(t.total_hpp), 0)) as gross_profit
    FROM outlets ot
    LEFT JOIN transactions t ON t.outlet_id = ot.id AND t.transaction_status = 'completed'
    WHERE ot.is_active = 1
    GROUP BY ot.id, ot.name
    ORDER BY total_omzet DESC
");
$stmtOutletPerf->execute();
$outletPerformance = $stmtOutletPerf->fetchAll();

// 3. Top Selling Menu (Menu Terlaris)
$stmtTop = $db->prepare("
    SELECT 
        p.name,
        SUM(ti.quantity) as total_sold,
        SUM(ti.subtotal) as total_revenue,
        SUM(ti.subtotal_hpp) as total_hpp,
        (SUM(ti.subtotal) - SUM(ti.subtotal_hpp)) as total_profit
    FROM transaction_items ti
    JOIN transactions t ON ti.transaction_id = t.id
    JOIN menus p ON ti.menu_id = p.id
    $whereClause
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
    LIMIT 5
");
$stmtTop->execute($params);
$topMenus = $stmtTop->fetchAll();

// 4. 10 Transaksi Penjualan Terakhir
$stmtRecent = $db->prepare("
    SELECT 
        t.id, 
        t.invoice_no, 
        t.customer_name, 
        t.payment_method, 
        t.total_amount, 
        t.total_hpp,
        (t.total_amount - t.total_hpp) as gross_profit, 
        t.created_at, 
        ot.name as outlet_name
    FROM transactions t
    JOIN outlets ot ON t.outlet_id = ot.id
    $whereClause
    ORDER BY t.id DESC
    LIMIT 10
");
$stmtRecent->execute($params);
$recentOrders = $stmtRecent->fetchAll();

echo json_encode([
    'summary' => $summary,
    'outlet_performance' => $outletPerformance,
    'top_menus' => $topMenus,
    'recent_orders' => $recentOrders
]);

