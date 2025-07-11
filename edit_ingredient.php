<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category")->fetchAll(PDO::FETCH_ASSOC);

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

$modal = isset($_GET['modal']) && $_GET['modal'] == 1;

if ($modal) {
    ?>
    <style>
    .ingredient-modal-header {
        background: #8B4543;
        color: #fff;
        border-top-left-radius: 1.25rem;
        border-top-right-radius: 1.25rem;
        padding: 1.5rem 2rem 1.2rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.35rem;
        font-weight: 700;
        border-bottom: 2px solid #e5d3d3;
        box-shadow: 0 2px 12px 0 rgba(139, 69, 67, 0.10);
        position: sticky;
        top: 0;
        z-index: 2;
        margin: 0;
    }
    .ingredient-modal-header .ingredient-header-icon {
        background: #D4A59A;
        color: #8B4543;
        border-radius: 50%;
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 0.5rem;
        box-shadow: 0 1px 4px rgba(139, 69, 67, 0.08);
    }
    .ingredient-modal-close {
        position: absolute;
        right: 2rem;
        top: 1.5rem;
        color: #fff;
        font-size: 2rem;
        background: none;
        border: none;
        cursor: pointer;
        z-index: 10;
        transition: color 0.2s, background 0.2s;
        border-radius: 50%;
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ingredient-modal-close:hover {
        background: #fff2;
        color: #D4A59A;
    }
    .ingredient-modal-body {
        background: #fff;
        border-bottom-left-radius: 1.25rem;
        border-bottom-right-radius: 1.25rem;
        padding: 2rem 2rem 1.5rem 2rem;
        margin: 0;
    }
    /* Remove default modal-content padding/margin if present */
    .modal-content {
        border-radius: 1.25rem !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: 0 4px 24px 0 rgba(139, 69, 67, 0.10);
    }
    /* Remove default modal-body padding if present */
    #editIngredientModalBody {
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }
    .ingredient-form-group {
        margin-bottom: 1.25rem;
    }
    .ingredient-form-label {
        font-weight: 600;
        color: #8B4543;
        margin-bottom: 0.5rem;
        display: block;
        letter-spacing: 0.5px;
    }
    .ingredient-form-input, .ingredient-form-select {
        width: 100%;
        border-radius: 0.5rem;
        border: 1.5px solid #C4B1B1;
        font-size: 1.08rem;
        padding: 0.7rem 1rem;
        color: #3C2A2A;
        background: #f8f9fa;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-shadow: 0 1px 2px rgba(139, 69, 67, 0.04);
    }
    .ingredient-form-input:focus, .ingredient-form-select:focus {
        border-color: #8B4543;
        outline: none;
        box-shadow: 0 0 0 2px #d4a59a33;
    }
    .ingredient-form-input[readonly] {
        background: #f5eded;
        color: #8B4543;
        font-weight: 600;
    }
    .ingredient-modal-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1.25rem;
        margin-top: 2rem;
    }
    .ingredient-modal-actions .btn-primary {
        background: #4A7C59;
        border: none;
        color: #fff;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.7rem 2rem;
        font-size: 1.08rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ingredient-modal-actions .btn-primary:hover {
        background: #3a6147;
    }
    .ingredient-modal-actions .btn-cancel {
        background: #f5eded;
        border: 1.5px solid #8B4543;
        color: #8B4543;
        font-weight: 600;
        font-size: 1.08rem;
        text-decoration: none;
        padding: 0.7rem 1.5rem;
        border-radius: 0.5rem;
        transition: background 0.15s, color 0.15s, border 0.15s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ingredient-modal-actions .btn-cancel:hover {
        background: #e5d3d3;
        color: #723937;
        border-color: #723937;
        text-decoration: underline;
    }
    </style>
    <div class="ingredient-modal-header">
        <span class="ingredient-header-icon"><i class="fas fa-carrot"></i></span>
        Edit Ingredient
        <button type="button" class="ingredient-modal-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
    </div>
    <div class="ingredient-modal-body">
    <?php if ($message !== '') {
        echo '<div class="alert alert-danger">' . $message . '</div>';
    } ?>
    <form method="POST" action="edit_ingredient.php?id=<?php echo htmlspecialchars($ingredient_id); ?>&modal=1">
        <div class="ingredient-form-group">
            <label for="category_id" class="ingredient-form-label">Category</label>
            <select name="category_id" id="category_id" class="ingredient-form-select">
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php if ($category_id == $category['category_id']) echo 'selected'; ?>><?php echo $category['category_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ingredient-form-group">
            <label for="ingredient_name" class="ingredient-form-label">Ingredient Name</label>
            <input type="text" name="ingredient_name" id="ingredient_name" class="ingredient-form-input" value="<?php echo htmlspecialchars($ingredient_name); ?>">
        </div>
        <div class="ingredient-form-group">
            <label class="ingredient-form-label">Current Quantity</label>
            <input type="text" class="ingredient-form-input" value="<?php echo htmlspecialchars($ingredient_quantity); ?>" readonly>
        </div>
        <div class="ingredient-form-group">
            <label for="change_quantity" class="ingredient-form-label">Change Quantity</label>
            <input type="number" name="change_quantity" id="change_quantity" class="ingredient-form-input" step="0.01">
        </div>
        <div class="ingredient-form-group">
            <label for="ingredient_unit" class="ingredient-form-label">Unit</label>
            <input type="text" name="ingredient_unit" id="ingredient_unit" class="ingredient-form-input" value="<?php echo htmlspecialchars($ingredient_unit); ?>">
        </div>
        <div class="ingredient-form-group">
            <label for="ingredient_status" class="ingredient-form-label">Status</label>
            <select name="ingredient_status" id="ingredient_status" class="ingredient-form-select">
                <option value="Available" <?php if ($ingredient_status == 'Available') echo 'selected'; ?>>Available</option>
                <option value="Out of Stock" <?php if ($ingredient_status == 'Out of Stock') echo 'selected'; ?>>Out of Stock</option>
            </select>
        </div>
        <div class="ingredient-modal-actions">
            <button type="button" class="btn-cancel" data-bs-dismiss="modal">&#10005; Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </div>
        <input type="hidden" name="ingredient_id" value="<?php echo htmlspecialchars($ingredient_id); ?>">
    </form>
    </div>
    <?php
    return;
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

<div class="modal-dialog" style="max-width: 500px; margin: 2rem auto;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Ingredient Details</h5>
            <a href="ingredients.php" class="btn-close"></a>
        </div>
        <div class="modal-body">
            <form method="POST" action="edit_ingredient.php?id=<?php echo htmlspecialchars($ingredient_id); ?>">
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Category:</div>
                    <div class="col-7">
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php if ($category_id == $category['category_id']) echo 'selected'; ?>><?php echo $category['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Ingredient Name:</div>
                    <div class="col-7">
                        <input type="text" name="ingredient_name" id="ingredient_name" class="form-control" value="<?php echo htmlspecialchars($ingredient_name); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Current Quantity:</div>
                    <div class="col-7">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($ingredient_quantity); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Change Quantity:</div>
                    <div class="col-7">
                        <input type="number" name="change_quantity" id="change_quantity" class="form-control" step="0.01">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Unit:</div>
                    <div class="col-7">
                        <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control" value="<?php echo htmlspecialchars($ingredient_unit); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Status:</div>
                    <div class="col-7">
                        <select name="ingredient_status" id="ingredient_status" class="form-select">
                            <option value="Available" <?php if ($ingredient_status == 'Available') echo 'selected'; ?>>Available</option>
                            <option value="Out of Stock" <?php if ($ingredient_status == 'Out of Stock') echo 'selected'; ?>>Out of Stock</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="ingredient_id" value="<?php echo htmlspecialchars($ingredient_id); ?>">
                    <button type="submit" name="action" value="restock" class="btn btn-success">Restock</button>
                    <button type="submit" name="action" value="deduct" class="btn btn-danger">Deduct</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="ingredients.php" class="btn btn-secondary">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
