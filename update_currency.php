<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Currency</title>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Currency Update Tool</h2>
    <?php
    require_once 'db_connect.php';

    try {
        // First check if we can connect to the database
        if (!$pdo) {
            throw new Exception("Database connection failed");
        }

        // Check if the configuration table exists and has records
        $check = $pdo->query("SELECT COUNT(*) FROM pos_configuration");
        $count = $check->fetchColumn();
        
        if ($count == 0) {
            throw new Exception("No configuration records found");
        }

        // Get current currency
        $before = $pdo->query("SELECT currency FROM pos_configuration LIMIT 1");
        $currentCurrency = $before->fetchColumn();
        echo "<p>Current currency symbol: " . htmlspecialchars($currentCurrency) . "</p>";

        // Update the currency
        $stmt = $pdo->prepare("UPDATE pos_configuration SET currency = '₱'");
        $result = $stmt->execute();
        
        if ($result) {
            // Verify the update
            $verify = $pdo->query("SELECT currency FROM pos_configuration LIMIT 1");
            $newCurrency = $verify->fetchColumn();
            echo "<p style='color: green;'>Currency symbol updated successfully!</p>";
            echo "<p>New currency symbol: " . htmlspecialchars($newCurrency) . "</p>";
        } else {
            echo "<p style='color: red;'>Update failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
    <p><a href="dashboard.php">Return to Dashboard</a></p>
</body>
</html> 