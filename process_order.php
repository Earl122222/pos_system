<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderType = $_POST['orderType'];
    $orderTotal = $_POST['orderTotal'];
    $items = $_POST['items'];
    $paymentMethod = $_POST['paymentMethod'];
    
    try {
        $pdo->beginTransaction();
        
        // Insert order
        $sql = "INSERT INTO pos_order (order_type, order_total, payment_method, order_created_by, order_date) 
                VALUES (:orderType, :orderTotal, :paymentMethod, :createdBy, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'orderType' => $orderType,
            'orderTotal' => $orderTotal,
            'paymentMethod' => $paymentMethod,
            'createdBy' => $_SESSION['user_id']
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insert order items
        foreach ($items as $item) {
            $sql = "INSERT INTO pos_order_item (order_id, product_id, quantity, price) 
                    VALUES (:orderId, :productId, :quantity, :price)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'orderId' => $orderId,
                'productId' => $item['productId'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }
        
        // Special handling for Grab Food orders
        if ($orderType === 'Grab Food') {
            // You can add any Grab Food specific processing here
            // For example, updating Grab Food specific metrics or sending notifications
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'orderId' => $orderId]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?> 