<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = [
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

    // Handle image upload if provided
    $product_image = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Invalid image format. Allowed formats: " . implode(', ', $allowed));
        }

        $upload_name = 'product_' . time() . '.' . $ext;
        $upload_path = 'uploads/products/' . $upload_name;
        
        // Create directory if it doesn't exist
        if (!file_exists('uploads/products')) {
            mkdir('uploads/products', 0777, true);
        }
        
        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload product image");
        }
        
        $product_image = $upload_path;
    }

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO pos_product (
            category_id,
            product_name,
            product_price,
            description,
            ingredients,
            product_image,
            product_status,
            created_at
        ) VALUES (
            :category_id,
            :product_name,
            :product_price,
            :description,
            :ingredients,
            :product_image,
            :product_status,
            NOW()
        )
    ");

    $stmt->execute([
        'category_id' => $_POST['category_id'],
        'product_name' => $_POST['product_name'],
        'product_price' => $_POST['product_price'],
        'description' => !empty($_POST['description']) ? $_POST['description'] : null,
        'ingredients' => !empty($_POST['ingredients']) ? $_POST['ingredients'] : null,
        'product_image' => $product_image,
        'product_status' => $_POST['product_status']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Product added successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 