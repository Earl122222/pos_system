<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo "Invalid user ID.";
        exit;
    }

    $user_id = $_POST['id'];

    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM pos_user WHERE user_id = ?");
    if ($stmt->execute([$user_id])) {
        echo "User deleted successfully.";
    } else {
        echo "Failed to delete user.";
    }
}

?>
