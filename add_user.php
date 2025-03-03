<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$message = '';

$user_name = '';
$user_email = '';
$user_password = '';
$user_type = '';
$user_status = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $user_password = trim($_POST['user_password']);
    $user_type = trim($_POST['user_type']);
    $user_status = trim($_POST['user_status']);
    
    // Validate inputs
    if (empty($user_name) || empty($user_email) || empty($user_password) || empty($user_type) || empty($user_status)) {
        $message = 'All fields are required.';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = :user_email");
        $stmt->execute(['user_email' => $user_email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = 'Email already exists.';
        } else {
            // Hash the password
            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO pos_user (user_name, user_email, user_password, user_type, user_status) VALUES (:user_name, :user_email, :user_password, :user_type, :user_status)");
                $stmt->execute([
                    'user_name'       => $user_name,
                    'user_email'      => $user_email,
                    'user_password'   => $hashed_password,
                    'user_type'       => $user_type,
                    'user_status'     => $user_status
                ]);
                header('location:user.php');
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include('header.php');
?>

<h1 class="mt-4">Add User</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
    <li class="breadcrumb-item active">Add User</li>
</ol>
    <?php
    if(isset($message) && $message !== ''){
        echo '
        <div class="alert alert-danger">
        '.$message.'
        </div>
        ';
    }
    ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Add User</div>
                <div class="card-body">
                    <form method="post" action="add_user.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="user_name">Name:</label>
                            <input type="text" id="user_name" name="user_name" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="user_email">Email:</label>
                            <input type="email" id="user_email" name="user_email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="user_password">Password:</label>
                            <input type="password" id="user_password" name="user_password" class="form-control" value="<?php echo htmlspecialchars($user_password); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="user_type">User Type:</label>
                            <select id="user_type" name="user_type" class="form-control">
                                <option value="Admin" <?php echo ($user_type == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="Cashier" <?php echo ($user_type == 'Cashier') ? 'selected' : ''; ?>>Cashier</option>
                                <option value="Stockman" <?php echo ($user_type == 'Stockman') ? 'selected' : ''; ?>>Stockman</option>
                            </select>
                        </div>
                        <div class="mt-2 text-center">
                            <input type="hidden" name="user_status" value="Active" />
                            <input type="submit" value="Add User" class="btn btn-primary" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
include('footer.php');
?>