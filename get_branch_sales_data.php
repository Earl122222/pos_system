<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

if (!isset($_GET['branch_id'])) {
    echo json_encode(['error' => 'Branch ID is required']);
    exit;
}

$branch_id = intval($_GET['branch_id']);
$period = isset($_GET['period']) ? $_GET['period'] : 'daily';
$today = date('Y-m-d');

try {
    // First, check if the branch is currently operating
    $stmt = $pdo->prepare("
        SELECT 
            operating_hours,
            status
        FROM pos_branch 
        WHERE branch_id = ?
    ");
    $stmt->execute([$branch_id]);
    $branch_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if there are any active cashiers
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pos_cashier_details cd
        JOIN pos_user u ON cd.user_id = u.user_id
        WHERE cd.branch_id = ?
        AND u.user_status = 'Active'
    ");
    $stmt->execute([$branch_id]);
    $active_cashiers = $stmt->fetchColumn();

    // If branch is not active or has no cashiers, return zeros
    if ($branch_info['status'] !== 'Active' || $active_cashiers === 0) {
        echo json_encode([
            'today_stats' => [
                'total_orders' => 0,
                'total_sales' => 0,
                'average_sale' => 0,
                'highest_sale' => 0
            ],
            'sales_trend' => [
                'labels' => [],
                'data' => []
            ],
            'payment_methods' => [
                'cash' => 0,
                'credit_card' => 0,
                'e_wallet' => 0
            ]
        ]);
        exit;
    }

    // Get today's stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT order_id) as total_orders,
            COALESCE(SUM(order_total), 0) as total_sales,
            COALESCE(AVG(order_total), 0) as average_sale,
            COALESCE(MAX(order_total), 0) as highest_sale
        FROM pos_order
        WHERE branch_id = ? 
        AND DATE(order_datetime) = ?
    ");
    $stmt->execute([$branch_id, $today]);
    $today_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sales trend data
    $labels = [];
    $data = [];
    
    switch ($period) {
        case 'today':
            // Today only
            $labels[] = date('M d', strtotime($today));
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(order_total), 0) as total
                FROM pos_order
                WHERE branch_id = ? 
                AND DATE(order_datetime) = ?
            ");
            $stmt->execute([$branch_id, $today]);
            $data[] = floatval($stmt->fetchColumn());
            break;

        case 'week':
            // Current week (Monday to Sunday)
            $week_start = date('Y-m-d', strtotime('monday this week'));
            $week_end = date('Y-m-d', strtotime('sunday this week'));
            for ($date = $week_start; strtotime($date) <= strtotime($week_end); $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
                $labels[] = date('D', strtotime($date));
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(order_total), 0) as total
                    FROM pos_order
                    WHERE branch_id = ? 
                    AND DATE(order_datetime) = ?
                ");
                $stmt->execute([$branch_id, $date]);
                $data[] = floatval($stmt->fetchColumn());
            }
            break;

        case 'month':
            // Current month
            $month_start = date('Y-m-01');
            $month_end = date('Y-m-t');
            $current_date = $month_start;
            while (strtotime($current_date) <= strtotime($month_end)) {
                $labels[] = date('d', strtotime($current_date));
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(order_total), 0) as total
                    FROM pos_order
                    WHERE branch_id = ? 
                    AND DATE(order_datetime) = ?
                ");
                $stmt->execute([$branch_id, $current_date]);
                $data[] = floatval($stmt->fetchColumn());
                $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
            }
            break;

        case 'year':
            // Current year by months
            $year_start = date('Y-01-01');
            $year_end = date('Y-12-31');
            for ($month = 1; $month <= 12; $month++) {
                $month_date = date('Y-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01');
                $labels[] = date('M', strtotime($month_date));
                
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(order_total), 0) as total
                    FROM pos_order
                    WHERE branch_id = ? 
                    AND YEAR(order_datetime) = YEAR(CURRENT_DATE)
                    AND MONTH(order_datetime) = ?
                ");
                $stmt->execute([$branch_id, $month]);
                $data[] = floatval($stmt->fetchColumn());
            }
            break;

        default: // fallback to daily view
            // Last 7 days
            $start_date = date('Y-m-d', strtotime('-6 days'));
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('M d', strtotime($date));
                
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(order_total), 0) as total
                    FROM pos_order
                    WHERE branch_id = ? 
                    AND DATE(order_datetime) = ?
                ");
                $stmt->execute([$branch_id, $date]);
                $data[] = floatval($stmt->fetchColumn());
            }
            break;
    }

    // Get payment methods distribution for today
    $stmt = $pdo->prepare("
        SELECT 
            payment_method,
            COUNT(*) as count
        FROM pos_order
        WHERE branch_id = ?
        AND DATE(order_datetime) = ?
        GROUP BY payment_method
    ");
    $stmt->execute([$branch_id, $today]);
    $payment_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $payment_methods = [
        'cash' => isset($payment_data['Cash']) ? $payment_data['Cash'] : 0,
        'credit_card' => isset($payment_data['Credit Card']) ? $payment_data['Credit Card'] : 0,
        'e_wallet' => isset($payment_data['E-Wallet']) ? $payment_data['E-Wallet'] : 0
    ];

    echo json_encode([
        'today_stats' => $today_stats,
        'sales_trend' => [
            'labels' => $labels,
            'data' => $data
        ],
        'payment_methods' => $payment_methods
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 