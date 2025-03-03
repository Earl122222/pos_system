<?php
require_once 'db_connect.php';

try {
    // Admin account details
    $admin_name = "Admin";
    $admin_email = "admin@admin.com";
    $admin_password = "admin123"; // This will be the password you can use to login
    
    // Hash the password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Check if admin email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = ?");
    $stmt->execute([$admin_email]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        // Update existing admin account
        $stmt = $pdo->prepare("UPDATE pos_user SET user_name = ?, user_password = ?, user_type = 'Admin', user_status = 'Active' WHERE user_email = ?");
        $stmt->execute([$admin_name, $hashed_password, $admin_email]);
        echo "Admin account updated successfully!<br>";
    } else {
        // Create new admin account
        $stmt = $pdo->prepare("INSERT INTO pos_user (user_name, user_email, user_password, user_type, user_status) VALUES (?, ?, ?, 'Admin', 'Active')");
        $stmt->execute([$admin_name, $admin_email, $hashed_password]);
        echo "New admin account created successfully!<br>";
    }
    
    echo "You can now login with:<br>";
    echo "Email: admin@admin.com<br>";
    echo "Password: admin123<br>";
    echo "<br><a href='login.php'>Go to Login Page</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 