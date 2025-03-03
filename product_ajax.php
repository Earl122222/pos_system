<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

$columns = [
    0 => 'product_id',
    1 => 'category_name',
    2 => 'product_name',
    3 => 'product_price',
    4 => 'product_status',
    5 => null,
    6 => null
];

$limit = $_GET['length'];
$start = $_GET['start'];
$order = $columns[$_GET['order'][0]['column']];
$dir = $_GET['order'][0]['dir'];

$searchValue = $_GET['search']['value'];

// Get total records
$totalRecordsStmt = $pdo->query("SELECT COUNT(*) FROM pos_product");
$totalRecords = $totalRecordsStmt->fetchColumn();

// Get total filtered records
$filterQuery = "SELECT COUNT(*) FROM pos_product INNER JOIN pos_category ON pos_category.category_id = pos_product.category_id WHERE 1=1";
if (!empty($searchValue)) {
    $filterQuery .= " AND (pos_category.category_name LIKE '%$searchValue%' OR pos_product.product_name LIKE '%$searchValue%' OR pos_product.product_price LIKE '%$searchValue%' OR pos_product.product_status LIKE '%$searchValue%')";
}
$totalFilteredRecordsStmt = $pdo->query($filterQuery);
$totalFilteredRecords = $totalFilteredRecordsStmt->fetchColumn();

// Fetch data
$dataQuery = "SELECT * FROM pos_product INNER JOIN pos_category ON pos_category.category_id = pos_product.category_id WHERE 1=1";
if (!empty($searchValue)) {
    $dataQuery .= " AND (pos_category.category_name LIKE '%$searchValue%' OR pos_product.product_name LIKE '%$searchValue%' OR pos_product.product_price LIKE '%$searchValue%' OR pos_product.product_status LIKE '%$searchValue%')";
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