<?php
require_once 'db_connect.php';

try {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h2>Database Fix Operations Log:</h2>";
    
    // 1. Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_order'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Creating pos_order table...<br>";
        
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
        echo "Table created successfully!<br>";
    } else {
        echo "Table pos_order exists.<br>";
        
        // 2. Check if order_type column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'order_type'");
        $columnExists = $stmt->rowCount() > 0;
        
        if (!$columnExists) {
            echo "Adding order_type column...<br>";
            
            // Drop the column if it somehow exists but is malformed
            try {
                $pdo->exec("ALTER TABLE pos_order DROP COLUMN IF EXISTS order_type");
            } catch (PDOException $e) {
                // Ignore error if column doesn't exist
            }
            
            // Add the column
            $sql = "ALTER TABLE pos_order 
                    ADD COLUMN order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') 
                    NOT NULL DEFAULT 'Dine-in' AFTER order_total";
            $pdo->exec($sql);
            echo "Column added successfully!<br>";
            
            // Update existing records
            $sql = "UPDATE pos_order SET order_type = 'Dine-in'";
            $pdo->exec($sql);
            echo "Existing records updated.<br>";
        } else {
            echo "Column order_type already exists.<br>";
        }
    }
    
    // 3. Verify the structure
    echo "<h3>Current Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE pos_order");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    // 4. Show sample data
    echo "<h3>Sample Data (First 5 rows):</h3>";
    $stmt = $pdo->query("SELECT * FROM pos_order LIMIT 5");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    echo "<h3>Status: Database structure has been verified and fixed.</h3>";
    echo "You can now return to the dashboard page.";
    
} catch (PDOException $e) {
    echo "<h3>Error:</h3>";
    echo $e->getMessage();
    echo "<br><br>";
    echo "<h4>Additional Debug Information:</h4>";
    echo "<pre>";
    print_r($e->errorInfo);
    echo "</pre>";
}
?> 