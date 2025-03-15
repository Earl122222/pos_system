<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$categorySql = "SELECT COUNT(*) FROM pos_category WHERE category_status = 'Active'";
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
                           WHERE c.category_status = 'Active'
                           GROUP BY c.category_name")
                  ->fetchAll(PDO::FETCH_ASSOC);

$confData = getConfigData($pdo);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mb-4">Dashboard Overview</h1>
    
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">Total Categories</div>
                <div class="stat-card-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $total_category; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">Total Products</div>
                <div class="stat-card-icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $total_product; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">Total Users</div>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $total_user; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">Total Branches</div>
                <div class="stat-card-icon">
                    <i class="fas fa-store"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $total_branch; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">Total Revenue</div>
                <div class="stat-card-icon">
                    <i class="fas fa-peso-sign"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $confData['currency'] . number_format(floatval($total_sales), 2); ?></div>
        </div>
    </div>

    <div class="charts-section">
        <div class="chart-card">
            <div class="chart-header">
                <h2 class="chart-title">Most Purchased Products</h2>
                <div class="chart-filters">
                    <select id="periodFilter" class="form-select">
                        <option value="day">Daily</option>
                        <option value="month">Monthly</option>
                        <option value="year">Yearly</option>
                    </select>
                    <input type="date" id="dateFilter" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="chart-body">
                <div class="chart-container">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h2 class="chart-title">Product Distribution</h2>
            </div>
            <div class="chart-body">
                <div class="chart-container">
                    <canvas id="productsPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.charts-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-top: 2rem;
}

.chart-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.chart-header {
    margin-bottom: 1rem;
}

.chart-title {
    font-size: 1.25rem;
    color: #2c3e50;
    margin: 0;
}

.chart-body {
    position: relative;
    width: 100%;
    height: 400px;
}

.chart-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.chart-filters {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.chart-filters select,
.chart-filters input {
    padding: 0.5rem;
    border: 1px solid rgba(168, 102, 102, 0.2);
    border-radius: 8px;
    font-size: 0.9rem;
    color: #2c3e50;
    background-color: white;
    transition: all 0.3s ease;
}

.chart-filters select:focus,
.chart-filters input:focus {
    outline: none;
    border-color: var(--color-600);
    box-shadow: 0 0 0 2px rgba(168, 102, 102, 0.1);
}

.chart-filters select:hover,
.chart-filters input:hover {
    border-color: var(--color-400);
}

@media (max-width: 1024px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .chart-body {
        height: 350px;
    }
}
</style>

<script>
// Register Chart.js plugins
Chart.register(ChartDataLabels);

// Initialize charts as global variables
let productsChart = null;
let pieChart = null;

// Chart configuration
const chartConfig = {
    type: 'line',
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)',
                    drawBorder: false
                },
                ticks: {
                    color: '#2c3e50',
                    font: {
                        size: 12,
                        family: 'Inter'
                    },
                    callback: function(value) {
                        return Math.round(value); // Show whole numbers only
                    }
                },
                title: {
                    display: true,
                    text: 'Quantity Sold',
                    color: '#2c3e50',
                    font: {
                        size: 14,
                        family: 'Inter',
                        weight: 'bold'
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#2c3e50',
                    font: {
                        size: 12,
                        family: 'Inter'
                    },
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    color: '#2c3e50',
                    font: {
                        size: 12,
                        family: 'Inter'
                    },
                    boxWidth: 15,
                    usePointStyle: true
                }
            },
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(44, 62, 80, 0.9)',
                titleFont: {
                    size: 14,
                    family: 'Inter',
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13,
                    family: 'Inter'
                },
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + ' units sold';
                    }
                }
            },
            datalabels: {
                display: false // Disable datalabels for the line chart
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        },
        animation: {
            duration: 750,
            easing: 'easeInOutQuart'
        }
    }
};

// Function to load product statistics
function loadProductStats() {
    const period = document.getElementById('periodFilter').value;
    const date = document.getElementById('dateFilter').value;

    // Show loading state
    if (productsChart) {
        productsChart.data.datasets.forEach(dataset => {
            dataset.data = dataset.data.map(() => null);
        });
        productsChart.update('none');
    }

    fetch(`get_product_stats.php?period=${period}&start_date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (!productsChart) {
                // Initial chart creation
                const ctx = document.getElementById('productsChart').getContext('2d');
                chartConfig.data = data;
                productsChart = new Chart(ctx, chartConfig);
            } else {
                // Update existing chart
                productsChart.data.labels = data.labels;
                productsChart.data.datasets = data.datasets;
                productsChart.update();
            }
        })
        .catch(error => {
            console.error('Error loading product stats:', error);
            if (productsChart) {
                // Clear data on error
                productsChart.data.datasets.forEach(dataset => {
                    dataset.data = [];
                });
                productsChart.update();
            }
        });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add debounced event listeners for filters
const debouncedLoadProductStats = debounce(loadProductStats, 300);

document.getElementById('periodFilter').addEventListener('change', debouncedLoadProductStats);
document.getElementById('dateFilter').addEventListener('change', debouncedLoadProductStats);

// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load initial product stats
    loadProductStats();

    // Initialize pie chart
    const categoryLabels = <?php echo json_encode(array_column($categories, 'category_name')); ?>;
    const categoryData = <?php echo json_encode(array_column($categories, 'total')); ?>;
    const backgroundColors = [
        'rgba(231, 76, 60, 0.8)',   // Red
        'rgba(52, 152, 219, 0.8)',  // Blue
        'rgba(46, 204, 113, 0.8)',  // Green
        'rgba(241, 196, 15, 0.8)',  // Yellow
        'rgba(155, 89, 182, 0.8)',  // Purple
        'rgba(52, 73, 94, 0.8)',    // Dark Blue
        'rgba(230, 126, 34, 0.8)',  // Orange
        'rgba(149, 165, 166, 0.8)'  // Gray
    ];

    const pieCtx = document.getElementById('productsPieChart').getContext('2d');
    pieChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: "Products per Category",
                data: categoryData,
                backgroundColor: backgroundColors.slice(0, categoryLabels.length),
                borderColor: '#ffffff',
                borderWidth: 2,
                hoverBackgroundColor: backgroundColors.map(color => color.replace('0.8', '1')),
                hoverBorderColor: '#ffffff',
                hoverBorderWidth: 4,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#2c3e50',
                        font: {
                            size: 12,
                            family: 'Inter'
                        },
                        padding: 20,
                        boxWidth: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(44, 62, 80, 0.95)',
                    titleFont: {
                        size: 14,
                        family: 'Inter',
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13,
                        family: 'Inter'
                    },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value * 100) / total).toFixed(1);
                            return `${label}: ${value} products (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    formatter: function(value, context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value * 100) / total).toFixed(1);
                        return percentage + '%';
                    },
                    display: function(context) {
                        return context.dataset.data[context.dataIndex] > 0;
                    }
                }
            },
            cutout: '60%',
            layout: {
                padding: 20
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });
});
</script>

<?php include('footer.php'); ?>