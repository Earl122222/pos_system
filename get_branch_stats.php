<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

if (!isset($_GET['branch_id'])) {
    echo json_encode(['error' => 'Branch ID is required']);
    exit;
}

$branch_id = intval($_GET['branch_id']);
$today = date('Y-m-d');
$current_time = date('H:i:s');
$expiry_threshold = date('Y-m-d', strtotime('+30 days')); // Items expiring within 30 days

try {
    // First, check if the branch is currently operating
    $stmt = $pdo->prepare("
        SELECT 
            operating_hours,
            status
        FROM pos_branch 
        WHERE branch_id = ?
    ");
    $stmt->execute([$branch_id]);
    $branch_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Parse operating hours
    $hours = explode(' - ', $branch_info['operating_hours']);
    $opening_time = date('H:i:s', strtotime($hours[0]));
    $closing_time = date('H:i:s', strtotime($hours[1]));

    // Check for active cashier sessions
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT cs.user_id) as active_cashiers,
            GROUP_CONCAT(DISTINCT u.username) as active_usernames
        FROM pos_cashier_sessions cs
        JOIN pos_user u ON cs.user_id = u.user_id
        WHERE cs.branch_id = ?
        AND cs.is_active = TRUE
        AND cs.logout_time IS NULL
        AND DATE(cs.login_time) = CURRENT_DATE()
    ");
    $stmt->execute([$branch_id]);
    $session_info = $stmt->fetch(PDO::FETCH_ASSOC);
    $active_cashiers = $session_info['active_cashiers'];
    $active_usernames = $session_info['active_usernames'];

    // Check if branch is currently operating
    $is_operating = false;
    if ($branch_info['status'] === 'Active' && $active_cashiers > 0) {
        if ($opening_time <= $closing_time) {
            $is_operating = ($current_time >= $opening_time && $current_time <= $closing_time);
        } else {
            // Handle cases where closing time is after midnight
            $is_operating = ($current_time >= $opening_time || $current_time <= $closing_time);
        }
    }

    // Get today's sales and orders (only if branch is operating)
    if ($is_operating) {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(order_total), 0) as total_sales
            FROM pos_order
            WHERE branch_id = ? 
            AND DATE(order_datetime) = ?
        ");
        $stmt->execute([$branch_id, $today]);
        $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $sales_data = [
            'total_orders' => 0,
            'total_sales' => 0
        ];
    }

    // Get low stock count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pos_branch_inventory bi
        JOIN pos_inventory i ON bi.inventory_id = i.inventory_id
        WHERE bi.branch_id = ?
        AND bi.current_stock <= i.minimum_stock
    ");
    $stmt->execute([$branch_id]);
    $low_stock_count = $stmt->fetchColumn();

    // Get expiring items count
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM pos_branch_inventory bi
        WHERE bi.branch_id = ?
        AND bi.expiry_date <= ?
        AND bi.current_stock > 0
    ");
    $stmt->execute([$branch_id, $expiry_threshold]);
    $expiring_count = $stmt->fetchColumn();

    echo json_encode([
        'today_sales' => floatval($sales_data['total_sales']),
        'today_orders' => intval($sales_data['total_orders']),
        'low_stock_count' => intval($low_stock_count),
        'expiring_count' => intval($expiring_count),
        'is_operating' => $is_operating,
        'has_active_cashiers' => $active_cashiers > 0,
        'active_cashiers' => $active_cashiers,
        'active_usernames' => $active_usernames ? explode(',', $active_usernames) : []
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 