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

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">
        | Stock Management Dashboard
    </h1>

    <!-- Overview Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Total Items</h5>
                            <h2 class="mb-0" id="totalItems">0</h2>
                        </div>
                        <i class="fa-solid fa-boxes fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Low Stock Items</h5>
                            <h2 class="mb-0" id="lowStockItems">0</h2>
                        </div>
                        <i class="fa-solid fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Stock Movements</h5>
                            <h2 class="mb-0" id="stockMovements">0</h2>
                        </div>
                        <i class="fa-solid fa-exchange-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Expiring Items</h5>
                            <h2 class="mb-0" id="expiringItems">0</h2>
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
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        <i class="fa-solid fa-plus"></i> Add Stock
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

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#stockTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']]
    });

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
            $('#totalItems').text(response.total_items);
            $('#lowStockItems').text(response.low_stock_items);
            $('#stockMovements').text(response.stock_movements);
            $('#expiringItems').text(response.expiring_items);

            // Update stock status chart
            stockStatusChart.data.datasets[0].data = [
                response.adequate_stock,
                response.low_stock,
                response.out_of_stock
            ];
            stockStatusChart.update();
        });

        // Update stock table
        $.get('get_stock_items.php', function(response) {
            const tbody = $('#stockTable tbody');
            tbody.empty();

            response.items.forEach(item => {
                const statusClass = item.current_stock <= item.minimum_stock ? 'text-danger' : 'text-success';
                tbody.append(`
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.current_stock}</td>
                        <td>${item.minimum_stock}</td>
                        <td><span class="${statusClass}">${item.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="adjustStock(${item.id})">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
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

    // Initial load
    updateDashboard();

    // Refresh data every 5 minutes
    setInterval(updateDashboard, 300000);
});
</script>

<?php include('footer.php'); ?> 