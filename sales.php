<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in as cashier and has an active session
if (!checkCashierLogin()) {
    // If no active session, but they're logged in, show error
    if (isset($_SESSION['error'])) {
        $error_message = $_SESSION['error'];
        unset($_SESSION['error']);
        // Display error in a user-friendly way
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Session Error</title>
            <link href="asset/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="container mt-5">
                <div class="alert alert-danger">
                    <h4>Error</h4>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <a href="logout.php" class="btn btn-primary">Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    // If not logged in at all, redirect to login
    header('Location: login.php');
    exit();
}

$confData = getConfigData($pdo);

// Get today's date
$today = date('Y-m-d');

// Get daily sales summary
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT o.order_id) as total_orders,
        COALESCE(SUM(o.order_total), 0) as total_sales,
        COALESCE(MIN(o.order_total), 0) as min_sale,
        COALESCE(MAX(o.order_total), 0) as max_sale,
        COALESCE(AVG(o.order_total), 0) as avg_sale
    FROM pos_order o
    WHERE DATE(o.order_datetime) = ? 
    AND o.order_created_by = ?
");
$stmt->execute([$today, $_SESSION['user_id']]);
$daily_summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Get today's orders with items
$orders_stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_number,
        o.order_datetime,
        o.order_total,
        GROUP_CONCAT(
            CONCAT(oi.product_qty, 'x ', p.product_name)
            SEPARATOR ', '
        ) as items
    FROM pos_order o
    LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
    LEFT JOIN pos_product p ON oi.product_id = p.product_id
    WHERE DATE(o.order_datetime) = ?
    AND o.order_created_by = ?
    GROUP BY o.order_id, o.order_number, o.order_datetime, o.order_total
    ORDER BY o.order_datetime DESC
");
$orders_stmt->execute([$today, $_SESSION['user_id']]);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Sales Dashboard</h1>
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
                        <h4 class="stat-value">₱<?php echo number_format($daily_summary['total_sales'], 2); ?></h4>
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
                        <h4 class="stat-value">₱<?php echo number_format($daily_summary['avg_sale'], 2); ?></h4>
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
                        <h4 class="stat-value">₱<?php echo number_format($daily_summary['max_sale'], 2); ?></h4>
                        <div class="stat-label">Highest Sale Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart and Top Products -->
    <div class="row mb-4">
        <!-- Daily Sales Chart -->
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h5 class="mb-0">Daily Sales Overview</h5>
                    <div class="chart-actions">
                        <select id="chartType" class="form-select form-select-sm">
                            <option value="quantity">Order Quantity</option>
                            <option value="total">Sales Total</option>
                        </select>
                    </div>
                </div>
                <div class="chart-card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-lg-4">
            <div class="top-products-card">
                <div class="top-products-header">
                    <h5 class="mb-0">Top 3 Products</h5>
                </div>
                <div class="top-products-body" id="topProductsList">
                    <!-- Top products will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Orders -->
    <div class="row">
        <div class="col-12">
            <div class="orders-card">
                <div class="orders-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Today's Orders</h5>
                        <a href="order_history.php" class="view-all-link">View All</a>
                    </div>
                </div>
                <div class="orders-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Order #</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($order = $orders_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . date('h:i A', strtotime($order['order_datetime'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($order['order_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($order['items']) . "</td>";
                                    echo "<td>₱" . number_format($order['order_total'], 2) . "</td>";
                                    echo "<td>
                                            <a href='print_order.php?id=" . $order['order_id'] . "' class='btn btn-primary btn-sm print-btn' target='_blank'>
                                                <i class='fas fa-print'></i>
                                            </a>
                                        </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <a href="add_order.php" class="btn btn-primary">Create Order</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Card Styles */
.stat-card {
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.stat-card-inner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-icon {
    font-size: 2.5rem;
    color: rgba(255, 255, 255, 0.9);
}

.stat-content {
    flex-grow: 1;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
}

.stat-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
}

/* Orders Card Styles */
.orders-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.orders-card-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.orders-card-body {
    padding: 1.25rem;
}

.view-all-link {
    color: #4e73df;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.view-all-link:hover {
    text-decoration: underline;
}

/* Table Styles */
.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    color: #5a5c69;
    border-bottom: 2px solid #e3e6f0;
}

.table td {
    vertical-align: middle;
    color: #858796;
    border-color: #e3e6f0;
}

.print-btn {
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.print-btn:hover {
    transform: translateY(-2px);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        font-size: 2rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .action-button {
        padding: 0.5rem 1rem;
    }
}

/* Chart Card Styles */
.chart-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    height: 100%;
}

.chart-card-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-card-body {
    padding: 1.25rem;
    height: 400px;
    position: relative;
}

.chart-actions {
    display: flex;
    gap: 1rem;
}

/* Top Products Card Styles */
.top-products-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    height: 100%;
}

.top-products-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.top-products-body {
    padding: 1.25rem;
}

.product-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.product-image {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    object-fit: cover;
    margin-right: 1rem;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.product-stats {
    font-size: 0.9rem;
    color: #6c757d;
}

.product-rank {
    font-size: 1.5rem;
    font-weight: 700;
    color: #4e73df;
    margin-left: 1rem;
}

/* Form Control Styles */
.form-select-sm {
    padding: 0.25rem 2rem 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background-color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-select-sm:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}
</style>

<script>
// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    let salesChart = null;
    const chartCtx = document.getElementById('salesChart').getContext('2d');
    
    // Function to load dashboard data
    function loadDashboardData() {
        const chartType = document.getElementById('chartType').value;
        
        fetch('get_dashboard_data.php?chart_type=' + chartType)
            .then(response => response.json())
            .then(data => {
                updateSalesChart(data);
                updateTopProducts(data.topProducts);
                updateSummaryCards(data.summary);
            })
            .catch(error => console.error('Error loading dashboard data:', error));
    }
    
    // Function to update summary cards
    function updateSummaryCards(summary) {
        document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = summary.total_orders;
        document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = '₱' + parseFloat(summary.total_sales).toFixed(2);
        document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = '₱' + parseFloat(summary.avg_sale).toFixed(2);
        document.querySelector('.stat-card:nth-child(4) .stat-value').textContent = '₱' + parseFloat(summary.max_sale).toFixed(2);
    }
    
    // Function to update sales chart
    function updateSalesChart(data) {
        const chartConfig = {
            type: 'line',
            data: {
                labels: data.purchaseTrend.labels,
                datasets: [{
                    label: 'Sales',
                    data: data.purchaseTrend.data,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
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
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        };

        if (salesChart) {
            salesChart.destroy();
        }
        salesChart = new Chart(chartCtx, chartConfig);
    }
    
    // Function to update top products
    function updateTopProducts(topProducts) {
        const container = document.getElementById('topProductsList');
        container.innerHTML = '';
        
        topProducts.labels.forEach((product, index) => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <img src="${topProducts.images[index]}" alt="${product}" class="product-image">
                <div class="product-info">
                    <div class="product-name">${product}</div>
                    <div class="product-stats">
                        Quantity: ${topProducts.data[index]}<br>
                        Revenue: ₱${topProducts.revenue[index].toFixed(2)}
                    </div>
                </div>
                <div class="product-rank">#${index + 1}</div>
            `;
            container.appendChild(card);
        });
    }
    
    // Add event listener for chart type change
    document.getElementById('chartType').addEventListener('change', loadDashboardData);
    
    // Initial load
    loadDashboardData();
});

document.addEventListener('DOMContentLoaded', function() {
    loadTopProducts();
});

function loadTopProducts() {
    fetch('get_top_products.php?period=daily')
        .then(response => response.json())
        .then(products => {
            const container = document.getElementById('topProductsList');
            container.innerHTML = '';
            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" class="product-image">
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-stats">
                            Quantity Sold: ${product.quantity}<br>
                            Revenue: ₱${product.revenue}
                        </div>
                        <a href="add_order.php?product_id=${product.id}" class="btn btn-primary mt-2">Order Now</a>
                    </div>
                `;
                container.appendChild(productCard);
            });
        })
        .catch(error => console.error('Error loading top products:', error));
}
</script>

<?php include('footer.php'); ?> 