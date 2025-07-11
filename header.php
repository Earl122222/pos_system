<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>More Bites</title>
        
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <!-- Inter Font -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Custom dashboard styles -->
        <link rel="stylesheet" href="styles/dashboard.css?v=<?php echo time(); ?>">
        
        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            // Check if CSS is loaded and initialize sidebar functionality
            window.onload = function() {
                const link = document.querySelector('link[href^="styles/dashboard.css"]');
                if (link) {
                    console.log('Dashboard CSS is linked');
                    // Force reload CSS
                    link.href = link.href.split('?')[0] + '?v=' + new Date().getTime();
                }
                
                // User dropdown functionality
                const userMenuBtn = document.querySelector('.user-menu');
                const userDropdown = document.querySelector('.dropdown-menu');
                
                if (userMenuBtn && userDropdown) {
                    // Toggle dropdown on button click
                    userMenuBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        userDropdown.classList.toggle('show');
                        userMenuBtn.setAttribute('aria-expanded', userDropdown.classList.contains('show'));
                    });
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                            userDropdown.classList.remove('show');
                            userMenuBtn.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
                
                // Sidebar toggle functionality
                const sidebarToggle = document.querySelector('.sidebar-toggle');
                const sidebar = document.querySelector('.app-sidebar');
                const appMain = document.querySelector('.app-main');
                
                if (sidebarToggle && sidebar && appMain) {
                    sidebarToggle.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Toggle collapsed class
                        sidebar.classList.toggle('collapsed');
                        
                        // Only close submenus, keep active states
                        document.querySelectorAll('.submenu').forEach(submenu => {
                            submenu.classList.remove('active');
                        });
                        
                        // Reset dropdown indicators
                        document.querySelectorAll('.dropdown-indicator').forEach(indicator => {
                            indicator.style.transform = 'translateY(-50%) rotate(0deg)';
                        });
                    });
                }
                
                // Handle active menu item
                const currentPath = window.location.pathname;
                const menuLinks = document.querySelectorAll('.menu-link');
                menuLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && currentPath.endsWith(href)) {
                        link.classList.add('active');
                        // If this is a submenu item, also activate its parent
                        const submenuWrapper = link.closest('.submenu-wrapper');
                        if (submenuWrapper) {
                            const parentLink = submenuWrapper.querySelector('.menu-link');
                            if (parentLink) {
                                parentLink.classList.add('active');
                                submenuWrapper.querySelector('.submenu').classList.add('active');
                            }
                        }
                    }
                });
            };
        </script>
        <style>
            /* Base resets */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                margin: 0;
                padding: 0;
                background-color: #f5f5f5;
            }

            /* Layout structure */
            .app-container {
                display: flex;
                min-height: 100vh;
                width: 100%;
                background-color: #f5f5f5;
            }

            .app-main {
                flex: 1;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                background-color: #f5f5f5;
            }

            .app-content {
                flex: 1;
                padding: 20px;
                background-color: #f5f5f5;
            }

            /* Container adjustments */
            .container-fluid {
                width: 100%;
                padding-right: 20px;
                padding-left: 20px;
                margin-right: auto;
                margin-left: auto;
            }

            /* Menu Items section */
            .menu-items-section {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
                overflow: hidden;
            }

            .menu-items-header {
                padding: 15px 20px;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
                background-color: #fff;
            }

            .menu-items-body {
                padding: 20px;
            }

            /* Order Details section */
            .order-details-section {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                padding: 20px;
                margin-top: 20px;
            }

            /* Header adjustments */
            .app-header {
                padding: 15px 20px;
                background: #fff;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
            }

            /* Existing styles continue below */
            .menu-link {
                position: relative;
                display: flex;
                align-items: center;
                padding: 12px 15px;
                text-decoration: none;
                color: rgba(255, 255, 255, 0.8);
                transition: all 0.3s ease;
            }

            .menu-link .dropdown-indicator {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 10px;
                color: rgba(255, 255, 255, 0.7);
                transition: all 0.3s ease;
            }

            .menu-link:hover .dropdown-indicator {
                color: rgba(255, 255, 255, 0.9);
            }

            .menu-link.active .dropdown-indicator {
                color: #ffffff;
                transform: translateY(-50%) rotate(180deg);
            }

            .menu-link i:not(.dropdown-indicator) {
                width: 20px;
                margin-right: 12px;
                text-align: center;
            }

            .menu-link span {
                flex: 1;
            }

            /* Hide dropdown icon when sidebar is collapsed */
            .app-sidebar.collapsed .dropdown-indicator {
                display: none;
            }

            /* Adjust padding when sidebar is collapsed */
            .app-sidebar.collapsed .menu-link {
                padding-right: 12px !important;
                justify-content: center;
            }

            .app-sidebar.collapsed .menu-link i:not(.dropdown-indicator) {
                margin-right: 0;
            }

            /* Submenu styles */
            .submenu-wrapper {
                position: relative;
            }

            .submenu {
                position: static;
                width: 100%;
                background: rgba(0, 0, 0, 0.1);
                list-style: none;
                padding: 0;
                margin: 0;
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .submenu.active {
                max-height: 1000px;
                opacity: 1;
                visibility: visible;
                padding: 8px;
            }

            .submenu-link {
                display: flex;
                align-items: center;
                padding: 10px 15px;
                color: rgba(255, 255, 255, 0.7);
                text-decoration: none;
                border-radius: 6px;
                transition: all 0.3s ease;
                font-size: 0.9em;
            }

            .submenu-link i {
                width: 20px;
                margin-right: 10px;
                font-size: 0.9em;
            }

            .submenu-link:hover {
                color: #fff;
                background: rgba(255, 255, 255, 0.1);
                padding-left: 20px;
            }

            /* Rotate dropdown indicator when active */
            .menu-link.active .dropdown-indicator {
                transform: translateY(-50%) rotate(180deg);
            }

            /* Hide submenu when sidebar is collapsed */
            .app-sidebar.collapsed .submenu {
                display: none;
            }

            /* Add these new styles */
            .menu-link.active {
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                position: relative;
            }

            .menu-link.active::after {
                content: '';
                position: absolute;
                left: 0;
                bottom: 0;
                width: 100%;
                height: 2px;
                background: #fff;
            }

            /* Adjust underline for collapsed state */
            .app-sidebar.collapsed .menu-link.active::after {
                width: 4px;
                height: 100%;
                top: 0;
                bottom: auto;
            }

            /* Keep active state visible in collapsed mode */
            .app-sidebar.collapsed .menu-link.active {
                background: rgba(255, 255, 255, 0.1);
            }

            #receiptContent {
                font-family: 'Inter', sans-serif;
                line-height: 1.6;
                color: #000;
            }

            #receiptContent h5 {
                margin-bottom: 20px;
                text-align: center;
            }

            #receiptContent .receipt-header,
            #receiptContent .receipt-footer {
                text-align: center;
                margin-bottom: 20px;
            }

            #receiptContent .receipt-details,
            #receiptContent .receipt-items {
                margin-bottom: 20px;
            }

            #receiptContent .receipt-details th,
            #receiptContent .receipt-details td,
            #receiptContent .receipt-items th,
            #receiptContent .receipt-items td {
                padding: 5px;
                text-align: left;
            }

            #receiptContent .receipt-total {
                font-weight: bold;
                text-align: right;
            }

            @media print {
                body * {
                    visibility: hidden;
                }
                #receiptContent, #receiptContent * {
                    visibility: visible;
                }
                #receiptContent {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 20px;
                    box-sizing: border-box;
                }
            }
        </style>
    </head>
    <body>
        <div class="app-container">
            <aside class="app-sidebar">
                <div class="sidebar-header">
                    <a href="dashboard.php" class="sidebar-brand">
                        <img src="asset/images/logo.png" alt="MoreBites" class="brand-logo">
                        <span>MoreBites</span>
                    </a>
                    <button class="sidebar-toggle">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
                <div class="sidebar-menu">
                    <nav id="mainMenu">
                        <ul class="menu-list">
                            <?php if ($_SESSION['user_type'] === 'Cashier'): ?>
                                <li class="menu-item">
                                    <a href="add_order.php" class="menu-link">
                                        <i class="fa-solid fa-cart-plus"></i>
                                        <span>Create Order</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="sales.php" class="menu-link">
                                        <i class="fa-solid fa-cash-register"></i>
                                        <span>Sales</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="order_history.php" class="menu-link">
                                        <i class="fa-solid fa-history"></i>
                                        <span>Order History</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="cashier_report.php" class="menu-link">
                                        <i class="fa-solid fa-chart-bar"></i>
                                        <span>My Performance</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="menu-item">
                                    <a href="<?php echo ($_SESSION['user_type'] === 'Stockman') ? 'stockman_dashboard.php' : 'dashboard.php'; ?>" class="menu-link">
                                        <i class="fa-solid fa-tachometer-alt"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <?php if(isset($_SESSION['user_type'])): ?>
                                    <?php if($_SESSION['user_type'] === 'Stockman'): ?>
                                        <li class="menu-item">
                                            <a href="request_stock.php" class="menu-link">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                <span>Request Stock</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if($_SESSION['user_type'] === 'Admin'): ?>
                                        <li class="menu-item">
                                            <a href="category.php" class="menu-link">
                                                <i class="fa-solid fa-th-list"></i>
                                                <span>Category</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <div class="submenu-wrapper">
                                                <a href="#" class="menu-link" onclick="toggleSubmenu(event, this)">
                                                    <i class="fa-solid fa-users"></i>
                                                    <span>User</span>
                                                    <i class="fa-solid fa-chevron-down dropdown-indicator"></i>
                                                </a>
                                                <ul class="submenu">
                                                    <li><a href="user.php" class="submenu-link"><i class="fa-solid fa-users-gear"></i>Manage Users</a></li>
                                                    <li><a href="add_user.php?role=cashier" class="submenu-link"><i class="fa-solid fa-cash-register"></i>Add Cashier</a></li>
                                                    <li><a href="add_user.php?role=stockman" class="submenu-link"><i class="fa-solid fa-boxes"></i>Add Stockman</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="menu-item">
                                            <a href="ingredients.php" class="menu-link">
                                                <i class="fa-solid fa-mortar-pestle"></i>
                                                <span>Ingredients</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="ingredient_requests.php" class="menu-link">
                                                <i class="fa-solid fa-clipboard-list"></i>
                                                <span>List of Request</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="product.php" class="menu-link">
                                                <i class="fa-solid fa-utensils"></i>
                                                <span>Product</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <div class="submenu-wrapper">
                                                <a href="#" class="menu-link" onclick="toggleSubmenu(event, this)">
                                                    <i class="fa-solid fa-store"></i>
                                                    <span>Branch</span>
                                                    <i class="fa-solid fa-chevron-down dropdown-indicator"></i>
                                                </a>
                                                <ul class="submenu">
                                                    <li><a href="add_branch.php" class="submenu-link"><i class="fa-solid fa-plus"></i>Add Branch</a></li>
                                                    <li><a href="branch_details.php" class="submenu-link"><i class="fa-solid fa-info-circle"></i>Branch Details</a></li>
                                                    <li><a href="branch_overview.php" class="submenu-link"><i class="fa-solid fa-chart-bar"></i>Branch Overview</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="menu-item">
                                            <a href="branch_sales.php?branch=main" class="menu-link">
                                                <i class="fa-solid fa-chart-line"></i>
                                                <span>Sales</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if($_SESSION['user_type'] === 'Cashier'): ?>
                                        <li class="menu-item">
                                            <a href="add_order.php" class="menu-link">
                                                <i class="fa-solid fa-cart-plus"></i>
                                                <span>Create Order</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="order.php" class="menu-link">
                                                <i class="fa-solid fa-history"></i>
                                                <span>Order History</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="cashier_report.php" class="menu-link">
                                                <i class="fa-solid fa-chart-line"></i>
                                                <span>My Performance</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </aside>

            <div class="app-main">
                <header class="app-header">
                    <div class="greeting">
                        <div class="welcome-text" id="greeting-text">
                        </div>
                    </div>
                    <div class="header-actions">
                        <div id="realtime-clock" class="realtime-clock">
                            <span id="current-date"></span>
                            <span id="current-time"></span>
                        </div>
                        <button class="header-action-btn">
                            <i class="fa-solid fa-bell"></i>
                            <span class="badge bg-danger">3</span>
                        </button>
                        <button class="header-action-btn">
                            <i class="fa-solid fa-envelope"></i>
                            <span class="badge bg-danger">5</span>
                        </button>
                        <div class="dropdown">
                            <button class="user-menu" type="button" aria-expanded="false">
                                <i class="fa-solid fa-user"></i>
                                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?></span>
                                <i class="fa-solid fa-chevron-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item" href="user_profile.php">
                                        <i class="fa-solid fa-user-circle"></i>
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="change_password.php">
                                        <i class="fa-solid fa-key"></i>
                                        <span>Change Password</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fa-solid fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <main class="app-content">
                    <div class="container-fluid px-4 mb-4">

<script>
function updateGreeting() {
    const now = new Date();
    const hour = now.getHours();
    let greeting = '';
    
    if (hour >= 5 && hour < 12) {
        greeting = 'Good Morning';
    } else if (hour >= 12 && hour < 17) {
        greeting = 'Good Afternoon';
    } else {
        greeting = 'Good Evening';
    }
    
    const username = '<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?>';
    document.getElementById('greeting-text').innerHTML = greeting + ', <span class="username">' + username + '</span>';
}

function updateClock() {
    const timeElement = document.getElementById('current-time');
    const dateElement = document.getElementById('current-date');
    const now = new Date();
    
    // Format time
    const time = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit',
        hour12: true 
    });
    
    // Format date
    const date = now.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
    
    dateElement.textContent = date;
    timeElement.textContent = time;
    updateGreeting(); // Update greeting with current time
}

// Update clock and greeting immediately and then every second
updateClock();
setInterval(updateClock, 1000);
</script>

<script>
    // Toggle submenu function
    function toggleSubmenu(event, element) {
        event.preventDefault();
        event.stopPropagation();
        
        const submenu = element.nextElementSibling;
        const isMenuClick = event.target === element || event.target.parentElement === element;
        
        if (isMenuClick) {
            // Toggle current submenu
            element.classList.toggle('active');
            submenu.classList.toggle('active');
            
            // Close other submenus
            document.querySelectorAll('.submenu-wrapper').forEach(wrapper => {
                if (wrapper !== element.parentElement) {
                    wrapper.querySelector('.menu-link').classList.remove('active');
                    wrapper.querySelector('.submenu').classList.remove('active');
                }
            });
        }
        
        // Keep active states for current page
        const currentPath = window.location.pathname;
        submenu.querySelectorAll('.submenu-link').forEach(link => {
            if (link.getAttribute('href') && currentPath.endsWith(link.getAttribute('href'))) {
                link.classList.add('active');
                element.classList.add('active');
                submenu.classList.add('active');
            }
        });
    }
    
    // Add this to window.onload
    window.addEventListener('load', function() {
        // Prevent document click from closing submenu
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.submenu-wrapper')) {
                // Close all submenus when clicking outside
                document.querySelectorAll('.menu-link, .submenu').forEach(el => {
                    el.classList.remove('active');
                });
            }
        });
    });
</script>

<!-- Modal Structure -->
<div id="receiptModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Receipt</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Receipt details will be inserted here -->
        <div id="receiptContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function showReceipt(orderData) {
  const receiptHTML = `
    <div class="receipt-header">
      <h5>MORE BITES</h5>
      <p>123 Main St<br>Email: admin@restaurant.com<br>Tel: 123-456-7890</p>
    </div>
    <div class="receipt-details">
      <table class="table">
        <tr><th>Order #:</th><td>${orderData.order_number}</td></tr>
        <tr><th>Date:</th><td>${new Date().toLocaleString()}</td></tr>
        <tr><th>Service:</th><td>${orderData.service_type}</td></tr>
        <tr><th>Payment:</th><td>${orderData.payment_method}</td></tr>
      </table>
    </div>
    <div class="receipt-items">
      <table class="table">
        <thead>
          <tr><th>Item</th><th>Amount</th></tr>
        </thead>
        <tbody>
          ${orderData.items.map(item => `
            <tr>
              <td>${item.name} x ${item.quantity}</td>
              <td>₱${item.total.toFixed(2)}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
    <div class="receipt-total">
      <p>Subtotal: ₱${orderData.subtotal.toFixed(2)}</p>
      <p>Tax: ₱${orderData.tax.toFixed(2)}</p>
      <p>Total: ₱${orderData.total.toFixed(2)}</p>
    </div>
    <div class="receipt-footer">
      <p>Thank you for dining with us!<br>Please come again</p>
    </div>
  `;

  document.getElementById('receiptContent').innerHTML = receiptHTML;
  $('#receiptModal').modal('show');
}

function printReceipt() {
  const printContents = document.getElementById('receiptContent').innerHTML;
  const originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
  location.reload();
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Check if the current page is the "Create Order" page
  if (window.location.pathname.includes('create_order.php')) {
    // Hide the menu
    document.getElementById('mainMenu').style.display = 'none';
  } else {
    // Show the menu on other pages
    document.getElementById('mainMenu').style.display = 'block';
  }
});
</script>