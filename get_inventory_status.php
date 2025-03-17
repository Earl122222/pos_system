<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    // Get inventory status for products with low stock
    $sql = "SELECT 
                p.product_name,
                COALESCE(SUM(i.quantity), 0) as current_stock,
                p.minimum_stock
            FROM pos_product p
            LEFT JOIN pos_inventory i ON p.product_id = i.product_id
            WHERE p.product_status = 'Active'
            GROUP BY p.product_id, p.product_name, p.minimum_stock
            HAVING current_stock <= p.minimum_stock * 1.5
            ORDER BY (current_stock / p.minimum_stock) ASC
            LIMIT 10";

    $stmt = $pdo->query($sql);
    $products = [];
    $current_stock = [];
    $minimum_stock = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[] = $row['product_name'];
        $current_stock[] = intval($row['current_stock']);
        $minimum_stock[] = intval($row['minimum_stock']);
    }

    echo json_encode([
        'labels' => $products,
        'current_stock' => $current_stock,
        'minimum_stock' => $minimum_stock
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 