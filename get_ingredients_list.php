<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT ingredient_name, ingredient_quantity, ingredient_unit, date_added, expiring_date FROM ingredients");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Format dates if present
foreach ($rows as &$row) {
    $row['date_added'] = $row['date_added'] ? date('Y-m-d', strtotime($row['date_added'])) : '';
    $row['expiring_date'] = $row['expiring_date'] ? date('Y-m-d', strtotime($row['expiring_date'])) : '';
}
echo json_encode($rows); 