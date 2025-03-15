<?php

require_once 'db_connect.php';
require_once 'product_functions.php';
require_once 'auth_function.php';

// Check admin login
checkAdminLogin();

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo 'Invalid request';
    exit;
}

$product_id = (int)$_POST['id'];

// Delete the product
$result = deleteProduct($pdo, $product_id);

if ($result['status'] === 'success') {
    echo 'Product deleted successfully';
} else {
    echo 'Error deleting product: ' . $result['message'];
}
?>
