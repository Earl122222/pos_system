<?php
require_once 'includes/db_connection.php';
session_start();

// Function to check for low ingredients and create notifications
function checkLowIngredients() {
    global $conn;
    $notifications = array();
    
    // Get all ingredients with low stock (below threshold)
    $query = "SELECT ingredient_name, quantity, unit 
              FROM ingredients 
              WHERE quantity <= minimum_stock 
              ORDER BY quantity ASC";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = array(
                'type' => 'low_stock',
                'message' => "Low stock alert: {$row['ingredient_name']} ({$row['quantity']} {$row['unit']} remaining)",
                'ingredient_name' => $row['ingredient_name'],
                'quantity' => $row['quantity'],
                'unit' => $row['unit']
            );
        }
    }
    
    return $notifications;
}

// Function to get notifications as JSON
function getNotificationsJson() {
    $notifications = checkLowIngredients();
    header('Content-Type: application/json');
    echo json_encode(array(
        'count' => count($notifications),
        'notifications' => $notifications
    ));
}

// If this file is called directly via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_notifications') {
    getNotificationsJson();
}
?> 