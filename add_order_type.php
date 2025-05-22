<?php
require_once 'db_connect.php';

try {
    // First, check if the column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM pos_order LIKE 'order_type'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;

    if (!$columnExists) {
        // Add the order_type column if it doesn't exist
        $sql = "ALTER TABLE pos_order 
                ADD COLUMN order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') 
                NOT NULL DEFAULT 'Dine-in'";
        $pdo->exec($sql);
        echo "Successfully added order_type column<br>";

        // Update existing records to 'Dine-in'
        $sql = "UPDATE pos_order SET order_type = 'Dine-in' WHERE order_type IS NULL";
        $pdo->exec($sql);
        echo "Successfully updated existing records<br>";
    } else {
        echo "Column order_type already exists<br>";
    }

    echo "Operation completed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 