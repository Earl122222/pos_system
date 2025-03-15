<?php
require_once 'db_connect.php';

/**
 * Add a new product to the database
 */
function addProduct($pdo, $data, $image_path) {
    try {
        $sql = "INSERT INTO pos_product (category_id, product_name, product_price, product_image, description, ingredients, product_status) 
                VALUES (:category_id, :product_name, :product_price, :product_image, :description, :ingredients, :product_status)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':product_name' => $data['product_name'],
            ':product_price' => $data['product_price'],
            ':product_image' => $image_path,
            ':description' => $data['description'],
            ':ingredients' => $data['ingredients'],
            ':product_status' => $data['product_status']
        ]);
        
        return ['status' => 'success', 'message' => 'Product added successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update an existing product
 */
function updateProduct($pdo, $data, $image_path = null) {
    try {
        $sql = "UPDATE pos_product SET 
                category_id = :category_id,
                product_name = :product_name,
                product_price = :product_price,
                description = :description,
                ingredients = :ingredients,
                product_status = :product_status";
        
        $params = [
            ':category_id' => $data['category_id'],
            ':product_name' => $data['product_name'],
            ':product_price' => $data['product_price'],
            ':description' => $data['description'],
            ':ingredients' => $data['ingredients'],
            ':product_status' => $data['product_status'],
            ':product_id' => $data['product_id']
        ];

        // Add image update if new image is provided
        if ($image_path) {
            $sql .= ", product_image = :product_image";
            $params[':product_image'] = $image_path;
        }

        $sql .= " WHERE product_id = :product_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return ['status' => 'success', 'message' => 'Product updated successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Delete a product
 */
function deleteProduct($pdo, $product_id) {
    try {
        // First get the product image path
        $stmt = $pdo->prepare("SELECT product_image FROM pos_product WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the product
        $stmt = $pdo->prepare("DELETE FROM pos_product WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Delete the image file if it exists
        if ($product && $product['product_image'] && file_exists($product['product_image'])) {
            unlink($product['product_image']);
        }
        
        return ['status' => 'success', 'message' => 'Product deleted successfully'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get a single product by ID
 */
function getProduct($pdo, $product_id) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.category_name 
                              FROM pos_product p 
                              JOIN pos_category c ON p.category_id = c.category_id 
                              WHERE p.product_id = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Handle file upload for product images
 */
function handleImageUpload($file) {
    $target_dir = "uploads/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        return ['status' => 'error', 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'];
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        return ['status' => 'error', 'message' => 'File is too large (max 5MB)'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['status' => 'success', 'path' => $target_file];
    } else {
        return ['status' => 'error', 'message' => 'Error uploading file'];
    }
}

/**
 * Get paginated products with search and sorting
 */
function getPaginatedProducts($pdo, $start = 0, $length = 10, $search = '', $orderColumn = 'product_id', $orderDir = 'ASC') {
    try {
        // Base query
        $baseQuery = "FROM pos_product p 
                     JOIN pos_category c ON p.category_id = c.category_id";
        
        // Search condition
        $searchCondition = "";
        $params = [];
        if (!empty($search)) {
            $searchCondition = " WHERE (
                p.product_name LIKE :search 
                OR c.category_name LIKE :search
                OR p.description LIKE :search
                OR p.ingredients LIKE :search
                OR p.product_status LIKE :search
                OR p.product_price LIKE :search
            )";
            $params[':search'] = "%{$search}%";
        }

        // Get total records without filtering
        $stmt = $pdo->query("SELECT COUNT(*) " . $baseQuery);
        $totalRecords = $stmt->fetchColumn();

        // Get filtered records count
        $stmt = $pdo->prepare("SELECT COUNT(*) " . $baseQuery . $searchCondition);
        if (!empty($search)) {
            $stmt->bindParam(':search', $params[':search']);
        }
        $stmt->execute();
        $filteredRecords = $stmt->fetchColumn();

        // Prepare the main query
        $query = "SELECT p.*, c.category_name " . $baseQuery . $searchCondition;
        
        // Add ordering
        if ($orderColumn) {
            $query .= " ORDER BY " . ($orderColumn == 'category_name' ? 'c.category_name' : 'p.' . $orderColumn) . " $orderDir";
        }
        
        // Add pagination
        $query .= " LIMIT :start, :length";

        // Execute the final query
        $stmt = $pdo->prepare($query);
        if (!empty($search)) {
            $stmt->bindParam(':search', $params[':search']);
        }
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':length', $length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];

    } catch (PDOException $e) {
        return [
            'error' => true,
            'message' => 'Database error: ' . $e->getMessage(),
            'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ];
    }
} 