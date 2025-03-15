<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
    $branch = isset($_GET['branch']) ? $_GET['branch'] : 'main';

    // Set the date range based on period
    switch($period) {
        case 'monthly':
            $date_range = 'INTERVAL 1 MONTH';
            break;
        case 'weekly':
            $date_range = 'INTERVAL 1 WEEK';
            break;
        default: // daily
            $date_range = 'INTERVAL 1 DAY';
            break;
    }

    // Get top products
    $sql = "
        SELECT 
            p.product_id,
            p.product_name,
            p.product_image,
            SUM(oi.product_qty) as total_quantity,
            SUM(oi.product_qty * oi.product_price) as total_revenue
        FROM pos_order o
        JOIN pos_order_item oi ON o.order_id = oi.order_id
        JOIN pos_product p ON oi.product_id = p.product_id
        WHERE o.order_datetime >= DATE_SUB(CURRENT_DATE, $date_range)
        GROUP BY p.product_id, p.product_name
        ORDER BY total_revenue DESC
        LIMIT 3
    ";

    $stmt = $pdo->query($sql);
    $products = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[] = [
            'name' => $row['product_name'],
            'image' => $row['product_image'] ? $row['product_image'] : 'assets/img/default-product.jpg',
            'quantity' => $row['total_quantity'],
            'revenue' => number_format($row['total_revenue'], 2)
        ];
    }

    echo json_encode($products);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 