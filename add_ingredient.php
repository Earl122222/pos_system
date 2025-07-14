<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

// Custom session check for AJAX
function checkSessionOrJson() {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => ['Session expired. Please log in again.']]);
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }
}

checkSessionOrJson();

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category")->fetchAll(PDO::FETCH_ASSOC);

$category_id = '';
$ingredient_name = '';
$ingredient_quantity = '';
$ingredient_unit = '';
$ingredient_status = 'Available';

$message = '';

// Helper to detect AJAX
function is_ajax() {
    return (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
    ) ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = [];

    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $ingredient_name = isset($_POST['ingredient_name']) ? trim($_POST['ingredient_name']) : '';
    $ingredient_quantity = isset($_POST['ingredient_quantity']) ? trim($_POST['ingredient_quantity']) : '';
    $ingredient_unit = isset($_POST['ingredient_unit']) ? trim($_POST['ingredient_unit']) : '';
    $date_added = isset($_POST['date_added']) ? $_POST['date_added'] : date('Y-m-d');
    $expiring_date = isset($_POST['expiring_date']) ? $_POST['expiring_date'] : null;
    $minimum_threshold = isset($_POST['minimum_threshold']) ? trim($_POST['minimum_threshold']) : 0;

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
    if (empty($date_added)) {
        $errors[] = 'Date Added is required.';
    }
    if ($minimum_threshold === '' || !is_numeric($minimum_threshold) || $minimum_threshold < 0) {
        $errors[] = 'Minimum Threshold is required and must be 0 or greater.';
    }

    // Check if the ingredient already exists in the same category
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE ingredient_name = :ingredient_name AND category_id = :category_id");
    $stmt->execute(['ingredient_name' => $ingredient_name, 'category_id' => $category_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $errors[] = 'Ingredient already exists in this category.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ingredients (category_id, ingredient_name, ingredient_quantity, ingredient_unit, date_added, expiring_date, minimum_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $ingredient_name, $ingredient_quantity, $ingredient_unit, $date_added, $expiring_date, $minimum_threshold]);
            if (is_ajax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            } else {
                header("Location: ingredients.php");
                exit;
            }
        } catch (PDOException $e) {
            if (is_ajax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
                exit;
            } else {
                $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
            }
        }
    } else {
        if (is_ajax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        } else {
            $message = '<ul class="list-unstyled">';
            foreach ($errors as $error) {
                $message .= '<li>' . $error . '</li>';
            }
            $message .= '</ul>';
        }
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
                        <select name="category_id" id="add_category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_name">Ingredient Name</label>
                        <input type="text" name="ingredient_name" id="ingredient_name" class="form-control" value="<?php echo htmlspecialchars($ingredient_name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_quantity">Quantity</label>
                        <input type="number" name="ingredient_quantity" id="ingredient_quantity" class="form-control" step="0.01" value="<?php echo htmlspecialchars($ingredient_quantity); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_unit">Unit</label>
                        <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control" value="<?php echo htmlspecialchars($ingredient_unit); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_added">Date Added</label>
                        <input type="date" name="date_added" id="date_added" class="form-control" value="<?php echo htmlspecialchars($date_added); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiring_date">Expiring Date</label>
                        <input type="date" name="expiring_date" id="expiring_date" class="form-control" value="<?php echo htmlspecialchars($expiring_date); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="minimum_threshold">Minimum Threshold</label>
                        <input type="number" name="minimum_threshold" id="minimum_threshold" class="form-control" step="1" value="<?php echo htmlspecialchars($minimum_threshold); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ingredient_status">Status</label>
                        <select name="ingredient_status" id="ingredient_status" class="form-select" required>
                            <option value="Available" <?php if ($ingredient_status == 'Available') echo 'selected'; ?>>Available</option>
                            <option value="Out of Stock" <?php if ($ingredient_status == 'Out of Stock') echo 'selected'; ?>>Out of Stock</option>
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
