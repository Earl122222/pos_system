<?php

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $ingredient_id = $_POST['id'];

    // Check if the ingredient exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE ingredient_id = ?");
    $stmt->execute([$ingredient_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Delete the ingredient
        $stmt = $pdo->prepare("DELETE FROM ingredients WHERE ingredient_id = ?");
        if ($stmt->execute([$ingredient_id])) {
            echo "Ingredient deleted successfully.";
        } else {
            echo "Error deleting ingredient.";
        }
    } else {
        echo "Ingredient not found.";
    }
} else {
    echo "Invalid request.";
}
?>
