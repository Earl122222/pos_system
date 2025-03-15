<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : 'daily';
$today = date('Y-m-d');

try {
    // Get all active branches
    $stmt = $pdo->query("
        SELECT branch_id, branch_name, branch_code 
        FROM pos_branch 
        WHERE status = 'Active' 
        ORDER BY branch_name
    ");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $comparison_data = [];

    // Determine date range based on period
    $start_date = $today;
    $end_date = $today;

    switch ($period) {
        case 'custom':
            if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                $start_date = $_GET['start_date'];
                $end_date = $_GET['end_date'];
            }
            break;

        case 'daily':
            // Already set to today
            break;

        case 'weekly':
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date = date('Y-m-d', strtotime('sunday this week'));
            break;

        case 'monthly':
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            break;

        case 'yearly':
            $start_date = date('Y-01-01');
            $end_date = date('Y-12-31');
            break;
    }

    foreach ($branches as $branch) {
        // Get branch's operating hours and active cashiers
        $stmt = $pdo->prepare("
            SELECT 
                b.operating_hours,
                COUNT(cd.id) as active_cashiers
            FROM pos_branch b
            LEFT JOIN pos_cashier_details cd ON b.branch_id = cd.branch_id
            LEFT JOIN pos_user u ON cd.user_id = u.user_id AND u.user_status = 'Active'
            WHERE b.branch_id = ?
            GROUP BY b.branch_id
        ");
        $stmt->execute([$branch['branch_id']]);
        $branch_info = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get sales data for the selected period
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(order_total), 0) as total_sales,
                COALESCE(AVG(order_total), 0) as average_sale
            FROM pos_order
            WHERE branch_id = ? 
            AND DATE(order_datetime) BETWEEN ? AND ?
        ");
        $stmt->execute([$branch['branch_id'], $start_date, $end_date]);
        $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get top selling products for the period
        $stmt = $pdo->prepare("
            SELECT 
                p.product_name,
                COUNT(*) as order_count,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.subtotal) as total_revenue
            FROM pos_order_item oi
            JOIN pos_order o ON oi.order_id = o.order_id
            JOIN pos_product p ON oi.product_id = p.product_id
            WHERE o.branch_id = ?
            AND DATE(o.order_datetime) BETWEEN ? AND ?
            GROUP BY p.product_id
            ORDER BY total_revenue DESC
            LIMIT 3
        ");
        $stmt->execute([$branch['branch_id'], $start_date, $end_date]);
        $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comparison_data[] = [
            'branch_id' => $branch['branch_id'],
            'branch_name' => $branch['branch_name'],
            'branch_code' => $branch['branch_code'],
            'operating_hours' => $branch_info['operating_hours'],
            'active_cashiers' => $branch_info['active_cashiers'],
            'total_orders' => $sales_data['total_orders'],
            'total_sales' => floatval($sales_data['total_sales']),
            'average_sale' => floatval($sales_data['average_sale']),
            'top_products' => $top_products,
            'period_start' => $start_date,
            'period_end' => $end_date
        ];
    }

    // Sort branches by total sales in descending order
    usort($comparison_data, function($a, $b) {
        return $b['total_sales'] - $a['total_sales'];
    });

    echo json_encode([
        'success' => true,
        'period' => $period,
        'date_range' => [
            'start' => $start_date,
            'end' => $end_date
        ],
        'data' => $comparison_data
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} 