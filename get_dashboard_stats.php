<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

function getDetailedStats($pdo, $type) {
    try {
        switch ($type) {
            case 'categories':
                $stmt = $pdo->query("
                    SELECT 
                        c.category_id,
                        c.category_name,
                        c.category_status,
                        COUNT(p.product_id) as product_count,
                        COALESCE(SUM(oi.quantity), 0) as items_sold,
                        COALESCE(SUM(oi.subtotal), 0) as total_revenue
                    FROM pos_category c
                    LEFT JOIN pos_product p ON c.category_id = p.category_id
                    LEFT JOIN pos_order_item oi ON p.product_id = oi.product_id
                    LEFT JOIN pos_order o ON oi.order_id = o.order_id AND o.status = 'completed'
                    WHERE c.category_status = 'Active'
                    GROUP BY c.category_id
                    ORDER BY total_revenue DESC
                ");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            case 'products':
                $stmt = $pdo->query("
                    SELECT 
                        p.product_id,
                        p.product_name,
                        p.product_price,
                        c.category_name,
                        COALESCE(SUM(oi.quantity), 0) as total_sold,
                        COALESCE(SUM(oi.subtotal), 0) as total_revenue,
                        COALESCE(i.current_stock, 0) as current_stock
                    FROM pos_product p
                    LEFT JOIN pos_category c ON p.category_id = c.category_id
                    LEFT JOIN pos_order_item oi ON p.product_id = oi.product_id
                    LEFT JOIN pos_order o ON oi.order_id = o.order_id AND o.status = 'completed'
                    LEFT JOIN (
                        SELECT product_id, SUM(quantity) as current_stock
                        FROM pos_inventory
                        GROUP BY product_id
                    ) i ON p.product_id = i.product_id
                    WHERE p.product_status = 'Available'
                    GROUP BY p.product_id
                    ORDER BY total_revenue DESC
                ");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            case 'branches':
                $stmt = $pdo->query("
                    SELECT 
                        b.branch_id,
                        b.branch_name,
                        b.branch_code,
                        COUNT(DISTINCT o.order_id) as total_orders,
                        COALESCE(SUM(o.order_total), 0) as total_revenue,
                        COUNT(DISTINCT cd.user_id) as total_cashiers,
                        COUNT(DISTINCT CASE WHEN cs.is_active = TRUE AND DATE(cs.login_time) = CURRENT_DATE() THEN cs.user_id END) as active_cashiers
                    FROM pos_branch b
                    LEFT JOIN pos_order o ON b.branch_id = o.branch_id AND o.status = 'completed'
                    LEFT JOIN pos_cashier_details cd ON b.branch_id = cd.branch_id
                    LEFT JOIN pos_cashier_sessions cs ON cd.user_id = cs.user_id
                    WHERE b.status = 'Active'
                    GROUP BY b.branch_id
                    ORDER BY total_revenue DESC
                ");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            case 'revenue':
                // Get revenue breakdown by time periods
                $daily = $pdo->query("
                    SELECT 
                        DATE(order_datetime) as date,
                        COUNT(DISTINCT order_id) as order_count,
                        SUM(order_total) as total_sales,
                        AVG(order_total) as avg_order_value
                    FROM pos_order 
                    WHERE status = 'completed'
                    AND DATE(order_datetime) >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                    GROUP BY DATE(order_datetime)
                    ORDER BY date DESC
                ")->fetchAll(PDO::FETCH_ASSOC);

                $monthly = $pdo->query("
                    SELECT 
                        DATE_FORMAT(order_datetime, '%Y-%m') as month,
                        COUNT(DISTINCT order_id) as order_count,
                        SUM(order_total) as total_sales,
                        AVG(order_total) as avg_order_value
                    FROM pos_order 
                    WHERE status = 'completed'
                    AND order_datetime >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(order_datetime, '%Y-%m')
                    ORDER BY month DESC
                ")->fetchAll(PDO::FETCH_ASSOC);

                // Get payment method breakdown
                $payment_methods = $pdo->query("
                    SELECT 
                        payment_method,
                        COUNT(*) as transaction_count,
                        SUM(order_total) as total_amount
                    FROM pos_order
                    WHERE status = 'completed'
                    GROUP BY payment_method
                ")->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'daily_revenue' => $daily,
                    'monthly_revenue' => $monthly,
                    'payment_methods' => $payment_methods
                ];
        }
    } catch (PDOException $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

function getDashboardOverview($pdo) {
    try {
        // Get total categories with product count
        $stmt = $pdo->query("
            SELECT COUNT(*) as total_categories,
            (
                SELECT COUNT(DISTINCT category_id) 
                FROM pos_product 
                WHERE product_status = 'Available'
            ) as active_categories
            FROM pos_category 
            WHERE category_status = 'Active'
        ");
        $category_stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get total products with availability breakdown
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_products,
                SUM(CASE WHEN product_status = 'Available' THEN 1 ELSE 0 END) as available_products,
                SUM(CASE WHEN product_status = 'Out of Stock' THEN 1 ELSE 0 END) as out_of_stock
            FROM pos_product
        ");
        $product_stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get total branches with activity stats
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_branches,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_branches,
                (
                    SELECT COUNT(DISTINCT branch_id) 
                    FROM pos_order 
                    WHERE DATE(order_datetime) = CURRENT_DATE
                    AND status = 'completed'
                ) as branches_with_sales_today
            FROM pos_branch
        ");
        $branch_stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get total revenue with detailed breakdown
        $stmt = $pdo->query("
            SELECT 
                COALESCE(SUM(order_total), 0) as total_revenue,
                COUNT(order_id) as total_orders,
                COALESCE(AVG(order_total), 0) as avg_order_value,
                (
                    SELECT COALESCE(SUM(order_total), 0)
                    FROM pos_order
                    WHERE DATE(order_datetime) = CURRENT_DATE
                    AND status = 'completed'
                ) as today_revenue,
                (
                    SELECT COALESCE(SUM(order_total), 0)
                    FROM pos_order
                    WHERE DATE(order_datetime) >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                    AND status = 'completed'
                ) as weekly_revenue,
                (
                    SELECT COALESCE(SUM(order_total), 0)
                    FROM pos_order
                    WHERE DATE(order_datetime) >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                    AND status = 'completed'
                ) as monthly_revenue
            FROM pos_order
            WHERE status = 'completed'
        ");
        $revenue_stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Format the overview response
        return [
            'success' => true,
            'data' => [
                'categories' => [
                    'total' => intval($category_stats['total_categories']),
                    'active' => intval($category_stats['active_categories']),
                    'with_products' => intval($category_stats['active_categories'])
                ],
                'products' => [
                    'total' => intval($product_stats['total_products']),
                    'available' => intval($product_stats['available_products']),
                    'out_of_stock' => intval($product_stats['out_of_stock'])
                ],
                'branches' => [
                    'total' => intval($branch_stats['total_branches']),
                    'active' => intval($branch_stats['active_branches']),
                    'with_sales_today' => intval($branch_stats['branches_with_sales_today'])
                ],
                'revenue' => [
                    'total' => floatval($revenue_stats['total_revenue']),
                    'today' => floatval($revenue_stats['today_revenue']),
                    'weekly' => floatval($revenue_stats['weekly_revenue']),
                    'monthly' => floatval($revenue_stats['monthly_revenue']),
                    'total_orders' => intval($revenue_stats['total_orders']),
                    'avg_order_value' => floatval($revenue_stats['avg_order_value'])
                ]
            ]
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ];
    }
}

function getDashboardStats($pdo) {
    try {
        // Get the overview statistics
        $overview = getDashboardOverview($pdo);
        if (!$overview['success']) {
            throw new PDOException($overview['error']);
        }

        $today = date('Y-m-d');
        
        // Get today's sales metrics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT order_id) as order_count,
                COALESCE(SUM(order_total), 0) as total_sales,
                COALESCE(AVG(order_total), 0) as avg_order_value
            FROM pos_order 
            WHERE DATE(order_datetime) = ? 
            AND status = 'completed'
        ");
        $stmt->execute([$today]);
        $today_sales = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get active cashiers count
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT cs.user_id) as active_cashiers
            FROM pos_cashier_sessions cs
            WHERE cs.is_active = TRUE 
            AND DATE(cs.login_time) = ?
        ");
        $stmt->execute([$today]);
        $active_cashiers = $stmt->fetchColumn();

        // Get top selling products today
        $stmt = $pdo->prepare("
            SELECT 
                p.product_name,
                COUNT(oi.order_id) as order_count,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.subtotal) as total_revenue
            FROM pos_order o
            JOIN pos_order_item oi ON o.order_id = oi.order_id
            JOIN pos_product p ON oi.product_id = p.product_id
            WHERE DATE(o.order_datetime) = ?
            AND o.status = 'completed'
            GROUP BY p.product_id, p.product_name
            ORDER BY total_revenue DESC
            LIMIT 5
        ");
        $stmt->execute([$today]);
        $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get low stock alerts
        $stmt = $pdo->query("
            SELECT 
                p.product_name,
                p.product_price,
                p.minimum_stock,
                COALESCE(i.current_stock, 0) as current_stock
            FROM pos_product p
            LEFT JOIN (
                SELECT product_id, SUM(quantity) as current_stock
                FROM pos_inventory
                GROUP BY product_id
            ) i ON p.product_id = i.product_id
            WHERE p.product_status = 'Available'
            AND COALESCE(i.current_stock, 0) <= p.minimum_stock
            ORDER BY (COALESCE(i.current_stock, 0) / p.minimum_stock)
            LIMIT 5
        ");
        $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get recent transactions
        $stmt = $pdo->prepare("
            SELECT 
                o.order_id,
                o.order_total,
                o.order_datetime,
                o.payment_method,
                u.user_name as cashier_name,
                b.branch_name,
                COUNT(oi.item_id) as item_count
            FROM pos_order o
            LEFT JOIN pos_user u ON o.order_created_by = u.user_id
            LEFT JOIN pos_branch b ON o.branch_id = b.branch_id
            LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
            WHERE o.status = 'completed'
            AND DATE(o.order_datetime) = ?
            GROUP BY o.order_id
            ORDER BY o.order_datetime DESC
            LIMIT 5
        ");
        $stmt->execute([$today]);
        $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update the response format to use the new overview
        return [
            'success' => true,
            'data' => [
                'overview' => $overview['data'],
                'today_sales' => [
                    'order_count' => intval($today_sales['order_count']),
                    'total_sales' => floatval($today_sales['total_sales']),
                    'avg_order_value' => floatval($today_sales['avg_order_value'])
                ],
                'active_cashiers' => intval($active_cashiers),
                'top_products' => array_map(function($product) {
                    return [
                        'name' => $product['product_name'],
                        'orders' => intval($product['order_count']),
                        'quantity' => intval($product['total_quantity']),
                        'revenue' => floatval($product['total_revenue'])
                    ];
                }, $top_products),
                'low_stock_alerts' => array_map(function($item) {
                    return [
                        'name' => $item['product_name'],
                        'current_stock' => intval($item['current_stock']),
                        'minimum_stock' => intval($item['minimum_stock']),
                        'price' => floatval($item['product_price'])
                    ];
                }, $low_stock),
                'recent_transactions' => array_map(function($tx) {
                    return [
                        'order_id' => $tx['order_id'],
                        'total' => floatval($tx['order_total']),
                        'datetime' => $tx['order_datetime'],
                        'payment_method' => $tx['payment_method'],
                        'cashier' => $tx['cashier_name'],
                        'branch' => $tx['branch_name'],
                        'items' => intval($tx['item_count'])
                    ];
                }, $recent_transactions)
            ]
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Handle the request
if (isset($_GET['type'])) {
    if ($_GET['type'] === 'overview') {
        echo json_encode(getDashboardOverview($pdo));
    } else {
        echo json_encode(['success' => true, 'data' => getDetailedStats($pdo, $_GET['type'])]);
    }
} else {
    echo json_encode(getDashboardStats($pdo));
} 