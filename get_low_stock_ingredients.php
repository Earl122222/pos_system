<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$stmt = $pdo->prepare("SELECT ingredient_name, ingredient_quantity, ingredient_unit, minimum_threshold, date_added, expiring_date FROM ingredients WHERE ingredient_quantity <= minimum_threshold");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as &$row) {
    $row['date_added'] = $row['date_added'] ? date('Y-m-d', strtotime($row['date_added'])) : '';
    $row['expiring_date'] = $row['expiring_date'] ? date('Y-m-d', strtotime($row['expiring_date'])) : '';
}
echo json_encode($rows); 