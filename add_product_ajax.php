<?php
require_once 'db_connect.php';
require_once 'product_functions.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['category_id', 'product_name', 'product_price', 'product_status'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit;
    }
}

// Check if image was uploaded
if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Product image is required']);
    exit;
}

// Handle image upload
$image_result = handleImageUpload($_FILES['product_image']);
if ($image_result['status'] === 'error') {
    echo json_encode($image_result);
    exit;
}

// Prepare product data
$product_data = [
    'category_id' => $_POST['category_id'],
    'product_name' => $_POST['product_name'],
    'product_price' => $_POST['product_price'],
    'description' => $_POST['description'] ?? '',
    'ingredients' => $_POST['ingredients'] ?? '',
    'product_status' => $_POST['product_status']
];

// Add product to database
$result = addProduct($pdo, $product_data, $image_result['path']);

// Return response
echo json_encode($result); 