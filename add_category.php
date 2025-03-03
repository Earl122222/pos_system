<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name']);
    $category_status = trim($_POST['category_status']);
    $message = '';

    // Validate inputs
    if (empty($category_name)) {
        $message = 'Category name is required.';
    } else {
        // Check if Category already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_category WHERE category_name = :category_name");
        $stmt->execute(['category_name' => $category_name]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = 'Category with this name already exists.';
        } else {
            // Insert into database
            try {
                $stmt = $pdo->prepare("INSERT INTO pos_category (category_name, category_status) VALUES (:category_name, :category_status)");
                $stmt->execute([
                    'category_name' => $category_name,
                    'category_status' => $category_status
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

<h1 class="mt-4">Add Category</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="category.php">Category Management</a></li>
    <li class="breadcrumb-item active">Add Category</li>
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
            <div class="card-header">Add Category</div>
            <div class="card-body">
            <form method="post" action="add_category.php">
                <div class="mb-3">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="category_status">Category Status</label>
                    <select id="category_status" name="category_status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <input type="submit" value="Add Category" class="btn btn-primary">
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>