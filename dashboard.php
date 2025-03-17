<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$categorySql = "SELECT COUNT(*) FROM pos_category WHERE status = 'active'";
$productSql = "SELECT COUNT(*) FROM pos_product";
$userSql = "SELECT COUNT(*) FROM pos_user";
$branchSql = "SELECT COUNT(*) FROM pos_branch WHERE status = 'Active'";
$orderSql = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'User' ?
    "SELECT SUM(order_total) FROM pos_order WHERE order_created_by = '" . $_SESSION['user_id'] . "'" :
    "SELECT SUM(order_total) FROM pos_order";

$stmt = $pdo->prepare($categorySql);
$stmt->execute();
$total_category = $stmt->fetchColumn();

$stmt = $pdo->prepare($productSql);
$stmt->execute();
$total_product = $stmt->fetchColumn();

$stmt = $pdo->prepare($userSql);
$stmt->execute();
$total_user = $stmt->fetchColumn();

$stmt = $pdo->prepare($branchSql);
$stmt->execute();
$total_branch = $stmt->fetchColumn();

$stmt = $pdo->prepare($orderSql);
$stmt->execute();
$total_sales = $stmt->fetchColumn();

// Fetch categories and product count per category dynamically
$categories = $pdo->query("SELECT c.category_name, COUNT(p.product_id) AS total 
                           FROM pos_category c 
                           LEFT JOIN pos_product p ON c.category_id = p.category_id 
                           WHERE c.status = 'active'
                           GROUP BY c.category_name")
                  ->fetchAll(PDO::FETCH_ASSOC);

$confData = getConfigData($pdo);

// Get initial data
$stmt = $pdo->query("SELECT COUNT(*) FROM pos_user WHERE user_type = 'Cashier' AND user_status = 'Active'");
$total_cashiers = $stmt->fetchColumn();

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">
        | Dashboard Overview
    </h1>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <!-- Total Categories -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-gradient-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-1">Total Categories</h6>
                            <h3 class="text-white mb-0" id="totalCategories">0</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-th-list"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="category.php" class="text-white text-sm">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-gradient-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-1">Total Products</h6>
                            <h3 class="text-white mb-0" id="totalProducts">0</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="product.php" class="text-white text-sm">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Branches -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-gradient-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-1">Total Branches</h6>
                            <h3 class="text-white mb-0" id="totalBranches">0</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="branch_details.php" class="text-white text-sm">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-gradient-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-1">Total Revenue</h6>
                            <h3 class="text-white mb-0" id="totalRevenue">₱0.00</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="sales_report.php" class="text-white text-sm">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashier Performance Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Cashier Performance</h5>
                        <p class="text-muted mb-0">Active cashiers and their performance metrics</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="cashierPeriod">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                    </select>
                        <button class="btn btn-sm btn-primary" id="refreshCashierStats">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <!-- Active Cashiers Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Active Cashiers</h6>
                                        <h4 class="mb-0" id="activeCashiers">0</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-user-clock text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Total Transactions Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Transactions</h6>
                                        <h4 class="mb-0" id="totalTransactions">0</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-receipt text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Average Transaction Time Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Avg. Transaction Time</h6>
                                        <h4 class="mb-0" id="avgTransactionTime">0m</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-clock text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Total Sales Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Sales</h6>
                                        <h4 class="mb-0" id="totalCashierSales">₱0.00</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-peso-sign text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cashier Performance Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="cashierPerformanceTable">
                            <thead>
                                <tr>
                                    <th>Cashier Name</th>
                                    <th>Branch</th>
                                    <th>Transactions</th>
                                    <th>Sales</th>
                                    <th>Avg. Transaction Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Sales Trend</h5>
                        <p class="text-muted mb-0">Overview of sales performance</p>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary period-selector active" data-period="daily">Daily</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="weekly">Weekly</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="monthly">Monthly</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Product Distribution Chart -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Product Distribution</h5>
                    <p class="text-muted mb-0">Products by category</p>
                </div>
                <div class="card-body">
                    <canvas id="productDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance and Inventory Status -->
    <div class="row">
        <!-- Branch Performance -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Branch Performance</h5>
                        <p class="text-muted mb-0">Today's sales by branch</p>
                    </div>
                    <a href="branch_comparison.php" class="btn btn-sm btn-primary">Compare Branches</a>
                </div>
                <div class="card-body">
                    <canvas id="branchPerformanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Inventory Status -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Inventory Status</h5>
                    <p class="text-muted mb-0">Low stock alerts</p>
                </div>
                <div class="card-body">
                    <canvas id="inventoryStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cashier Details Modal -->
<div class="modal fade" id="cashierDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cashier Performance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Hourly Sales</h6>
                                <canvas id="cashierHourlySalesChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Payment Methods</h6>
                                <canvas id="cashierPaymentMethodsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Recent Transactions</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="cashierTransactionsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Order ID</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamically populated -->
                                </tbody>
                            </table>
                        </div>
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
    margin-bottom: 1.5rem;
}

.card-header {
    background: transparent;
    border-bottom: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Stats Card Styling */
.stats-card {
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-icon i {
    font-size: 1.5rem;
    color: white;
}

/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(45deg, #8B4543, #A65D5D);
}

.bg-gradient-success {
    background: linear-gradient(45deg, #4A7C59, #6B9C77);
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #C4804D, #E5A06B);
}

.bg-gradient-info {
    background: linear-gradient(45deg, #3B7B9E, #5B9BC0);
}

/* Button Styling */
.btn-outline-secondary {
    color: #8B4543;
    border-color: #8B4543;
}

.btn-outline-secondary:hover,
.btn-outline-secondary.active {
    background-color: #8B4543;
    border-color: #8B4543;
    color: white;
}

.btn-primary {
    background-color: #8B4543;
    border-color: #8B4543;
}

.btn-primary:hover {
    background-color: #723937;
    border-color: #723937;
}

/* Text Styling */
.text-sm {
    font-size: 0.875rem;
}

.text-muted {
    color: #6c757d !important;
}

/* Chart Container */
canvas {
    max-width: 100%;
}

/* Additional styles for cashier section */
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cashier-status {
    padding: 0.25rem 0.75rem;
    border-radius: 50rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-active {
    background-color: rgba(74, 124, 89, 0.1);
    color: #4A7C59;
}

.status-inactive {
    background-color: rgba(139, 69, 67, 0.1);
    color: #8B4543;
}

.modal-header {
    background-color: #8B4543;
    color: white;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}
</style>

<script>
let salesTrendChart = null;
let productDistributionChart = null;
let branchPerformanceChart = null;
let inventoryStatusChart = null;

// Function to format currency
function formatCurrency(value) {
    return '₱' + parseFloat(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Function to initialize charts
function initializeCharts() {
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    salesTrendChart = new Chart(salesTrendCtx, {
    type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                borderColor: '#8B4543',
                backgroundColor: 'rgba(139, 69, 67, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
    options: {
        responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });

    // Product Distribution Chart
    const productDistributionCtx = document.getElementById('productDistributionChart').getContext('2d');
    productDistributionChart = new Chart(productDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#8B4543', '#4A7C59', '#C4804D', '#3B7B9E', '#A65D5D'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        plugins: {
            legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Branch Performance Chart
    const branchPerformanceCtx = document.getElementById('branchPerformanceChart').getContext('2d');
    branchPerformanceChart = new Chart(branchPerformanceCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                backgroundColor: '#8B4543'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                callbacks: {
                    label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });

    // Inventory Status Chart
    const inventoryStatusCtx = document.getElementById('inventoryStatusChart').getContext('2d');
    inventoryStatusChart = new Chart(inventoryStatusCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Current Stock',
                data: [],
                backgroundColor: '#8B4543'
            }, {
                label: 'Minimum Stock',
                data: [],
                backgroundColor: '#C4804D'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function to update dashboard data
function updateDashboard() {
    // Update stats
    $.get('get_dashboard_stats.php', function(response) {
        $('#totalCategories').text(response.total_categories);
        $('#totalProducts').text(response.total_products);
        $('#totalBranches').text(response.total_branches);
        $('#totalRevenue').text(formatCurrency(response.total_revenue));
    });

    // Update sales trend
    const period = $('.period-selector.active').data('period');
    $.get('get_sales_trend.php', { period: period }, function(response) {
        salesTrendChart.data.labels = response.labels;
        salesTrendChart.data.datasets[0].data = response.data;
        salesTrendChart.update();
    });

    // Update product distribution
    $.get('get_product_distribution.php', function(response) {
        productDistributionChart.data.labels = response.labels;
        productDistributionChart.data.datasets[0].data = response.data;
        productDistributionChart.update();
    });

    // Update branch performance
    $.get('get_branch_performance.php', function(response) {
        branchPerformanceChart.data.labels = response.labels;
        branchPerformanceChart.data.datasets[0].data = response.data;
        branchPerformanceChart.update();
    });

    // Update inventory status
    $.get('get_inventory_status.php', function(response) {
        inventoryStatusChart.data.labels = response.labels;
        inventoryStatusChart.data.datasets[0].data = response.current_stock;
        inventoryStatusChart.data.datasets[1].data = response.minimum_stock;
        inventoryStatusChart.update();
    });
}

// Function to update cashier performance data
function updateCashierPerformance() {
    const period = $('#cashierPeriod').val();
    
    $.get('get_cashier_performance.php', { period: period }, function(response) {
        // Update summary stats
        $('#activeCashiers').text(response.active_cashiers);
        $('#totalTransactions').text(response.total_transactions);
        $('#avgTransactionTime').text(response.avg_transaction_time);
        $('#totalCashierSales').text(formatCurrency(response.total_sales));

        // Update table
        const tbody = $('#cashierPerformanceTable tbody');
        tbody.empty();

        response.cashiers.forEach(cashier => {
            const paymentBreakdown = `
                <div class="small">
                    <div>Cash: ${formatCurrency(cashier.cash_sales)}</div>
                    <div>Card: ${formatCurrency(cashier.card_sales)}</div>
                    <div>E-Wallet: ${formatCurrency(cashier.ewallet_sales)}</div>
                </div>
            `;

            const orderTypeBreakdown = `
                <div class="small">
                    <div>Dine-in: ${cashier.dine_in_orders}</div>
                    <div>Takeout: ${cashier.takeout_orders}</div>
                    <div>Delivery: ${cashier.delivery_orders}</div>
                </div>
            `;

            tbody.append(`
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${cashier.profile_image || 'assets/img/default-avatar.png'}" 
                                 class="rounded-circle me-2" width="32" height="32">
                            ${cashier.name}
                        </div>
                    </td>
                    <td>${cashier.branch}</td>
                    <td>
                        <div>${cashier.transactions}</div>
                        ${orderTypeBreakdown}
                    </td>
                    <td>
                        <div>${formatCurrency(cashier.sales)}</div>
                        ${paymentBreakdown}
                    </td>
                    <td>
                        <div>${cashier.avg_time}</div>
                        <div class="small">Avg. Order: ${formatCurrency(cashier.avg_order_value)}</div>
                    </td>
                    <td>
                        <span class="cashier-status ${cashier.is_active ? 'status-active' : 'status-inactive'}">
                            ${cashier.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-details" data-id="${cashier.id}">
                            View Details
                        </button>
                    </td>
                </tr>
            `);
        });
    });
}

// Function to show cashier details modal
function showCashierDetails(cashierId) {
    const modal = $('#cashierDetailsModal');
    modal.modal('show');

    $.get('get_cashier_details.php', { id: cashierId }, function(response) {
        // Initialize hourly sales chart
        const hourlyCtx = document.getElementById('cashierHourlySalesChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: response.hourly_sales.labels,
                datasets: [{
                    label: 'Total Sales',
                    data: response.hourly_sales.data,
                    borderColor: '#8B4543',
                    backgroundColor: 'rgba(139, 69, 67, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                                return formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // Initialize payment methods chart
        const paymentCtx = document.getElementById('cashierPaymentMethodsChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: response.payment_methods.labels,
                datasets: [{
                    data: response.payment_methods.data,
                    backgroundColor: ['#8B4543', '#4A7C59', '#C4804D']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const method = response.payment_methods.labels[context.dataIndex];
                                const count = context.raw;
                                const total = response.payment_methods.total[context.dataIndex];
                                return `${method}: ${count} orders (${formatCurrency(total)})`;
                            }
                        }
                    }
                }
            }
        });

        // Populate transactions table
        const tbody = $('#cashierTransactionsTable tbody');
        tbody.empty();

        response.transactions.forEach(tx => {
            tbody.append(`
                <tr>
                    <td>${tx.time}</td>
                    <td>${tx.order_id}</td>
                    <td>${tx.items}</td>
                    <td>
                        <div>${formatCurrency(tx.total)}</div>
                        <div class="small">
                            <div>Subtotal: ${formatCurrency(tx.subtotal)}</div>
                            <div>Tax: ${formatCurrency(tx.tax)}</div>
                            <div>Discount: ${formatCurrency(tx.discount)} (${tx.discount_type})</div>
                        </div>
                    </td>
                    <td>
                        <div>${tx.payment_method}</div>
                        <div class="small">${tx.service_type}</div>
                    </td>
                    <td>
                        <span class="badge ${tx.status === 'completed' ? 'bg-success' : 'bg-warning'}">
                            ${tx.status}
                        </span>
                    </td>
                </tr>
            `);
        });
    });
}

$(document).ready(function() {
    // Initialize charts
    initializeCharts();

    // Initial update
    updateDashboard();

    // Set up period selector buttons
    $('.period-selector').click(function() {
        $('.period-selector').removeClass('active');
        $(this).addClass('active');
        updateDashboard();
    });

    // Auto-refresh every 5 minutes
    setInterval(updateDashboard, 300000);

    // Initialize cashier performance
    updateCashierPerformance();

    // Set up event handlers
    $('#cashierPeriod').change(updateCashierPerformance);
    $('#refreshCashierStats').click(updateCashierPerformance);

    $(document).on('click', '.view-details', function() {
        const cashierId = $(this).data('id');
        showCashierDetails(cashierId);
    });

    // Add cashier performance to auto-refresh
    setInterval(updateCashierPerformance, 300000);
});
</script>

<?php include('footer.php'); ?>