<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

include('header.php');
?>

<style>
.status-adequate {
    background-color: #28a745 !important;
    color: #fff !important;
    font-weight: 500;
    padding: 0.25em 0.75em;
    border-radius: 0.5em;
    display: inline-block;
}
.status-low {
    background-color: #ffc107 !important;
    color: #212529 !important;
    font-weight: 500;
    padding: 0.25em 0.75em;
    border-radius: 0.5em;
    display: inline-block;
}
.status-out {
    background-color: #dc3545 !important;
    color: #fff !important;
    font-weight: 500;
    padding: 0.25em 0.75em;
    border-radius: 0.5em;
    display: inline-block;
}
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">
        | Stock Management Dashboard
    </h1>

    <!-- Overview Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4 dashboard-card" id="cardAllIngredients" style="cursor:pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">All Ingredients</h5>
                        </div>
                        <i class="fa-solid fa-carrot fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4 dashboard-card" id="cardLowStock" style="cursor:pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Low Stock Items</h5>
                        </div>
                        <i class="fa-solid fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4 dashboard-card" id="cardStockMovements" style="cursor:pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Stock Movements</h5>
                        </div>
                        <i class="fa-solid fa-exchange-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4 dashboard-card" id="cardExpiringItems" style="cursor:pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Expiring Items</h5>
                        </div>
                        <i class="fa-solid fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Management Section -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa-solid fa-boxes me-1"></i>
                        Stock Inventory
                    </div>
                    <button class="btn btn-primary btn-sm" id="addIngredientBtn" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                        <i class="fa-solid fa-plus"></i> Add Ingredient
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="stockTable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Stock items will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa-solid fa-chart-pie me-1"></i>
                    Stock Status
                </div>
                <div class="card-body">
                    <canvas id="stockStatusChart" width="100%" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stock Movements -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa-solid fa-history me-1"></i>
                    Recent Stock Movements
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="movementsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Previous Stock</th>
                                    <th>New Stock</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Stock movements will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addStockForm">
                    <div class="mb-3">
                        <label for="itemName" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="itemName" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="minimumStock" class="form-label">Minimum Stock Level</label>
                        <input type="number" class="form-control" id="minimumStock" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveStockBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- All Ingredients Modal -->
<div class="modal fade" id="allIngredientsModal" tabindex="-1" aria-labelledby="allIngredientsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: #8B4543; color: #fff;">
        <h5 class="modal-title" id="allIngredientsModalLabel"><i class="fa-solid fa-carrot me-2"></i> All Ingredients</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="allIngredientsTable">
            <thead>
              <tr>
                <th>Ingredient Name</th>
                <th>Quantity Remaining</th>
                <th>Unit</th>
                <th>Date Added</th>
                <th>Expiring Date</th>
              </tr>
            </thead>
            <tbody>
              <!-- Data will be loaded here via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Ingredient Modal (copied from ingredients.php, ensure categories are loaded) -->
<?php
// Fetch categories for the dropdown (if not already fetched)
if (!isset($categories)) {
    $categories = $pdo->query("SELECT category_id, category_name FROM pos_category")->fetchAll(PDO::FETCH_ASSOC);
}
?>
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
            <label for="add_date_added" class="form-label">Date Added</label>
            <input type="date" name="date_added" id="add_date_added" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="mb-3">
            <label for="add_expiring_date" class="form-label">Expiring Date</label>
            <input type="date" name="expiring_date" id="add_expiring_date" class="form-control">
          </div>
          <div class="mb-3">
            <label for="add_minimum_threshold" class="form-label">Minimum Threshold</label>
            <input type="number" name="minimum_threshold" id="add_minimum_threshold" class="form-control" min="0" required>
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
      <div class="modal-header" style="background: #8B4543; color: #fff;">
        <h5 class="modal-title" id="editIngredientModalLabel"><i class="fas fa-edit me-2"></i> Edit Ingredient</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="editIngredientError" class="alert alert-danger d-none"></div>
        <form id="editIngredientForm">
          <input type="hidden" name="ingredient_id" id="edit_ingredient_id">
          <div class="mb-3">
            <label for="edit_category_id" class="form-label">Category</label>
            <select name="category_id" id="edit_category_id" class="form-select" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['category_id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_ingredient_name" class="form-label">Ingredient Name</label>
            <input type="text" name="ingredient_name" id="edit_ingredient_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_ingredient_quantity" class="form-label">Quantity</label>
            <input type="number" name="ingredient_quantity" id="edit_ingredient_quantity" class="form-control" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="edit_ingredient_unit" class="form-label">Unit</label>
            <input type="text" name="ingredient_unit" id="edit_ingredient_unit" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_date_added" class="form-label">Date Added</label>
            <input type="date" name="date_added" id="edit_date_added" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_expiring_date" class="form-label">Expiring Date</label>
            <input type="date" name="expiring_date" id="edit_expiring_date" class="form-control">
          </div>
          <div class="mb-3">
            <label for="edit_minimum_threshold" class="form-label">Minimum Threshold</label>
            <input type="number" name="minimum_threshold" id="edit_minimum_threshold" class="form-control" min="0" required>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Expiring Ingredients Modal -->
<div class="modal fade" id="expiringIngredientsModal" tabindex="-1" aria-labelledby="expiringIngredientsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: #8B4543; color: #fff;">
        <h5 class="modal-title" id="expiringIngredientsModalLabel"><i class="fa-solid fa-clock me-2"></i> Expiring Ingredients</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="expiringIngredientsTable">
            <thead>
              <tr>
                <th>Ingredient Name</th>
                <th>Quantity Remaining</th>
                <th>Unit</th>
                <th>Date Added</th>
                <th>Expiring Date</th>
              </tr>
            </thead>
            <tbody>
              <!-- Data will be loaded here via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Low Stock Ingredients Modal -->
<div class="modal fade" id="lowStockIngredientsModal" tabindex="-1" aria-labelledby="lowStockIngredientsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: #ffc107; color: #fff;">
        <h5 class="modal-title" id="lowStockIngredientsModalLabel"><i class="fa-solid fa-exclamation-triangle me-2"></i> Low Stock Ingredients</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="lowStockIngredientsTable">
            <thead>
              <tr>
                <th>Ingredient Name</th>
                <th>Quantity Remaining</th>
                <th>Unit</th>
                <th>Minimum Threshold</th>
                <th>Date Added</th>
                <th>Expiring Date</th>
              </tr>
            </thead>
            <tbody>
              <!-- Data will be loaded here via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.dashboard-card { transition: box-shadow 0.2s, transform 0.2s; }
.dashboard-card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.18); transform: translateY(-2px) scale(1.03); z-index:2; }
#expiringIngredientsTable_paginate {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.5rem;
}
#expiringIngredientsTable_paginate .pagination {
    font-size: 0.85rem;
    gap: 0.25rem;
}
#expiringIngredientsTable_paginate .paginate_button {
    padding: 0.15rem 0.6rem;
    border-radius: 0.25rem;
}
#allIngredientsTable_paginate {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.5rem;
}
#allIngredientsTable_paginate .pagination {
    font-size: 0.85rem;
    gap: 0.25rem;
}
#allIngredientsTable_paginate .paginate_button {
    padding: 0.15rem 0.6rem;
    border-radius: 0.25rem;
}
#lowStockIngredientsTable_paginate {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.5rem;
}
#lowStockIngredientsTable_paginate .pagination {
    font-size: 0.85rem;
    gap: 0.25rem;
}
#lowStockIngredientsTable_paginate .paginate_button {
    padding: 0.15rem 0.6rem;
    border-radius: 0.25rem;
}
</style>
<script>
$(document).ready(function() {
    // Initialize DataTables for stockTable with AJAX
    $('#stockTable').DataTable({
        ajax: {
            url: 'get_ingredients_inventory.php',
            dataSrc: ''
        },
        columns: [
            { data: 'ingredient_name' },
            { data: 'ingredient_quantity' },
            { data: 'minimum_threshold' },
            {
                data: null,
                render: function(data, type, row) {
                    let status = '';
                    let statusClass = '';
                    const qty = parseFloat(row.ingredient_quantity);
                    const min = parseFloat(row.minimum_threshold);
                    if (qty === 0) {
                        status = 'Out of Stock';
                        statusClass = 'status-out';
                    } else if (qty <= min) {
                        status = 'Low Stock';
                        statusClass = 'status-low';
                    } else {
                        status = 'Adequate';
                        statusClass = 'status-adequate';
                    }
                    return `<span class="${statusClass}">${status}</span>`;
                }
            },
            {
                data: 'ingredient_id',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary edit-ingredient-btn" data-id="${data}"><i class="fa-solid fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger ms-1 delete-ingredient-btn" data-id="${data}"><i class="fa-solid fa-trash"></i></button>
                    `;
                },
                orderable: false
            }
        ],
        pageLength: 10,
        order: [[0, 'asc']]
    });

    // Add event delegation for edit and delete buttons:
    $('#stockTable').on('click', '.edit-ingredient-btn', function() {
        var id = $(this).data('id');
        editIngredient(id);
    });
    $('#stockTable').on('click', '.delete-ingredient-btn', function() {
        var id = $(this).data('id');
        deleteIngredient(id);
    });

    // Remove manual stock table update code (no $.get for stockTable)

    $('#movementsTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']]
    });

    // Initialize stock status chart
    const ctx = document.getElementById('stockStatusChart').getContext('2d');
    const stockStatusChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Adequate', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Function to update dashboard data
    function updateDashboard() {
        // Update overview cards
        $.get('get_stockman_stats.php', function(response) {
            // The numbers are now handled by the AJAX call itself, so we just update the chart
            // and the all ingredients count.
            // For the other cards, we just update the chart.

            // Update stock status chart
            stockStatusChart.data.datasets[0].data = [
                response.adequate_stock,
                response.low_stock,
                response.out_of_stock
            ];
            stockStatusChart.update();

            // Update all ingredients count
            $('#totalIngredients').text(response.total_ingredients);
        });

        // Update movements table
        $.get('get_stock_movements.php', function(response) {
            const tbody = $('#movementsTable tbody');
            tbody.empty();

            response.movements.forEach(movement => {
                const typeClass = movement.type === 'IN' ? 'text-success' : 'text-danger';
                tbody.append(`
                    <tr>
                        <td>${movement.date}</td>
                        <td>${movement.item_name}</td>
                        <td><span class="${typeClass}">${movement.type}</span></td>
                        <td>${movement.quantity}</td>
                        <td>${movement.previous_stock}</td>
                        <td>${movement.new_stock}</td>
                        <td>${movement.reference}</td>
                    </tr>
                `);
            });
        });
    }

    // Save stock button click handler
    $('#saveStockBtn').click(function() {
        const formData = {
            item_name: $('#itemName').val(),
            quantity: $('#quantity').val(),
            minimum_stock: $('#minimumStock').val()
        };

        $.post('add_stock_item.php', formData, function(response) {
            if (response.success) {
                $('#addStockModal').modal('hide');
                $('#addStockForm')[0].reset();
                updateDashboard();
                alert('Stock item added successfully!');
            } else {
                alert('Error: ' + response.message);
            }
        });
    });

    // Add Ingredient AJAX (same as in ingredients.php, but now with new fields)
    var reloadStockTableAfterModal = false;
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
                    reloadStockTableAfterModal = true;
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

    // Only reload the DataTable after the modal is fully hidden and after SweetAlert
    $('#addIngredientModal').on('hidden.bs.modal', function () {
        if (reloadStockTableAfterModal) {
            reloadStockTableAfterModal = false;
            setTimeout(function() {
                $('#stockTable').DataTable().ajax.reload();
                // Remove any lingering modal-backdrop and modal-open class
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
            }, 200); // slight delay to ensure modal is gone
        }
    });

    // Initial load
    updateDashboard();

    // Refresh data every 5 minutes
    setInterval(updateDashboard, 300000);

    // Function to update recent stock movements table
    function updateStockMovements() {
        $.ajax({
            url: 'get_stock_movements.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const tbody = $('#movementsTable tbody');
                tbody.empty();
                if (response.success && response.movements.length > 0) {
                    response.movements.forEach(movement => {
                        const type = movement.movement_type || movement.type;
                        const typeClass = type === 'IN' ? 'text-success' : (type === 'OUT' ? 'text-danger' : 'text-warning');
                        tbody.append(`
                            <tr>
                                <td>${movement.created_at || movement.date}</td>
                                <td>${movement.item_name}</td>
                                <td><span class="badge ${typeClass}">${type}</span></td>
                                <td>${movement.quantity}</td>
                                <td>${movement.previous_stock}</td>
                                <td>${movement.new_stock}</td>
                                <td>${movement.reference_type ? movement.reference_type + ' #' + movement.reference_id : (movement.reference || '')}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="7" class="text-center text-muted">No recent stock movements found.</td></tr>');
                }
            },
            error: function() {
                const tbody = $('#movementsTable tbody');
                tbody.empty();
                tbody.append('<tr><td colspan="7" class="text-center text-danger">Failed to load stock movements.</td></tr>');
            }
        });
    }

    // Call on page load and every 5 minutes
    updateStockMovements();
    setInterval(updateStockMovements, 300000);

    // Card click handlers
    $('#cardAllIngredients').on('click', function() {
        $('#allIngredientsModal').modal('show');
        loadAllIngredients();
    });
    $('#cardLowStock').on('click', function() {
        $('#lowStockIngredientsModal').modal('show');
        if (!$.fn.DataTable.isDataTable('#lowStockIngredientsTable')) {
            $('#lowStockIngredientsTable').DataTable({
                ajax: {
                    url: 'get_low_stock_ingredients.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'ingredient_name' },
                    { data: 'ingredient_quantity' },
                    { data: 'ingredient_unit' },
                    { data: 'minimum_threshold' },
                    { data: 'date_added' },
                    { data: 'expiring_date' }
                ],
                pageLength: 5,
                lengthChange: false,
                searching: false,
                ordering: false,
                info: false
            });
        } else {
            $('#lowStockIngredientsTable').DataTable().ajax.reload();
        }
    });
    $('#cardStockMovements').on('click', function() {
        $('html, body').animate({
            scrollTop: $("#movementsTable").closest('.card').offset().top - 80
        }, 500);
    });
    $('#cardExpiringItems').on('click', function() {
        $('#expiringIngredientsModal').modal('show');
        loadExpiringIngredients();
    });

    // All Ingredients card click handler
    function loadAllIngredients() {
        var tableBody = $('#allIngredientsTable tbody');
        tableBody.html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $.ajax({
            url: 'get_ingredients_list.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (Array.isArray(data) && data.length > 0) {
                    var rows = data.map(function(row) {
                        return `<tr>
                            <td>${row.ingredient_name}</td>
                            <td>${row.ingredient_quantity}</td>
                            <td>${row.ingredient_unit}</td>
                            <td>${row.date_added || ''}</td>
                            <td>${row.expiring_date || ''}</td>
                        </tr>`;
                    }).join('');
                    tableBody.html(rows);
                } else {
                    tableBody.html('<tr><td colspan="5" class="text-center">No ingredients found.</td></tr>');
                }
            },
            error: function() {
                tableBody.html('<tr><td colspan="5" class="text-center text-danger">Failed to load data.</td></tr>');
            }
        });
    }

    function loadExpiringIngredients() {
        var tableBody = $('#expiringIngredientsTable tbody');
        tableBody.html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $.ajax({
            url: 'get_expiring_ingredients.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (Array.isArray(data) && data.length > 0) {
                    var rows = data.map(function(row) {
                        return `<tr>
                            <td>${row.ingredient_name}</td>
                            <td>${row.ingredient_quantity}</td>
                            <td>${row.ingredient_unit}</td>
                            <td>${row.date_added || ''}</td>
                            <td>${row.expiring_date || ''}</td>
                        </tr>`;
                    }).join('');
                    tableBody.html(rows);
                } else {
                    tableBody.html('<tr><td colspan="5" class="text-center">No expiring ingredients found.</td></tr>');
                }
            },
            error: function() {
                tableBody.html('<tr><td colspan="5" class="text-center text-danger">Failed to load data.</td></tr>');
            }
        });
    }

    // In the Expiring Ingredients modal JS, replace loadExpiringIngredients with DataTables initialization:
    $('#expiringIngredientsModal').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#expiringIngredientsTable')) {
            $('#expiringIngredientsTable').DataTable({
                ajax: {
                    url: 'get_expiring_ingredients.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'ingredient_name' },
                    { data: 'ingredient_quantity' },
                    { data: 'ingredient_unit' },
                    { data: 'date_added' },
                    { data: 'expiring_date' }
                ],
                pageLength: 5,
                lengthChange: false,
                searching: false,
                ordering: false,
                info: false
            });
        } else {
            $('#expiringIngredientsTable').DataTable().ajax.reload();
        }
    });

    // In the All Ingredients modal JS, replace loadAllIngredients with DataTables initialization:
    $('#allIngredientsModal').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#allIngredientsTable')) {
            $('#allIngredientsTable').DataTable({
                ajax: {
                    url: 'get_ingredients_list.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'ingredient_name' },
                    { data: 'ingredient_quantity' },
                    { data: 'ingredient_unit' },
                    { data: 'date_added' },
                    { data: 'expiring_date' }
                ],
                pageLength: 5,
                lengthChange: false,
                searching: false,
                ordering: false,
                info: false
            });
        } else {
            $('#allIngredientsTable').DataTable().ajax.reload();
        }
    });

    window.editIngredient = function(id) {
        // Fetch ingredient data
        $.get('get_ingredient.php', { id: id }, function(data) {
            if (data && data.ingredient_id) {
                $('#edit_ingredient_id').val(data.ingredient_id);
                $('#edit_category_id').val(data.category_id);
                $('#edit_ingredient_name').val(data.ingredient_name);
                $('#edit_ingredient_quantity').val(data.ingredient_quantity);
                $('#edit_ingredient_unit').val(data.ingredient_unit);
                $('#edit_date_added').val(data.date_added);
                $('#edit_expiring_date').val(data.expiring_date);
                $('#edit_minimum_threshold').val(data.minimum_threshold);
                $('#editIngredientError').addClass('d-none').html('');
                $('#editIngredientModal').modal('show');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load ingredient data.' });
            }
        }, 'json');
    };

    $('#editIngredientForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var errorBox = $('#editIngredientError');
        errorBox.addClass('d-none').html('');
        var formData = form.serialize();
        $.ajax({
            url: 'edit_ingredient.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editIngredientModal').modal('hide');
                    form[0].reset();
                    $('#stockTable').DataTable().ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Ingredient updated successfully!',
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
$(document).on('shown.bs.modal hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('body').css('padding-right', '');
});
</script>

<?php include('footer.php'); ?> 