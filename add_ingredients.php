<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE category_status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = [];

    $category_id = $_POST['category_id'];
    $ingredient_name = trim($_POST['ingredient_name']);
    $ingredient_quantity = trim($_POST['ingredient_quantity']);
    $ingredient_unit = trim($_POST['ingredient_unit']);
    $ingredient_status = $_POST['ingredient_status'];

    // Validate fields
    if (empty($category_id)) {
        $errors[] = 'Category is required.';
    }
    if (empty($ingredient_name)) {
        $errors[] = 'Ingredient Name is required.';
    }
    if (empty($ingredient_quantity) || !is_numeric($ingredient_quantity)) {
        $errors[] = 'Valid Ingredient Quantity is required.';
    }
    if (empty($ingredient_unit)) {
        $errors[] = 'Unit of Measurement is required.';
    }

    // Check if the ingredient already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE ingredient_name = :ingredient_name");
    $stmt->execute(['ingredient_name' => $ingredient_name]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $errors[] = 'Ingredient already exists.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO ingredients (category_id, ingredient_name, ingredient_quantity, ingredient_unit, ingredient_status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $ingredient_name, $ingredient_quantity, $ingredient_unit, $ingredient_status]);
        header("Location: ingredients.php");
        exit;
    } else {
        $message = '<ul class="list-unstyled">';
        foreach ($errors as $error) {
            $message .= '<li>' . $error . '</li>';
        }
        $message .= '</ul>';
    }
}

include('header.php');

?>

<h1 class="mt-4">Add Ingredient</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="ingredients.php">Ingredient Management</a></li>
    <li class="breadcrumb-item active">Add Ingredient</li>
</ol>

<?php
if ($message !== '') {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Add Ingredient</div>
            <div class="card-body">
                <form method="POST" action="add_ingredient.php">
                    <div class="mb-3">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_name">Ingredient Name</label>
                        <input type="text" name="ingredient_name" id="ingredient_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_quantity">Quantity</label>
                        <input type="number" name="ingredient_quantity" id="ingredient_quantity" class="form-control" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_unit">Unit</label>
                        <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_status">Status</label>
                        <select name="ingredient_status" id="ingredient_status" class="form-select">
                            <option value="Available">Available</option>
                            <option value="Out of Stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-primary">Add Ingredient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
