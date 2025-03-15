<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

// Get parameters from DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Base query
$base_query = "
    FROM pos_order o
    LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
    WHERE o.order_created_by = :user_id
    AND DATE(o.order_datetime) BETWEEN :start_date AND :end_date
";

// Search condition
if (!empty($search)) {
    $base_query .= " AND (
        o.order_number LIKE :search
        OR oi.product_name LIKE :search
    )";
}

// Count total records
$count_query = "SELECT COUNT(DISTINCT o.order_id) as total " . $base_query;
$stmt = $pdo->prepare($count_query);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':start_date', $start_date);
$stmt->bindValue(':end_date', $end_date);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get filtered records
$query = "
    SELECT 
        o.order_id,
        o.order_number,
        o.order_datetime,
        o.order_total,
        GROUP_CONCAT(CONCAT(oi.product_qty, 'x ', oi.product_name) SEPARATOR ', ') as items
    " . $base_query . "
    GROUP BY o.order_id
    ORDER BY o.order_datetime DESC
    LIMIT :start, :length
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':start_date', $start_date);
$stmt->bindValue(':end_date', $end_date);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare response
$response = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_records,
    'data' => $records
];

header('Content-Type: application/json');
echo json_encode($response); 