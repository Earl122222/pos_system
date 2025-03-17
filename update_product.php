<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = [
        'product_id',
        'category_id',
        'product_name',
        'product_price',
        'product_status'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate numeric fields
    if (!is_numeric($_POST['product_price']) || $_POST['product_price'] <= 0) {
        throw new Exception('Invalid product price');
    }

    $productId = intval($_POST['product_id']);

    // Check if product exists
    $stmt = $pdo->prepare("SELECT product_image FROM pos_product WHERE product_id = ?");
    $stmt->execute([$productId]);
    $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentProduct) {
        throw new Exception('Product not found');
    }

    // Handle image upload if provided
    $product_image = $currentProduct['product_image'];
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Invalid image format. Allowed formats: " . implode(', ', $allowed));
        }

        $upload_name = 'product_' . time() . '.' . $ext;
        $upload_path = 'uploads/products/' . $upload_name;
        
        if (!file_exists('uploads/products')) {
            mkdir('uploads/products', 0777, true);
        }
        
        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload product image");
        }

        // Delete old image if exists
        if ($currentProduct['product_image'] && file_exists($currentProduct['product_image'])) {
            unlink($currentProduct['product_image']);
        }
        
        $product_image = $upload_path;
    }

    // Update product in database
    $stmt = $pdo->prepare("
        UPDATE pos_product SET
            category_id = :category_id,
            product_name = :product_name,
            product_price = :product_price,
            description = :description,
            ingredients = :ingredients,
            product_image = :product_image,
            product_status = :product_status,
            updated_at = NOW()
        WHERE product_id = :product_id
    ");

    $stmt->execute([
        'category_id' => $_POST['category_id'],
        'product_name' => $_POST['product_name'],
        'product_price' => $_POST['product_price'],
        'description' => !empty($_POST['description']) ? $_POST['description'] : null,
        'ingredients' => !empty($_POST['ingredients']) ? $_POST['ingredients'] : null,
        'product_image' => $product_image,
        'product_status' => $_POST['product_status'],
        'product_id' => $productId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 