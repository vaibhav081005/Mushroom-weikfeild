<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$cart_count = getCartCount();
$user = getUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #66bb6a;
            --accent-color: #ff6f00;
            --bg-color: #ffffff;
            --surface-color: #f5f5f5;
            --text-color: #212121;
            --text-secondary: #757575;
            --border-color: #e0e0e0;
            --shadow: 0 2px 8px rgba(0,0,0,0.1);
            --shadow-hover: 0 4px 16px rgba(0,0,0,0.15);
        }

        [data-theme="dark"] {
            --primary-color: #66bb6a;
            --secondary-color: #2e7d32;
            --accent-color: #ffb74d;
            --bg-color: #121212;
            --surface-color: #1e1e1e;
            --text-color: #ffffff;
            --text-secondary: #b0b0b0;
            --border-color: #333333;
            --shadow: 0 2px 8px rgba(0,0,0,0.3);
            --shadow-hover: 0 4px 16px rgba(0,0,0,0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
            padding-bottom: 70px; /* Space for mobile bottom nav */
        }

        /* Desktop Navbar */
        .desktop-navbar {
            background-color: var(--surface-color);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active {
            color: var(--primary-color) !important;
            border-bottom: 2px solid var(--primary-color);
        }

        /* Mobile Bottom Navigation */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--surface-color);
            box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 8px 0;
        }

        .mobile-bottom-nav .nav-item {
            flex: 1;
            text-align: center;
        }

        .mobile-bottom-nav .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 0 !important;
            font-size: 0.75rem;
            border: none !important;
        }

        .mobile-bottom-nav .nav-link i {
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .mobile-bottom-nav .nav-link.active {
            color: var(--primary-color) !important;
        }

        .badge-cart {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }

        /* Theme Toggle */
        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .theme-toggle:hover {
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .desktop-navbar .navbar-nav {
                display: none;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            body {
                padding-bottom: 70px;
            }
        }

        @media (min-width: 769px) {
            .mobile-bottom-nav {
                display: none !important;
            }

            body {
                padding-bottom: 0;
            }
        }

        /* Cards */
        .card {
            background-color: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-4px);
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
        }

        .btn-accent {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: white !important;
        }

        /* Form Controls */
        .form-control, .form-select {
            background-color: var(--surface-color);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--surface-color);
            border-color: var(--primary-color);
            color: var(--text-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
        }

        /* Mobile App Bar */
        .mobile-app-bar {
            display: none;
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: var(--shadow);
        }

        @media (max-width: 768px) {
            .mobile-app-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .desktop-navbar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile App Bar -->
    <div class="mobile-app-bar">
        <div class="d-flex align-items-center">
            <i class="fas fa-leaf me-2"></i>
            <span class="fw-bold"><?php echo SITE_NAME; ?></span>
        </div>
        <div>
            <button class="theme-toggle" onclick="toggleTheme()" style="color: white;">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
        </div>
    </div>

    <!-- Desktop Navbar -->
    <nav class="navbar navbar-expand-lg desktop-navbar">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-leaf me-2"></i><?php echo SITE_NAME; ?>
            </a>
            
            <div class="d-flex align-items-center">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'products' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/products.php">
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'cart' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="badge bg-danger ms-1"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if ($user): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-mdb-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/signup.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <button class="theme-toggle ms-3" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon-desktop"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <div class="nav-item">
            <a class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link <?php echo $current_page == 'products' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/products.php">
                <i class="fas fa-th-large"></i>
                <span>Products</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link <?php echo $current_page == 'cart' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/cart.php">
                <div style="position: relative; display: inline-block;">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="badge-cart"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </div>
                <span>Cart</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link <?php echo in_array($current_page, ['profile', 'orders']) ? 'active' : ''; ?>" 
               href="<?php echo $user ? SITE_URL . '/profile.php' : SITE_URL . '/auth/login.php'; ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <script>
        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const icons = document.querySelectorAll('#theme-icon, #theme-icon-desktop');
            icons.forEach(icon => {
                icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            });
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        });
    </script>
