<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$message = '';
$success = false;
$user_id = $_SESSION['user_id'];
$user_name = '';
$user_email = '';

// Fetch the current user data
if (!empty($user_id)) {
    $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_name = $user['user_name'];
        $user_email = $user['user_email'];
    } else {
        $message = 'User not found.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    
    // Validate inputs
    if (empty($user_name) || empty($user_email)) {
        $message = 'All fields are required.';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } else {
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = :user_email AND user_id != :user_id");
        $stmt->execute(['user_email' => $user_email, 'user_id' => $user_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = 'Email already exists.';
        } else {
            // Update the database
            if (empty($message)) {
                try {
                    $stmt = $pdo->prepare("UPDATE pos_user SET user_name = :user_name, user_email = :user_email WHERE user_id = :user_id");
                    $stmt->execute([
                        'user_name'       => $user_name,
                        'user_email'      => $user_email,
                        'user_id'         => $user_id
                    ]);
                    $success = true;
                } catch (PDOException $e) {
                    $message = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

include('header.php');
?>

<h1 class="mt-4">Profile</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="task.php">Task</a></li>
    <li class="breadcrumb-item active">Profile</a></li>
</ol>
    <?php
    if(isset($message) && $message !== ''){
        echo '
        <div class="alert alert-danger">
        '.$message.'
        </div>
        ';
    }
    if($success){
        echo '<div class="alert alert-success">Data updated successfully</div>';
    }
    ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><b>Change Profile Details</b></div>
                <div class="card-body">
                    <form method="post" action="user_profile.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="user_name">Name:</label>
                            <input type="text" id="user_name" name="user_name" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="user_email">Email:</label>
                            <input type="text" id="user_email" name="user_email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>">
                        </div>
                        <div class="mt-2 text-center">
                            <input type="submit" value="Save" class="btn btn-primary" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
include('footer.php');
?>