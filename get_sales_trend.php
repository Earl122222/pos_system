<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $period = $_GET['period'] ?? 'daily';
    $labels = [];
    $data = [];

    switch ($period) {
        case 'daily':
            // Get hourly sales for today
            $sql = "SELECT 
                        HOUR(created_at) as hour,
                        COALESCE(SUM(total_amount), 0) as total
                    FROM pos_orders 
                    WHERE DATE(created_at) = CURDATE()
                    AND status = 'Completed'
                    GROUP BY HOUR(created_at)
                    ORDER BY HOUR(created_at)";

            $stmt = $pdo->query($sql);
            
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

        case 'weekly':
            // Get daily sales for the past week
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COALESCE(SUM(total_amount), 0) as total
                    FROM pos_orders 
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    AND status = 'Completed'
                    GROUP BY DATE(created_at)
                    ORDER BY DATE(created_at)";

            $stmt = $pdo->query($sql);

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

        case 'monthly':
            // Get monthly sales for the past 6 months
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COALESCE(SUM(total_amount), 0) as total
                    FROM pos_orders 
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                    AND status = 'Completed'
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month";

            $stmt = $pdo->query($sql);

            // Get the past 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $labels[] = date('M Y', strtotime($month));
                $data[$month] = 0;
            }

            // Fill in actual data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$row['month']] = floatval($row['total']);
            }
            break;
    }

    echo json_encode([
        'labels' => $labels,
        'data' => array_values($data)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 