<?php
require_once __DIR__ . '/config/config.php';

requireLogin();

$page_title = 'Checkout';
$user_id = $_SESSION['user_id'];
$user = getUser();

// Get cart items
$pdo = getPDOConnection();
$stmt = $pdo->prepare("
    SELECT c.*, p.title, p.price, p.discount_price, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? AND p.is_active = 1
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    redirect(SITE_URL . '/cart.php');
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

$discount = 0;
$coupon_id = null;
$coupon_code = '';

// Handle coupon
if (isset($_POST['apply_coupon'])) {
    $coupon_code = sanitize($_POST['coupon_code']);
    
    $stmt = $pdo->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND is_active = 1 
        AND (expires_at IS NULL OR expires_at > NOW())
        AND (usage_limit IS NULL OR used_count < usage_limit)
    ");
    $stmt->execute([$coupon_code]);
    $coupon = $stmt->fetch();
    
    if ($coupon) {
        if ($subtotal >= $coupon['min_purchase']) {
            if ($coupon['type'] === 'flat') {
                $discount = $coupon['value'];
            } else {
                $discount = ($subtotal * $coupon['value']) / 100;
                if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
                    $discount = $coupon['max_discount'];
                }
            }
            $coupon_id = $coupon['id'];
            $_SESSION['applied_coupon'] = $coupon;
        }
    }
}

if (isset($_SESSION['applied_coupon'])) {
    $coupon = $_SESSION['applied_coupon'];
    $coupon_code = $coupon['code'];
    if ($coupon['type'] === 'flat') {
        $discount = $coupon['value'];
    } else {
        $discount = ($subtotal * $coupon['value']) / 100;
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    }
    $coupon_id = $coupon['id'];
}

$tax_percentage = floatval(getSetting('tax_percentage', 18));
$tax = (($subtotal - $discount) * $tax_percentage) / 100;
$total = $subtotal - $discount + $tax;

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $billing_name = sanitize($_POST['billing_name']);
    $billing_email = sanitize($_POST['billing_email']);
    $billing_phone = sanitize($_POST['billing_phone']);
    $billing_address = sanitize($_POST['billing_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    // Create order
    $order_number = generateOrderNumber();
    
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_number, subtotal, discount, tax, total, coupon_id, 
                           payment_method, payment_gateway, billing_name, billing_email, 
                           billing_phone, billing_address, payment_status, order_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'processing')
    ");
    
    $stmt->execute([
        $user_id, $order_number, $subtotal, $discount, $tax, $total, $coupon_id,
        $payment_method, getSetting('payment_gateway', 'razorpay'),
        $billing_name, $billing_email, $billing_phone, $billing_address
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Add order items
    foreach ($cart_items as $item) {
        $price = $item['discount_price'] ?? $item['price'];
        $item_subtotal = $price * $item['quantity'];
        
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_title, price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_id, $item['product_id'], $item['title'], 
            $price, $item['quantity'], $item_subtotal
        ]);
    }
    
    // Update coupon usage
    if ($coupon_id) {
        $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
        $stmt->execute([$coupon_id]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    unset($_SESSION['applied_coupon']);
    
    // Redirect to payment or order confirmation
    $_SESSION['order_id'] = $order_id;
    redirect(SITE_URL . '/payment.php?order=' . $order_number);
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .checkout-container {
        padding: 2rem 0;
    }

    .checkout-section {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow);
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-color);
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
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 1rem;
    }

    .payment-method {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .payment-method:hover {
        border-color: var(--primary-color);
    }

    .payment-method input[type="radio"] {
        margin-right: 0.75rem;
    }

    .payment-method.selected {
        border-color: var(--primary-color);
        background: rgba(46, 125, 50, 0.05);
    }

    .order-summary-box {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--shadow);
        position: sticky;
        top: 100px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .summary-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .summary-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .coupon-input-group {
        display: flex;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .checkout-section {
            padding: 1.5rem;
        }

        .order-summary-box {
            position: static;
            margin-top: 2rem;
        }
    }
</style>

<div class="container checkout-container">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-lock me-2"></i>Secure Checkout
    </h1>

    <form method="POST" action="" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-lg-7">
                <!-- Billing Information -->
                <div class="checkout-section">
                    <h4 class="section-title">
                        <i class="fas fa-user me-2"></i>Billing Information
                    </h4>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="billing_name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="billing_email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" name="billing_phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" name="billing_address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-section">
                    <h4 class="section-title">
                        <i class="fas fa-credit-card me-2"></i>Payment Method
                    </h4>

                    <div class="payment-method" onclick="selectPayment(this, 'razorpay')">
                        <label class="d-flex align-items-center mb-0">
                            <input type="radio" name="payment_method" value="razorpay" checked>
                            <div>
                                <div class="fw-bold">Razorpay</div>
                                <small class="text-secondary">Pay with Credit/Debit Card, UPI, Net Banking</small>
                            </div>
                        </label>
                    </div>

                    <div class="payment-method" onclick="selectPayment(this, 'stripe')">
                        <label class="d-flex align-items-center mb-0">
                            <input type="radio" name="payment_method" value="stripe">
                            <div>
                                <div class="fw-bold">Stripe</div>
                                <small class="text-secondary">International payments with Credit/Debit Cards</small>
                            </div>
                        </label>
                    </div>

                    <div class="payment-method" onclick="selectPayment(this, 'paypal')">
                        <label class="d-flex align-items-center mb-0">
                            <input type="radio" name="payment_method" value="paypal">
                            <div>
                                <div class="fw-bold">PayPal</div>
                                <small class="text-secondary">Pay securely with your PayPal account</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Order Summary -->
                <div class="order-summary-box">
                    <h4 class="fw-bold mb-4">Order Summary</h4>

                    <!-- Order Items -->
                    <div class="mb-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo $item['image'] ? PRODUCT_IMAGE_URL . '/' . $item['image'] : 'https://via.placeholder.com/60'; ?>" 
                                     class="order-item-image" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="flex-fill">
                                    <div class="fw-bold small"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <small class="text-secondary">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <div class="fw-bold">
                                    <?php
                                    $price = $item['discount_price'] ?? $item['price'];
                                    echo formatPrice($price * $item['quantity']);
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Coupon Code -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Have a coupon code?</label>
                        <div class="coupon-input-group">
                            <input type="text" class="form-control" name="coupon_code" 
                                   placeholder="Enter code" value="<?php echo htmlspecialchars($coupon_code); ?>">
                            <button type="submit" name="apply_coupon" class="btn btn-outline-primary">
                                Apply
                            </button>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="fw-bold"><?php echo formatPrice($subtotal); ?></span>
                    </div>

                    <?php if ($discount > 0): ?>
                        <div class="summary-row">
                            <span class="text-success">
                                <i class="fas fa-tag me-1"></i>Discount (<?php echo htmlspecialchars($coupon_code); ?>)
                            </span>
                            <span class="fw-bold text-success">-<?php echo formatPrice($discount); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>Tax (<?php echo $tax_percentage; ?>%)</span>
                        <span class="fw-bold"><?php echo formatPrice($tax); ?></span>
                    </div>

                    <div class="summary-row">
                        <span class="fw-bold">Total Amount</span>
                        <span class="summary-total"><?php echo formatPrice($total); ?></span>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-primary w-100 btn-lg mt-3">
                        <i class="fas fa-lock me-2"></i>Place Order
                    </button>

                    <div class="text-center mt-3">
                        <small class="text-secondary">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your payment information is secure
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function selectPayment(element, method) {
    document.querySelectorAll('.payment-method').forEach(pm => {
        pm.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}

// Set initial selected payment method
document.addEventListener('DOMContentLoaded', function() {
    const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
    if (checkedRadio) {
        checkedRadio.closest('.payment-method').classList.add('selected');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
