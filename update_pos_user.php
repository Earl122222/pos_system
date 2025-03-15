<?php
require_once 'db_connect.php';

try {
    // Add new columns to pos_user table
    $alterQueries = [
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS branch_id INT AFTER user_status",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS employee_id VARCHAR(50) AFTER branch_id",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS shift_schedule VARCHAR(50) AFTER employee_id",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS date_hired DATE AFTER shift_schedule",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(100) AFTER date_hired",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS emergency_number VARCHAR(20) AFTER emergency_contact",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS address TEXT AFTER emergency_number",
        "ALTER TABLE pos_user ADD COLUMN IF NOT EXISTS notes TEXT AFTER address",
        "ALTER TABLE pos_user ADD FOREIGN KEY IF NOT EXISTS (branch_id) REFERENCES pos_branch(branch_id)"
    ];

    foreach ($alterQueries as $query) {
        $pdo->exec($query);
    }

    // Update existing users with default values if needed
    $updateQuery = "
        UPDATE pos_user 
        SET user_status = 'Active' 
        WHERE user_status IS NULL
    ";
    $pdo->exec($updateQuery);

    echo "Successfully updated pos_user table structure!";

} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?> 