<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    // Check if branch_id is provided
    if (!isset($_GET['branch_id'])) {
        throw new Exception('Branch ID is required');
    }

    $branchId = $_GET['branch_id'];
    $period = $_GET['period'] ?? 'today';

    // Get today's statistics
    $todayStats = getTodayStats($pdo, $branchId);

    // Get sales trend data
    $salesTrend = getSalesTrend($pdo, $branchId, $period);

    // Get payment methods distribution
    $paymentMethods = getPaymentMethods($pdo, $branchId);

    echo json_encode([
        'today_stats' => $todayStats,
        'sales_trend' => $salesTrend,
        'payment_methods' => $paymentMethods
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getTodayStats($pdo, $branchId) {
    $sql = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(AVG(total_amount), 0) as average_sale,
                COALESCE(MAX(total_amount), 0) as highest_sale
            FROM pos_orders 
            WHERE branch_id = :branch_id 
            AND DATE(created_at) = CURDATE()
            AND status = 'Completed'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['branch_id' => $branchId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSalesTrend($pdo, $branchId, $period) {
    $labels = [];
    $data = [];

    switch ($period) {
        case 'today':
            // Get hourly sales for today
            $sql = "SELECT 
                        HOUR(created_at) as hour,
                        COALESCE(SUM(total_amount), 0) as total
                    FROM pos_orders 
                    WHERE branch_id = :branch_id 
                    AND DATE(created_at) = CURDATE()
                    AND status = 'Completed'
                    GROUP BY HOUR(created_at)
                    ORDER BY HOUR(created_at)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['branch_id' => $branchId]);
            
            // Initialize all hours with 0
            for ($i = 0; $i < 24; $i++) {
                $labels[] = sprintf("%02d:00", $i);
                $data[$i] = 0;
            }

            // Fill in actual data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$row['hour']] = floatval($row['total']);
            }
            break;

        case 'week':
            // Get daily sales for the past week
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COALESCE(SUM(total_amount), 0) as total
                    FROM pos_orders 
                    WHERE branch_id = :branch_id 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    AND status = 'Completed'
                    GROUP BY DATE(created_at)
                    ORDER BY DATE(created_at)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['branch_id' => $branchId]);

            // Get the past 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('M d', strtotime($date));
                $data[$date] = 0;
            }

            // Fill in actual data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$row['date']] = floatval($row['total']);
            }
            break;

        case 'month':
            // Get daily sales for the current month
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COALESCE(SUM(total_amount), 0) as total
                    FROM pos_orders 
                    WHERE branch_id = :branch_id 
                    AND MONTH(created_at) = MONTH(CURRENT_DATE())
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    AND status = 'Completed'
                    GROUP BY DATE(created_at)
                    ORDER BY DATE(created_at)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['branch_id' => $branchId]);

            // Get all days in current month up to today
            $firstDay = date('Y-m-01');
            $today = date('Y-m-d');
            $current = $firstDay;

            while (strtotime($current) <= strtotime($today)) {
                $labels[] = date('M d', strtotime($current));
                $data[$current] = 0;
                $current = date('Y-m-d', strtotime($current . ' +1 day'));
            }

            // Fill in actual data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$row['date']] = floatval($row['total']);
            }
            break;
    }

    return [
        'labels' => $labels,
        'data' => array_values($data)
    ];
}

function getPaymentMethods($pdo, $branchId) {
    $sql = "SELECT 
                payment_method,
                COUNT(*) as count
            FROM pos_orders 
            WHERE branch_id = :branch_id 
            AND DATE(created_at) = CURDATE()
            AND status = 'Completed'
            GROUP BY payment_method";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['branch_id' => $branchId]);

    $methods = [
        'cash' => 0,
        'credit_card' => 0,
        'e_wallet' => 0
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $method = strtolower(str_replace(' ', '_', $row['payment_method']));
        if (isset($methods[$method])) {
            $methods[$method] = intval($row['count']);
        }
    }

    return $methods;
}
?> 