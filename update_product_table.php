<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

try {
    // Remove current_stock and minimum_stock columns from pos_product table
    $sql = "ALTER TABLE pos_product 
            DROP COLUMN IF EXISTS current_stock,
            DROP COLUMN IF EXISTS minimum_stock";
    
    $pdo->exec($sql);
    
    echo "Successfully removed stock columns from product table!";
    
} catch (PDOException $e) {
    echo "Error updating table structure: " . $e->getMessage();
} 