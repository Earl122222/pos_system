<?php

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $category_id = $_POST['id'];

    // Check if category exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_category WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Delete category
        $stmt = $pdo->prepare("DELETE FROM pos_category WHERE category_id = ?");
        if ($stmt->execute([$category_id])) {
            echo "Category deleted successfully.";
        } else {
            echo "Error deleting category.";
        }
    } else {
        echo "Category not found.";
    }
} else {
    echo "Invalid request.";
}
?>
