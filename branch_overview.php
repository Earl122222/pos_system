<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Get all active branches
$stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">
        | Branch Overview
    </h1>

    <!-- Branch Cards -->
    <div class="row" id="branchCards">
        <?php foreach ($branches as $branch): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card branch-card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo htmlspecialchars($branch['branch_name']); ?></h5>
                        <span class="badge bg-info"><?php echo htmlspecialchars($branch['branch_code']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sales Summary -->
                    <div class="sales-summary mb-4">
                        <h6 class="text-muted mb-3">Today's Performance</h6>
                        <div class="operating-status mb-2" id="status-<?php echo $branch['branch_id']; ?>">
                            <span class="badge bg-secondary">Checking status...</span>
                        </div>
                        <div class="active-cashiers mb-2" id="cashiers-<?php echo $branch['branch_id']; ?>">
                            <small class="text-muted">Active Cashiers: </small>
                            <span class="cashier-list">Loading...</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card bg-primary text-white p-3 rounded">
                                    <div class="stat-label">Sales</div>
                                    <div class="stat-value" id="sales-<?php echo $branch['branch_id']; ?>">₱0.00</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card bg-success text-white p-3 rounded">
                                    <div class="stat-label">Orders</div>
                                    <div class="stat-value" id="orders-<?php echo $branch['branch_id']; ?>">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Alerts -->
                    <div class="inventory-alerts">
                        <h6 class="text-muted mb-3">Inventory Status</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <div>
                                            <div class="alert-label">Low Stock</div>
                                            <div class="alert-value" id="lowstock-<?php echo $branch['branch_id']; ?>">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="alert alert-danger mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock me-2"></i>
                                        <div>
                                            <div class="alert-label">Expiring</div>
                                            <div class="alert-value" id="expiring-<?php echo $branch['branch_id']; ?>">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-primary view-sales-btn" data-branch-id="<?php echo $branch['branch_id']; ?>" data-branch-name="<?php echo htmlspecialchars($branch['branch_name']); ?>">
                            <i class="fas fa-chart-line me-1"></i> View Sales
                        </button>
                        <a href="branch_inventory.php?branch=<?php echo $branch['branch_id']; ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-boxes me-1"></i> View Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Sales Trend Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Comparison</h5>
                    <select id="trendPeriod" class="form-select form-select-sm" style="width: auto;">
                        <option value="daily">Last 7 Days</option>
                        <option value="weekly">Last 4 Weeks</option>
                        <option value="monthly">Last 6 Months</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="salesComparisonChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Comparison Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">Branch Performance Comparison</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="date-filter d-flex gap-2">
                                <input type="date" id="startDate" class="form-control form-control-sm">
                                <input type="date" id="endDate" class="form-control form-control-sm">
                            </div>
                            <select id="comparisonPeriod" class="form-select form-select-sm" style="width: auto;">
                                <option value="custom">Custom Range</option>
                                <option value="daily" selected>Today</option>
                                <option value="weekly">This Week</option>
                                <option value="monthly">This Month</option>
                                <option value="yearly">This Year</option>
                            </select>
                            <button id="applyFilter" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="branchComparisonTable">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Orders</th>
                                    <th class="text-end">Total Sales</th>
                                    <th class="text-end">Average Sale</th>
                                    <th>Top Products</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Comparison data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Modal -->
<div class="modal fade" id="branchSalesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="stats-cards">
                    <div class="stat-card bg-gradient-primary">
                        <div class="stat-card-inner">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value" id="modalTotalOrders">0</h4>
                                <div class="stat-label">Total Orders Today</div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-gradient-success">
                        <div class="stat-card-inner">
                            <div class="stat-icon">
                                <i class="fas fa-peso-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value" id="modalTotalSales">₱0.00</h4>
                                <div class="stat-label">Total Sales Today</div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-gradient-warning">
                        <div class="stat-card-inner">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value" id="modalAverageSale">₱0.00</h4>
                                <div class="stat-label">Average Sale Today</div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-gradient-info">
                        <div class="stat-card-inner">
                            <div class="stat-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value" id="modalHighestSale">₱0.00</h4>
                                <div class="stat-label">Highest Sale Today</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">Sales Trend</h2>
                            <div class="chart-filters">
                                <select id="modalTrendPeriod" class="form-select form-select-sm">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="modalSalesTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">Payment Methods</h2>
                        </div>
                        <div class="chart-body">
                            <canvas id="modalPaymentMethodsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.branch-card {
    transition: transform 0.2s ease;
}

.branch-card:hover {
    transform: translateY(-5px);
}

.stat-card {
    border-radius: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.alert-label {
    font-size: 0.75rem;
    opacity: 0.9;
}

.alert-value {
    font-size: 1.125rem;
    font-weight: 600;
}

.card-footer {
    background: none;
    border-top: 1px solid rgba(0,0,0,0.05);
    padding: 1rem;
}

.form-select-sm {
    font-size: 0.875rem;
    padding: 0.25rem 2rem 0.25rem 0.5rem;
}

.operating-status {
    text-align: center;
    margin-bottom: 1rem;
}

.operating-status .badge {
    font-size: 0.85rem;
    padding: 0.5em 1em;
}

.active-cashiers {
    font-size: 0.9rem;
    padding: 0.5rem 0;
}

.active-cashiers .cashier-list {
    font-weight: 500;
}

.branch-closed .stat-card {
    opacity: 0.7;
    background-color: #6c757d !important;
}

.modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, var(--color-600) 0%, var(--color-800) 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

.modal-title {
    font-weight: 500;
}

.btn-close {
    filter: brightness(0) invert(1);
}

.modal .card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    height: 100%;
}

.modal .card-title {
    color: var(--primary-color);
}

.comparison-rank {
    font-size: 1.25rem;
    font-weight: 600;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f8f9fa;
}

.rank-1 {
    background-color: #ffd700;
    color: #000;
}

.rank-2 {
    background-color: #c0c0c0;
    color: #000;
}

.rank-3 {
    background-color: #cd7f32;
    color: #fff;
}

.top-products-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.top-products-list li {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.top-products-list li:last-child {
    margin-bottom: 0;
}

.product-quantity {
    color: #6c757d;
    font-size: 0.8125rem;
}

.date-filter {
    display: none;
}

.date-filter.show {
    display: flex !important;
}

.form-control-sm, .form-select-sm {
    height: 31px;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
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

/* Chart Card Styles */
.chart-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.chart-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.chart-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-title {
    font-size: 1.25rem;
    color: #2c3e50;
    margin: 0;
    font-weight: 600;
}

.chart-body {
    padding: 1.25rem;
    height: 300px;
    position: relative;
}

.chart-filters {
    display: flex;
    gap: 1rem;
}

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

/* Responsive Adjustments */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        font-size: 2rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .chart-card {
        margin-bottom: 1rem;
    }
    
    .chart-body {
        height: 250px;
    }
}
</style>

<script>
$(document).ready(function() {
    let modalSalesChart;
    let modalPaymentChart;
    let currentBranchId;
    let updateTimer;

    // Initialize modal charts
    function initializeModalCharts() {
        const salesCtx = document.getElementById('modalSalesTrendChart').getContext('2d');
        modalSalesChart = new Chart(salesCtx, {
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

        const paymentCtx = document.getElementById('modalPaymentMethodsChart').getContext('2d');
        modalPaymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'Credit Card', 'E-Wallet'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#4A7C59', '#C4804D', '#8B4543']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Function to update branch statistics
    function updateBranchStats() {
        <?php foreach ($branches as $branch): ?>
        $.ajax({
            url: 'get_branch_stats.php',
            data: { branch_id: <?php echo $branch['branch_id']; ?> },
            success: function(response) {
                const branchCard = $('#sales-<?php echo $branch['branch_id']; ?>').closest('.branch-card');
                const statusBadge = $('#status-<?php echo $branch['branch_id']; ?>');
                const cashiersList = $('#cashiers-<?php echo $branch['branch_id']; ?> .cashier-list');
                
                if (response.is_operating) {
                    statusBadge.html('<span class="badge bg-success">Currently Operating</span>');
                    branchCard.find('.stat-card').parent().removeClass('branch-closed');
                } else {
                    let statusText = 'Closed';
                    if (!response.has_active_cashiers) {
                        statusText = 'No Active Cashiers';
                    }
                    statusBadge.html('<span class="badge bg-secondary">' + statusText + '</span>');
                    branchCard.find('.stat-card').parent().addClass('branch-closed');
                }

                // Update active cashiers list
                if (response.active_cashiers > 0) {
                    let cashierText = response.active_usernames.join(', ');
                    cashiersList.html('<span class="text-success">' + cashierText + '</span>');
                } else {
                    cashiersList.html('<span class="text-muted">No active cashiers</span>');
                }

                $('#sales-<?php echo $branch['branch_id']; ?>').text('₱' + response.today_sales.toLocaleString());
                $('#orders-<?php echo $branch['branch_id']; ?>').text(response.today_orders);
                $('#lowstock-<?php echo $branch['branch_id']; ?>').text(response.low_stock_count);
                $('#expiring-<?php echo $branch['branch_id']; ?>').text(response.expiring_count);

                // Update modal data if this is the current branch being viewed
                if (currentBranchId === <?php echo $branch['branch_id']; ?> && $('#branchSalesModal').is(':visible')) {
                    updateModalData(<?php echo $branch['branch_id']; ?>);
                }
            }
        });
        <?php endforeach; ?>
    }

    // Function to update modal data
    function updateModalData(branchId) {
        $.ajax({
            url: 'get_branch_sales_data.php',
            data: { 
                branch_id: branchId,
                period: $('#modalTrendPeriod').val()
            },
            success: function(response) {
                $('#modalTotalOrders').text(response.today_stats.total_orders);
                $('#modalTotalSales').text('₱' + response.today_stats.total_sales.toLocaleString());
                $('#modalAverageSale').text('₱' + response.today_stats.average_sale.toLocaleString());
                $('#modalHighestSale').text('₱' + response.today_stats.highest_sale.toLocaleString());

                // Update sales trend chart
                modalSalesChart.data.labels = response.sales_trend.labels;
                modalSalesChart.data.datasets[0].data = response.sales_trend.data;
                modalSalesChart.update();

                // Update payment methods chart
                modalPaymentChart.data.datasets[0].data = [
                    response.payment_methods.cash,
                    response.payment_methods.credit_card,
                    response.payment_methods.e_wallet
                ];
                modalPaymentChart.update();
            }
        });
    }

    // Initialize charts when document is ready
    initializeModalCharts();

    // Handle view sales button click
    $('.view-sales-btn').click(function() {
        const branchId = $(this).data('branch-id');
        const branchName = $(this).data('branch-name');
        currentBranchId = branchId;
        
        // Update modal title
        $('.modal-title').text(branchName + ' Sales Report');
        
        // Show modal
        $('#branchSalesModal').modal('show');
        
        // Update modal data
        updateModalData(branchId);
        
        // Clear existing timer and start new one
        if (updateTimer) clearInterval(updateTimer);
        updateTimer = setInterval(() => updateModalData(branchId), 60000); // Update every minute
    });

    // Handle modal period change
    $('#modalTrendPeriod').change(function() {
        if (currentBranchId) {
            updateModalData(currentBranchId);
        }
    });

    // Handle modal close
    $('#branchSalesModal').on('hidden.bs.modal', function() {
        if (updateTimer) clearInterval(updateTimer);
        currentBranchId = null;
    });

    // Initial load and refresh timer
    updateBranchStats();
    setInterval(updateBranchStats, 30000); // Update every 30 seconds

    // Set default dates
    const today = new Date();
    const firstDayOfYear = new Date(today.getFullYear(), 0, 1);
    $('#startDate').val(firstDayOfYear.toISOString().split('T')[0]);
    $('#endDate').val(today.toISOString().split('T')[0]);

    // Handle period change
    $('#comparisonPeriod').change(function() {
        const period = $(this).val();
        const dateFilter = $('.date-filter');
        
        if (period === 'custom') {
            dateFilter.addClass('show');
        } else {
            dateFilter.removeClass('show');
            updateBranchComparison();
        }
    });

    // Handle apply filter button
    $('#applyFilter').click(function() {
        updateBranchComparison();
    });

    // Function to update branch comparison table
    function updateBranchComparison() {
        const period = $('#comparisonPeriod').val();
        const data = {
            period: period
        };

        if (period === 'custom') {
            data.start_date = $('#startDate').val();
            data.end_date = $('#endDate').val();
        }

        $.ajax({
            url: 'get_branch_comparison.php',
            data: data,
            success: function(response) {
                if (response.success) {
                    let tableBody = '';
                    response.data.forEach((branch, index) => {
                        const rank = index + 1;
                        const rankClass = rank <= 3 ? `rank-${rank}` : '';
                        
                        let status = 'Currently Operating';
                        let statusClass = 'bg-success';
                        
                        if (branch.active_cashiers === 0) {
                            status = 'No Active Cashiers';
                            statusClass = 'bg-secondary';
                        }

                        let topProductsHtml = '<ul class="top-products-list">';
                        branch.top_products.forEach(product => {
                            topProductsHtml += `
                                <li>${product.product_name} 
                                    <span class="product-quantity">(${product.total_quantity} units - ₱${product.total_revenue.toLocaleString()})</span>
                                </li>`;
                        });
                        topProductsHtml += '</ul>';

                        tableBody += `
                            <tr>
                                <td>
                                    <div class="comparison-rank ${rankClass}">${rank}</div>
                                </td>
                                <td>
                                    <strong>${branch.branch_name}</strong><br>
                                    <small class="text-muted">${branch.branch_code}</small>
                                </td>
                                <td><span class="badge ${statusClass}">${status}</span></td>
                                <td class="text-end">${branch.total_orders.toLocaleString()}</td>
                                <td class="text-end">₱${branch.total_sales.toLocaleString()}</td>
                                <td class="text-end">₱${branch.average_sale.toLocaleString()}</td>
                                <td>${topProductsHtml}</td>
                            </tr>`;
                    });
                    
                    $('#branchComparisonTable tbody').html(tableBody);
                }
            }
        });
    }
});
</script>

<?php include('footer.php'); ?> 
<?php include('footer.php'); ?> 