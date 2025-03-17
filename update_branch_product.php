<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array('success' => false);
    
    // Get and validate input
    $branch_product_id = isset($_POST['branch_product_id']) ? intval($_POST['branch_product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    if ($branch_product_id <= 0 || $quantity < 0) {
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
    
    $branchProduct = $result->fetch_assoc();
    
    // Check if branch and product are still active
    if ($branchProduct['branch_status'] !== 'active') {
        $response['message'] = 'Branch is not active';
        echo json_encode($response);
        exit;
    }
    
    if ($branchProduct['product_status'] !== 'active') {
        $response['message'] = 'Product is not active';
        echo json_encode($response);
        exit;
    }
    
    // Update the quantity
    $updateQuery = "UPDATE pos_branch_product SET quantity = ? WHERE branch_product_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ii', $quantity, $branch_product_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Quantity updated successfully';
    } else {
        $response['message'] = 'Error updating quantity';
    }
    
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
} 