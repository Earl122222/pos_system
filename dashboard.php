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

// Add Grab Food queries
$grabFoodSalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'grab'";
$grabFoodOrdersSql = "SELECT COUNT(*) FROM pos_order WHERE service_type = 'grab'";
$grabFoodLastMonthSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order 
                         WHERE service_type = 'grab' 
                         AND MONTH(order_datetime) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
                         AND YEAR(order_datetime) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";

// Add service type sales queries
$dineInSalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'dine-in'";
$takeoutSalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'takeout'";
$deliverySalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'delivery'";

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

// Execute Grab Food queries
$stmt = $pdo->prepare($grabFoodSalesSql);
$stmt->execute();
$grab_food_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($grabFoodOrdersSql);
$stmt->execute();
$grab_food_orders = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($grabFoodLastMonthSql);
$stmt->execute();
$grab_food_last_month = $stmt->fetchColumn() ?: 1; // Avoid division by zero

// Calculate Grab Food metrics
$grab_food_average = $grab_food_orders > 0 ? $grab_food_sales / $grab_food_orders : 0;
$grab_food_growth = $grab_food_last_month > 0 ? 
    (($grab_food_sales - $grab_food_last_month) / $grab_food_last_month) * 100 : 0;

// Execute service type queries
$stmt = $pdo->prepare($dineInSalesSql);
$stmt->execute();
$dine_in_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($takeoutSalesSql);
$stmt->execute();
$takeout_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($deliverySalesSql);
$stmt->execute();
$delivery_sales = $stmt->fetchColumn() ?: 0;

$confData = getConfigData($pdo);

// Get initial data
$stmt = $pdo->query("SELECT COUNT(*) FROM pos_user WHERE user_type = 'Cashier' AND user_status = 'Active'");
$total_cashiers = $stmt->fetchColumn();

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4" style="color: #8B4543; font-size: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
        <i class="fas fa-chart-line"></i>
        Dashboard Overview
    </h1>

    <!-- Main Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Total Revenue</h6>
                        <h2 style="color: #9C27B0;">₱<?php echo number_format($total_sales, 2); ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: linear-gradient(135deg, rgba(156,39,176,0.1), rgba(156,39,176,0.05));">
                        <i class="fas fa-peso-sign" style="color: #9C27B0;"></i>
                    </div>
                </div>
                <a href="sales_report.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #9C27B0, #8E24AA); color: white; border: none;">
                    View Sales Report
                </a>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Categories</h6>
                        <h2 style="color: #FF6B6B;"><?php echo $total_category; ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(255,107,107,0.1);">
                        <i class="fas fa-th-list" style="color: #FF6B6B;"></i>
                    </div>
                </div>
                <a href="category.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #FF6B6B, #FF5252); color: white; border: none;">
                    View Categories
                </a>
            </div>
        </div>

        <!-- Products -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Products</h6>
                        <h2 style="color: #4CAF50;"><?php echo $total_product; ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(76,175,80,0.1);">
                        <i class="fas fa-box" style="color: #4CAF50;"></i>
                    </div>
                </div>
                <a href="product.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #4CAF50, #43A047); color: white; border: none;">
                    View Products
                </a>
            </div>
        </div>

        <!-- Branches -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Branches</h6>
                        <h2 style="color: #2196F3;"><?php echo $total_branch; ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(33,150,243,0.1);">
                        <i class="fas fa-store" style="color: #2196F3;"></i>
                    </div>
                </div>
                <a href="branch.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #2196F3, #1E88E5); color: white; border: none;">
                    View Branches
                </a>
            </div>
        </div>
    </div>

    <!-- Service Type Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Dine In -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Dine In Sales</h6>
                        <h2 class="sales-amount" style="color: #9C27B0;">₱<?php echo number_format($dine_in_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper" style="background: linear-gradient(135deg, rgba(156,39,176,0.1), rgba(156,39,176,0.05));">
                        <i class="fas fa-utensils" style="color: #9C27B0;"></i>
                    </div>
                </div>
                <a href="sales_report.php?type=dine-in" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #9C27B0, #8E24AA); color: white; border: none;">
                    View Dine In Sales
                </a>
            </div>
        </div>

        <!-- Takeout -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Takeout Sales</h6>
                        <h2 class="sales-amount" style="color: #FF6B6B;">₱<?php echo number_format($takeout_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper" style="background: rgba(255,107,107,0.1);">
                        <i class="fas fa-shopping-bag" style="color: #FF6B6B;"></i>
                    </div>
                </div>
                <a href="sales_report.php?type=takeout" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #FF6B6B, #FF5252); color: white; border: none;">
                    View Takeout Sales
                </a>
            </div>
        </div>

        <!-- Delivery -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Delivery Sales</h6>
                        <h2 class="sales-amount" style="color: #4CAF50;">₱<?php echo number_format($delivery_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper" style="background: rgba(76,175,80,0.1);">
                        <i class="fas fa-motorcycle" style="color: #4CAF50;"></i>
                    </div>
                </div>
                <a href="sales_report.php?type=delivery" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #4CAF50, #43A047); color: white; border: none;">
                    View Delivery Sales
                </a>
            </div>
        </div>

        <!-- Grab Food -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Grab Food Sales</h6>
                        <h2 class="sales-amount" style="color: #00B14F;">₱<?php echo number_format($grab_food_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper grab-icon">
                        <div class="grab-icon-container">
                            <i class="fas fa-motorcycle grab-motorcycle"></i>
                        </div>
                    </div>
                </div>
                <a href="sales_report.php?type=grab" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #00B14F, #009F47); color: white; border: none;">
                    View Grab Food Sales
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="chart-card">
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
        <div class="col-xl-4 col-lg-5">
            <div class="chart-card">
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
        <!-- Branch Performance - Made wider -->
        <div class="col-xl-8 col-lg-7">
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

        <!-- Inventory Status - Made narrower -->
        <div class="col-xl-4 col-lg-5">
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

    <!-- Cashier Performance Section - Full width -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card cashier-performance-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Cashier Performance</h5>
                        <p class="text-muted mb-0">Active cashiers and their performance metrics</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm period-select" id="cashierPeriod">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                        <button class="btn btn-sm btn-primary btn-refresh" id="refreshCashierStats">
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
                    <div class="table-responsive cashier-table">
                        <table class="table table-hover" id="cashierPerformanceTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Total</th>
                                    <th>Qty</th>
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

    <!-- Menu Items Section -->
    <div class="menu-section">
        <div class="menu-header">
            <i class="fas fa-utensils"></i>
            <h2>Menu Items</h2>
        </div>

        <!-- Menu Categories -->
        <div class="menu-categories">
            <div class="category-card active">
                <div class="category-icon">
                    <i class="fas fa-border-all"></i>
                </div>
                <div class="category-name">All Menu</div>
                <div class="item-count">4 Items</div>
            </div>
            
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-ice-cream"></i>
                </div>
                <div class="category-name">Halo-Halo</div>
                <div class="item-count">2 Items</div>
            </div>
            
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-pizza-slice"></i>
                </div>
                <div class="category-name">Pizza</div>
                <div class="item-count">2 Items</div>
            </div>
        </div>

        <!-- Menu Items Grid -->
        <div class="menu-items-grid">
            <!-- Menu Item 1 -->
            <div class="menu-item-card">
                <img src="assets/img/menu/mais-con-yelo.jpg" alt="Mais Con yelo" class="item-image">
                <div class="item-details">
                    <h3 class="item-name">Mais Con yelo</h3>
                    <p class="item-description">Delicious food with special sauce</p>
                    <div class="item-price">₱95.00</div>
                    <div class="item-actions">
                        <div class="quantity-control">
                            <button class="qty-btn minus">-</button>
                            <input type="text" class="qty-input" value="0" readonly>
                            <button class="qty-btn plus">+</button>
                        </div>
                        <button class="add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menu Item 2 -->
            <div class="menu-item-card">
                <img src="assets/img/menu/mango.jpg" alt="Mango" class="item-image">
                <div class="item-details">
                    <h3 class="item-name">Mango</h3>
                    <p class="item-description">Delicious food with special sauce</p>
                    <div class="item-price">₱95.00</div>
                    <div class="item-actions">
                        <div class="quantity-control">
                            <button class="qty-btn minus">-</button>
                            <input type="text" class="qty-input" value="1" readonly>
                            <button class="qty-btn plus">+</button>
                        </div>
                        <button class="add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menu Item 3 -->
            <div class="menu-item-card">
                <img src="assets/img/menu/sadsa.jpg" alt="Sadsa" class="item-image">
                <div class="item-details">
                    <h3 class="item-name">Sadsa</h3>
                    <p class="item-description">Delicious food with special sauce</p>
                    <div class="item-price">₱231.00</div>
                    <div class="item-actions">
                        <div class="quantity-control">
                            <button class="qty-btn minus">-</button>
                            <input type="text" class="qty-input" value="0" readonly>
                            <button class="qty-btn plus">+</button>
                        </div>
                        <button class="add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                    </div>
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
/* Enhanced Card Styling */
.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 0.25rem 1.5rem rgba(139, 69, 67, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 2rem rgba(139, 69, 67, 0.12);
}

.card-header {
    background: linear-gradient(to right, rgba(255,255,255,0.95), rgba(255,255,255,0.98));
    border-bottom: 1px solid rgba(139, 69, 67, 0.08);
    padding: 1.25rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.98);
}

/* Main Stats Cards */
.main-stats-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.main-stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2),
        rgba(255, 255, 255, 0.1)
    );
    z-index: 1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.main-stats-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.main-stats-card:hover::before {
    opacity: 1;
}

.main-stats-card h6 {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.main-stats-card:hover h6 {
    color: #333;
}

.main-stats-card h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0;
    line-height: 1.2;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.main-stats-card:hover h2 {
    transform: scale(1.05);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.main-stats-card .rounded-circle {
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.9) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.main-stats-card:hover .rounded-circle {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.main-stats-card .rounded-circle i {
    font-size: 1.5rem;
    transition: all 0.4s ease;
}

.main-stats-card:hover .rounded-circle i {
    transform: scale(1.1);
}

.main-stats-card .btn {
    border-radius: 0.75rem;
    font-weight: 500;
    padding: 0.75rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    z-index: 2;
}

.main-stats-card .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2),
        rgba(255, 255, 255, 0.1)
    );
    transform: translateX(-100%);
    transition: transform 0.4s ease;
}

.main-stats-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.main-stats-card .btn:hover::before {
    transform: translateX(0);
}

/* Responsive Adjustments for Main Stats Cards */
@media (max-width: 768px) {
    .main-stats-card {
        padding: 1.25rem;
    }
    
    .main-stats-card h2 {
        font-size: 2rem;
    }
    
    .main-stats-card .rounded-circle {
        width: 3rem;
        height: 3rem;
    }
    
    .main-stats-card .rounded-circle i {
        font-size: 1.25rem;
    }
}

/* Secondary Stats Cards */
.secondary-stats-card {
    background: rgba(255,255,255,0.95);
    border-radius: 1rem;
    padding: 1.25rem;
    height: 100%;
}

.secondary-stats-card h6 {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.secondary-stats-card h3 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0;
}

/* Grab Food Card Styling */
.grab-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
    border-radius: 1.25rem;
    box-shadow: 0 0.5rem 2rem rgba(0,177,79,0.08);
}

.grab-card:hover {
    box-shadow: 0 0.75rem 3rem rgba(0,177,79,0.12);
}

.grab-stats {
    background: rgba(0,177,79,0.03);
    border-radius: 1rem;
    padding: 1.25rem;
    transition: all 0.3s ease;
}

.grab-stats:hover {
    background: rgba(0,177,79,0.05);
    transform: translateY(-2px);
}

/* Chart Cards */
.chart-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
    border-radius: 1.25rem;
}

.chart-card .card-header {
    padding: 1.5rem;
}

.chart-card .card-body {
    padding: 1.5rem;
}

/* Button Styling */
.btn {
    border-radius: 0.75rem;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #8B4543, #723937);
    border: none;
    box-shadow: 0 0.25rem 1rem rgba(139, 69, 67, 0.15);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #723937, #5E2F2D);
    box-shadow: 0 0.5rem 1.5rem rgba(139, 69, 67, 0.2);
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border: 1px solid rgba(139, 69, 67, 0.2);
    color: #8B4543;
}

.btn-outline-secondary:hover,
.btn-outline-secondary.active {
    background: linear-gradient(135deg, #8B4543, #723937);
    border-color: transparent;
    color: white;
    box-shadow: 0 0.25rem 1rem rgba(139, 69, 67, 0.15);
}

/* Table Styling */
.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    padding: 1rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.08);
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid rgba(139, 69, 67, 0.05);
}

/* Status Badges */
.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.status-badge.active {
    background: rgba(74, 124, 89, 0.1);
    color: #4A7C59;
}

.status-badge.inactive {
    background: rgba(139, 69, 67, 0.1);
    color: #8B4543;
}

/* Animation Effects */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.5s ease-out;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(139, 69, 67, 0.05);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: rgba(139, 69, 67, 0.2);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(139, 69, 67, 0.3);
}

/* Add styles for service type breakdown */
.service-type-breakdown {
    background: rgba(0,177,79,0.02);
    border-radius: 1rem;
    padding: 1.25rem;
}

.service-stat {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem;
    transition: all 0.3s ease;
}

.service-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.05);
}

.service-stat small {
    font-size: 0.75rem;
    font-weight: 500;
}

.service-stat h5 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

/* Enhanced Service Type Cards */
.service-type-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.service-type-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.1),
        rgba(255, 255, 255, 0.05)
    );
    z-index: 1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.service-type-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.service-type-card:hover::before {
    opacity: 1;
}

.service-type-card .text-muted {
    font-size: 0.875rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.service-type-card:hover .text-muted {
    color: #333 !important;
}

.service-type-card .sales-amount {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.service-type-card:hover .sales-amount {
    transform: scale(1.05);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.service-icon-wrapper {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.9) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.service-icon-wrapper i {
    font-size: 1.5rem;
    transition: all 0.4s ease;
}

.service-type-card:hover .service-icon-wrapper {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.service-type-card:hover .service-icon-wrapper i {
    transform: scale(1.1);
}

/* Grab Food Specific Styles */
.grab-icon {
    padding: 0;
    background: #00B14F !important;
    border-radius: 0.75rem;
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 15px rgba(0, 177, 79, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.grab-icon-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.grab-motorcycle {
    color: white;
    font-size: 1.75rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.service-type-card:hover .grab-icon {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 25px rgba(0, 177, 79, 0.3);
}

.service-type-card:hover .grab-motorcycle {
    animation: rideMotorcycle 1s ease-in-out infinite;
}

@keyframes rideMotorcycle {
    0% { transform: translateX(0) rotate(0); }
    25% { transform: translateX(4px) rotate(5deg); }
    75% { transform: translateX(-4px) rotate(-5deg); }
    100% { transform: translateX(0) rotate(0); }
}

/* Button Enhancements */
.service-type-card .btn {
    border-radius: 0.75rem;
    font-weight: 500;
    padding: 0.75rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    z-index: 2;
}

.service-type-card .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2),
        rgba(255, 255, 255, 0.1)
    );
    transform: translateX(-100%);
    transition: transform 0.4s ease;
}

.service-type-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.service-type-card .btn:hover::before {
    transform: translateX(0);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .service-type-card {
        padding: 1.25rem;
    }
    
    .service-type-card .sales-amount {
        font-size: 1.75rem;
    }
    
    .service-icon-wrapper {
        width: 3rem;
        height: 3rem;
    }
}

/* Cashier Performance Section Styling */
.cashier-performance-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.cashier-performance-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 1;
}

.cashier-performance-card:hover::before {
    opacity: 1;
}

.cashier-stats-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.cashier-stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.cashier-stats-card .rounded-circle {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.cashier-stats-card:hover .rounded-circle {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.cashier-stats-card i {
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.cashier-stats-card:hover i {
    transform: scale(1.1);
}

/* Enhanced Table Styling */
.cashier-table {
    border-collapse: separate;
    border-spacing: 0 0.5rem;
    margin-top: -0.5rem;
}

.cashier-table thead th {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.cashier-table tbody tr {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.cashier-table tbody tr:hover {
    transform: translateY(-3px) scale(1.01);
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.cashier-table td {
    padding: 1rem 1.5rem;
    border: none;
    vertical-align: middle;
}

.cashier-table td:first-child {
    border-top-left-radius: 0.75rem;
    border-bottom-left-radius: 0.75rem;
    font-weight: 500;
}

.cashier-table td:last-child {
    border-top-right-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
    text-align: center;
}

.cashier-table td:nth-child(2) {
    font-weight: 600;
    color: #8B4543;
}

.cashier-table td:nth-child(3) {
    font-weight: 500;
    color: #4A7C59;
    text-align: center;
}

/* Quantity Badge */
.qty-badge {
    background: rgba(74, 124, 89, 0.1);
    color: #4A7C59;
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    transition: all 0.3s ease;
}

.qty-badge:hover {
    background: rgba(74, 124, 89, 0.2);
    transform: translateY(-2px);
}

/* Item Name */
.item-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.item-name i {
    color: #8B4543;
    font-size: 1rem;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.cashier-table tr:hover .item-name i {
    opacity: 1;
    transform: scale(1.1);
}

/* Total Amount */
.total-amount {
    color: #8B4543;
    font-weight: 600;
    transition: all 0.3s ease;
}

.cashier-table tr:hover .total-amount {
    transform: scale(1.05);
    text-shadow: 1px 1px 2px rgba(139, 69, 67, 0.1);
}

/* Menu Items Section Styling */
.menu-section {
    padding: 2rem 0;
}

.menu-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.menu-header i {
    font-size: 1.5rem;
    color: #8B4543;
}

.menu-header h2 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #2D3436;
    margin: 0;
}

.menu-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.category-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 1;
}

.category-card:hover,
.category-card.active {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.category-card:hover::before,
.category-card.active::before {
    opacity: 1;
}

.category-icon {
    width: 3.5rem;
    height: 3.5rem;
    background: rgba(139, 69, 67, 0.1);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    transition: all 0.4s ease;
}

.category-card:hover .category-icon,
.category-card.active .category-icon {
    transform: scale(1.1) rotate(10deg);
    background: rgba(139, 69, 67, 0.2);
}

.category-icon i {
    font-size: 1.5rem;
    color: #8B4543;
    transition: all 0.4s ease;
}

.category-card:hover .category-icon i,
.category-card.active .category-icon i {
    transform: scale(1.1);
}

.category-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2D3436;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.category-card:hover .category-name,
.category-card.active .category-name {
    color: #8B4543;
}

.item-count {
    font-size: 0.875rem;
    color: #636E72;
    transition: all 0.3s ease;
}

.category-card:hover .item-count,
.category-card.active .item-count {
    color: #2D3436;
}

/* Menu Items Grid */
.menu-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.menu-item-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-item-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.item-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 1rem 1rem 0 0;
    transition: all 0.4s ease;
}

.menu-item-card:hover .item-image {
    transform: scale(1.05);
}

.item-details {
    padding: 1.5rem;
}

.item-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2D3436;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.menu-item-card:hover .item-name {
    color: #8B4543;
}

.item-description {
    font-size: 0.875rem;
    color: #636E72;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.item-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #8B4543;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.menu-item-card:hover .item-price {
    transform: scale(1.05);
    text-shadow: 2px 2px 4px rgba(139, 69, 67, 0.1);
}

.item-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(139, 69, 67, 0.1);
    padding: 0.5rem;
    border-radius: 0.75rem;
}

.qty-btn {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: white;
    border-radius: 0.5rem;
    color: #8B4543;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.qty-btn:hover {
    background: #8B4543;
    color: white;
    transform: scale(1.1);
}

.qty-input {
    width: 3rem;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 600;
    color: #2D3436;
}

.add-to-cart-btn {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border: none;
    background: linear-gradient(135deg, #8B4543, #723937);
    color: white;
    border-radius: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.add-to-cart-btn:hover {
    background: linear-gradient(135deg, #723937, #5E2F2D);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(139, 69, 67, 0.2);
}

.add-to-cart-btn i {
    font-size: 1.125rem;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover i {
    transform: scale(1.1);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .menu-categories {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }

    .menu-items-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .item-image {
        height: 180px;
    }

    .item-details {
        padding: 1.25rem;
    }

    .item-name {
        font-size: 1.125rem;
    }

    .item-price {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .menu-categories {
        grid-template-columns: repeat(2, 1fr);
    }

    .menu-items-grid {
        grid-template-columns: 1fr;
    }
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
        const tbody = $('#cashierPerformanceTable tbody');
        tbody.empty();

        if (response.cashiers && response.cashiers.length > 0) {
            response.cashiers.forEach(cashier => {
                tbody.append(`
                    <tr>
                        <td>
                            <div class="item-name d-flex align-items-center">
                                <img src="${cashier.profile_image}" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                ${cashier.name}
                            </div>
                        </td>
                        <td>${cashier.branch || '-'}</td>
                        <td><span class="badge ${cashier.is_active ? 'bg-success' : 'bg-secondary'}">${cashier.is_active ? 'Active' : 'Inactive'}</span></td>
                        <td>${cashier.transactions}</td>
                        <td>₱${formatCurrency(cashier.sales)}</td>
                        <td>${cashier.avg_time}</td>
                    </tr>
                `);
            });
        } else {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">No cashiers found for this period.</td></tr>');
        }
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

// Enhanced Cashier Performance Functions
function initializeCashierPerformanceEffects() {
    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('.cashier-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', () => {
            row.style.transform = 'translateY(-3px) scale(1.01)';
            row.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.12)';
            row.style.background = 'rgba(255, 255, 255, 0.9)';
            row.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        row.addEventListener('mouseleave', () => {
            row.style.transform = 'none';
            row.style.boxShadow = 'none';
            row.style.background = 'rgba(255, 255, 255, 0.7)';
        });
    });

    // Add glass effect to stats cards
    const statsCards = document.querySelectorAll('.cashier-stats-card');
    statsCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 12px 28px rgba(0, 0, 0, 0.12)';
            card.style.borderColor = 'rgba(255, 255, 255, 0.6)';
            
            // Animate icon
            const icon = card.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1.1) rotate(10deg)';
            }
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'none';
            card.style.boxShadow = 'none';
            card.style.borderColor = 'rgba(255, 255, 255, 0.4)';
            
            // Reset icon
            const icon = card.querySelector('i');
            if (icon) {
                icon.style.transform = 'none';
            }
        });
    });

    // Add smooth transitions to status badges
    const statusBadges = document.querySelectorAll('.cashier-status');
    statusBadges.forEach(badge => {
        badge.addEventListener('mouseenter', () => {
            badge.style.transform = 'translateY(-2px)';
            if (badge.classList.contains('status-active')) {
                badge.style.background = 'rgba(74, 222, 128, 0.3)';
            } else {
                badge.style.background = 'rgba(248, 113, 113, 0.3)';
            }
        });

        badge.addEventListener('mouseleave', () => {
            badge.style.transform = 'none';
            if (badge.classList.contains('status-active')) {
                badge.style.background = 'rgba(74, 222, 128, 0.2)';
            } else {
                badge.style.background = 'rgba(248, 113, 113, 0.2)';
            }
        });
    });
}

// Function to animate value changes
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    const isPrice = element.textContent.includes('₱');
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = start + (end - start) * easeOutQuart;
        
        // Format the number based on whether it's a price
        if (isPrice) {
            element.textContent = formatCurrency(current);
        } else {
            element.textContent = Math.round(current).toString();
        }
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Function to handle refresh button animation
function initializeRefreshButton() {
    const refreshBtn = document.getElementById('refreshCashierStats');
    const refreshIcon = refreshBtn.querySelector('i');
    let isRefreshing = false;

    refreshBtn.addEventListener('click', () => {
        if (!isRefreshing) {
            isRefreshing = true;
            refreshIcon.style.transform = 'rotate(360deg)';
            refreshBtn.disabled = true;
            refreshBtn.style.opacity = '0.7';

            // Simulate refresh delay
            setTimeout(() => {
                refreshIcon.style.transform = 'rotate(0deg)';
                refreshBtn.disabled = false;
                refreshBtn.style.opacity = '1';
                isRefreshing = false;
            }, 1000);
        }
    });
}

// Function to enhance cashier profile interactions
function enhanceCashierProfiles() {
    const profiles = document.querySelectorAll('.cashier-profile');
    profiles.forEach(profile => {
        const img = profile.querySelector('img');
        
        profile.addEventListener('mouseenter', () => {
            img.style.transform = 'scale(1.1)';
            img.style.borderColor = '#8B4543';
            profile.style.transform = 'translateX(5px)';
        });

        profile.addEventListener('mouseleave', () => {
            img.style.transform = 'none';
            img.style.borderColor = 'rgba(255, 255, 255, 0.8)';
            profile.style.transform = 'none';
        });
    });
}

// Function to add glass effect to the main container
function addGlassEffect() {
    const container = document.querySelector('.cashier-performance-card');
    
    container.addEventListener('mousemove', (e) => {
        const { left, top, width, height } = container.getBoundingClientRect();
        const x = (e.clientX - left) / width;
        const y = (e.clientY - top) / height;
        
        container.style.background = `
            linear-gradient(
                ${Math.atan2(y - 0.5, x - 0.5) * (180 / Math.PI)}deg,
                rgba(255, 255, 255, 0.8),
                rgba(255, 255, 255, 0.6)
            )
        `;
    });

    container.addEventListener('mouseleave', () => {
        container.style.background = 'rgba(255, 255, 255, 0.7)';
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

    $(document).on('click', '.btn-view-details', function() {
        const cashierId = $(this).data('id');
        showCashierDetails(cashierId);
    });

    // Add cashier performance to auto-refresh
    setInterval(updateCashierPerformance, 300000);

    // Initialize new enhancements
    initializeCashierPerformanceEffects();
    initializeRefreshButton();
    enhanceCashierProfiles();
    addGlassEffect();

    // Add animation when updating stats
    const oldUpdateCashierPerformance = updateCashierPerformance;
    updateCashierPerformance = function() {
        const oldValues = {
            activeCashiers: parseInt($('#activeCashiers').text()) || 0,
            totalTransactions: parseInt($('#totalTransactions').text()) || 0,
            avgTransactionTime: parseFloat($('#avgTransactionTime').text()) || 0,
            totalSales: parseFloat($('#totalCashierSales').text().replace(/[₱,]/g, '')) || 0
        };

        oldUpdateCashierPerformance.call(this);

        // Animate the changes after data is updated
        setTimeout(() => {
            const newValues = {
                activeCashiers: parseInt($('#activeCashiers').text()) || 0,
                totalTransactions: parseInt($('#totalTransactions').text()) || 0,
                avgTransactionTime: parseFloat($('#avgTransactionTime').text()) || 0,
                totalSales: parseFloat($('#totalCashierSales').text().replace(/[₱,]/g, '')) || 0
            };

            // Animate each value
            animateValue(document.getElementById('activeCashiers'), oldValues.activeCashiers, newValues.activeCashiers, 1000);
            animateValue(document.getElementById('totalTransactions'), oldValues.totalTransactions, newValues.totalTransactions, 1000);
            animateValue(document.getElementById('avgTransactionTime'), oldValues.avgTransactionTime, newValues.avgTransactionTime, 1000);
            animateValue(document.getElementById('totalCashierSales'), oldValues.totalSales, newValues.totalSales, 1000);
        }, 100);
    };

    // Update table headers if needed
    $('#cashierPerformanceTable thead tr').html(`
        <th>Cashier</th>
        <th>Branch</th>
        <th>Status</th>
        <th>Transactions</th>
        <th>Sales</th>
        <th>Avg. Time</th>
    `);
});

// Menu Items Interaction
document.addEventListener('DOMContentLoaded', function() {
    // Category Selection
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', () => {
            categoryCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        });
    });

    // Quantity Controls
    const quantityControls = document.querySelectorAll('.quantity-control');
    quantityControls.forEach(control => {
        const input = control.querySelector('.qty-input');
        const minusBtn = control.querySelector('.minus');
        const plusBtn = control.querySelector('.plus');

        minusBtn.addEventListener('click', () => {
            let value = parseInt(input.value);
            if (value > 0) {
                input.value = value - 1;
            }
        });

        plusBtn.addEventListener('click', () => {
            let value = parseInt(input.value);
            input.value = value + 1;
        });
    });

    // Add to Cart Animation
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.style.transform = 'scale(0)';
            this.style.width = '50px';
            this.textContent = '';
            this.appendChild(icon);
            icon.style.transform = 'scale(1.2)';
            
            setTimeout(() => {
                icon.style.transform = 'scale(1)';
                this.style.width = '';
                this.innerHTML = `
                    <i class="fas fa-shopping-cart"></i>
                    Add to Cart
                `;
            }, 1500);
        });
    });
});
</script>

<?php include('footer.php'); ?>