<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
}

// Function to redirect if already logged in
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        if ($_SESSION['user_type'] === 'Cashier') {
            header('Location: add_order.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    }
}

// Function to check admin only access
function checkAdminLogin() {
    requireLogin();
    if ($_SESSION['user_type'] !== 'Admin') {
        if ($_SESSION['user_type'] === 'Cashier') {
            header('Location: add_order.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } 
}

// Function to check if user is either admin or regular user
function checkAdminOrUserLogin() {
    requireLogin();
    if (!in_array($_SESSION['user_type'], ['Admin', 'User'])) {
        if ($_SESSION['user_type'] === 'Cashier') {
            header('Location: add_order.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    }
}

// Function to check if user is a cashier
function checkCashierLogin() {
    requireLogin();
    if ($_SESSION['user_type'] !== 'Cashier') {
        if ($_SESSION['user_type'] === 'Admin') {
            header('Location: dashboard.php');
        } else {
            header('Location: add_order.php');
        }
        exit;
    }
}

// Function to check if user has order access (Admin or Cashier)
function checkOrderAccess() {
    requireLogin();
    if (!in_array($_SESSION['user_type'], ['Admin', 'Cashier'])) {
        header('Location: dashboard.php');
        exit;
    }
}

// Function to get configuration data
function getConfigData($pdo) {
    try {
        $stmt = $pdo->query('SELECT * FROM pos_configuration');
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return first row or default values if no data
        return !empty($data) ? $data[0] : [
            'currency' => '$',
            'timezone' => 'UTC'
        ];
    } catch (PDOException $e) {
        return [
            'currency' => '$',
            'timezone' => 'UTC'
        ];
    }
}

// Function to get category name by ID
function getCategoryName($pdo, $category_id) {
    try {
        $stmt = $pdo->prepare('SELECT category_name FROM pos_category WHERE category_id = ?');
        $stmt->execute([$category_id]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return '';
    }
}

?>