<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE category_status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$errors = [];
$ingredient_id = isset($_GET['id']) ? $_GET['id'] : '';
$category_id = '';
$ingredient_name = '';
$ingredient_quantity = 0; // Ensure it's numeric
$ingredient_unit = '';
$ingredient_status = 'Available';

if ($ingredient_id) {
    $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_id = :ingredient_id");
    $stmt->execute(['ingredient_id' => $ingredient_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ingredient) {
        $category_id = $ingredient['category_id'];
        $ingredient_name = $ingredient['ingredient_name'];
        $ingredient_quantity = (float) $ingredient['ingredient_quantity']; // Convert to float
        $ingredient_unit = $ingredient['ingredient_unit'];
        $ingredient_status = $ingredient['ingredient_status'];
    } else {
        $message = 'Ingredient not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $category_id = $_POST['category_id'];
    $ingredient_name = trim($_POST['ingredient_name']);
    $ingredient_unit = trim($_POST['ingredient_unit']);
    $ingredient_status = $_POST['ingredient_status'];
    $action = $_POST['action'] ?? ''; // Determine restock or deduct action
    $change_quantity = (float) $_POST['change_quantity']; // Convert to float

    // Validate fields
    if (empty($category_id)) {
        $errors[] = 'Category is required.';
    }
    if (empty($ingredient_name)) {
        $errors[] = 'Ingredient Name is required.';
    }
    if (empty($ingredient_unit)) {
        $errors[] = 'Unit of Measurement is required.';
    }
    if ($change_quantity < 0) {
        $errors[] = 'Quantity cannot be negative.';
    }

    // Adjust quantity based on restock or deduct action
    if ($action === "restock") {
        $ingredient_quantity += $change_quantity;
    } elseif ($action === "deduct") {
        if ($ingredient_quantity >= $change_quantity) {
            $ingredient_quantity -= $change_quantity;
        } else {
            $errors[] = 'Cannot deduct more than available quantity.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE ingredients SET category_id = ?, ingredient_name = ?, ingredient_quantity = ?, ingredient_unit = ?, ingredient_status = ? WHERE ingredient_id = ?");
        $stmt->execute([$category_id, $ingredient_name, $ingredient_quantity, $ingredient_unit, $ingredient_status, $ingredient_id]);
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

<h1 class="mt-4">Edit Ingredient</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="ingredients.php">Ingredient Management</a></li>
    <li class="breadcrumb-item active">Edit Ingredient</li>
</ol>

<?php
if ($message !== '') {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Edit Ingredient</div>
            <div class="card-body">
                <form method="POST" action="edit_ingredient.php?id=<?php echo htmlspecialchars($ingredient_id); ?>">
                    <div class="mb-3">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php if ($category_id == $category['category_id']) echo 'selected'; ?>><?php echo $category['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_name">Ingredient Name</label>
                        <input type="text" name="ingredient_name" id="ingredient_name" class="form-control" value="<?php echo htmlspecialchars($ingredient_name); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_quantity">Current Quantity</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($ingredient_quantity); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="change_quantity">Change Quantity</label>
                        <input type="number" name="change_quantity" id="change_quantity" class="form-control" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_unit">Unit</label>
                        <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control" value="<?php echo htmlspecialchars($ingredient_unit); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_status">Status</label>
                        <select name="ingredient_status" id="ingredient_status" class="form-select">
                            <option value="Available" <?php if ($ingredient_status == 'Available') echo 'selected'; ?>>Available</option>
                            <option value="Out of Stock" <?php if ($ingredient_status == 'Out of Stock') echo 'selected'; ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="mt-4 text-center">
                        <input type="hidden" name="ingredient_id" value="<?php echo htmlspecialchars($ingredient_id); ?>">
                        <button type="submit" name="action" value="restock" class="btn btn-success">Restock</button>
                        <button type="submit" name="action" value="deduct" class="btn btn-danger">Deduct</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
