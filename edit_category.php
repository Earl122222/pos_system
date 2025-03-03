<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$category_id = $_GET['id'] ?? '';
$category_name = '';
$category_status = 'Active';
$message = '';

// Fetch the current category data
if (!empty($category_id)) {
    $stmt = $pdo->prepare("SELECT * FROM pos_category WHERE category_id = :category_id");
    $stmt->execute(['category_id' => $category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        $category_name = $category['category_name'];
        $category_status = $category['category_status'];
    } else {
        $message = 'Category not found.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name']);
    $category_status = trim($_POST['category_status']);
    $category_id = $_POST['category_id'];
    // Validate inputs
    if (empty($category_name)) {
        $message = 'Category name is required.';
    } else {
        // Check if category name already exists for another category
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_category WHERE category_name = :category_name AND category_id != :category_id");
        $stmt->execute([
            'category_name' => $category_name,
            'category_id' => $category_id
        ]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = 'Category with this name already exists.';
        } else {
            // Update the database
            try {
                $stmt = $pdo->prepare("UPDATE pos_category SET category_name = :category_name, category_status = :category_status WHERE category_id = :category_id");
                $stmt->execute([
                    'category_name' => $category_name,
                    'category_status' => $category_status,
                    'category_id' => $category_id
                ]);
                header('location:category.php');
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include('header.php');
?>

<h1 class="mt-4">Edit Category</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="category.php">Category Management</a></li>
    <li class="breadcrumb-item active">Edit Category</li>
</ol>

<div class="row">
    <div class="col-md-4">
        <?php
        if(isset($message) && $message !== ''){
            echo '
            <div class="alert alert-danger">
            '.$message.'
            </div>
            ';
        }
        ?>
        <div class="card">
            <div class="card-header">Edit Category</div>
            <div class="card-body">
            <form method="post" action="edit_category.php?id=<?php echo htmlspecialchars($category_id); ?>">
                <div class="mb-3">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" class="form-control" value="<?php echo htmlspecialchars($category_name); ?>">
                </div>
                <div class="mb-3">
                    <label for="category_status">Category Status</label>
                    <select id="category_status" name="category_status" class="form-select">
                        <option value="Active" <?php if ($category_status == 'Active') echo 'selected'; ?>>Active</option>
                        <option value="Inactive" <?php if ($category_status == 'Inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_id); ?>">
                <input type="submit" value="Update Category" class="btn btn-primary">
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>