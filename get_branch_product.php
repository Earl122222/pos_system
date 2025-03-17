<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = array('success' => false);
    
    // Get and validate input
    $branch_product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($branch_product_id <= 0) {
        $response['message'] = 'Invalid input data';
        echo json_encode($response);
        exit;
    }
    
    // Get branch product details
    $query = "SELECT bp.*, b.branch_name, p.product_name 
              FROM pos_branch_product bp
              JOIN pos_branch b ON bp.branch_id = b.branch_id
              JOIN pos_product p ON bp.product_id = p.product_id
              WHERE bp.branch_product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $branch_product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'Branch product not found';
        echo json_encode($response);
        exit;
    }
    
    $branchProduct = $result->fetch_assoc();
    
    $response['success'] = true;
    $response['data'] = array(
        'branch_product_id' => $branchProduct['branch_product_id'],
        'branch_name' => $branchProduct['branch_name'],
        'product_name' => $branchProduct['product_name'],
        'quantity' => $branchProduct['quantity']
    );
    
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
} 