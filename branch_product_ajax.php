<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Get pagination parameters
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

// Get search value
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Get ordering parameters
$orderColumn = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : 0;
$orderDir = isset($_GET['order'][0]['dir']) ? strtoupper($_GET['order'][0]['dir']) : 'ASC';

// Column mapping for ordering
$columns = [
    0 => 'b.branch_name',
    1 => 'p.product_name',
    2 => 'p.product_price',
    3 => 'bp.quantity',
    4 => 'p.product_status'
];

// Get the column name to order by
$orderColumnName = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'b.branch_name';

try {
    // Base query
    $baseQuery = "FROM pos_branch_product bp
                  JOIN pos_branch b ON bp.branch_id = b.branch_id
                  JOIN pos_product p ON bp.product_id = p.product_id";
    
    // Search condition
    $searchCondition = "";
    $params = [];
    if (!empty($search)) {
        $searchCondition = " WHERE (
            b.branch_name LIKE :search 
            OR p.product_name LIKE :search
            OR p.product_status LIKE :search
            OR CAST(p.product_price AS CHAR) LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

    // Add branch filter if user is a cashier
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Cashier') {
        $stmt = $pdo->prepare("SELECT branch_id FROM pos_cashier_details WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $branch_id = $stmt->fetchColumn();
        
        if ($branch_id) {
            $searchCondition .= empty($searchCondition) ? " WHERE " : " AND ";
            $searchCondition .= "bp.branch_id = :branch_id";
            $params[':branch_id'] = $branch_id;
        }
    }

    // Get total records
    $stmt = $pdo->query("SELECT COUNT(*) " . $baseQuery);
    $totalRecords = $stmt->fetchColumn();

    // Get filtered records count
    $stmt = $pdo->prepare("SELECT COUNT(*) " . $baseQuery . $searchCondition);
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $filteredRecords = $stmt->fetchColumn();

    // Main query for data
    $query = "SELECT 
        bp.branch_product_id,
        b.branch_name,
        p.product_name,
        p.product_price,
        bp.quantity,
        p.product_status,
        p.product_image,
        bp.created_at,
        bp.updated_at
    " . $baseQuery . $searchCondition;
    
    // Add ordering
    $query .= " ORDER BY " . $orderColumnName . " " . $orderDir;
    
    // Add pagination
    $query .= " LIMIT :start, :length";

    // Prepare and execute the final query
    $stmt = $pdo->prepare($query);
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    foreach ($data as &$row) {
        $row['product_image'] = !empty($row['product_image']) ? $row['product_image'] : 'uploads/no-image.jpg';
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $row['updated_at'] = date('Y-m-d H:i:s', strtotime($row['updated_at']));
    }

    // Prepare the response
    $response = [
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    // Handle any database errors
    $response = [
        "draw" => $draw,
        "error" => "Database error: " . $e->getMessage(),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ];
    echo json_encode($response);
} 