<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT ingredient_id, ingredient_name, ingredient_quantity, minimum_threshold FROM ingredients");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows); 