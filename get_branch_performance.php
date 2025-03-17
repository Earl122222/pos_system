<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    // Get sales performance by branch for today
    $sql = "SELECT 
                b.branch_name,
                COALESCE(SUM(o.total_amount), 0) as total_sales
            FROM pos_branch b
            LEFT JOIN pos_orders o ON b.branch_id = o.branch_id
                AND DATE(o.created_at) = CURDATE()
                AND o.status = 'Completed'
            WHERE b.status = 'Active'
            GROUP BY b.branch_id, b.branch_name
            ORDER BY total_sales DESC";

    $stmt = $pdo->query($sql);
    $branches = [];
    $sales = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $branches[] = $row['branch_name'];
        $sales[] = floatval($row['total_sales']);
    }

    echo json_encode([
        'labels' => $branches,
        'data' => $sales
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 