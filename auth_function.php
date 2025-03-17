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
            // Check if there's a stored redirect URL
            $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'sales.php';
            unset($_SESSION['redirect_url']); // Clear the stored URL
            header('Location: ' . $redirect);
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
            header('Location: sales.php');
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
    // First check if user is logged in and is a cashier
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Cashier') {
        $_SESSION['redirect_url'] = $_SERVER['PHP_SELF'];
        header('Location: login.php');
        exit();
    }

    global $pdo;
    
    try {
        // Check for active session
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM pos_cashier_sessions 
            WHERE user_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $hasActiveSession = $stmt->fetchColumn() > 0;

        if (!$hasActiveSession) {
            // Get cashier's branch
            $stmt = $pdo->prepare("
                SELECT branch_id 
                FROM pos_cashier_details 
                WHERE user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $cashier = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cashier) {
                // Instead of redirecting, just return false
                $_SESSION['error'] = 'Cashier not assigned to any branch';
                return false;
            }

            // End any existing active sessions
            $stmt = $pdo->prepare("
                UPDATE pos_cashier_sessions 
                SET is_active = FALSE, logout_time = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND is_active = TRUE
            ");
            $stmt->execute([$_SESSION['user_id']]);

            // Create new session
            $stmt = $pdo->prepare("
                INSERT INTO pos_cashier_sessions (user_id, branch_id, login_time, is_active) 
                VALUES (?, ?, CURRENT_TIMESTAMP, TRUE)
            ");
            if (!$stmt->execute([$_SESSION['user_id'], $cashier['branch_id']])) {
                $_SESSION['error'] = 'Failed to create cashier session';
                return false;
            }
        }
        return true;
    } catch (PDOException $e) {
        error_log('Error in checkCashierLogin: ' . $e->getMessage());
        $_SESSION['error'] = 'Database error occurred. Please try again.';
        return false;
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
            'currency' => '₱',
            'timezone' => 'UTC'
        ];
    } catch (PDOException $e) {
        return [
            'currency' => '₱',
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

function createCashierSession($pdo, $user_id) {
    try {
        // Get cashier's branch
        $stmt = $pdo->prepare("
            SELECT branch_id 
            FROM pos_cashier_details 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cashier = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cashier) {
            throw new Exception('Cashier not assigned to any branch');
        }

        // End any existing active sessions
        $stmt = $pdo->prepare("
            UPDATE pos_cashier_sessions 
            SET is_active = FALSE, logout_time = CURRENT_TIMESTAMP 
            WHERE user_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$user_id]);

        // Create new session
        $stmt = $pdo->prepare("
            INSERT INTO pos_cashier_sessions (user_id, branch_id, login_time, is_active) 
            VALUES (?, ?, CURRENT_TIMESTAMP, TRUE)
        ");
        $stmt->execute([$user_id, $cashier['branch_id']]);

        return true;
    } catch (Exception $e) {
        error_log('Error creating cashier session: ' . $e->getMessage());
        return false;
    }
}

?>