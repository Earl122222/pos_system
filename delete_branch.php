<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('Branch ID is required');
    }

    $branchId = intval($_POST['id']);

    // Check if branch exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_branch WHERE branch_id = ?");
    $stmt->execute([$branchId]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Branch not found');
    }

    // Check if branch has associated cashiers
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_cashier_details WHERE branch_id = ?");
    $stmt->execute([$branchId]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cannot delete branch: There are cashiers assigned to this branch');
    }

    // Delete the branch
    $stmt = $pdo->prepare("DELETE FROM pos_branch WHERE branch_id = ?");
    $stmt->execute([$branchId]);

    echo json_encode([
        'success' => true,
        'message' => 'Branch deleted successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 