<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

// Check admin login
checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $productId = intval($_POST['product_id']);

    // Start transaction
    $pdo->beginTransaction();

    // Get product details before deletion (for image cleanup)
    $stmt = $pdo->prepare("SELECT product_image FROM pos_product WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Delete product from database
    $stmt = $pdo->prepare("DELETE FROM pos_product WHERE product_id = ?");
    if (!$stmt->execute([$productId])) {
        throw new Exception('Failed to delete product from database');
    }

    // Delete product image if it exists
    if ($product['product_image'] && file_exists($product['product_image'])) {
        if (!unlink($product['product_image'])) {
            // Log error but don't throw exception
            error_log("Failed to delete product image: " . $product['product_image']);
        }
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
