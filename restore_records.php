<?php
require_once 'db_connect.php';

try {
    // Start transaction
    $pdo->beginTransaction();

    // 1. Insert categories exactly as shown in the image
    $categories = [
        ['id' => 1, 'name' => 'Beverages', 'status' => 'active', 'description' => 'Drinks and refreshments'],
        ['id' => 2, 'name' => 'Main Course', 'status' => 'active', 'description' => 'Primary dishes and entrees'],
        ['id' => 3, 'name' => 'Desserts', 'status' => 'active', 'description' => 'Sweet treats and desserts'],
        ['id' => 4, 'name' => 'Appetizers', 'status' => 'active', 'description' => 'Starters and small plates'],
        ['id' => 5, 'name' => 'Side Dishes', 'status' => 'active', 'description' => 'Complementary dishes']
    ];

    // Clear existing categories
    $pdo->exec("TRUNCATE TABLE pos_category");
    
    // Insert categories
    $stmt = $pdo->prepare("
        INSERT INTO pos_category (category_id, category_name, status, description) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($categories as $category) {
        $stmt->execute([$category['id'], $category['name'], $category['status'], $category['description']]);
    }

    // 2. Insert products exactly as shown in the image
    $products = [
        [
            'id' => 1,
            'category_id' => 1,
            'name' => 'Iced Coffee',
            'price' => 120.00,
            'status' => 'Available',
            'current_stock' => 100,
            'minimum_stock' => 10,
            'created_at' => '2025-03-17 02:00:52'
        ],
        [
            'id' => 2,
            'category_id' => 2,
            'name' => 'Grilled Chicken',
            'price' => 250.00,
            'status' => 'Available',
            'current_stock' => 50,
            'minimum_stock' => 10,
            'created_at' => '2025-03-17 02:00:52'
        ],
        [
            'id' => 3,
            'category_id' => 3,
            'name' => 'Chocolate Cake',
            'price' => 180.00,
            'status' => 'Available',
            'current_stock' => 30,
            'minimum_stock' => 10,
            'created_at' => '2025-03-17 02:00:52'
        ],
        [
            'id' => 4,
            'category_id' => 4,
            'name' => 'Spring Rolls',
            'price' => 150.00,
            'status' => 'Available',
            'current_stock' => 80,
            'minimum_stock' => 10,
            'created_at' => '2025-03-17 02:00:52'
        ],
        [
            'id' => 5,
            'category_id' => 5,
            'name' => 'French Fries',
            'price' => 100.00,
            'status' => 'Available',
            'current_stock' => 150,
            'minimum_stock' => 10,
            'created_at' => '2025-03-17 02:00:52'
        ]
    ];

    // Clear existing products
    $pdo->exec("TRUNCATE TABLE pos_product");
    
    // Insert products
    $stmt = $pdo->prepare("
        INSERT INTO pos_product (
            product_id, 
            category_id, 
            product_name, 
            product_price, 
            product_status,
            description,
            ingredients,
            product_image,
            current_stock,
            minimum_stock,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, ?, ?, ?, NULL)
    ");
    
    foreach ($products as $product) {
        $stmt->execute([
            $product['id'],
            $product['category_id'],
            $product['name'],
            $product['price'],
            $product['status'],
            $product['current_stock'],
            $product['minimum_stock'],
            $product['created_at']
        ]);
    }

    // Commit transaction
    $pdo->commit();

    echo "Records restored successfully! Your original categories and products have been restored.";

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error restoring records: " . $e->getMessage();
}
?> 