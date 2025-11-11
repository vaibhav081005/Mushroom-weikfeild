<?php
require_once __DIR__ . '/config/config.php';

requireLogin();

$page_title = 'My Orders';
$user_id = $_SESSION['user_id'];

// Get user orders
$pdo = getPDOConnection();
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .orders-container {
        min-height: 60vh;
        padding: 2rem 0;
    }

    .order-card {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: var(--shadow-hover);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1rem;
    }

    .order-number {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    .order-status {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .status-completed {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-processing {
        background: #fff3e0;
        color: #f57c00;
    }

    .status-pending {
        background: #e3f2fd;
        color: #1976d2;
    }

    .status-cancelled {
        background: #ffebee;
        color: #c62828;
    }

    .order-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 1rem;
    }

    .download-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .empty-orders {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-orders i {
        font-size: 5rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .order-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-item-image {
            width: 100%;
            height: 150px;
            margin-bottom: 1rem;
        }
    }
</style>

<div class="container orders-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">
            <i class="fas fa-shopping-bag me-2"></i>My Orders
        </h1>
        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Shop More
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <i class="fas fa-shopping-bag"></i>
            <h3 class="fw-bold mb-3">No Orders Yet</h3>
            <p class="text-secondary mb-4">Start shopping and your orders will appear here!</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Browse Products
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php
            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.*, p.image, p.file_path 
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order['id']]);
            $order_items = $stmt->fetchAll();
            
            // Status badge class
            $status_class = 'status-' . $order['order_status'];
            $payment_status_class = 'status-' . $order['payment_status'];
            ?>
            
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number">
                            Order #<?php echo htmlspecialchars($order['order_number']); ?>
                        </div>
                        <small class="text-secondary">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <div class="mb-2">
                            <span class="order-status <?php echo $payment_status_class; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        <div class="fw-bold" style="color: var(--primary-color); font-size: 1.25rem;">
                            <?php echo formatPrice($order['total']); ?>
                        </div>
                    </div>
                </div>

                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo $item['image'] ? PRODUCT_IMAGE_URL . '/' . $item['image'] : 'https://via.placeholder.com/80'; ?>" 
                                 class="order-item-image" alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                            
                            <div class="flex-fill">
                                <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($item['product_title']); ?></h6>
                                <div class="text-secondary small mb-2">
                                    Quantity: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?>
                                </div>
                                
                                <?php if ($order['payment_status'] === 'completed'): ?>
                                    <?php if ($item['download_expires_at'] && strtotime($item['download_expires_at']) > time()): ?>
                                        <small class="text-success">
                                            <i class="fas fa-clock me-1"></i>
                                            Download expires: <?php echo date('M d, Y', strtotime($item['download_expires_at'])); ?>
                                        </small>
                                    <?php elseif ($item['download_expires_at']): ?>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            Download expired
                                        </small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <?php if ($order['payment_status'] === 'completed'): ?>
                                    <?php if ($item['download_expires_at'] && strtotime($item['download_expires_at']) > time()): ?>
                                        <a href="<?php echo SITE_URL; ?>/download.php?item=<?php echo $item['id']; ?>" 
                                           class="btn btn-primary download-btn">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                        <div class="text-secondary small mt-2 text-center">
                                            Downloads: <?php echo $item['download_count']; ?>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-secondary download-btn" disabled>
                                            <i class="fas fa-ban me-1"></i>Expired
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary download-btn" disabled>
                                        <i class="fas fa-lock me-1"></i>Pending
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" 
                     style="border-top: 1px solid var(--border-color);">
                    <div>
                        <?php if ($order['transaction_id']): ?>
                            <small class="text-secondary">
                                Transaction ID: <?php echo htmlspecialchars($order['transaction_id']); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="<?php echo SITE_URL; ?>/invoice.php?order=<?php echo $order['order_number']; ?>" 
                           class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-file-invoice me-1"></i>Invoice
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
