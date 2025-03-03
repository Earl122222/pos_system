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
        header('Location: dashboard.php');
        exit();
    }
}

// Function to check admin only access
function checkAdminLogin() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
        header('Location: index.php');
        exit;
    } 
}

// Function to check if user is either admin or regular user
function checkAdminOrUserLogin() {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        header('Location: login.php');
        exit();
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