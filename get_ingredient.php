<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_id = ?");
    $stmt->execute([$_GET['id']]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($ingredient);
} else {
    echo json_encode(['error' => 'No ID provided']);
} 