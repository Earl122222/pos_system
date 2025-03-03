<?php
require_once 'db_connect.php';

try {
    // Get all users
    $stmt = $pdo->query("SELECT user_id, user_password FROM pos_user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update each user's password with hashed version
    $updateStmt = $pdo->prepare("UPDATE pos_user SET user_password = ? WHERE user_id = ?");
    
    foreach ($users as $user) {
        // Only hash if the password isn't already hashed
        if (strlen($user['user_password']) < 60) { // Bcrypt hashes are always 60 characters
            $hashed_password = password_hash($user['user_password'], PASSWORD_DEFAULT);
            $updateStmt->execute([$hashed_password, $user['user_id']]);
        }
    }
    
    echo "All passwords have been successfully updated to hashed versions!";
    echo "<br><a href='login.php'>Go to Login Page</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 