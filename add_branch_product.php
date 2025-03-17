<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array('success' => false);
    
    // Get and validate input
    $branch_id = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    if ($branch_id <= 0 || $product_id <= 0 || $quantity < 0) {
        $response['message'] = 'Invalid input data';
        echo json_encode($response);
        exit;
    }
    
    // Check if branch exists and is active
    $branchQuery = "SELECT status FROM pos_branch WHERE branch_id = ?";
    $stmt = $conn->prepare($branchQuery);
    $stmt->bind_param('i', $branch_id);
    $stmt->execute();
    $branchResult = $stmt->get_result();
    
    if ($branchResult->num_rows === 0) {
        $response['message'] = 'Branch not found';
        echo json_encode($response);
        exit;
    }
    
    $branchStatus = $branchResult->fetch_assoc()['status'];
    if ($branchStatus !== 'active') {
        $response['message'] = 'Branch is not active';
        echo json_encode($response);
        exit;
    }
    
    // Check if product exists and is active
    $productQuery = "SELECT status FROM pos_product WHERE product_id = ?";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $productResult = $stmt->get_result();
    
    if ($productResult->num_rows === 0) {
        $response['message'] = 'Product not found';
        echo json_encode($response);
        exit;
    }
    
    $productStatus = $productResult->fetch_assoc()['status'];
    if ($productStatus !== 'active') {
        $response['message'] = 'Product is not active';
        echo json_encode($response);
        exit;
    }
    
    // Check if product is already assigned to the branch
    $checkQuery = "SELECT branch_product_id FROM pos_branch_product WHERE branch_id = ? AND product_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $branch_id, $product_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $response['message'] = 'Product is already assigned to this branch';
        echo json_encode($response);
        exit;
    }
    
    // Insert the new branch product
    $insertQuery = "INSERT INTO pos_branch_product (branch_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('iii', $branch_id, $product_id, $quantity);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Product added to branch successfully';
    } else {
        $response['message'] = 'Error adding product to branch';
    }
    
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
} 