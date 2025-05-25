<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

// Get and validate input
$item_name = trim($_POST['item_name'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);
$minimum_stock = intval($_POST['minimum_stock'] ?? 0);

// Validate input
if (empty($item_name)) {
    echo json_encode(['success' => false, 'error' => 'Item name is required']);
    exit();
}

if ($quantity < 0) {
    echo json_encode(['success' => false, 'error' => 'Quantity must be non-negative']);
    exit();
}

if ($minimum_stock < 0) {
    echo json_encode(['success' => false, 'error' => 'Minimum stock must be non-negative']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if item already exists
    $stmt = $pdo->prepare("SELECT inventory_id FROM pos_inventory WHERE item_name = ? AND status = 'Active'");
    $stmt->execute([$item_name]);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_item) {
        // Update existing item
        $stmt = $pdo->prepare("
            UPDATE pos_inventory 
            SET current_stock = current_stock + ?,
                minimum_stock = ?,
                last_updated = CURRENT_TIMESTAMP
            WHERE inventory_id = ?
        ");
        $stmt->execute([$quantity, $minimum_stock, $existing_item['inventory_id']]);
        $inventory_id = $existing_item['inventory_id'];
    } else {
        // Insert new item
        $stmt = $pdo->prepare("
            INSERT INTO pos_inventory 
            (item_name, current_stock, minimum_stock, status, created_at, last_updated)
            VALUES (?, ?, ?, 'Active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$item_name, $quantity, $minimum_stock]);
        $inventory_id = $pdo->lastInsertId();
    }

    // Record stock movement
    $stmt = $pdo->prepare("
        INSERT INTO pos_stock_movement 
        (inventory_id, movement_type, quantity, previous_stock, new_stock, reference_type, reference_id, created_at)
        VALUES (?, 'IN', ?, 0, ?, 'MANUAL', ?, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([$inventory_id, $quantity, $quantity, $_SESSION['user_id']]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Stock item added successfully',
        'inventory_id' => $inventory_id
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log('Error in add_stock_item.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} 