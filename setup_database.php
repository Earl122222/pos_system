<?php
require_once 'db_connect.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('database/create_tables.sql');
    
    // Split SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Database tables created successfully!<br>";
    
    // Check if order_type column exists
    $checkColumn = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'order_type'");
    if ($checkColumn->rowCount() == 0) {
        // Add order_type column if it doesn't exist
        $pdo->exec("ALTER TABLE pos_order 
                    ADD COLUMN order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') 
                    NOT NULL DEFAULT 'Dine-in'");
        echo "Added order_type column<br>";
    }
    
    // Update existing orders
    $pdo->exec("UPDATE pos_order SET order_type = 'Dine-in' WHERE order_type IS NULL");
    echo "Updated existing orders<br>";
    
    echo "<br>Setup completed successfully!";
    
} catch (PDOException $e) {
    echo "Error setting up database: " . $e->getMessage();
    // Print the statement that caused the error if available
    if (isset($statement)) {
        echo "<br>Failed statement: " . $statement;
    }
}
?> 