<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();

// Get filter parameters
$period = isset($_GET['period']) ? $_GET['period'] : 'day';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');

try {
    // Set the date format and range based on period
    switch($period) {
        case 'month':
            $date_format = '%Y-%m';
            $date_sql = "DATE_FORMAT(o.order_datetime, '%Y-%m')";
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-t', strtotime($start_date));
            break;
        case 'year':
            $date_format = '%Y';
            $date_sql = "DATE_FORMAT(o.order_datetime, '%Y')";
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($start_date));
            break;
        default: // day
            $date_format = '%Y-%m-%d';
            $date_sql = "DATE(o.order_datetime)";
            $end_date = $start_date;
            break;
    }

    // First, get the top 5 most sold products for the selected period
    $top_products_sql = "
        SELECT 
            oi.product_name,
            SUM(oi.product_qty) as total_quantity
        FROM pos_order o
        JOIN pos_order_item oi ON o.order_id = oi.order_id
        WHERE o.order_datetime BETWEEN :start_date AND :end_date
        GROUP BY oi.product_name
        ORDER BY total_quantity DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($top_products_sql);
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
    $top_products = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if (empty($top_products)) {
        // Return empty dataset if no products found
        echo json_encode([
            'labels' => [],
            'datasets' => []
        ]);
        exit;
    }

    // Get daily sales data for these products
    $sql = "
        SELECT 
            oi.product_name,
            $date_sql as date_group,
            SUM(oi.product_qty) as total_quantity
        FROM pos_order o
        JOIN pos_order_item oi ON o.order_id = oi.order_id
        WHERE o.order_datetime BETWEEN :start_date AND :end_date 
        AND oi.product_name IN (" . str_repeat('?,', count($top_products) - 1) . "?)
        GROUP BY oi.product_name, date_group
        ORDER BY date_group ASC, total_quantity DESC
    ";

    $stmt = $pdo->prepare($sql);
    $params = array_merge([$start_date, $end_date], $top_products);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate date range
    $dates = [];
    $current = new DateTime($start_date);
    $last = new DateTime($end_date);
    $interval = new DateInterval(($period === 'year' ? 'P1M' : ($period === 'month' ? 'P1D' : 'P1D')));
    
    while ($current <= $last) {
        $dates[] = $current->format($period === 'year' ? 'Y' : ($period === 'month' ? 'Y-m' : 'Y-m-d'));
        $current->add($interval);
    }

    // Colors for different products
    $colors = [
        'rgba(231, 76, 60, 0.8)',   // Red
        'rgba(52, 152, 219, 0.8)',  // Blue
        'rgba(46, 204, 113, 0.8)',  // Green
        'rgba(241, 196, 15, 0.8)',  // Yellow
        'rgba(155, 89, 182, 0.8)'   // Purple
    ];

    // Prepare datasets
    $datasets = [];
    foreach ($top_products as $index => $product) {
        $productData = array_fill(0, count($dates), 0); // Initialize with zeros
        
        // Fill in actual data where it exists
        foreach ($data as $row) {
            if ($row['product_name'] === $product) {
                $dateIndex = array_search($row['date_group'], $dates);
                if ($dateIndex !== false) {
                    $productData[$dateIndex] = (int)$row['total_quantity'];
                }
            }
        }

        $datasets[] = [
            'label' => $product,
            'data' => $productData,
            'backgroundColor' => $colors[$index],
            'borderColor' => $colors[$index],
            'borderWidth' => 2,
            'fill' => false,
            'tension' => 0.4
        ];
    }

    // Format dates for display
    $display_dates = array_map(function($date) use ($period) {
        switch($period) {
            case 'year':
                return date('Y', strtotime($date));
            case 'month':
                return date('M Y', strtotime($date));
            default:
                return date('M d', strtotime($date));
        }
    }, $dates);

    // Prepare response
    $response = [
        'labels' => $display_dates,
        'datasets' => $datasets
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
} 