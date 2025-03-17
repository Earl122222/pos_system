<?php
require_once 'db_connect.php';

try {
    // Create pos_cashier_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS pos_cashier_sessions (
        session_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        branch_id INT NOT NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        logout_time TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES pos_user(user_id),
        FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Cashier sessions table created successfully!";
    
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 