<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('User ID is required');
    }

    $user_id = $_GET['id'];
    
    // Get user details
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            CASE 
                WHEN u.profile_image IS NULL OR u.profile_image = '' THEN 'uploads/profiles/default.png'
                ELSE u.profile_image 
            END as profile_image
        FROM pos_user u 
        WHERE u.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // If user is a cashier, get additional details
    if ($user['user_type'] === 'Cashier') {
        $stmt = $pdo->prepare("
            SELECT 
                cd.*,
                b.branch_name
            FROM pos_cashier_details cd
            LEFT JOIN pos_branch b ON b.branch_id = cd.branch_id
            WHERE cd.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cashier_details = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cashier_details) {
            $user['cashier_details'] = $cashier_details;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $user
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 