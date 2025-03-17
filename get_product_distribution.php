<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    // Get product distribution by category
    $sql = "SELECT 
                c.category_name,
                COUNT(p.product_id) as product_count
            FROM pos_category c
            LEFT JOIN pos_product p ON c.category_id = p.category_id
            WHERE c.category_status = 'Active'
            AND (p.product_status = 'Active' OR p.product_status IS NULL)
            GROUP BY c.category_id, c.category_name
            ORDER BY product_count DESC
            LIMIT 5";

    $stmt = $pdo->query($sql);
    $categories = [];
    $counts = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row['category_name'];
        $counts[] = intval($row['product_count']);
    }

    echo json_encode([
        'labels' => $categories,
        'data' => $counts
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 