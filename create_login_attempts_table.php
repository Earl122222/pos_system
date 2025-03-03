<?php
require_once 'db_connect.php';

try {
    // Create login_attempts table
    $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        timestamp DATETIME NOT NULL,
        INDEX (email, timestamp)
    )";
    
    $pdo->exec($sql);
    echo "Login attempts table created successfully";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 