<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Orders';

// Get filters
$status = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

// Get orders
$pdo = getPDOConnection();
$query = "SELECT o.*, u.name as user_name, u.email as user_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND o.payment_status = ?";
    $params[] = $status;
}

if ($search) {
    $query .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN payment_status = 'completed' THEN total ELSE 0 END) as total_revenue
    FROM orders");
$stats = $stmt->fetch();

include __DIR__ . '/includes/header.php';
?>

<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2e7d32;
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

    .status-refunded {
        background: #e3f2fd;
        color: #1976d2;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Orders</h2>
            <p class="text-secondary mb-0">Manage customer orders</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-secondary mb-2">Total Orders</div>
                <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-secondary mb-2">Completed</div>
                <div class="stat-number text-success"><?php echo number_format($stats['completed_orders']); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-secondary mb-2">Pending</div>
                <div class="stat-number text-warning"><?php echo number_format($stats['pending_orders']); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="text-secondary mb-2">Total Revenue</div>
                <div class="stat-number"><?php echo formatPrice($stats['total_revenue']); ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search by order number, customer name or email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo $status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-secondary">
                                    <i class="fas fa-shopping-cart fa-3x mb-3 d-block"></i>
                                    No orders found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                // Get order items count
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                $stmt->execute([$order['id']]);
                                $items_count = $stmt->fetch()['count'];
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($order['user_name'] ?? 'N/A'); ?></div>
                                        <small class="text-secondary"><?php echo htmlspecialchars($order['user_email'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                        <small class="text-secondary"><?php echo date('h:i A', strtotime($order['created_at'])); ?></small>
                                    </td>
                                    <td><?php echo $items_count; ?> item(s)</td>
                                    <td class="fw-bold"><?php echo formatPrice($order['total']); ?></td>
                                    <td>
                                        <div><?php echo ucfirst($order['payment_method']); ?></div>
                                        <?php if ($order['transaction_id']): ?>
                                            <small class="text-secondary"><?php echo substr($order['transaction_id'], 0, 15); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="<?php echo SITE_URL; ?>/admin/order-detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>/invoice.php?order=<?php echo $order['order_number']; ?>" 
                                               class="btn btn-sm btn-outline-secondary" target="_blank" title="Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
