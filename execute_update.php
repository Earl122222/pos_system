<?php
require_once 'db_connect.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('update_db.sql');
    $pdo->exec($sql);
    
    // Verify the columns were added
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('description', $columns) && in_array('ingredients', $columns)) {
        echo "Success: The description and ingredients columns have been added to the pos_product table.";
    } else {
        echo "Error: Could not verify if columns were added successfully.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 