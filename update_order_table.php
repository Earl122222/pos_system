<?php
require_once 'db_connect.php';

try {
    // Add order_type column
    $sql = "ALTER TABLE pos_order 
            ADD COLUMN IF NOT EXISTS order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') 
            NOT NULL DEFAULT 'Dine-in'";
    $pdo->exec($sql);
    
    // Update existing orders
    $sql = "UPDATE pos_order SET order_type = 'Dine-in' WHERE order_type IS NULL";
    $pdo->exec($sql);
    
    echo "Table structure updated successfully!";
} catch (PDOException $e) {
    echo "Error updating table structure: " . $e->getMessage();
}
?> 