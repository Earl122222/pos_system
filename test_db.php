<?php
require_once 'db_connect.php';

try {
    // Test the connection
    $test = $pdo->query("SELECT 1");
    echo "Database connection successful!\n";
    
    // Try to select from pos_configuration
    $result = $pdo->query("SELECT currency FROM pos_configuration");
    $current = $result->fetchColumn();
    echo "Current currency symbol: " . $current . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
