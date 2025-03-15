<?php
require_once 'db_connect.php';

try {
    // Fix pos_user table
    $alterUserQueries = [
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) NULL",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS user_status ENUM('Active', 'Inactive') DEFAULT 'Active'",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS user_type ENUM('Admin', 'Cashier') DEFAULT 'Cashier'",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) NULL",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "ALTER TABLE pos_user DROP COLUMN IF EXISTS shift_schedule"
    ];

    // Fix pos_branch table
    $alterBranchQueries = [
        "ALTER TABLE pos_branch ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) NULL",
        "ALTER TABLE pos_branch ADD COLUMN IF NOT EXISTS status ENUM('Active', 'Inactive') DEFAULT 'Active'",
        "ALTER TABLE pos_branch ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];

    // Fix pos_cashier_details table
    $alterCashierQueries = [
        "ALTER TABLE pos_cashier_details ADD COLUMN IF NOT EXISTS emergency_number VARCHAR(20) NULL",
        "ALTER TABLE pos_cashier_details ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) NULL",
        "ALTER TABLE pos_cashier_details ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "ALTER TABLE pos_cashier_details DROP COLUMN IF EXISTS shift_schedule"
    ];

    // Create uploads directory if it doesn't exist
    $uploadsDir = 'uploads/profiles';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
        echo "Created uploads directory: $uploadsDir\n";
    }

    // Execute all alter queries
    foreach ($alterUserQueries as $query) {
        try {
            $pdo->exec($query);
            echo "Executed: $query\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Duplicate column name") !== false) {
                $modifyQuery = str_replace("ADD COLUMN IF NOT EXISTS", "MODIFY COLUMN", $query);
                $pdo->exec($modifyQuery);
                echo "Modified: $modifyQuery\n";
            } else {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }

    foreach ($alterBranchQueries as $query) {
        try {
            $pdo->exec($query);
            echo "Executed: $query\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Duplicate column name") !== false) {
                $modifyQuery = str_replace("ADD COLUMN IF NOT EXISTS", "MODIFY COLUMN", $query);
                $pdo->exec($modifyQuery);
                echo "Modified: $modifyQuery\n";
            } else {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }

    foreach ($alterCashierQueries as $query) {
        try {
            $pdo->exec($query);
            echo "Executed: $query\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Duplicate column name") !== false) {
                $modifyQuery = str_replace("ADD COLUMN IF NOT EXISTS", "MODIFY COLUMN", $query);
                $pdo->exec($modifyQuery);
                echo "Modified: $modifyQuery\n";
            } else {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\nDatabase structure updated successfully!";

} catch (Exception $e) {
    echo "Error updating database structure: " . $e->getMessage();
}
?> 