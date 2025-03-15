<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    // Get filter parameters
    $branch = isset($_POST['branch']) ? $_POST['branch'] : 'all';
    $status = isset($_POST['status']) ? $_POST['status'] : 'all';
    
    // Base query
    $query = "SELECT r.*, b.branch_name 
              FROM ingredient_requests r 
              LEFT JOIN branches b ON r.branch_id = b.branch_id 
              WHERE 1=1";
    
    // Apply filters
    if ($branch !== 'all') {
        $query .= " AND r.branch_id = :branch";
    }
    if ($status !== 'all') {
        $query .= " AND r.status = :status";
    }
    
    // Prepare and execute query
    $stmt = $pdo->prepare($query);
    
    if ($branch !== 'all') {
        $stmt->bindParam(':branch', $branch);
    }
    if ($status !== 'all') {
        $stmt->bindParam(':status', $status);
    }
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = array();
    foreach ($requests as $request) {
        $data[] = array(
            'request_id' => $request['request_id'],
            'branch_name' => $request['branch_name'],
            'request_date' => $request['request_date'],
            'ingredients' => $request['ingredients'],
            'status' => $request['status']
        );
    }
    
    echo json_encode(array(
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
} 