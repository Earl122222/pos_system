<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

if (!isset($_GET['branch_id'])) {
    echo json_encode(['error' => 'Branch ID is required']);
    exit;
}

$branch_id = $_GET['branch_id'];
$today = date('Y-m-d');
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

try {
    // Get today's sales
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(order_total), 0) as total
        FROM pos_order
        WHERE branch_id = ? 
        AND DATE(order_datetime) = ?
    ");
    $stmt->execute([$branch_id, $today]);
    $today_sales = $stmt->fetchColumn();

    // Get monthly sales
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(order_total), 0) as total
        FROM pos_order
        WHERE branch_id = ?
        AND order_datetime BETWEEN ? AND ?
    ");
    $stmt->execute([$branch_id, $month_start, $month_end]);
    $monthly_sales = $stmt->fetchColumn();

    // Get sales trend (last 7 days)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(order_datetime) as date,
            COALESCE(SUM(order_total), 0) as total
        FROM pos_order
        WHERE branch_id = ?
        AND order_datetime >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        GROUP BY DATE(order_datetime)
        ORDER BY date
    ");
    $stmt->execute([$branch_id]);
    $trend_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sales_trend = [
        'labels' => [],
        'values' => []
    ];

    foreach ($trend_data as $data) {
        $sales_trend['labels'][] = date('M d', strtotime($data['date']));
        $sales_trend['values'][] = floatval($data['total']);
    }

    // Get top 5 products
    $stmt = $pdo->prepare("
        SELECT 
            p.product_name,
            COUNT(*) as total_orders,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.subtotal) as total_revenue
        FROM pos_order_item oi
        JOIN pos_order o ON oi.order_id = o.order_id
        JOIN pos_product p ON oi.product_id = p.product_id
        WHERE o.branch_id = ?
        AND o.order_datetime BETWEEN ? AND ?
        GROUP BY p.product_id
        ORDER BY total_revenue DESC
        LIMIT 5
    ");
    $stmt->execute([$branch_id, $month_start, $month_end]);
    $top_products_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $top_products = [
        'labels' => [],
        'values' => []
    ];

    foreach ($top_products_data as $product) {
        $top_products['labels'][] = $product['product_name'];
        $top_products['values'][] = floatval($product['total_revenue']);
    }

    echo json_encode([
        'today_sales' => floatval($today_sales),
        'monthly_sales' => floatval($monthly_sales),
        'sales_trend' => $sales_trend,
        'top_products' => $top_products
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 