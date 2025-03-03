<?php

require_once 'db_connect.php';

$columns = [
    0 => 'user_id',
    1 => 'user_name',
    2 => 'user_email',
    3 => 'user_type',
    4 => 'user_status',
    5 => null
];

$limit = $_GET['length'];
$start = $_GET['start'];
$order = $columns[$_GET['order'][0]['column']];
$dir = $_GET['order'][0]['dir'];

$searchValue = $_GET['search']['value'];

// Get total records
$totalRecordsStmt = $pdo->query("SELECT COUNT(*) FROM pos_user");
$totalRecords = $totalRecordsStmt->fetchColumn();

// Get total filtered records
$filterQuery = "SELECT COUNT(*) FROM pos_user WHERE 1=1";
if (!empty($searchValue)) {
    $filterQuery .= " AND (user_name LIKE '%$searchValue%' OR user_email LIKE '%$searchValue%' OR user_type LIKE '%$searchValue%' OR user_status LIKE '%$searchValue%')";
}
$totalFilteredRecordsStmt = $pdo->query($filterQuery);
$totalFilteredRecords = $totalFilteredRecordsStmt->fetchColumn();

// Fetch data
$dataQuery = "SELECT * FROM pos_user WHERE 1=1";
if (!empty($searchValue)) {
    $dataQuery .= " AND (user_name LIKE '%$searchValue%' OR user_email LIKE '%$searchValue%' OR user_type LIKE '%$searchValue%' OR user_status LIKE '%$searchValue%')";
}
$dataQuery .= " ORDER BY $order $dir LIMIT $start, $limit";
$dataStmt = $pdo->query($dataQuery);
$data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    "draw"              => intval($_GET['draw']),
    "recordsTotal"      => intval($totalRecords),
    "recordsFiltered"   => intval($totalFilteredRecords),
    "data"              => $data
];

echo json_encode($response);

?>