<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

header('Content-Type: application/json');

try {
    $today = date('Y-m-d');
    $chart_type = $_GET['chart_type'] ?? 'quantity';
    
    // Get daily summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT o.order_id) as total_orders,
            COALESCE(SUM(o.order_total), 0) as total_sales,
            COALESCE(MIN(o.order_total), 0) as min_sale,
            COALESCE(MAX(o.order_total), 0) as max_sale,
            COALESCE(AVG(o.order_total), 0) as avg_sale
        FROM pos_order o
        WHERE DATE(o.order_datetime) = ? 
        AND o.order_created_by = ?
    ");
    $stmt->execute([$today, $_SESSION['user_id']]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get today's top selling products based on chart type
    if ($chart_type === 'quantity') {
        $top_products_sql = "
            SELECT 
                p.product_name,
                p.product_image,
                SUM(oi.product_qty) as total_quantity,
                SUM(oi.item_total) as total_revenue
            FROM pos_order o
            JOIN pos_order_item oi ON o.order_id = oi.order_id
            JOIN pos_product p ON oi.product_id = p.product_id
            WHERE DATE(o.order_datetime) = ?
            AND o.order_created_by = ?
            GROUP BY p.product_name, p.product_image
            ORDER BY total_quantity DESC
            LIMIT 3
        ";
    } else {
        $top_products_sql = "
            SELECT 
                p.product_name,
                p.product_image,
                SUM(oi.product_qty) as total_quantity,
                SUM(oi.item_total) as total_revenue
            FROM pos_order o
            JOIN pos_order_item oi ON o.order_id = oi.order_id
            JOIN pos_product p ON oi.product_id = p.product_id
            WHERE DATE(o.order_datetime) = ?
            AND o.order_created_by = ?
            GROUP BY p.product_name, p.product_image
            ORDER BY total_revenue DESC
            LIMIT 3
        ";
    }
    
    $top_products_stmt = $pdo->prepare($top_products_sql);
    $top_products_stmt->execute([$today, $_SESSION['user_id']]);
    $top_products = $top_products_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get purchase trend data (last 7 days)
    $trend_sql = "
        SELECT 
            DATE(o.order_datetime) as date,
            COUNT(DISTINCT o.order_id) as order_count,
            SUM(o.order_total) as total_sales
        FROM pos_order o
        WHERE o.order_datetime >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        AND o.order_created_by = ?
        GROUP BY DATE(o.order_datetime)
        ORDER BY date ASC
    ";

    $trend_stmt = $pdo->prepare($trend_sql);
    $trend_stmt->execute([$_SESSION['user_id']]);
    $trend_data = $trend_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for chart
    $purchaseTrend = [
        'labels' => [],
        'data' => []
    ];

    // Generate last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $purchaseTrend['labels'][] = date('M d', strtotime($date));
        $purchaseTrend['data'][] = 0; // Default to 0
    }

    // Fill in actual data
    foreach ($trend_data as $day) {
        $index = array_search(date('M d', strtotime($day['date'])), $purchaseTrend['labels']);
        if ($index !== false) {
            $purchaseTrend['data'][$index] = $chart_type === 'quantity' ? 
                (int)$day['order_count'] : 
                (float)$day['total_sales'];
        }
    }

    // Format top products data
    $topProducts = [
        'labels' => [],
        'data' => [],
        'revenue' => [],
        'images' => []
    ];

    foreach ($top_products as $product) {
        $topProducts['labels'][] = $product['product_name'];
        $topProducts['data'][] = (int)$product['total_quantity'];
        $topProducts['revenue'][] = (float)$product['total_revenue'];
        $topProducts['images'][] = $product['product_image'] ?? 'asset/images/default-food.jpg';
    }

    // Prepare response
    $response = [
        'summary' => $summary,
        'purchaseTrend' => $purchaseTrend,
        'topProducts' => $topProducts
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 