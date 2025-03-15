<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : 'daily';

try {
    // Get all active branches
    $branches = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $datasets = [];
    $colors = [
        '#8B4543', '#4A7C59', '#C4804D', '#A65D5D', '#6D788D',
        '#D4A59A', '#B33A3A', '#8592a3', '#E39191', '#F3B7B7'
    ];

    switch ($period) {
        case 'daily':
            // Last 7 days
            $start_date = date('Y-m-d', strtotime('-6 days'));
            $sql = "
                SELECT 
                    DATE(order_datetime) as date,
                    branch_id,
                    COALESCE(SUM(order_total), 0) as total_sales
                FROM pos_order
                WHERE order_datetime >= ?
                GROUP BY DATE(order_datetime), branch_id
                ORDER BY date
            ";
            
            // Generate labels for last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $labels[] = date('M d', strtotime("-$i days"));
            }
            break;

        case 'weekly':
            // Last 4 weeks
            $start_date = date('Y-m-d', strtotime('-3 weeks'));
            $sql = "
                SELECT 
                    DATE(DATE_SUB(order_datetime, INTERVAL WEEKDAY(order_datetime) DAY)) as week_start,
                    branch_id,
                    COALESCE(SUM(order_total), 0) as total_sales
                FROM pos_order
                WHERE order_datetime >= ?
                GROUP BY week_start, branch_id
                ORDER BY week_start
            ";
            
            // Generate labels for last 4 weeks
            for ($i = 3; $i >= 0; $i--) {
                $week_start = date('M d', strtotime("-$i weeks"));
                $labels[] = "Week of $week_start";
            }
            break;

        case 'monthly':
            // Last 6 months
            $start_date = date('Y-m-d', strtotime('-5 months'));
            $sql = "
                SELECT 
                    DATE_FORMAT(order_datetime, '%Y-%m-01') as month_start,
                    branch_id,
                    COALESCE(SUM(order_total), 0) as total_sales
                FROM pos_order
                WHERE order_datetime >= ?
                GROUP BY month_start, branch_id
                ORDER BY month_start
            ";
            
            // Generate labels for last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $labels[] = date('M Y', strtotime("-$i months"));
            }
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$start_date]);
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize sales data by branch
    $branch_sales = [];
    foreach ($sales_data as $row) {
        $branch_id = $row['branch_id'];
        if (!isset($branch_sales[$branch_id])) {
            $branch_sales[$branch_id] = array_fill(0, count($labels), 0);
        }
        
        // Find the index for this date in our labels
        $date_key = '';
        switch ($period) {
            case 'daily':
                $date_key = date('M d', strtotime($row['date']));
                break;
            case 'weekly':
                $date_key = "Week of " . date('M d', strtotime($row['week_start']));
                break;
            case 'monthly':
                $date_key = date('M Y', strtotime($row['month_start']));
                break;
        }
        
        $index = array_search($date_key, $labels);
        if ($index !== false) {
            $branch_sales[$branch_id][$index] = floatval($row['total_sales']);
        }
    }

    // Create datasets for each branch
    foreach ($branches as $index => $branch) {
        $color = $colors[$index % count($colors)];
        $datasets[] = [
            'label' => $branch['branch_name'],
            'data' => isset($branch_sales[$branch['branch_id']]) ? $branch_sales[$branch['branch_id']] : array_fill(0, count($labels), 0),
            'borderColor' => $color,
            'backgroundColor' => $color,
            'fill' => false,
            'tension' => 0.1
        ];
    }

    echo json_encode([
        'labels' => $labels,
        'datasets' => $datasets
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 