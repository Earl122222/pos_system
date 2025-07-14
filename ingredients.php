<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category")->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<style>
/* Modern Card and Table Styling */
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
    background: #ffffff;
}

.card-header {
    background: var(--primary-color);
    color: var(--text-light);
    border-bottom: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.card-header i {
    color: var(--text-light);
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table thead th {
    background-color: var(--hover-color);
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--primary-color);
    padding: 1rem;
    white-space: nowrap;
}

.table tbody tr {
    background: white;
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.1);
    background: var(--hover-color);
}

.table tbody td {
    padding: 1rem;
    border: none;
    background: transparent;
}

.table tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

/* Search and Length Menu */
.dataTables_wrapper {
    padding: 1.5rem;
}

.dataTables_length select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238B4543' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    transition: all 0.2s ease;
}

.dataTables_length select:hover {
    border-color: var(--primary-color);
}

.dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    min-width: 300px;
    transition: all 0.2s ease;
}

.dataTables_filter input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* Pagination */
.dataTables_paginate {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.5rem;
}

.dataTables_paginate .paginate_button {
    min-width: 36px;
    height: 36px;
    padding: 0;
    margin: 0 2px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.35rem;
    border: 1px solid transparent;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-dark) !important;
    background-color: white;
    transition: all 0.2s ease;
}

.dataTables_paginate .paginate_button:hover {
    color: var(--primary-color) !important;
    background: var(--hover-color);
    border-color: var(--primary-color);
}

.dataTables_paginate .paginate_button.current {
    background: var(--primary-color);
    color: white !important;
    border-color: var(--primary-color);
    font-weight: 600;
}

.dataTables_paginate .paginate_button.disabled {
    color: var(--border-color) !important;
    border-color: var(--border-color);
    cursor: not-allowed;
}

/* Buttons */
.btn-success {
    background: var(--success-color);
    border: none;
    border-radius: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    color: white;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background: darken(var(--success-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(74, 124, 89, 0.15);
}

/* Status Badges */
.badge {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.35rem;
}

.badge.bg-success {
    background: var(--success-color) !important;
    color: white;
}

.badge.bg-danger {
    background: var(--danger-color) !important;
    color: white;
}

/* Action Buttons */
.btn-group .btn, .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.35rem;
    margin: 0 0.125rem;
    border: none;
    transition: all 0.2s ease;
}

.btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-warning:hover {
    background: darken(var(--warning-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.15);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: darken(var(--danger-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15);
}

/* Info Text */
.dataTables_info {
    color: var(--text-dark);
    font-size: 0.875rem;
    padding-top: 1.5rem;
}

/* Breadcrumb */
.breadcrumb {
    padding: 0.75rem 1rem;
    background: var(--hover-color);
    border-radius: 0.35rem;
    margin-bottom: 1.5rem;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: var(--text-dark);
}

/* Page Title */
h1 {
    color: var(--text-dark);
    font-weight: 400;
    margin-bottom: 1.5rem;
}
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">| Ingredient Management</h1>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-mortar-pestle me-1"></i>
                        Ingredient List
                    </div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                        <i class="fas fa-plus me-1"></i> Add Ingredient
                    </button>
                </div>
                <div class="card-body">
                    <table id="ingredientTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <!--<th>ID</th>-->
                                <th>Category</th>
                                <th>Ingredient Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Ingredient Modal -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: #8B4543; color: #fff;">
        <h5 class="modal-title" id="addIngredientModalLabel"><i class="fas fa-plus me-2"></i> Add Ingredient</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="addIngredientError" class="alert alert-danger d-none"></div>
        <form id="addIngredientForm">
          <div class="mb-3">
            <label for="add_category_id" class="form-label">Category</label>
            <select name="category_id" id="add_category_id" class="form-select" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['category_id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="add_ingredient_name" class="form-label">Ingredient Name</label>
            <input type="text" name="ingredient_name" id="add_ingredient_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_ingredient_quantity" class="form-label">Quantity</label>
            <input type="number" name="ingredient_quantity" id="add_ingredient_quantity" class="form-control" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="add_ingredient_unit" class="form-label">Unit</label>
            <input type="text" name="ingredient_unit" id="add_ingredient_unit" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_ingredient_status" class="form-label">Status</label>
            <select name="ingredient_status" id="add_ingredient_status" class="form-select" required>
              <option value="Available">Available</option>
              <option value="Out of Stock">Out of Stock</option>
            </select>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Ingredient</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Ingredient Modal -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" aria-labelledby="editIngredientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body" id="editIngredientModalBody">
        <!-- AJAX-loaded content here -->
      </div>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>

<!-- Add SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#ingredientTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ingredients_ajax.php",
            "type": "GET"
        },
        "columns": [
            //{ "data": "ingredient_id" },
            { "data": "category_name" },
            { "data": "ingredient_name" },
            { "data": "ingredient_quantity" },
            { "data": "ingredient_unit" },
            {
                // Status column with color badge
                "data": null,
                "render": function(data, type, row) {
                    // Assume minimum_threshold is available in row, otherwise set a default
                    const min = row.minimum_threshold !== undefined ? parseFloat(row.minimum_threshold) : 1;
                    const qty = parseFloat(row.ingredient_quantity);
                    let status = '';
                    let badgeClass = '';
                    if (qty === 0) {
                        status = 'Out of Stock';
                        badgeClass = 'bg-danger';
                    } else if (qty <= min) {
                        status = 'Low Stock';
                        badgeClass = 'bg-warning';
                    } else {
                        status = 'Adequate';
                        badgeClass = 'bg-success';
                    }
                    return `<span class="badge ${badgeClass}">${status}</span>`;
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                        <div class="text-center">
                            <a href="#" class="btn btn-warning btn-sm edit-ingredient-btn" data-id="${row.ingredient_id}">Edit</a>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${row.ingredient_id}">Delete</button>
                        </div>`;
                }
            }
        ]
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let ingredientId = $(this).data('id');
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            showCancelButton: true,
            confirmButtonColor: '#B33A3A',
            cancelButtonColor: '#8B4543',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {popup: 'rounded-4'}
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_ingredient.php',
                    type: 'POST',
                    data: { id: ingredientId },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Ingredient has been deleted successfully.',
                            confirmButtonColor: '#8B4543',
                            customClass: {popup: 'rounded-4'}
                        });
                        $('#ingredientTable').DataTable().ajax.reload();
                    }
                });
            }
        });
    });

    // Add Ingredient AJAX
    $('#addIngredientForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var modal = $('#addIngredientModal');
        var errorBox = $('#addIngredientError');
        errorBox.addClass('d-none').html('');
        var formData = form.serialize();
        $.ajax({
            url: 'add_ingredient.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    modal.modal('hide');
                    form[0].reset();
                    $('#ingredientTable').DataTable().ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Ingredient added successfully!',
                        confirmButtonColor: '#8B4543',
                        customClass: {popup: 'rounded-4'}
                    });
                } else if (response.errors) {
                    errorBox.removeClass('d-none').html(response.errors.join('<br>'));
                } else {
                    errorBox.removeClass('d-none').html('An error occurred.');
                }
            },
            error: function(xhr) {
                errorBox.removeClass('d-none').html('An error occurred.');
            }
        });
    });
});

$(document).on('click', '.edit-ingredient-btn', function(e) {
    e.preventDefault();
    var ingredientId = $(this).data('id');
    $('#editIngredientModalBody').html('<div class="text-center p-4">Loading...</div>');
    $('#editIngredientModal').modal('show');
    $.get('edit_ingredient.php', { id: ingredientId, modal: 1 }, function(data) {
        $('#editIngredientModalBody').html(data);
    });
});

$(document).on('submit', '#editIngredientModalBody form', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = form.serialize();
    $.post(form.attr('action'), formData, function(response) {
        Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Ingredient has been updated successfully.',
            confirmButtonColor: '#8B4543',
            customClass: {popup: 'rounded-4'}
        }).then(() => {
            $('#editIngredientModal').modal('hide');
            $('#ingredientTable').DataTable().ajax.reload();
        });
    });
});
// Intercept Cancel button
$(document).on('click', '#editIngredientModalBody .btn-cancel', function(e) {
    e.preventDefault();
    Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonColor: '#B33A3A',
        cancelButtonColor: '#8B4543',
        confirmButtonText: 'Yes, cancel!',
        cancelButtonText: 'No, keep editing',
        customClass: {popup: 'rounded-4'}
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            // Re-open the edit modal or form here
            $('#editIngredientModal').modal('show');
        }
    });
});
</script>
