<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $category_name = trim($_POST['category_name']);
        $category_status = trim($_POST['category_status']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;

        // Validate inputs
        if (empty($category_name)) {
            throw new Exception('Category name is required.');
        }

        // Check if Category already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_category WHERE category_name = :category_name");
        $stmt->execute(['category_name' => $category_name]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            throw new Exception('Category with this name already exists.');
        }

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO pos_category (category_name, description, status) VALUES (:category_name, :description, :status)");
        $stmt->execute([
            'category_name' => $category_name,
            'description' => $description,
            'status' => strtolower($category_status)
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Category added successfully'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}