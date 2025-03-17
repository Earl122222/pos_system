<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('User ID is required');
    }

    $user_id = intval($_POST['id']);

    // Start transaction
    $pdo->beginTransaction();

    // Check if user exists and get their type
    $stmt = $pdo->prepare("SELECT user_type FROM pos_user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Check if user has any orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_order WHERE order_created_by = ?");
    $stmt->execute([$user_id]);
    $orderCount = $stmt->fetchColumn();

    if ($orderCount > 0) {
        throw new Exception('Cannot delete user: This user has created orders in the system. Deactivate the user instead.');
    }

    // If user is a cashier, delete related records first
    if ($user['user_type'] === 'Cashier') {
        // Delete cashier sessions
        $stmt = $pdo->prepare("DELETE FROM pos_cashier_sessions WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Delete cashier details
        $stmt = $pdo->prepare("DELETE FROM pos_cashier_details WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    // Finally, delete the user
    $stmt = $pdo->prepare("DELETE FROM pos_user WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

?>
