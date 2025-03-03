<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$categorySql = "SELECT COUNT(*) FROM pos_category WHERE category_status = 'Active'";
$productSql = "SELECT COUNT(*) FROM pos_product";
$userSql = "SELECT COUNT(*) FROM pos_user";
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
                <div class="stat-card-title">Total Revenue</div>
                <div class="stat-card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $confData['currency'] . number_format(floatval($total_sales), 2); ?></div>
        </div>
    </div>

    <div class="charts-section">
        <div class="chart-card">
            <div class="chart-header">
                <h2 class="chart-title">Revenue Trends</h2>
            </div>
            <div class="chart-body">
                <canvas id="salesLineChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h2 class="chart-title">Product Distribution</h2>
            </div>
            <div class="chart-body">
                <canvas id="productsPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Register Chart.js plugins
Chart.register(ChartDataLabels);

// Sales Line Chart
const salesData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    datasets: [{
        label: 'Total Sales',
        data: [1000, 2000, 1500, 3000, 2500, 4000, 4500],
        backgroundColor: 'rgba(231, 76, 60, 0.1)',
        borderColor: '#e74c3c',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#e74c3c',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 10,
        pointHoverBackgroundColor: '#e74c3c',
        pointHoverBorderColor: '#ffffff',
        pointHoverBorderWidth: 3
    }]
};

// Create Sales Line Chart
const salesChart = new Chart(document.getElementById('salesLineChart'), {
    type: 'line',
    data: salesData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
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
                        return '<?php echo $confData['currency']; ?>' + value.toLocaleString();
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
                    }
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
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Revenue: <?php echo $confData['currency']; ?>' + context.parsed.y.toLocaleString();
                    }
                }
            },
            datalabels: {
                color: '#2c3e50',
                anchor: 'end',
                align: 'top',
                formatter: function(value) {
                    return '<?php echo $confData['currency']; ?>' + value.toLocaleString();
                },
                font: {
                    weight: 'bold'
                },
                display: function(context) {
                    return context.dataIndex === context.dataset.data.length - 1;
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        },
        animation: {
            duration: 1000,
            easing: 'easeInOutQuart'
        },
        hover: {
            mode: 'index',
            intersect: false
        }
    }
});

// Products Pie Chart
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

// Create Products Pie Chart
const pieChart = new Chart(document.getElementById('productsPieChart'), {
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
            duration: 1000,
            easing: 'easeInOutQuart'
        }
    }
});
</script>

<?php include('footer.php'); ?>