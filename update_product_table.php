<?php
require_once 'db_connect.php';

try {
    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_product LIKE 'description'");
    $descriptionExists = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_product LIKE 'ingredients'");
    $ingredientsExists = $stmt->fetch();
    
    // Add columns if they don't exist
    if (!$descriptionExists) {
        $pdo->exec("ALTER TABLE pos_product ADD COLUMN description TEXT AFTER product_image");
    }
    
    if (!$ingredientsExists) {
        $pdo->exec("ALTER TABLE pos_product ADD COLUMN ingredients TEXT AFTER description");
    }
    
    echo "Database updated successfully!";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
} 