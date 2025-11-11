<?php
require_once __DIR__ . '/config/config.php';

requireLogin();

$order_number = sanitize($_GET['order'] ?? '');

if (empty($order_number)) {
    redirect(SITE_URL);
}

$pdo = getPDOConnection();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$order_number, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect(SITE_URL);
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.title, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id']]);
$order_items = $stmt->fetchAll();

$page_title = 'Order Confirmed';

include __DIR__ . '/includes/header.php';
?>

<style>
    .success-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .success-header {
        background: linear-gradient(135deg, #4caf50, #66bb6a);
        color: white;
        border-radius: 16px;
        padding: 3rem 2rem;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(76, 175, 80, 0.3);
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 3rem;
        color: white;
        animation: scaleIn 0.5s ease;
    }

    @keyframes scaleIn {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }

    .order-info {
        background: var(--bg-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 2rem 0;
        text-align: left;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .success-card {
            padding: 2rem 1.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="success-container">
    <!-- Success Header -->
    <div class="success-header">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="fw-bold mb-2">Order Placed Successfully!</h1>
        <p class="mb-0" style="font-size: 1.1rem; opacity: 0.95;">
            Thank you for shopping with Weikfield Mushrooms
        </p>
    </div>

    <!-- Order Bill -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Order Invoice</h5>
                    <p class="text-secondary mb-0">Order #<?php echo htmlspecialchars($order_number); ?></p>
                </div>
                <div class="text-end">
                    <p class="mb-0 small text-secondary">Order Date</p>
                    <p class="fw-bold mb-0"><?php echo date('d M, Y h:i A', strtotime($order['created_at'])); ?></p>
                </div>
            </div>

            <hr>

            <!-- Billing Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">Billing Information</h6>
                    <p class="mb-1"><strong><?php echo htmlspecialchars($order['billing_name']); ?></strong></p>
                    <p class="mb-1 small"><?php echo htmlspecialchars($order['billing_email']); ?></p>
                    <p class="mb-1 small"><?php echo htmlspecialchars($order['billing_phone']); ?></p>
                    <p class="mb-0 small text-secondary"><?php echo htmlspecialchars($order['billing_address']); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold mb-2">Payment Method</h6>
                    <p class="mb-1">
                        <?php 
                        $payment_icons = [
                            'upi' => 'fa-mobile-alt',
                            'card' => 'fa-credit-card',
                            'netbanking' => 'fa-university',
                            'cod' => 'fa-money-bill-wave'
                        ];
                        $icon = $payment_icons[$order['payment_method']] ?? 'fa-wallet';
                        ?>
                        <i class="fas <?php echo $icon; ?> me-2"></i>
                        <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?>
                    </p>
                    <span class="badge bg-success">Paid</span>
                </div>
            </div>

            <hr>

            <!-- Order Items -->
            <h6 class="fw-bold mb-3">Order Items</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo $item['image'] ?: 'https://via.placeholder.com/50'; ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    <span><?php echo htmlspecialchars($item['title']); ?></span>
                                </div>
                            </td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                            <td class="text-end fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <!-- Order Summary -->
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($order['subtotal']); ?></span>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount:</span>
                        <span>-<?php echo formatPrice($order['discount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (18%):</span>
                        <span><?php echo formatPrice($order['tax']); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <strong style="font-size: 1.25rem;">Total Paid:</strong>
                        <strong style="font-size: 1.25rem; color: #2e7d32;">
                            <?php echo formatPrice($order['total']); ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thank You Message -->
    <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #e8f5e9, #f1f8f4);">
        <div class="card-body p-4 text-center">
            <h4 class="fw-bold mb-3" style="color: #2e7d32;">
                <i class="fas fa-heart me-2"></i>Thank You for Your Order!
            </h4>
            <p class="mb-3">We appreciate your business and hope you enjoy your Weikfield mushroom products!</p>
            <p class="mb-0 text-secondary">
                <i class="fas fa-envelope me-2"></i>
                Order confirmation has been sent to <strong><?php echo htmlspecialchars($order['billing_email']); ?></strong>
            </p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/orders.php" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-list me-2"></i>My Orders
            </a>
        </div>
        <div class="col-md-4">
            <button onclick="window.print()" class="btn btn-outline-primary btn-lg w-100">
                <i class="fas fa-print me-2"></i>Print Invoice
            </button>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-success btn-lg w-100">
                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
            </a>
        </div>
    </div>

    <!-- Visit Again Message -->
    <div class="text-center mt-4 p-4">
        <h5 class="fw-bold mb-2" style="color: #2e7d32;">Visit Us Again!</h5>
        <p class="text-secondary mb-3">We'd love to serve you again with fresh, quality mushroom products.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">
                <i class="fas fa-home me-1"></i>Home
            </a>
            <a href="<?php echo SITE_URL; ?>/products.php" class="text-decoration-none">
                <i class="fas fa-shopping-cart me-1"></i>Shop
            </a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="text-decoration-none">
                <i class="fas fa-phone me-1"></i>Contact
            </a>
        </div>
    </div>
</div>

<style>
@media print {
    .success-header, .action-buttons, nav, footer, .btn { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
