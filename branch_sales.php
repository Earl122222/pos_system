<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

// Get branch parameter
$branch = isset($_GET['branch']) ? $_GET['branch'] : 'main';
$branch_name = ucfirst($branch === 'main' ? 'Main Branch' : "Branch $branch");

// Get today's date
$today = date('Y-m-d');

// Get daily sales summary for the branch
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT o.order_id) as total_orders,
        COALESCE(SUM(o.order_total), 0) as total_sales,
        COALESCE(MIN(o.order_total), 0) as min_sale,
        COALESCE(MAX(o.order_total), 0) as max_sale,
        COALESCE(AVG(o.order_total), 0) as avg_sale
    FROM pos_order o
    WHERE DATE(o.order_datetime) = ?
");
$stmt->execute([$today]);
$daily_summary = $stmt->fetch(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $branch_name; ?> Sales Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active"><?php echo $branch_name; ?> Sales</li>
    </ol>

    <!-- Sales Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-primary">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value"><?php echo $daily_summary['total_orders']; ?></h4>
                        <div class="stat-label">Total Orders Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-success">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value"><?php echo $confData['currency'] . number_format($daily_summary['total_sales'], 2); ?></h4>
                        <div class="stat-label">Total Sales Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-warning">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value"><?php echo $confData['currency'] . number_format($daily_summary['avg_sale'], 2); ?></h4>
                        <div class="stat-label">Average Sale Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-info">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value"><?php echo $confData['currency'] . number_format($daily_summary['max_sale'], 2); ?></h4>
                        <div class="stat-label">Highest Sale Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Analysis Section -->
    <div class="row mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Trend</h5>
                    <div class="chart-actions">
                        <select id="trendPeriod" class="form-select form-select-sm">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Methods Distribution -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Methods</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Best Selling Products Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Best Selling Products</h5>
                    <div class="chart-actions">
                        <select id="productPeriod" class="form-select form-select-sm">
                            <option value="daily">Today</option>
                            <option value="weekly">This Week</option>
                            <option value="monthly">This Month</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="topProductsContainer">
                        <!-- Top products will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    background: #fff;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    object-fit: cover;
}

.product-details {
    flex: 1;
}

.product-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.product-stats {
    font-size: 0.875rem;
    color: #666;
}

.product-rank {
    font-size: 1.5rem;
    font-weight: 700;
    color: #4e73df;
}

.chart-actions select {
    min-width: 120px;
}
</style>

<script>
$(document).ready(function() {
    // Initialize charts
    initializeSalesTrendChart();
    initializePaymentMethodsChart();
    loadTopProducts();

    // Event listeners for period changes
    $('#trendPeriod').change(function() {
        updateSalesTrendChart($(this).val());
    });

    $('#productPeriod').change(function() {
        loadTopProducts($(this).val());
    });
});

function initializeSalesTrendChart() {
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    window.salesTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
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
                            return '<?php echo $confData['currency']; ?>' + value;
                        }
                    }
                }
            }
        }
    });
    updateSalesTrendChart('daily');
}

function initializePaymentMethodsChart() {
    const ctx = document.getElementById('paymentMethodsChart').getContext('2d');
    window.paymentMethodsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Credit Card', 'E-Wallet'],
            datasets: [{
                data: [65, 20, 15],
                backgroundColor: [
                    'rgb(75, 192, 192)',
                    'rgb(255, 99, 132)',
                    'rgb(255, 205, 86)'
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
}

function updateSalesTrendChart(period) {
    $.ajax({
        url: 'get_sales_trend.php',
        data: { 
            period: period,
            branch: '<?php echo $branch; ?>'
        },
        success: function(response) {
            window.salesTrendChart.data.labels = response.labels;
            window.salesTrendChart.data.datasets[0].data = response.data;
            window.salesTrendChart.update();
        }
    });
}

function loadTopProducts(period = 'daily') {
    $.ajax({
        url: 'get_top_products.php',
        data: { 
            period: period,
            branch: '<?php echo $branch; ?>'
        },
        success: function(response) {
            const container = $('#topProductsContainer');
            container.empty();

            response.forEach((product, index) => {
                container.append(`
                    <div class="col-md-6 col-lg-4">
                        <div class="product-card">
                            <div class="product-info">
                                <img src="${product.image}" alt="${product.name}" class="product-image">
                                <div class="product-details">
                                    <div class="product-name">${product.name}</div>
                                    <div class="product-stats">
                                        Quantity Sold: ${product.quantity}<br>
                                        Revenue: <?php echo $confData['currency']; ?>${product.revenue}
                                    </div>
                                </div>
                                <div class="product-rank">#${index + 1}</div>
                            </div>
                        </div>
                    </div>
                `);
            });
        }
    });
}
</script>

<?php include('footer.php'); ?> 