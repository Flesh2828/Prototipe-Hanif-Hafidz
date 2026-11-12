<?php
/**
 * API Daftar Cabang Outlet
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$db = getDb();
$stmt = $db->query('SELECT * FROM outlets WHERE is_active = 1 ORDER BY id ASC');
echo json_encode($stmt->fetchAll());

