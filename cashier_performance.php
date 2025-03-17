<?php
require_once 'auth_function.php';
checkAdminLogin();

$page_title = "Cashier Performance";
include 'header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Cashier Performance Dashboard</h1>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Performance Overview</h5>
                    <div>
                        <select id="periodSelect" class="form-select form-select-sm">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6>Active Cashiers</h6>
                                    <h2 id="activeCashiers">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6>Total Transactions</h6>
                                    <h2 id="totalTransactions">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6>Avg. Transaction Time</h6>
                                    <h2 id="avgTransactionTime">0.0m</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6>Total Sales</h6>
                                    <h2 id="totalSales">₱0.00</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table id="cashierTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Cashier</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Transactions</th>
                                    <th>Sales</th>
                                    <th>Avg. Time</th>
                                    <th>Performance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
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
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="cashierImage" src="" alt="Cashier" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            <h4 id="cashierName"></h4>
                            <p id="cashierBranch" class="text-muted"></p>
                        </div>
                        <div class="col-md-8">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5>Performance Score: <span id="performanceScore"></span></h5>
                                    <div class="progress mb-3">
                                        <div id="performanceBar" class="progress-bar" role="progressbar"></div>
                                    </div>
                                    <div id="performanceMetrics"></div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Order Types</h6>
                                            <div id="orderTypesChart"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Payment Methods</h6>
                                            <div id="paymentMethodsChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('periodSelect');
    let cashierTable;

    function formatCurrency(amount) {
        return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function updateDashboard() {
        fetch(`get_cashier_performance.php?period=${periodSelect.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update summary cards
                    document.getElementById('activeCashiers').textContent = data.active_cashiers;
                    document.getElementById('totalTransactions').textContent = data.total_transactions;
                    document.getElementById('avgTransactionTime').textContent = data.avg_transaction_time;
                    document.getElementById('totalSales').textContent = formatCurrency(data.total_sales);

                    // Update table
                    if (cashierTable) {
                        cashierTable.clear();
                        data.cashiers.forEach(cashier => {
                            const row = [
                                `<div class="d-flex align-items-center">
                                    <img src="${cashier.profile_image}" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                    <span>${cashier.name}</span>
                                </div>`,
                                cashier.branch,
                                `<span class="badge ${cashier.is_active ? 'bg-success' : 'bg-secondary'}">${cashier.is_active ? 'Active' : 'Inactive'}</span>`,
                                cashier.transactions,
                                formatCurrency(cashier.sales),
                                cashier.avg_time,
                                `<div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar ${getPerformanceClass(cashier.performance_score)}" 
                                             style="width: ${cashier.performance_score}%"></div>
                                    </div>
                                    <span>${cashier.performance_score}</span>
                                </div>`,
                                `<button class="btn btn-sm btn-primary" onclick="showCashierDetails(${JSON.stringify(cashier)})">
                                    Details
                                </button>`
                            ];
                            cashierTable.row.add(row);
                        });
                        cashierTable.draw();
                    }
                }
            });
    }

    function getPerformanceClass(score) {
        if (score >= 95) return 'bg-success';
        if (score >= 85) return 'bg-info';
        if (score >= 75) return 'bg-warning';
        return 'bg-danger';
    }

    // Initialize DataTable
    cashierTable = $('#cashierTable').DataTable({
        order: [[6, 'desc']], // Sort by performance by default
        pageLength: 10,
        responsive: true
    });

    // Event listeners
    periodSelect.addEventListener('change', updateDashboard);

    // Initial load
    updateDashboard();
});

function showCashierDetails(cashier) {
    const modal = new bootstrap.Modal(document.getElementById('cashierDetailsModal'));
    
    // Update basic info
    document.getElementById('cashierImage').src = cashier.profile_image;
    document.getElementById('cashierName').textContent = cashier.name;
    document.getElementById('cashierBranch').textContent = cashier.branch;
    
    // Update performance score
    document.getElementById('performanceScore').textContent = `${cashier.performance_score} - ${cashier.performance_rating}`;
    const performanceBar = document.getElementById('performanceBar');
    performanceBar.style.width = `${cashier.performance_score}%`;
    performanceBar.className = `progress-bar ${getPerformanceClass(cashier.performance_score)}`;

    // Update performance metrics
    const metricsHtml = `
        <div class="row g-3">
            <div class="col-md-6">
                <p class="mb-1">Average Order Value Impact</p>
                <h6 class="${cashier.performance_metrics.avg_order_impact >= 0 ? 'text-success' : 'text-danger'}">
                    ${cashier.performance_metrics.avg_order_impact >= 0 ? '+' : ''}${cashier.performance_metrics.avg_order_impact} points
                </h6>
            </div>
            <div class="col-md-6">
                <p class="mb-1">Transaction Speed Impact</p>
                <h6 class="${cashier.performance_metrics.speed_impact >= 0 ? 'text-success' : 'text-danger'}">
                    ${cashier.performance_metrics.speed_impact >= 0 ? '+' : ''}${cashier.performance_metrics.speed_impact} points
                </h6>
            </div>
            <div class="col-md-6">
                <p class="mb-1">Sales Mix Impact</p>
                <h6 class="text-success">+${cashier.performance_metrics.sales_mix_impact} points</h6>
            </div>
            <div class="col-md-6">
                <p class="mb-1">Payment Method Diversity</p>
                <h6>${cashier.performance_metrics.payment_diversity}/3 methods used</h6>
            </div>
        </div>
    `;
    document.getElementById('performanceMetrics').innerHTML = metricsHtml;

    // Create charts
    createOrderTypesChart(cashier);
    createPaymentMethodsChart(cashier);

    modal.show();
}

function createOrderTypesChart(cashier) {
    const ctx = document.getElementById('orderTypesChart');
    if (window.orderTypesChart) {
        window.orderTypesChart.destroy();
    }
    
    window.orderTypesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Dine-in', 'Takeout', 'Delivery'],
            datasets: [{
                data: [
                    cashier.dine_in_orders,
                    cashier.takeout_orders,
                    cashier.delivery_orders
                ],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function createPaymentMethodsChart(cashier) {
    const ctx = document.getElementById('paymentMethodsChart');
    if (window.paymentMethodsChart) {
        window.paymentMethodsChart.destroy();
    }
    
    window.paymentMethodsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Card', 'E-Wallet'],
            datasets: [{
                data: [
                    cashier.cash_sales,
                    cashier.card_sales,
                    cashier.ewallet_sales
                ],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function getPerformanceClass(score) {
    if (score >= 95) return 'bg-success';
    if (score >= 85) return 'bg-info';
    if (score >= 75) return 'bg-warning';
    return 'bg-danger';
}
</script>

<?php include 'footer.php'; ?> 