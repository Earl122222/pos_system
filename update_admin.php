<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Plain password
    $password = 'admin123';
    
    // Update the admin account with plain password
    $stmt = $pdo->prepare("UPDATE pos_user SET user_password = ?, user_status = 'Active' WHERE user_email = 'admin@example.com'");
    $stmt->execute([$password]);
    
    echo "Admin account updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 