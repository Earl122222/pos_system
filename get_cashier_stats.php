<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    // Get cashier's branch ID
    $stmt = $pdo->prepare("
        SELECT branch_id 
        FROM pos_cashier_details 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $branch_id = $stmt->fetchColumn();

    if (!$branch_id) {
        throw new Exception('Cashier not assigned to any branch');
    }

    // Check if cashier is currently active
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pos_cashier_sessions 
        WHERE user_id = ? 
        AND branch_id = ?
        AND is_active = TRUE 
        AND logout_time IS NULL
        AND DATE(login_time) = CURRENT_DATE()
    ");
    $stmt->execute([$user_id, $branch_id]);
    $is_active = $stmt->fetchColumn() > 0;

    // Get today's performance stats for specific branch
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT o.order_id) as total_orders,
            COALESCE(SUM(o.order_total), 0) as total_sales,
            COALESCE(AVG(o.order_total), 0) as average_sale,
            COALESCE(SUM(oi.product_qty), 0) as items_sold
        FROM pos_order o
        LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
        WHERE o.order_created_by = ?
        AND o.branch_id = ?
        AND DATE(o.order_datetime) = ?
    ");
    $stmt->execute([$user_id, $branch_id, $today]);
    $today_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sales trend data based on period
    $labels = [];
    $data = [];

    switch ($period) {
        case 'today':
            // Hourly breakdown for today
            for ($hour = 0; $hour < 24; $hour++) {
                $start_time = sprintf('%s %02d:00:00', $today, $hour);
                $end_time = sprintf('%s %02d:59:59', $today, $hour);
                
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(order_total), 0) as total
                    FROM pos_order
                    WHERE order_created_by = ?
                    AND branch_id = ?
                    AND order_datetime BETWEEN ? AND ?
                ");
                $stmt->execute([$user_id, $branch_id, $start_time, $end_time]);
                
                $labels[] = sprintf('%02d:00', $hour);
                $data[] = floatval($stmt->fetchColumn());
            }
            break;

        case 'week':
            // Daily breakdown for current week
            $week_start = date('Y-m-d', strtotime('monday this week'));
            $week_end = date('Y-m-d', strtotime('sunday this week'));
            
            for ($date = $week_start; strtotime($date) <= strtotime($week_end); $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(order_total), 0) as total
                    FROM pos_order
                    WHERE order_created_by = ?
                    AND branch_id = ?
                    AND DATE(order_datetime) = ?
                ");
                $stmt->execute([$user_id, $branch_id, $date]);
                
                $labels[] = date('D', strtotime($date));
                $data[] = floatval($stmt->fetchColumn());
            }
            break;

        case 'month':
            // Daily breakdown for current month
            $month_start = date('Y-m-01');
            $month_end = date('Y-m-t');
            
            for ($date = $month_start; strtotime($date) <= strtotime($month_end); $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(order_total), 0) as total
                    FROM pos_order
                    WHERE order_created_by = ?
                    AND branch_id = ?
                    AND DATE(order_datetime) = ?
                ");
                $stmt->execute([$user_id, $branch_id, $date]);
                
                $labels[] = date('d', strtotime($date));
                $data[] = floatval($stmt->fetchColumn());
            }
            break;
    }

    // Get top selling products for specific branch
    $stmt = $pdo->prepare("
        SELECT 
            p.product_name,
            SUM(oi.product_qty) as quantity,
            SUM(oi.item_total) as total_revenue
        FROM pos_order o
        JOIN pos_order_item oi ON o.order_id = oi.order_id
        JOIN pos_product p ON oi.product_id = p.product_id
        WHERE o.order_created_by = ?
        AND o.branch_id = ?
        AND DATE(o.order_datetime) = ?
        GROUP BY p.product_id, p.product_name
        ORDER BY quantity DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id, $branch_id, $today]);
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'is_active' => $is_active,
        'today_orders' => intval($today_stats['total_orders']),
        'today_sales' => floatval($today_stats['total_sales']),
        'average_sale' => floatval($today_stats['average_sale']),
        'items_sold' => intval($today_stats['items_sold']),
        'sales_trend' => [
            'labels' => $labels,
            'data' => $data
        ],
        'top_products' => $top_products
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 