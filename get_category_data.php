<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

// Prepare SQL query to get order values by category
$stmt = $pdo->prepare("
SELECT p.category_id, SUM(o.product_qty * o.product_price) as total
FROM pos_order_item o
JOIN pos_product p ON o.product_name = p.product_name
GROUP BY p.category_id
");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JSON output
$categories = [];
$totals = [];
foreach ($data as $row) {
$categories[] = getCategoryName($pdo, $row['category_id']);
$totals[] = (float)$row['total'];
}

// Output JSON
echo json_encode(['categories' => $categories, 'totals' => $totals]);

?>