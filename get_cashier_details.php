<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Cashier ID is required');
    }

    $cashierId = $_GET['id'];
    $today = date('Y-m-d');

    // Get hourly sales data
    $stmt = $pdo->prepare("
        SELECT 
            HOUR(order_datetime) as hour,
            COUNT(order_id) as order_count,
            SUM(order_total) as total_sales,
            SUM(CASE WHEN payment_method = 'cash' THEN order_total ELSE 0 END) as cash_sales,
            SUM(CASE WHEN payment_method = 'credit_card' THEN order_total ELSE 0 END) as card_sales,
            SUM(CASE WHEN payment_method = 'e_wallet' THEN order_total ELSE 0 END) as ewallet_sales,
            COUNT(CASE WHEN service_type = 'dine-in' THEN 1 END) as dine_in_count,
            COUNT(CASE WHEN service_type = 'takeout' THEN 1 END) as takeout_count,
            COUNT(CASE WHEN service_type = 'delivery' THEN 1 END) as delivery_count
        FROM pos_order
        WHERE order_created_by = ?
        AND DATE(order_datetime) = CURRENT_DATE()
        AND status = 'completed'
        GROUP BY HOUR(order_datetime)
        ORDER BY hour
    ");
    
    $stmt->execute([$cashierId]);
    $hourly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format hourly sales data
    $hours = range(0, 23);
    $hourly_sales = [
        'labels' => [],
        'data' => []
    ];

    foreach ($hours as $hour) {
        $found = false;
        foreach ($hourly_data as $data) {
            if ((int)$data['hour'] === $hour) {
                $hourly_sales['labels'][] = sprintf('%02d:00', $hour);
                $hourly_sales['data'][] = (float)$data['total_sales'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $hourly_sales['labels'][] = sprintf('%02d:00', $hour);
            $hourly_sales['data'][] = 0;
        }
    }

    // Get payment methods distribution
    $stmt = $pdo->prepare("
        SELECT 
            payment_method,
            COUNT(*) as count,
            SUM(order_total) as total,
            AVG(order_total) as avg_order_value,
            COUNT(CASE WHEN service_type = 'dine-in' THEN 1 END) as dine_in_count,
            COUNT(CASE WHEN service_type = 'takeout' THEN 1 END) as takeout_count,
            COUNT(CASE WHEN service_type = 'delivery' THEN 1 END) as delivery_count
        FROM pos_order
        WHERE order_created_by = ?
        AND DATE(order_datetime) = CURRENT_DATE()
        AND status = 'completed'
        GROUP BY payment_method
    ");
    
    $stmt->execute([$cashierId]);
    $payment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $payment_methods = [
        'labels' => [],
        'data' => []
    ];

    foreach ($payment_data as $data) {
        $payment_methods['labels'][] = $data['payment_method'];
        $payment_methods['data'][] = (int)$data['count'];
    }

    // Get recent transactions
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id,
            o.order_datetime,
            o.order_total,
            o.payment_method,
            o.service_type,
            o.status,
            o.order_subtotal,
            o.order_tax,
            o.order_discount,
            o.discount_type,
            COUNT(oi.item_id) as item_count
        FROM pos_order o
        LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
        WHERE o.order_created_by = ?
        AND DATE(o.order_datetime) = CURRENT_DATE()
        GROUP BY o.order_id
        ORDER BY o.order_datetime DESC
        LIMIT 10
    ");
    
    $stmt->execute([$cashierId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format transactions data
    $formatted_transactions = array_map(function($tx) {
        return [
            'time' => date('H:i', strtotime($tx['order_datetime'])),
            'order_id' => $tx['order_id'],
            'items' => $tx['item_count'],
            'subtotal' => $tx['order_subtotal'],
            'tax' => $tx['order_tax'],
            'discount' => $tx['order_discount'],
            'discount_type' => $tx['discount_type'],
            'total' => $tx['order_total'],
            'payment_method' => $tx['payment_method'],
            'service_type' => $tx['service_type'],
            'status' => $tx['status']
        ];
    }, $transactions);

    // Get cashier information
    $stmt = $pdo->prepare("
        SELECT 
            u.user_name,
            u.profile_image,
            b.branch_name,
            COUNT(DISTINCT o.order_id) as total_orders,
            COALESCE(SUM(o.order_total), 0) as total_sales,
            COALESCE(AVG(TIMESTAMPDIFF(MINUTE, o.order_datetime, o.completed_at)), 0) as avg_transaction_time
        FROM pos_user u
        LEFT JOIN pos_cashier_details cd ON u.user_id = cd.user_id
        LEFT JOIN pos_branch b ON cd.branch_id = b.branch_id
        LEFT JOIN pos_order o ON u.user_id = o.order_created_by
            AND DATE(o.order_datetime) = CURRENT_DATE()
            AND o.status = 'Completed'
        WHERE u.user_id = ?
        GROUP BY u.user_id, u.user_name, u.profile_image, b.branch_name
    ");
    
    $stmt->execute([$cashierId]);
    $cashier_info = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'cashier_info' => [
            'name' => $cashier_info['user_name'],
            'profile_image' => !empty($cashier_info['profile_image']) ? 
                $cashier_info['profile_image'] : 'uploads/profiles/default.png',
            'branch' => $cashier_info['branch_name'],
            'total_orders' => $cashier_info['total_orders'],
            'total_sales' => $cashier_info['total_sales'],
            'avg_transaction_time' => number_format($cashier_info['avg_transaction_time'], 1) . 'm'
        ],
        'hourly_sales' => $hourly_sales,
        'payment_methods' => $payment_methods,
        'transactions' => $formatted_transactions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 