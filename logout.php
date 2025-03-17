<?php
require_once 'db_connect.php';

session_start();

// If the user is a cashier, end their active session
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Cashier' && isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE pos_cashier_sessions 
            SET is_active = FALSE, 
                logout_time = CURRENT_TIMESTAMP 
            WHERE user_id = ? 
            AND is_active = TRUE
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log('Error ending cashier session: ' . $e->getMessage());
    }
}

// Clear all session variables
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;

?>