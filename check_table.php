<?php
require_once 'db_connect.php';

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_order'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "Table pos_order exists<br>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE pos_order");
        echo "<h3>Table Structure:</h3>";
        echo "<pre>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "Table pos_order does not exist<br>";
        
        // Create the table
        $sql = "CREATE TABLE pos_order (
            order_id INT PRIMARY KEY AUTO_INCREMENT,
            order_number VARCHAR(50) NOT NULL,
            order_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') NOT NULL DEFAULT 'Dine-in',
            payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash',
            order_status VARCHAR(20) NOT NULL DEFAULT 'Completed',
            order_created_by INT NOT NULL,
            order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "Created pos_order table with proper structure<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 