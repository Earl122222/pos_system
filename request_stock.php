<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo "Access denied. Only Stockman can access this page.";
    exit();
}

include('header.php');

// Fetch all active ingredients with category and quantity
$stmt = $pdo->query("SELECT i.ingredient_id, i.ingredient_name, i.ingredient_unit, i.ingredient_quantity, c.category_name FROM ingredients i LEFT JOIN pos_category c ON i.category_id = c.category_id WHERE i.ingredient_status = 'Active'");
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-4">
    <h2 class="mb-4" style="color: #8B4543;">Request Stock</h2>
    <form action="process_ingredient_request.php" method="POST" id="requestStockForm">
        <div class="mb-3">
            <label for="ingredients" class="form-label">Select Ingredients</label>
            <div id="ingredient-list">
                <?php foreach ($ingredients as $ingredient): ?>
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-6">
                            <input type="checkbox" name="ingredients[]" value="<?php echo $ingredient['ingredient_id']; ?>" id="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                            <label for="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                                <strong><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></strong>
                                <span class="text-muted">(<?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>)</span><br>
                                <small>Category: <?php echo htmlspecialchars($ingredient['category_name']); ?> | Current Stock: <?php echo htmlspecialchars($ingredient['ingredient_quantity']); ?></small>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="number" class="form-control" name="quantity[<?php echo $ingredient['ingredient_id']; ?>]" min="1" placeholder="Quantity" disabled>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>
<script>
// Enable quantity input only if ingredient is checked
$(document).ready(function() {
    $('#ingredient-list input[type="checkbox"]').change(function() {
        const qtyInput = $(this).closest('.row').find('input[type="number"]');
        qtyInput.prop('disabled', !this.checked);
        if (!this.checked) qtyInput.val('');
    });
});
</script>
<?php include('footer.php'); ?> 