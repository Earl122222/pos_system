<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkOrderAccess();

header('Content-Type: application/json');

try {
    $today = date('Y-m-d');
    $quantity_period = $_GET['quantity_period'] ?? 'hourly';
    $total_period = $_GET['total_period'] ?? 'hourly';
    
    // Initialize response arrays
    $labels = [];
    $quantities = [];
    $totals = [];
    
    // Get order data based on selected period
    if ($quantity_period === 'hourly') {
        // For hourly data (today)
        $sql = "
            SELECT 
                DATE_FORMAT(o.order_datetime, '%H:00') as period,
                COUNT(DISTINCT o.order_id) as order_count,
                SUM(o.order_total) as total_amount
            FROM pos_order o
            WHERE DATE(o.order_datetime) = ?
            AND o.order_created_by = ?
            GROUP BY DATE_FORMAT(o.order_datetime, '%H:00')
            ORDER BY period
        ";
        
        // Generate all hourly periods for today
        for ($i = 0; $i < 24; $i++) {
            $hour = sprintf("%02d:00", $i);
            $labels[] = $hour;
            $quantities[$hour] = 0;
            $totals[$hour] = 0;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$today, $_SESSION['user_id']]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $quantities[$row['period']] = intval($row['order_count']);
            $totals[$row['period']] = floatval($row['total_amount']);
        }
        
        $quantities = array_values($quantities);
        $totals = array_values($totals);
    } else {
        // For daily data (last 30 days)
        $sql = "
            SELECT 
                DATE(o.order_datetime) as period,
                COUNT(DISTINCT o.order_id) as order_count,
                SUM(o.order_total) as total_amount
            FROM pos_order o
            WHERE o.order_datetime >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            AND o.order_created_by = ?
            GROUP BY DATE(o.order_datetime)
            ORDER BY period
        ";
        
        // Generate last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M d', strtotime($date));
            $quantities[$date] = 0;
            $totals[$date] = 0;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = $row['period'];
            $quantities[$date] = intval($row['order_count']);
            $totals[$date] = floatval($row['total_amount']);
        }
        
        $quantities = array_values($quantities);
        $totals = array_values($totals);
    }
    
    echo json_encode([
        'labels' => $labels,
        'quantities' => $quantities,
        'totals' => $totals
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 