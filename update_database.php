<?php
// Ensure no output before session_start
ob_start();
session_start();

require_once 'db_connect.php';

try {
    // Array of SQL statements to execute
    $sql_statements = [];

    // Check if order_subtotal column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'order_subtotal'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN order_subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0 AFTER order_total";
    }

    // Check if order_tax column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'order_tax'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN order_tax DECIMAL(10, 2) DEFAULT 0 AFTER order_subtotal";
    }

    // Check if order_discount column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'order_discount'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN order_discount DECIMAL(10, 2) DEFAULT 0 AFTER order_tax";
    }

    // Check if discount_type column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'discount_type'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN discount_type ENUM('none', 'senior', 'pwd') DEFAULT 'none' AFTER order_discount";
    }

    // Check if payment_method column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'payment_method'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN payment_method ENUM('cash', 'credit_card', 'e_wallet') DEFAULT 'cash' AFTER discount_type";
    }

    // Check if service_type column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'service_type'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN service_type ENUM('dine-in', 'takeout', 'delivery') DEFAULT 'dine-in' AFTER payment_method";
    }

    // Check if order_status column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'order_status'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN order_status ENUM('completed', 'cancelled', 'refunded') DEFAULT 'completed' AFTER service_type";
    }

    // Check if item_total column exists in pos_order_item
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order_item LIKE 'item_total'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order_item ADD COLUMN item_total DECIMAL(10, 2) NOT NULL DEFAULT 0 AFTER product_price";
    }

    // Execute all SQL statements
    foreach ($sql_statements as $sql) {
        $pdo->exec($sql);
        echo "Executed: $sql\n";
    }

    echo "Database structure updated successfully!";
} catch (PDOException $e) {
    echo "Error updating database structure: " . $e->getMessage();
}

ob_end_flush();
?> 