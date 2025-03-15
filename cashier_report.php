<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

$confData = getConfigData($pdo);
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get cashier details
$stmt = $pdo->prepare("
    SELECT 
        cd.*,
        b.branch_name,
        u.username
    FROM pos_cashier_details cd
    JOIN pos_branch b ON cd.branch_id = b.branch_id
    JOIN pos_user u ON cd.user_id = u.user_id
    WHERE cd.user_id = ?
");
$stmt->execute([$user_id]);
$cashier_details = $stmt->fetch(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">My Performance Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="sales.php">Sales</a></li>
        <li class="breadcrumb-item active">My Report</li>
    </ol>

    <!-- Cashier Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Cashier Information</h5>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($cashier_details['username']); ?></p>
                            <p class="mb-1"><strong>Employee ID:</strong> <?php echo htmlspecialchars($cashier_details['employee_id']); ?></p>
                            <p class="mb-0"><strong>Branch:</strong> <?php echo htmlspecialchars($cashier_details['branch_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Shift Details</h5>
                            <p class="mb-1"><strong>Schedule:</strong> <?php echo htmlspecialchars($cashier_details['shift_schedule']); ?></p>
                            <p class="mb-0"><strong>Status:</strong> <span id="activeStatus" class="badge bg-secondary">Checking...</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Today's Orders</h6>
                            <h3 class="mb-0" id="todayOrders">0</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-shopping-cart text-primary"></i>
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
                            <h6 class="mb-0">Today's Sales</h6>
                            <h3 class="mb-0" id="todaySales">₱0.00</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-peso-sign text-success"></i>
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
                            <h6 class="mb-0">Average Sale</h6>
                            <h3 class="mb-0" id="avgSale">₱0.00</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-chart-line text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Items Sold</h6>
                            <h3 class="mb-0" id="itemsSold">0</h3>
                        </div>
                        <div class="icon-circle bg-white">
                            <i class="fas fa-box text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Performance</h5>
                    <select id="salesPeriod" class="form-select form-select-sm" style="width: auto;">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Products</h5>
                </div>
                <div class="card-body">
                    <canvas id="productsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movements -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Recent Stock Movements</h5>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" id="refreshStockBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockMovementsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Previous Stock</th>
                            <th>New Stock</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Stock movements will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-circle i {
    font-size: 1.5rem;
}

.card {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background: none;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 1rem;
}

.table th {
    font-weight: 500;
    background-color: #f8f9fa;
}
</style>

<script>
$(document).ready(function() {
    let salesChart;
    let productsChart;
    
    // Initialize charts
    function initializeCharts() {
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Sales',
                    data: [],
                    borderColor: '#8B4543',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        const productsCtx = document.getElementById('productsChart').getContext('2d');
        productsChart = new Chart(productsCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#4A7C59',
                        '#C4804D',
                        '#8B4543',
                        '#6B5B95',
                        '#FFA07A'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Function to update performance stats
    function updateStats() {
        $.ajax({
            url: 'get_cashier_stats.php',
            success: function(response) {
                $('#todayOrders').text(response.today_orders);
                $('#todaySales').text('₱' + response.today_sales.toLocaleString());
                $('#avgSale').text('₱' + response.average_sale.toLocaleString());
                $('#itemsSold').text(response.items_sold);
                
                if (response.is_active) {
                    $('#activeStatus').removeClass('bg-secondary bg-danger').addClass('bg-success').text('Currently Active');
                } else {
                    $('#activeStatus').removeClass('bg-secondary bg-success').addClass('bg-danger').text('Not Active');
                }

                // Update sales chart
                salesChart.data.labels = response.sales_trend.labels;
                salesChart.data.datasets[0].data = response.sales_trend.data;
                salesChart.update();

                // Update products chart
                productsChart.data.labels = response.top_products.map(p => p.product_name);
                productsChart.data.datasets[0].data = response.top_products.map(p => p.quantity);
                productsChart.update();
            }
        });
    }

    // Function to update stock movements table
    function updateStockMovements() {
        $.ajax({
            url: 'get_stock_movements.php',
            success: function(response) {
                let tableBody = '';
                response.movements.forEach(movement => {
                    const type = movement.movement_type;
                    const typeClass = type === 'IN' ? 'text-success' : (type === 'OUT' ? 'text-danger' : 'text-warning');
                    
                    tableBody += `
                        <tr>
                            <td>${movement.created_at}</td>
                            <td>${movement.item_name}</td>
                            <td><span class="badge ${typeClass}">${type}</span></td>
                            <td>${movement.quantity}</td>
                            <td>${movement.previous_stock}</td>
                            <td>${movement.new_stock}</td>
                            <td>${movement.reference_type} #${movement.reference_id}</td>
                        </tr>
                    `;
                });
                $('#stockMovementsTable tbody').html(tableBody);
            }
        });
    }

    // Initialize charts
    initializeCharts();

    // Initial load
    updateStats();
    updateStockMovements();

    // Handle sales period change
    $('#salesPeriod').change(function() {
        updateStats();
    });

    // Handle refresh button click
    $('#refreshStockBtn').click(function() {
        updateStockMovements();
    });

    // Refresh timer
    setInterval(updateStats, 60000); // Update every minute
    setInterval(updateStockMovements, 300000); // Update every 5 minutes
});
</script>

<?php include('footer.php'); ?> 