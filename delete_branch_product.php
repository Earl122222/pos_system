<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array('success' => false);
    
    // Get and validate input
    $branch_product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($branch_product_id <= 0) {
        $response['message'] = 'Invalid input data';
        echo json_encode($response);
        exit;
    }
    
    // Check if branch product exists
    $checkQuery = "SELECT bp.*, b.status as branch_status, p.status as product_status 
                  FROM pos_branch_product bp
                  JOIN pos_branch b ON bp.branch_id = b.branch_id
                  JOIN pos_product p ON bp.product_id = p.product_id
                  WHERE bp.branch_product_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('i', $branch_product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'Branch product not found';
        echo json_encode($response);
        exit;
    }
    
    // Delete the branch product
    $deleteQuery = "DELETE FROM pos_branch_product WHERE branch_product_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $branch_product_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Branch product deleted successfully';
    } else {
        $response['message'] = 'Error deleting branch product';
    }
    
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
} 