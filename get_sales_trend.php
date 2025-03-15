<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
    $branch = isset($_GET['branch']) ? $_GET['branch'] : 'main';

    // Initialize response arrays
    $labels = [];
    $data = [];

    // Set the date format and range based on period
    switch($period) {
        case 'monthly':
            $date_format = '%Y-%m';
            $interval = 'INTERVAL 12 MONTH';
            $group_by = "DATE_FORMAT(o.order_datetime, '%Y-%m')";
            break;
        case 'weekly':
            $date_format = '%Y-%u';
            $interval = 'INTERVAL 12 WEEK';
            $group_by = "DATE_FORMAT(o.order_datetime, '%Y-%u')";
            break;
        default: // daily
            $date_format = '%Y-%m-%d';
            $interval = 'INTERVAL 30 DAY';
            $group_by = "DATE(o.order_datetime)";
            break;
    }

    // Get sales data
    $sql = "
        SELECT 
            $group_by as date_group,
            SUM(o.order_total) as total_sales
        FROM pos_order o
        WHERE o.order_datetime >= DATE_SUB(CURRENT_DATE, $interval)
        GROUP BY date_group
        ORDER BY date_group ASC
    ";

    $stmt = $pdo->query($sql);
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data based on period
    foreach ($sales_data as $row) {
        switch($period) {
            case 'monthly':
                $date = date('M Y', strtotime($row['date_group'] . '-01'));
                break;
            case 'weekly':
                $year = substr($row['date_group'], 0, 4);
                $week = substr($row['date_group'], 5);
                $date = "Week $week, $year";
                break;
            default:
                $date = date('M d', strtotime($row['date_group']));
                break;
        }
        
        $labels[] = $date;
        $data[] = floatval($row['total_sales']);
    }

    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 