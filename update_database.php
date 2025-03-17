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

    // Add branch_id column to pos_order if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'branch_id'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN branch_id INT AFTER order_created_by, ADD FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)";
    }

    // Add completed_at column to pos_order if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'completed_at'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN completed_at TIMESTAMP NULL AFTER order_datetime";
    }

    // Add status column to pos_order if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM pos_order LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        $sql_statements[] = "ALTER TABLE pos_order ADD COLUMN status ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'completed' AFTER service_type";
    }

    // Create pos_cashier_details table if it doesn't exist
    $sql_statements[] = "CREATE TABLE IF NOT EXISTS pos_cashier_details (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        branch_id INT NOT NULL,
        employee_id VARCHAR(50) NOT NULL UNIQUE,
        date_hired DATE NOT NULL,
        emergency_contact VARCHAR(100) NOT NULL,
        emergency_number VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES pos_user(user_id),
        FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
    )";

    // Create pos_cashier_sessions table if it doesn't exist
    $sql_statements[] = "CREATE TABLE IF NOT EXISTS pos_cashier_sessions (
        session_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        branch_id INT NOT NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        logout_time TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES pos_user(user_id),
        FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
    )";

    // Create pos_branch table if it doesn't exist
    $sql_statements[] = "CREATE TABLE IF NOT EXISTS pos_branch (
        branch_id INT PRIMARY KEY AUTO_INCREMENT,
        branch_name VARCHAR(100) NOT NULL,
        branch_code VARCHAR(20) NOT NULL UNIQUE,
        contact_number VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        street_address TEXT NOT NULL,
        barangay VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        province VARCHAR(100) NOT NULL,
        complete_address TEXT NOT NULL,
        opening_date DATE NOT NULL,
        operating_hours VARCHAR(50) NOT NULL,
        notes TEXT,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    // Insert a default branch if none exists
    $sql_statements[] = "INSERT INTO pos_branch (
        branch_name, 
        branch_code, 
        contact_number, 
        email, 
        street_address, 
        barangay, 
        city, 
        province, 
        complete_address, 
        opening_date, 
        operating_hours
    ) VALUES (
        'Main Branch',
        'BR-MAIN01',
        '09123456789',
        'main@example.com',
        '123 Main Street',
        'Centro',
        'Manila',
        'Metro Manila',
        '123 Main Street, Centro, Manila, Metro Manila',
        CURRENT_DATE,
        '08:00 - 20:00'
    )";

    // Now let's assign any unassigned cashiers to the main branch
    $sql_statements[] = "INSERT INTO pos_cashier_details (
        user_id, 
        branch_id, 
        employee_id, 
        date_hired, 
        emergency_contact,
        emergency_number,
        address
    )
    SELECT 
        u.user_id,
        (SELECT branch_id FROM pos_branch LIMIT 1),
        CONCAT('EMP', LPAD(u.user_id, 4, '0')),
        CURRENT_DATE,
        'Emergency Contact',
        '09123456789',
        'Default Address'
    FROM pos_user u
    LEFT JOIN pos_cashier_details cd ON u.user_id = cd.user_id
    WHERE u.user_type = 'Cashier'
    AND cd.id IS NULL";

    // Execute all SQL statements
    foreach ($sql_statements as $sql) {
        $pdo->exec($sql);
        echo "Executed: $sql\n";
    }

    // Update existing orders to set completed_at if it's null
    $pdo->exec("UPDATE pos_order SET completed_at = order_datetime WHERE completed_at IS NULL");
    
    // Update existing orders to set status if it's null
    $pdo->exec("UPDATE pos_order SET status = 'completed' WHERE status IS NULL");

    echo "Database structure updated successfully! Default branch and cashier assignments have been created.";
} catch (PDOException $e) {
    echo "Error updating database structure: " . $e->getMessage();
}

ob_end_flush();
?> 