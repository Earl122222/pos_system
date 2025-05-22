<?php
require_once 'db_connect.php';

try {
    // First, check if the table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'pos_order'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the table if it doesn't exist
        $pdo->exec("CREATE TABLE pos_order (
            order_id INT PRIMARY KEY AUTO_INCREMENT,
            order_number VARCHAR(50) NOT NULL,
            order_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') NOT NULL DEFAULT 'Dine-in',
            payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash',
            order_status VARCHAR(20) NOT NULL DEFAULT 'Completed',
            order_created_by INT NOT NULL,
            order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "Created pos_order table<br>";
    } else {
        // Check if order_type column exists
        $columns = $pdo->query("SHOW COLUMNS FROM pos_order")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('order_type', $columns)) {
            // Add order_type column
            $pdo->exec("ALTER TABLE pos_order 
                       ADD COLUMN order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') 
                       NOT NULL DEFAULT 'Dine-in'");
            echo "Added order_type column<br>";
        }
    }
    
    // Set default value for existing rows
    $pdo->exec("UPDATE pos_order SET order_type = 'Dine-in' WHERE order_type IS NULL");
    echo "Updated existing orders<br>";
    
    echo "Table structure fixed successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 