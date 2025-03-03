<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

// Get the date for 7 days ago
$start_date = date('Y-m-d', strtotime('-6 days'));
$end_date = date('Y-m-d');

$sql = '';
if($_SESSION['user_type'] === 'Admin'){
    $sql = "
    SELECT DATE(order_datetime) as date, SUM(order_total) as total
    FROM pos_order
    WHERE DATE(order_datetime) BETWEEN ? AND ?
    GROUP BY DATE(order_datetime)
    ORDER BY DATE(order_datetime)
    ";
} else {
    $sql = "
    SELECT DATE(order_datetime) as date, SUM(order_total) as total
    FROM pos_order
    WHERE order_created_by = '".$_SESSION['user_id']."' AND order_datetime BETWEEN ? AND ?
    GROUP BY DATE(order_datetime)
    ORDER BY DATE(order_datetime)
    ";
}

// Prepare SQL query to get daily order values
$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JSON output
$dates = [];
$totals = [];
foreach ($data as $row) {
    $dates[] = $row['date'];
    $totals[] = (float)$row['total'];
}

// Output JSON
echo json_encode(['dates' => $dates, 'totals' => $totals]);

?>