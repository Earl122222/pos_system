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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        
        <!-- Inter Font -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Custom dashboard styles -->
        <link rel="stylesheet" href="styles/dashboard.css?v=<?php echo time(); ?>">
        
        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        
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
                        sidebar.classList.toggle('collapsed');
                    });
                }
                
                // Handle active menu item
                const currentPath = window.location.pathname;
                const menuLinks = document.querySelectorAll('.menu-link');
                menuLinks.forEach(link => {
                    if (link.getAttribute('href') === currentPath.split('/').pop()) {
                        link.classList.add('active');
                    }
                });
            };
        </script>
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
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu-list">
                        <li class="menu-item">
                            <a href="dashboard.php" class="menu-link">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'): ?>
                        <li class="menu-item">
                            <a href="category.php" class="menu-link">
                                <i class="fas fa-th-list"></i>
                                <span>Category</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="user.php" class="menu-link">
                                <i class="fas fa-users"></i>
                                <span>User</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="ingredients.php" class="menu-link">
                                <i class="fas fa-mortar-pestle"></i>
                                <span>Ingredients</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="product.php" class="menu-link">
                                <i class="fas fa-utensils"></i>
                                <span>Product</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="menu-item">
                            <a href="add_order.php" class="menu-link">
                                <i class="fas fa-cart-plus"></i>
                                <span>Create Order</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="order.php" class="menu-link">
                                <i class="fas fa-history"></i>
                                <span>Order History</span>
                            </a>
                        </li>
                    </ul>
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
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger">3</span>
                        </button>
                        <button class="header-action-btn">
                            <i class="fas fa-envelope"></i>
                            <span class="badge bg-danger">5</span>
                        </button>
                        <div class="dropdown">
                            <button class="user-menu" type="button" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?></span>
                                <i class="fas fa-chevron-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item" href="user_profile.php">
                                        <i class="fas fa-user-circle"></i>
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="change_password.php">
                                        <i class="fas fa-key"></i>
                                        <span>Change Password</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
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