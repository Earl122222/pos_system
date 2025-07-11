<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$message = '';
$user_id = (isset($_GET['id'])) ? $_GET['id'] :'';
$user_name = '';
$user_email = '';
$user_status = 'Active';

// Fetch the current user data
if (!empty($user_id)) {
    $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_name = $user["user_name"];
        $user_email = $user["user_email"];
        $user_status = $user["user_status"];
        $contact_number = $user["contact_number"] ?? '';
        $profile_image = $user["profile_image"] ?? '';
        $user_type = $user["user_type"] ?? '';
        $branch_id = $user["branch_id"] ?? '';
        $employee_id = $user["employee_id"] ?? '';
        $date_hired = $user["date_hired"] ?? '';
        $emergency_contact = $user["emergency_contact"] ?? '';
        $emergency_number = $user["emergency_number"] ?? '';
        $address = $user["address"] ?? '';
        $notes = $user["notes"] ?? '';
    } else {
        $message = 'User not found.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch current user data for fallback
    $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_name = trim($_POST['user_name']) !== '' ? trim($_POST['user_name']) : $current['user_name'];
    $user_email = trim($_POST['user_email']) !== '' ? trim($_POST['user_email']) : $current['user_email'];
    $contact_number = trim($_POST['contact_number']) !== '' ? trim($_POST['contact_number']) : $current['contact_number'];
    $address = trim($_POST['address']) !== '' ? trim($_POST['address']) : $current['address'];

    // Handle password
    $user_password = trim($_POST['user_password']);
    $update_password = !empty($user_password);

    // Handle profile image
    $profile_image = $current['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $upload_name = 'profile_' . time() . '.' . $ext;
            $upload_path = 'uploads/profiles/' . $upload_name;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = $upload_path;
            }
        }
    }

    // Validate email format if changed
    if ($user_email !== $current['user_email'] && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
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
            try {
                $sql = "UPDATE pos_user SET user_name = :user_name, user_email = :user_email, contact_number = :contact_number, address = :address, profile_image = :profile_image";
                $params = [
                    'user_name' => $user_name,
                    'user_email' => $user_email,
                    'contact_number' => $contact_number,
                    'address' => $address,
                    'profile_image' => $profile_image,
                    'user_id' => $user_id
                ];
                if ($update_password) {
                    $sql .= ", user_password = :user_password";
                    $params['user_password'] = password_hash($user_password, PASSWORD_DEFAULT);
                }
                $sql .= " WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                header('location:user.php');
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit User</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
        <li class="breadcrumb-item active">Edit User</li>
    </ol>
    <?php if(isset($message) && $message !== ''): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="edit_user.php?id=<?php echo htmlspecialchars($user_id); ?>" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-4">Basic Information</h5>
                <div class="form-group mb-3">
                    <label for="user_name" class="form-label">Full Name*</label>
                    <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="user_email" class="form-label">Email Address*</label>
                    <input type="email" class="form-control" id="user_email" name="user_email" value="<?php echo htmlspecialchars($user_email); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="user_password" class="form-label">Password (leave blank to keep current)</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="user_password" name="user_password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="contact_number" class="form-label">Contact Number*</label>
                    <input type="tel" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <?php if (!empty($profile_image)): ?>
                        <div class="mb-2"><img src="<?php echo $profile_image; ?>" alt="Profile Image" style="max-height: 80px;"></div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                </div>
            </div>
            <div class="col-md-6">
                <h5 class="mb-4">Additional Information</h5>
                <div class="form-group mb-3">
                    <label for="address" class="form-label">Complete Address*</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update User
            </button>
            <a href="user.php" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php
include('footer.php');
?>