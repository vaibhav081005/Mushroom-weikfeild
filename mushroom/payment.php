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
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$order_items = $stmt->fetchAll();

$page_title = 'Payment';

// Get payment gateway settings
$payment_gateway = getSetting('payment_gateway', 'razorpay');
$razorpay_key = getSetting('razorpay_key_id', '');

include __DIR__ . '/includes/header.php';
?>

<style>
    .payment-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .payment-card {
        max-width: 600px;
        width: 100%;
        background: var(--surface-color);
        border-radius: 16px;
        padding: 3rem;
        box-shadow: var(--shadow-hover);
        text-align: center;
    }

    .payment-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 2.5rem;
        color: white;
    }

    .order-details {
        background: var(--bg-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 2rem 0;
        text-align: left;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .amount-display {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 1rem 0;
    }

    @media (max-width: 768px) {
        .payment-card {
            padding: 2rem 1.5rem;
        }

        .amount-display {
            font-size: 2rem;
        }
    }
</style>

<div class="container payment-container">
    <div class="payment-card">
        <div class="payment-icon">
            <i class="fas fa-credit-card"></i>
        </div>

        <h2 class="fw-bold mb-2">Complete Your Payment</h2>
        <p class="text-secondary mb-4">Order #<?php echo htmlspecialchars($order_number); ?></p>

        <div class="amount-display">
            <?php echo formatPrice($order['total']); ?>
        </div>

        <div class="order-details">
            <div class="detail-row">
                <span class="text-secondary">Subtotal</span>
                <span class="fw-bold"><?php echo formatPrice($order['subtotal']); ?></span>
            </div>

            <?php if ($order['discount'] > 0): ?>
                <div class="detail-row">
                    <span class="text-success">
                        <i class="fas fa-tag me-1"></i>Discount
                    </span>
                    <span class="fw-bold text-success">-<?php echo formatPrice($order['discount']); ?></span>
                </div>
            <?php endif; ?>

            <div class="detail-row">
                <span class="text-secondary">Tax</span>
                <span class="fw-bold"><?php echo formatPrice($order['tax']); ?></span>
            </div>

            <div class="detail-row">
                <span class="fw-bold">Total Amount</span>
                <span class="fw-bold" style="color: var(--primary-color); font-size: 1.25rem;">
                    <?php echo formatPrice($order['total']); ?>
                </span>
            </div>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold mb-3">Order Items</h6>
            <?php foreach ($order_items as $item): ?>
                <div class="text-start mb-2">
                    <small class="text-secondary">
                        <i class="fas fa-check-circle me-1" style="color: var(--primary-color);"></i>
                        <?php echo htmlspecialchars($item['product_title']); ?>
                        (x<?php echo $item['quantity']; ?>)
                    </small>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($payment_gateway === 'razorpay' && $razorpay_key): ?>
            <button id="rzp-button" class="btn btn-primary btn-lg w-100 mb-3">
                <i class="fas fa-lock me-2"></i>Pay with Razorpay
            </button>
        <?php else: ?>
            <button onclick="simulatePayment()" class="btn btn-primary btn-lg w-100 mb-3">
                <i class="fas fa-lock me-2"></i>Simulate Payment (Demo)
            </button>
        <?php endif; ?>

        <button onclick="window.location.href='<?php echo SITE_URL; ?>/orders.php'" 
                class="btn btn-outline-secondary w-100">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </button>

        <div class="mt-4">
            <small class="text-secondary">
                <i class="fas fa-shield-alt me-1"></i>
                Secure payment powered by <?php echo ucfirst($payment_gateway); ?>
            </small>
        </div>
    </div>
</div>

<?php if ($payment_gateway === 'razorpay' && $razorpay_key): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo $razorpay_key; ?>",
    "amount": <?php echo $order['total'] * 100; ?>,
    "currency": "<?php echo getSetting('currency', 'INR'); ?>",
    "name": "<?php echo SITE_NAME; ?>",
    "description": "Order #<?php echo $order_number; ?>",
    "order_id": "<?php echo $order_number; ?>",
    "handler": function (response) {
        // Payment successful
        fetch('<?php echo SITE_URL; ?>/api/payment-success.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: <?php echo $order['id']; ?>,
                payment_id: response.razorpay_payment_id,
                order_number: '<?php echo $order_number; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo SITE_URL; ?>/order-success.php?order=<?php echo $order_number; ?>';
            }
        });
    },
    "prefill": {
        "name": "<?php echo htmlspecialchars($order['billing_name']); ?>",
        "email": "<?php echo htmlspecialchars($order['billing_email']); ?>",
        "contact": "<?php echo htmlspecialchars($order['billing_phone']); ?>"
    },
    "theme": {
        "color": "#2e7d32"
    }
};

var rzp = new Razorpay(options);

document.getElementById('rzp-button').onclick = function(e) {
    rzp.open();
    e.preventDefault();
}
</script>
<?php else: ?>
<script>
function simulatePayment() {
    if (confirm('This is a demo payment. Click OK to simulate successful payment.')) {
        fetch('<?php echo SITE_URL; ?>/api/payment-success.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: <?php echo $order['id']; ?>,
                payment_id: 'DEMO_' + Date.now(),
                order_number: '<?php echo $order_number; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo SITE_URL; ?>/order-success.php?order=<?php echo $order_number; ?>';
            }
        });
    }
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
