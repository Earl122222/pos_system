<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

function calculatePerformanceRating($metrics) {
    // Base score starts at 100
    $score = 100;
    
    // Calculate average order value impact (-5 to +5 points)
    $avgOrderTarget = 500; // Target average order value
    $avgOrderDiff = (($metrics['avg_order_value'] - $avgOrderTarget) / $avgOrderTarget) * 5;
    $score += max(-5, min(5, $avgOrderDiff));
    
    // Transaction speed impact (-10 to +10 points)
    $targetSpeed = 5; // Target minutes per transaction
    if ($metrics['avg_transaction_time'] > 0) {
        $speedDiff = (($targetSpeed - $metrics['avg_transaction_time']) / $targetSpeed) * 10;
        $score += max(-10, min(10, $speedDiff));
    }
    
    // Sales mix impact (up to +5 points)
    $totalOrders = $metrics['dine_in_orders'] + $metrics['takeout_orders'] + $metrics['delivery_orders'];
    if ($totalOrders > 0) {
        $salesMixScore = min(5, ($metrics['dine_in_orders'] / $totalOrders) * 3 +
                              ($metrics['delivery_orders'] / $totalOrders) * 2);
        $score += $salesMixScore;
    }
    
    // Payment method diversity impact (up to +5 points)
    $totalSales = $metrics['cash_sales'] + $metrics['card_sales'] + $metrics['ewallet_sales'];
    if ($totalSales > 0) {
        $methodCount = 0;
        if ($metrics['cash_sales'] > 0) $methodCount++;
        if ($metrics['card_sales'] > 0) $methodCount++;
        if ($metrics['ewallet_sales'] > 0) $methodCount++;
        $score += $methodCount * 1.67; // Up to 5 points for using all methods
    }
    
    return [
        'score' => round($score, 1),
        'rating' => $score >= 95 ? 'Excellent' :
                   ($score >= 85 ? 'Good' :
                   ($score >= 75 ? 'Average' : 'Needs Improvement')),
        'metrics' => [
            'avg_order_impact' => round($avgOrderDiff, 1),
            'speed_impact' => isset($speedDiff) ? round($speedDiff, 1) : 0,
            'sales_mix_impact' => isset($salesMixScore) ? round($salesMixScore, 1) : 0,
            'payment_diversity' => $methodCount ?? 0
        ]
    ];
}

try {
    $period = $_GET['period'] ?? 'today';
    $today = date('Y-m-d');

    // Get active cashiers count and details with enhanced metrics
    $stmt = $pdo->prepare("
        SELECT 
            u.user_id,
            u.user_name,
            u.profile_image,
            b.branch_name,
            cs.is_active,
            COUNT(DISTINCT o.order_id) as total_transactions,
            COALESCE(SUM(o.order_total), 0) as total_sales,
            COALESCE(AVG(TIMESTAMPDIFF(MINUTE, o.order_datetime, o.completed_at)), 0) as avg_transaction_time,
            COALESCE(SUM(CASE WHEN o.payment_method = 'cash' THEN o.order_total ELSE 0 END), 0) as cash_sales,
            COALESCE(SUM(CASE WHEN o.payment_method = 'credit_card' THEN o.order_total ELSE 0 END), 0) as card_sales,
            COALESCE(SUM(CASE WHEN o.payment_method = 'e_wallet' THEN o.order_total ELSE 0 END), 0) as ewallet_sales,
            COALESCE(AVG(o.order_total), 0) as avg_order_value,
            COUNT(DISTINCT CASE WHEN o.service_type = 'dine-in' THEN o.order_id END) as dine_in_orders,
            COUNT(DISTINCT CASE WHEN o.service_type = 'takeout' THEN o.order_id END) as takeout_orders,
            COUNT(DISTINCT CASE WHEN o.service_type = 'delivery' THEN o.order_id END) as delivery_orders,
            COALESCE(AVG(oi.item_count), 0) as avg_items_per_order,
            MAX(o.order_total) as highest_sale,
            COUNT(DISTINCT CASE WHEN o.order_total > (
                SELECT AVG(order_total) * 1.5 
                FROM pos_order 
                WHERE DATE(order_datetime) = CURRENT_DATE()
            ) THEN o.order_id END) as high_value_orders
        FROM pos_user u
        LEFT JOIN pos_cashier_details cd ON u.user_id = cd.user_id
        LEFT JOIN pos_branch b ON cd.branch_id = b.branch_id
        LEFT JOIN pos_cashier_sessions cs ON u.user_id = cs.user_id 
            AND cs.is_active = TRUE 
            AND DATE(cs.login_time) = CURRENT_DATE()
        LEFT JOIN pos_order o ON u.user_id = o.order_created_by
            AND o.status = 'completed'
            AND CASE 
                WHEN ? = 'today' THEN DATE(o.order_datetime) = CURRENT_DATE()
                WHEN ? = 'week' THEN o.order_datetime >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
                WHEN ? = 'month' THEN o.order_datetime >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            END
        LEFT JOIN (
            SELECT order_id, COUNT(*) as item_count 
            FROM pos_order_item 
            GROUP BY order_id
        ) oi ON o.order_id = oi.order_id
        WHERE u.user_type = 'Cashier'
        AND u.user_status = 'Active'
        GROUP BY u.user_id, u.user_name, u.profile_image, b.branch_name, cs.is_active
        ORDER BY cs.is_active DESC, total_sales DESC
    ");
    
    $stmt->execute([$period, $period, $period]);
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate summary statistics
    $active_cashiers = 0;
    $total_transactions = 0;
    $total_sales = 0;
    $total_time = 0;

    foreach ($cashiers as &$cashier) {
        // Format profile image URL
        $cashier['profile_image'] = !empty($cashier['profile_image']) ? 
            $cashier['profile_image'] : 'uploads/profiles/default.png';

        // Count active cashiers
        if ($cashier['is_active']) {
            $active_cashiers++;
        }

        // Sum up totals
        $total_transactions += $cashier['total_transactions'];
        $total_sales += $cashier['total_sales'];
        $total_time += ($cashier['avg_transaction_time'] * $cashier['total_transactions']);

        // Calculate performance rating
        $performance = calculatePerformanceRating($cashier);
        $cashier['performance_score'] = $performance['score'];
        $cashier['performance_rating'] = $performance['rating'];
        $cashier['performance_metrics'] = $performance['metrics'];

        // Format the average transaction time
        $cashier['avg_time'] = number_format($cashier['avg_transaction_time'], 1) . 'm';
    }

    // Calculate overall average transaction time
    $avg_transaction_time = $total_transactions > 0 ? 
        number_format($total_time / $total_transactions, 1) . 'm' : '0m';

    echo json_encode([
        'success' => true,
        'active_cashiers' => $active_cashiers,
        'total_transactions' => $total_transactions,
        'total_sales' => $total_sales,
        'avg_transaction_time' => $avg_transaction_time,
        'cashiers' => array_map(function($cashier) {
            return [
                'id' => $cashier['user_id'],
                'name' => $cashier['user_name'],
                'profile_image' => $cashier['profile_image'],
                'branch' => $cashier['branch_name'],
                'transactions' => $cashier['total_transactions'],
                'sales' => $cashier['total_sales'],
                'avg_time' => $cashier['avg_time'],
                'is_active' => (bool)$cashier['is_active'],
                'cash_sales' => $cashier['cash_sales'],
                'card_sales' => $cashier['card_sales'],
                'ewallet_sales' => $cashier['ewallet_sales'],
                'avg_order_value' => $cashier['avg_order_value'],
                'dine_in_orders' => $cashier['dine_in_orders'],
                'takeout_orders' => $cashier['takeout_orders'],
                'delivery_orders' => $cashier['delivery_orders'],
                'avg_items_per_order' => round($cashier['avg_items_per_order'], 1),
                'highest_sale' => $cashier['highest_sale'],
                'high_value_orders' => $cashier['high_value_orders'],
                'performance_score' => $cashier['performance_score'],
                'performance_rating' => $cashier['performance_rating'],
                'performance_metrics' => $cashier['performance_metrics']
            ];
        }, $cashiers)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 