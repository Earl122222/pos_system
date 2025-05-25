<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get all active inventory items
    $stmt = $pdo->query("
        SELECT 
            i.inventory_id as id,
            i.item_name,
            i.current_stock,
            i.minimum_stock,
            CASE 
                WHEN i.current_stock = 0 THEN 'Out of Stock'
                WHEN i.current_stock <= i.minimum_stock THEN 'Low Stock'
                ELSE 'Adequate'
            END as status,
            i.expiry_date,
            i.last_updated
        FROM pos_inventory i
        WHERE i.status = 'Active'
        ORDER BY i.item_name ASC
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates and add additional information
    foreach ($items as &$item) {
        $item['expiry_date'] = $item['expiry_date'] ? date('Y-m-d', strtotime($item['expiry_date'])) : null;
        $item['last_updated'] = date('Y-m-d H:i:s', strtotime($item['last_updated']));
        
        // Calculate days until expiry
        if ($item['expiry_date']) {
            $expiry = new DateTime($item['expiry_date']);
            $today = new DateTime();
            $days_until_expiry = $today->diff($expiry)->days;
            $item['days_until_expiry'] = $days_until_expiry;
        } else {
            $item['days_until_expiry'] = null;
        }
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);

} catch (PDOException $e) {
    error_log('Error in get_stock_items.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} 