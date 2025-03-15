<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    // Get the user's branch assignment
    $stmt = $pdo->prepare("
        SELECT branch_id 
        FROM pos_cashier_details 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $branch_id = $stmt->fetchColumn();

    if (!$branch_id) {
        echo json_encode(['error' => 'User is not assigned to any branch']);
        exit;
    }

    if ($action === 'login') {
        // First, check if there's an active session
        $stmt = $pdo->prepare("
            SELECT session_id 
            FROM pos_cashier_sessions 
            WHERE user_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$user_id]);
        $active_session = $stmt->fetchColumn();

        if (!$active_session) {
            // Create new session
            $stmt = $pdo->prepare("
                INSERT INTO pos_cashier_sessions (user_id, branch_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$user_id, $branch_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Session started']);
    } 
    else if ($action === 'logout') {
        // Update existing session
        $stmt = $pdo->prepare("
            UPDATE pos_cashier_sessions 
            SET logout_time = CURRENT_TIMESTAMP, is_active = FALSE
            WHERE user_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$user_id]);

        echo json_encode(['success' => true, 'message' => 'Session ended']);
    }
    else {
        echo json_encode(['error' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 