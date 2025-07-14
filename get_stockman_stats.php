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
    // Get total items count
    $stmt = $pdo->query("SELECT COUNT(*) FROM pos_inventory WHERE status = 'Active'");
    $total_items = $stmt->fetchColumn();

    // Get low stock items count
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM pos_inventory i
        WHERE i.current_stock <= i.minimum_stock 
        AND i.status = 'Active'
    ");
    $low_stock_items = $stmt->fetchColumn();

    // Get stock movements count for today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pos_stock_movement 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $stock_movements = $stmt->fetchColumn();

    // Get expiring items count
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM pos_inventory 
        WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND expiry_date >= CURDATE()
        AND status = 'Active'
    ");
    $expiring_items = $stmt->fetchColumn();

    // Get stock status distribution
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN current_stock > minimum_stock THEN 1 ELSE 0 END) as adequate_stock,
            SUM(CASE WHEN current_stock <= minimum_stock AND current_stock > 0 THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN current_stock = 0 THEN 1 ELSE 0 END) as out_of_stock
        FROM pos_inventory
        WHERE status = 'Active'
    ");
    $stock_status = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get total ingredients count
    $stmt = $pdo->query("SELECT COUNT(*) FROM ingredients");
    $total_ingredients = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'total_items' => $total_items,
        'low_stock_items' => $low_stock_items,
        'stock_movements' => $stock_movements,
        'expiring_items' => $expiring_items,
        'adequate_stock' => (int)$stock_status['adequate_stock'],
        'low_stock' => (int)$stock_status['low_stock'],
        'out_of_stock' => (int)$stock_status['out_of_stock'],
        'total_ingredients' => (int)$total_ingredients
    ]);

} catch (PDOException $e) {
    error_log('Error in get_stockman_stats.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} 