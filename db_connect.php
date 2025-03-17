<?php
require_once 'db_config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set timezone to match your server's timezone
    $pdo->exec("SET time_zone = '+08:00'");
    
} catch (PDOException $e) {
    // Try alternative connection if online connection fails
    if (DB_MODE === 'online') {
        try {
            // Fallback to local connection
            $pdo = new PDO(
                "mysql:host=" . LOCAL_DB_HOST . ";dbname=" . LOCAL_DB_NAME,
                LOCAL_DB_USER,
                LOCAL_DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET time_zone = '+08:00'");
            
            // Log the fallback
            error_log('Fallback to local database connection');
        } catch (PDOException $e2) {
            die('Database connection failed. Please check your configuration.');
        }
    } else {
        die('Database connection failed. Please check your configuration.');
    }
}
?>
                