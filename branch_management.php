<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Get branch list
$stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active'");
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">| Branch Management Dashboard</h1>

    <!-- Branch Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <label for="branchSelect" class="me-3 mb-0">Select Branch:</label>
                        <select id="branchSelect" class="form-select" style="max-width: 300px;">
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo $branch['branch_id']; ?>">
                                    <?php echo $branch['branch_name'] . ' (' . $branch['branch_code'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Today's Sales</h6>
                            <h3 class="mb-0" id="todaySales">₱0.00</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-cash-register text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Monthly Sales</h6>
                            <h3 class="mb-0" id="monthlySales">₱0.00</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-chart-line text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Low Stock Items</h6>
                            <h3 class="mb-0" id="lowStockCount">0</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Expiring Soon</h6>
                            <h3 class="mb-0" id="expiringCount">0</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-clock text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    Sales Trend
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Top Products
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Alerts -->
    <div class="row">
        <!-- Low Stock Items -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Low Stock Alerts
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="lowStockTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Required</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Items -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-clock me-1"></i>
                    Expiring Items
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="expiringTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Card Styling */
.card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

.card-header {
    background: #8B4543;
    color: #ffffff;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem 0.75rem 0 0;
    font-weight: 500;
}

.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-circle i {
    font-size: 1.2rem;
}

/* Table Styling */
.table {
    margin-bottom: 0;
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Status Badges */
.badge {
    padding: 0.5rem 1rem;
    border-radius: 0.35rem;
    font-weight: 600;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #000;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#lowStockTable').DataTable({
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
    });

    $('#expiringTable').DataTable({
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
    });

    // Handle branch selection change
    $('#branchSelect').change(function() {
        const branchId = $(this).val();
        updateDashboard(branchId);
    });

    // Initial load
    updateDashboard($('#branchSelect').val());
});

function updateDashboard(branchId) {
    // Update sales data
    $.ajax({
        url: 'get_branch_sales.php',
        data: { branch_id: branchId },
        success: function(response) {
            $('#todaySales').text('₱' + response.today_sales.toFixed(2));
            $('#monthlySales').text('₱' + response.monthly_sales.toFixed(2));
            updateSalesTrendChart(response.sales_trend);
            updateTopProductsChart(response.top_products);
        }
    });

    // Update inventory alerts
    $.ajax({
        url: 'get_branch_inventory.php',
        data: { branch_id: branchId },
        success: function(response) {
            $('#lowStockCount').text(response.low_stock_count);
            $('#expiringCount').text(response.expiring_count);
            updateLowStockTable(response.low_stock_items);
            updateExpiringTable(response.expiring_items);
        }
    });
}

function updateSalesTrendChart(data) {
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Sales',
                data: data.values,
                borderColor: '#8B4543',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateTopProductsChart(data) {
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#8B4543',
                    '#A65D5D',
                    '#C47777',
                    '#E39191',
                    '#F3B7B7'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateLowStockTable(items) {
    const table = $('#lowStockTable').DataTable();
    table.clear();
    
    items.forEach(item => {
        table.row.add([
            item.name,
            item.current_stock,
            item.minimum_required,
            `<span class="badge ${item.current_stock === 0 ? 'badge-danger' : 'badge-warning'}">${item.status}</span>`
        ]);
    });
    
    table.draw();
}

function updateExpiringTable(items) {
    const table = $('#expiringTable').DataTable();
    table.clear();
    
    items.forEach(item => {
        table.row.add([
            item.name,
            item.expiry_date,
            item.days_left,
            item.quantity
        ]);
    });
    
    table.draw();
}
</script>

<?php include('footer.php'); ?> 