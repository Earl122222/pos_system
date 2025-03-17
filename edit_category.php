<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $category_id = $_POST['category_id'];
        $category_name = trim($_POST['category_name']);
        $category_status = strtolower(trim($_POST['category_status']));
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;

        // Validate inputs
        if (empty($category_name)) {
            throw new Exception('Category name is required.');
        }

        // Check if category exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_category WHERE category_name = :category_name AND category_id != :category_id");
        $stmt->execute([
            'category_name' => $category_name,
            'category_id' => $category_id
        ]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Category with this name already exists.');
        }

        // Update the category
        $stmt = $pdo->prepare("
            UPDATE pos_category 
            SET category_name = :category_name,
                description = :description,
                status = :status 
            WHERE category_id = :category_id
        ");

        $stmt->execute([
            'category_name' => $category_name,
            'description' => $description,
            'status' => $category_status,
            'category_id' => $category_id
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// If GET request, return category data
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pos_category WHERE category_id = ?");
        $stmt->execute([$_GET['id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            throw new Exception('Category not found');
        }

        echo json_encode([
            'success' => true,
            'data' => $category
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>