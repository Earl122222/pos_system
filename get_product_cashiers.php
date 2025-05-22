<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $product_id = intval($_GET['product_id']);

    // Get cashiers who have access to this product through their branch assignments
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.user_id,
            u.user_name,
            u.user_status,
            b.branch_name,
            cd.shift_schedule
        FROM pos_user u
        JOIN pos_cashier_details cd ON u.user_id = cd.user_id
        JOIN pos_branch b ON cd.branch_id = b.branch_id
        JOIN pos_branch_product bp ON b.branch_id = bp.branch_id
        WHERE bp.product_id = ?
        AND u.user_type = 'Cashier'
        ORDER BY b.branch_name, u.user_name
    ");
    
    $stmt->execute([$product_id]);
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $cashiers
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 