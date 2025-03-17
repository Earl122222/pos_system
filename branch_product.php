<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Get user's branch if they are a cashier
$user_branch_id = null;
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Cashier') {
    $stmt = $pdo->prepare("SELECT branch_id FROM pos_cashier_details WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_branch_id = $stmt->fetchColumn();
}

// Get active branches
$stmt = $pdo->prepare("
    SELECT branch_id, branch_name 
    FROM pos_branch 
    WHERE status = 'Active'
    " . ($user_branch_id ? " AND branch_id = ?" : "") . "
    ORDER BY branch_name
");

if ($user_branch_id) {
    $stmt->execute([$user_branch_id]);
} else {
    $stmt->execute();
}
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active products
$stmt = $pdo->query("
    SELECT product_id, product_name 
    FROM pos_product 
    WHERE product_status = 'Available'
    ORDER BY product_name
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<style>
/* Inherit existing styles from category.php */
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --primary-light: #A65D5D;
    --accent-color: #D4A59A;
    --text-light: #F3E9E7;
    --text-dark: #3C2A2A;
    --border-color: #C4B1B1;
    --hover-color: #F5EDED;
    --danger-color: #B33A3A;
    --success-color: #4A7C59;
    --warning-color: #C4804D;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
    border: none;
    border-radius: 0.75rem;
}

.card-header {
    background: var(--primary-color);
    color: var(--text-light);
    border-bottom: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
}
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4">Branch Products Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Branch Products</li>
    </ol>

    <!-- Add Branch Product Form -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Add Product to Branch
        </div>
        <div class="card-body">
            <form id="addBranchProductForm">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option value="">Select Branch</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= htmlspecialchars($branch['branch_id']) ?>">
                                        <?= htmlspecialchars($branch['branch_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= htmlspecialchars($product['product_id']) ?>">
                                        <?= htmlspecialchars($product['product_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Initial Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Product to Branch</button>
            </form>
        </div>
    </div>

    <!-- Branch Products Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Branch Products
        </div>
        <div class="card-body">
            <table id="branchProductsTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Branch Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBranchProductForm">
                    <input type="hidden" id="edit_branch_product_id" name="branch_product_id">
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <input type="text" class="form-control" id="edit_branch_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="edit_product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEdit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#branchProductsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: 'branch_product_ajax.php',
        columns: [
            { data: 'branch_name' },
            { data: 'product_name' },
            { 
                data: 'product_price',
                render: function(data) {
                    return '₱' + parseFloat(data).toFixed(2);
                }
            },
            { data: 'quantity' },
            { data: 'product_status' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button type="button" class="btn btn-primary btn-sm edit-btn" data-id="${row.branch_product_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="${row.branch_product_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // Add Branch Product
    $('#addBranchProductForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'add_branch_product.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    $('#addBranchProductForm')[0].reset();
                    table.ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            }
        });
    });

    // Edit Branch Product
    $('#branchProductsTable').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        $.ajax({
            url: 'get_branch_product.php',
            method: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    $('#edit_branch_product_id').val(response.data.branch_product_id);
                    $('#edit_branch_name').val(response.data.branch_name);
                    $('#edit_product_name').val(response.data.product_name);
                    $('#edit_quantity').val(response.data.quantity);
                    $('#editModal').modal('show');
                }
            }
        });
    });

    // Save Edit
    $('#saveEdit').on('click', function() {
        $.ajax({
            url: 'update_branch_product.php',
            method: 'POST',
            data: $('#editBranchProductForm').serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    $('#editModal').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            }
        });
    });

    // Delete Branch Product
    $('#branchProductsTable').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_branch_product.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                            table.ajax.reload();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    }
                });
            }
        });
    });
});
</script>

<?php include('footer.php'); ?> 