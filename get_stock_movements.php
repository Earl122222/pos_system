<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Allow both cashier and stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

try {
    // Get branch ID for cashier or stockman
    if ($user_type === 'Cashier') {
        $stmt = $pdo->prepare("SELECT branch_id FROM pos_cashier_details WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
        if (!$branch_id) {
            throw new Exception('Cashier not assigned to any branch');
        }
        $where = 'sm.user_id = ? AND sm.branch_id = ?';
        $params = [$user_id, $branch_id, $limit];
    } elseif ($user_type === 'Stockman') {
        // Try to get branch_id from pos_user or another appropriate table
        $stmt = $pdo->prepare("SELECT branch_id FROM pos_user WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
        if (!$branch_id) {
            throw new Exception('Stockman not assigned to any branch');
        }
        $where = 'sm.branch_id = ?';
        $params = [$branch_id, $limit];
    } else {
        throw new Exception('Unauthorized user type');
    }

    // Get stock movements for the branch
    $stmt = $pdo->prepare("
        SELECT 
            sm.*, 
            i.item_name, 
            DATE_FORMAT(sm.created_at, '%Y-%m-%d %H:%i:%s') as formatted_date
        FROM pos_stock_movement sm
        JOIN pos_inventory i ON sm.inventory_id = i.inventory_id
        WHERE $where
        ORDER BY sm.created_at DESC
        LIMIT ?
    ");
    $stmt->execute($params);
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the movements for display
    $formatted_movements = array_map(function($movement) {
        return [
            'created_at' => $movement['formatted_date'],
            'item_name' => $movement['item_name'],
            'movement_type' => $movement['movement_type'],
            'quantity' => $movement['quantity'],
            'previous_stock' => $movement['previous_stock'],
            'new_stock' => $movement['new_stock'],
            'reference_type' => $movement['reference_type'],
            'reference_id' => $movement['reference_id']
        ];
    }, $movements);

    echo json_encode([
        'success' => true,
        'movements' => $formatted_movements
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 