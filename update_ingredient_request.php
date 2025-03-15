<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_POST['request_id']) || !isset($_POST['status'])) {
        throw new Exception('Missing required parameters');
    }

    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $updatedBy = $_SESSION['user_id'];
    $updateDate = date('Y-m-d H:i:s');

    // Update request status
    $query = "UPDATE ingredient_requests 
              SET status = :status,
                  notes = :notes,
                  updated_by = :updated_by,
                  updated_at = :updated_at
              WHERE request_id = :request_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':updated_by', $updatedBy);
    $stmt->bindParam(':updated_at', $updateDate);
    $stmt->bindParam(':request_id', $requestId);

    if ($stmt->execute()) {
        echo json_encode(array('success' => true));
    } else {
        throw new Exception('Failed to update request status');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
} 