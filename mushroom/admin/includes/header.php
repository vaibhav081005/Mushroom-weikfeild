<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/config.php';
}

requireAdmin();

$admin = getAdmin();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #2e7d32;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar-brand i {
            margin-right: 0.75rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.25rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left: 3px solid white;
        }

        .sidebar-menu i {
            width: 20px;
            margin-right: 0.75rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: all 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2e7d32;
            cursor: pointer;
            margin-right: 1rem;
            display: none;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2e7d32, #66bb6a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .top-navbar {
                padding: 1rem;
            }
        }

        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo SITE_URL; ?>/admin" class="sidebar-brand">
                <i class="fas fa-leaf"></i>
                <span>Admin Panel</span>
            </a>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo SITE_URL; ?>/admin" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="<?php echo $current_page == 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="<?php echo $current_page == 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="<?php echo $current_page == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/coupons.php" class="<?php echo $current_page == 'coupons' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Coupons</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="<?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/reports.php" class="<?php echo $current_page == 'reports' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>View Website</span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/admin/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="navbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="navbar-right">
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-globe me-1"></i>View Site
                </a>

                <div class="admin-profile">
                    <div class="admin-avatar">
                        <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                    </div>
                    <div class="d-none d-md-block">
                        <div class="fw-bold small"><?php echo htmlspecialchars($admin['name']); ?></div>
                        <small class="text-secondary"><?php echo ucfirst($admin['role']); ?></small>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="page-content">
