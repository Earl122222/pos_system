<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$message = '';

// Get the role from URL parameter
$selected_role = isset($_GET['role']) ? ucfirst($_GET['role']) : 'Admin';

$user_name = '';
$user_email = '';
$user_password = '';
$user_type = $selected_role; // Set default user type based on URL parameter
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

<h1 class="mt-4">Add <?php echo $selected_role; ?></h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
    <li class="breadcrumb-item active">Add <?php echo $selected_role; ?></li>
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
                <div class="card-header">Add <?php echo $selected_role; ?></div>
                <div class="card-body">
                    <form method="post" action="add_user.php?role=<?php echo strtolower($selected_role); ?>" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="user_name">Name:</label>
                            <input type="text" id="user_name" name="user_name" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_email">Email:</label>
                            <input type="email" id="user_email" name="user_email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_password">Password:</label>
                            <div class="input-group">
                                <input type="password" id="user_password" name="user_password" class="form-control" value="<?php echo htmlspecialchars($user_password); ?>" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($selected_role); ?>">
                        <div class="mt-2 text-center">
                            <input type="hidden" name="user_status" value="Active" />
                            <input type="submit" value="Add <?php echo $selected_role; ?>" class="btn btn-primary" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('user_password');
    const icon = document.querySelector('.fa-eye');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php
include('footer.php');
?>