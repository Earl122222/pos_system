<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Get pagination parameters
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

// Get search value
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Get ordering parameters
$orderColumn = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : 1;
$orderDir = isset($_GET['order'][0]['dir']) ? strtoupper($_GET['order'][0]['dir']) : 'ASC';

// Column mapping for ordering
$columns = [
    0 => 'branch_code',
    1 => 'branch_name',
    2 => 'contact_number',
    3 => 'email',
    4 => 'complete_address',
    5 => 'operating_hours',
    6 => 'status'
];

// Get the column name to order by
$orderColumnName = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'branch_name';

try {
    // Base query
    $baseQuery = "FROM pos_branch";
    
    // Search condition
    $searchCondition = "";
    $params = [];
    if (!empty($search)) {
        $searchCondition = " WHERE (
            branch_code LIKE :search 
            OR branch_name LIKE :search
            OR contact_number LIKE :search
            OR email LIKE :search
            OR complete_address LIKE :search
            OR operating_hours LIKE :search
            OR status LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

    // Get total records without filtering
    $stmt = $pdo->query("SELECT COUNT(*) FROM pos_branch");
    $totalRecords = $stmt->fetchColumn();

    // Get filtered records count
    $stmt = $pdo->prepare("SELECT COUNT(*) " . $baseQuery . $searchCondition);
    if (!empty($search)) {
        $stmt->bindParam(':search', $params[':search']);
    }
    $stmt->execute();
    $filteredRecords = $stmt->fetchColumn();

    // Main query for data
    $query = "SELECT 
        branch_id,
        branch_code,
        branch_name,
        contact_number,
        email,
        complete_address,
        operating_hours,
        status
    " . $baseQuery . $searchCondition;
    
    // Add ordering
    $query .= " ORDER BY " . $orderColumnName . " " . $orderDir;
    
    // Add pagination
    $query .= " LIMIT :start, :length";

    // Prepare and execute the final query
    $stmt = $pdo->prepare($query);
    if (!empty($search)) {
        $stmt->bindParam(':search', $params[':search']);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response
    $response = [
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data
    ];

    header('Content-Type: application/json');
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
    header('Content-Type: application/json');
    echo json_encode($response);
}
?> 