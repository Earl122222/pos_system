<?php
require_once 'db_connect.php';

try {
    // Start transaction
    $pdo->beginTransaction();

    // 1. Create necessary tables if they don't exist
    $tables = [
        // pos_user table
        "CREATE TABLE IF NOT EXISTS pos_user (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            user_name VARCHAR(100) NOT NULL,
            user_email VARCHAR(100) NOT NULL UNIQUE,
            user_password VARCHAR(255) NOT NULL,
            user_type ENUM('Admin', 'Cashier') NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            profile_image VARCHAR(255),
            user_status ENUM('Active', 'Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // pos_branch table
        "CREATE TABLE IF NOT EXISTS pos_branch (
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
        )",

        // pos_cashier_details table
        "CREATE TABLE IF NOT EXISTS pos_cashier_details (
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
        )",

        // pos_cashier_sessions table
        "CREATE TABLE IF NOT EXISTS pos_cashier_sessions (
            session_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            branch_id INT NOT NULL,
            login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            logout_time TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (user_id) REFERENCES pos_user(user_id),
            FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
        )",

        // pos_category table
        "CREATE TABLE IF NOT EXISTS pos_category (
            category_id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(255) NOT NULL,
            category_status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'
        )",

        // pos_product table
        "CREATE TABLE IF NOT EXISTS pos_product (
            product_id INT PRIMARY KEY AUTO_INCREMENT,
            category_id INT,
            product_name VARCHAR(255) NOT NULL,
            product_image VARCHAR(100) NOT NULL,
            product_price DECIMAL(10, 2) NOT NULL,
            description TEXT,
            ingredients TEXT,
            product_status ENUM('Available', 'Out of Stock') NOT NULL DEFAULT 'Available',
            FOREIGN KEY (category_id) REFERENCES pos_category(category_id)
        )",

        // pos_order table
        "CREATE TABLE IF NOT EXISTS pos_order (
            order_id INT PRIMARY KEY AUTO_INCREMENT,
            order_number VARCHAR(255) UNIQUE NOT NULL,
            order_total DECIMAL(10, 2) NOT NULL,
            order_subtotal DECIMAL(10, 2) NOT NULL,
            order_tax DECIMAL(10, 2) DEFAULT 0,
            order_discount DECIMAL(10, 2) DEFAULT 0,
            discount_type ENUM('none', 'senior', 'pwd') DEFAULT 'none',
            payment_method ENUM('cash', 'credit_card', 'e_wallet') DEFAULT 'cash',
            service_type ENUM('dine-in', 'takeout', 'delivery') DEFAULT 'dine-in',
            status ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'completed',
            order_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            order_created_by INT,
            branch_id INT,
            FOREIGN KEY (order_created_by) REFERENCES pos_user(user_id),
            FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
        )",

        // pos_order_item table
        "CREATE TABLE IF NOT EXISTS pos_order_item (
            order_item_id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT,
            product_id INT,
            product_qty INT NOT NULL,
            product_price DECIMAL(10, 2) NOT NULL,
            item_total DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES pos_order(order_id),
            FOREIGN KEY (product_id) REFERENCES pos_product(product_id)
        )"
    ];

    // Execute table creation queries
    foreach ($tables as $table) {
        $pdo->exec($table);
    }

    // 2. Insert default branch if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM pos_branch");
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO pos_branch (
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
            )
        ");
        $stmt->execute();
    }

    // 3. Create default admin user if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM pos_user WHERE user_type = 'Admin'");
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO pos_user (
                user_name,
                user_email,
                user_password,
                user_type,
                contact_number,
                user_status
            ) VALUES (
                'Admin',
                'admin@example.com',
                ?,
                'Admin',
                '09123456789',
                'Active'
            )
        ");
        $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
    }

    // 4. Create default categories if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM pos_category");
    if ($stmt->fetchColumn() == 0) {
        $categories = ['Food', 'Beverages', 'Desserts', 'Snacks'];
        $stmt = $pdo->prepare("INSERT INTO pos_category (category_name, category_status) VALUES (?, 'Active')");
        foreach ($categories as $category) {
            $stmt->execute([$category]);
        }
    }

    // 5. Create uploads directories if they don't exist
    $directories = [
        'uploads',
        'uploads/profiles',
        'uploads/products'
    ];

    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    // Commit transaction
    $pdo->commit();

    echo "Database restored successfully! Default records have been created.";

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error restoring database: " . $e->getMessage();
}
?> 