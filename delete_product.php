<?php

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $product_id = $_POST['id'];

    // Check if product exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_product WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM pos_product WHERE product_id = ?");
        if ($stmt->execute([$product_id])) {
            echo "Product deleted successfully.";
        } else {
            echo "Error deleting product.";
        }
    } else {
        echo "Product not found.";
    }
} else {
    echo "Invalid request.";
}
?>
