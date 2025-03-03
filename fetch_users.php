<?php
require_once 'db_connect.php';

if (isset($_POST['department_id'])) {
    $department_id = $_POST['department_id'];
    $stmt = $pdo->prepare("SELECT user_id, CONCAT(user_first_name, ' ', user_last_name) AS user_name FROM task_user WHERE department_id = ? AND user_status = 'Enable'");
    $stmt->execute([$department_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<option value="">Select User</option>';
    foreach ($users as $user) {
        echo '<option value="' . $user['user_id'] . '">' . $user['user_name'] . '</option>';
    }
}
?>