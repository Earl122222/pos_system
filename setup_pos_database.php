<?php
require_once 'db_connect.php';

try {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h2>POS Database Setup Log:</h2>";
    
    // Create pos_order table
    $sql = "CREATE TABLE IF NOT EXISTS pos_order (
        order_id INT PRIMARY KEY AUTO_INCREMENT,
        order_number VARCHAR(50) NOT NULL,
        order_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') NOT NULL DEFAULT 'Dine-in',
        payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash',
        order_status VARCHAR(20) NOT NULL DEFAULT 'Completed',
        order_created_by INT NOT NULL,
        order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_order_number (order_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "pos_order table created/verified<br>";

    // Create pos_order_item table
    $sql = "CREATE TABLE IF NOT EXISTS pos_order_item (
        order_item_id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES pos_order(order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "pos_order_item table created/verified<br>";

    // Create pos_product table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS pos_product (
        product_id INT PRIMARY KEY AUTO_INCREMENT,
        product_name VARCHAR(100) NOT NULL,
        category_id INT,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "pos_product table created/verified<br>";

    // Create pos_category table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS pos_category (
        category_id INT PRIMARY KEY AUTO_INCREMENT,
        category_name VARCHAR(50) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "pos_category table created/verified<br>";

    // Add foreign key for category_id in pos_product if it doesn't exist
    $sql = "ALTER TABLE pos_product 
            ADD CONSTRAINT fk_product_category 
            FOREIGN KEY (category_id) REFERENCES pos_category(category_id)";
    try {
        $pdo->exec($sql);
        echo "Added foreign key constraint to pos_product<br>";
    } catch (PDOException $e) {
        // Constraint might already exist, ignore the error
    }

    // Verify pos_order table structure
    echo "<h3>Verifying pos_order table structure:</h3>";
    $stmt = $pdo->query("DESCRIBE pos_order");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasOrderType = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'order_type') {
            $hasOrderType = true;
            break;
        }
    }
    
    if (!$hasOrderType) {
        echo "Adding order_type column to pos_order table...<br>";
        $sql = "ALTER TABLE pos_order 
                ADD COLUMN order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') 
                NOT NULL DEFAULT 'Dine-in' 
                AFTER order_total";
        $pdo->exec($sql);
        echo "Added order_type column<br>";
    }

    // Update any NULL order_type values
    $sql = "UPDATE pos_order SET order_type = 'Dine-in' WHERE order_type IS NULL";
    $pdo->exec($sql);
    echo "Updated any NULL order_type values to 'Dine-in'<br>";

    // Show final table structure
    echo "<h3>Final pos_order Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE pos_order");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";

    // Show sample data
    echo "<h3>Sample Orders (First 5 rows):</h3>";
    $stmt = $pdo->query("SELECT * FROM pos_order LIMIT 5");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";

    echo "<h3>Setup Complete!</h3>";
    echo "The database structure has been verified and fixed. You can now return to the dashboard.";

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