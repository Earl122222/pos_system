<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

if (!isset($_GET['branch_id'])) {
    echo json_encode(['error' => 'Branch ID is required']);
    exit;
}

$branch_id = $_GET['branch_id'];
$today = date('Y-m-d');
$expiry_threshold = date('Y-m-d', strtotime('+30 days')); // Items expiring within 30 days

try {
    // Get low stock items
    $stmt = $pdo->prepare("
        SELECT 
            i.item_name,
            bi.current_stock,
            i.minimum_stock,
            CASE 
                WHEN bi.current_stock = 0 THEN 'Out of Stock'
                WHEN bi.current_stock < i.minimum_stock THEN 'Low Stock'
                ELSE 'Sufficient'
            END as status
        FROM pos_branch_inventory bi
        JOIN pos_inventory i ON bi.inventory_id = i.inventory_id
        WHERE bi.branch_id = ?
        AND bi.current_stock <= i.minimum_stock
        ORDER BY 
            bi.current_stock = 0 DESC,
            bi.current_stock ASC
    ");
    $stmt->execute([$branch_id]);
    $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get expiring items
    $stmt = $pdo->prepare("
        SELECT 
            i.item_name,
            bi.expiry_date,
            bi.current_stock as quantity,
            DATEDIFF(bi.expiry_date, CURRENT_DATE) as days_left
        FROM pos_branch_inventory bi
        JOIN pos_inventory i ON bi.inventory_id = i.inventory_id
        WHERE bi.branch_id = ?
        AND bi.expiry_date <= ?
        AND bi.current_stock > 0
        ORDER BY bi.expiry_date ASC
    ");
    $stmt->execute([$branch_id, $expiry_threshold]);
    $expiring_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format expiring items data
    foreach ($expiring_items as &$item) {
        $item['expiry_date'] = date('M d, Y', strtotime($item['expiry_date']));
        $item['days_left'] = intval($item['days_left']);
    }

    echo json_encode([
        'low_stock_count' => count($low_stock_items),
        'expiring_count' => count($expiring_items),
        'low_stock_items' => $low_stock_items,
        'expiring_items' => $expiring_items
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 