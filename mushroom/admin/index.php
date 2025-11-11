<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Dashboard';
$admin = getAdmin();

// Get statistics
$pdo = getPDOConnection();

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
$total_products = $stmt->fetch()['count'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_blocked = 0");
$total_users = $stmt->fetch()['count'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$total_orders = $stmt->fetch()['count'];

// Total revenue
$stmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE payment_status = 'completed'");
$total_revenue = $stmt->fetch()['total'] ?? 0;

// Recent orders
$stmt = $pdo->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// Top products
$stmt = $pdo->query("
    SELECT p.*, COUNT(oi.id) as order_count 
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.is_active = 1
    GROUP BY p.id
    ORDER BY order_count DESC
    LIMIT 5
");
$top_products = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2e7d32;
    }

    .stat-label {
        color: #757575;
        font-size: 0.875rem;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .table-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-completed {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-pending {
        background: #fff3e0;
        color: #f57c00;
    }

    .status-failed {
        background: #ffebee;
        color: #c62828;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-secondary mb-0">Welcome back, <?php echo htmlspecialchars($admin['name']); ?>!</p>
        </div>
        <div>
            <span class="text-secondary">
                <i class="fas fa-calendar me-2"></i><?php echo date('M d, Y'); ?>
            </span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #2196f3, #64b5f6);">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_products); ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4caf50, #81c784);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_users); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ff9800, #ffb74d);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #9c27b0, #ba68c8);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-number"><?php echo formatPrice($total_revenue); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Recent Orders</h5>
                    <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">
                                        No orders yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo formatPrice($order['total']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>/admin/order-detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-lg-4 mb-4">
            <div class="table-card">
                <h5 class="fw-bold mb-3">Top Products</h5>

                <?php if (empty($top_products)): ?>
                    <p class="text-center text-secondary py-4">No products yet</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($top_products as $product): ?>
                            <div class="list-group-item d-flex align-items-center px-0">
                                <img src="<?php echo $product['image'] ? PRODUCT_IMAGE_URL . '/' . $product['image'] : 'https://via.placeholder.com/50'; ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <div class="ms-3 flex-fill">
                                    <div class="fw-bold small"><?php echo htmlspecialchars(substr($product['title'], 0, 30)); ?></div>
                                    <small class="text-secondary"><?php echo $product['order_count']; ?> orders</small>
                                </div>
                                <div class="fw-bold text-success">
                                    <?php echo formatPrice($product['discount_price'] ?? $product['price']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
