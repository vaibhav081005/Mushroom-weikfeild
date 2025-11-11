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
$total_items = 0;
foreach ($cart_items as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
    $total_items += $item['quantity'];
}

$discount = 0;
$coupon_id = null;
$coupon_code = '';

// Handle coupon
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

// Get current step
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        if ($coupon && $subtotal >= $coupon['min_purchase']) {
            $_SESSION['applied_coupon'] = $coupon;
            redirect(SITE_URL . '/checkout-new.php?step=1');
        } else {
            $error = "Invalid or expired coupon code";
        }
    }
    
    if (isset($_POST['remove_coupon'])) {
        unset($_SESSION['applied_coupon']);
        redirect(SITE_URL . '/checkout-new.php?step=1');
    }
    
    if (isset($_POST['proceed_to_payment'])) {
        $_SESSION['checkout_data'] = [
            'billing_name' => sanitize($_POST['billing_name']),
            'billing_email' => sanitize($_POST['billing_email']),
            'billing_phone' => sanitize($_POST['billing_phone']),
            'billing_address' => sanitize($_POST['billing_address']),
            'billing_city' => sanitize($_POST['billing_city']),
            'billing_state' => sanitize($_POST['billing_state']),
            'billing_pincode' => sanitize($_POST['billing_pincode'])
        ];
        redirect(SITE_URL . '/checkout-new.php?step=2');
    }
    
    if (isset($_POST['place_order'])) {
        $payment_method = sanitize($_POST['payment_method']);
        $checkout_data = $_SESSION['checkout_data'];
        
        // Create order
        $order_number = generateOrderNumber();
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, subtotal, discount, tax, total, coupon_id, 
                               payment_method, billing_name, billing_email, 
                               billing_phone, billing_address, payment_status, order_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'processing')
        ");
        
        $billing_full = $checkout_data['billing_address'] . ', ' . $checkout_data['billing_city'] . ', ' . 
                       $checkout_data['billing_state'] . ' - ' . $checkout_data['billing_pincode'];
        
        $stmt->execute([
            $user_id, $order_number, $subtotal, $discount, $tax, $total, $coupon_id,
            $payment_method, $checkout_data['billing_name'], $checkout_data['billing_email'],
            $checkout_data['billing_phone'], $billing_full
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        foreach ($cart_items as $item) {
            $price = $item['discount_price'] ?? $item['price'];
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $price]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Update coupon usage
        if ($coupon_id) {
            $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
            $stmt->execute([$coupon_id]);
        }
        
        // Clear session data
        unset($_SESSION['applied_coupon']);
        unset($_SESSION['checkout_data']);
        
        redirect(SITE_URL . '/order-success.php?order=' . $order_number);
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .checkout-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .checkout-steps {
        display: flex;
        justify-content: center;
        margin-bottom: 3rem;
        position: relative;
    }
    
    .checkout-steps::before {
        content: '';
        position: absolute;
        top: 25px;
        left: 25%;
        right: 25%;
        height: 2px;
        background: #ddd;
        z-index: 0;
    }
    
    .step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    
    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #ddd;
        color: #666;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-weight: 700;
        font-size: 1.2rem;
        transition: all 0.3s;
    }
    
    .step.active .step-circle {
        background: #2e7d32;
        color: white;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
    }
    
    .step.completed .step-circle {
        background: #4caf50;
        color: white;
    }
    
    .step-label {
        font-weight: 600;
        color: #666;
    }
    
    .step.active .step-label {
        color: #2e7d32;
    }
    
    .checkout-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 2rem;
    }
    
    .checkout-main {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    }
    
    .checkout-sidebar {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        height: fit-content;
        position: sticky;
        top: 100px;
    }
    
    .order-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #eee;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .order-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .order-item-details {
        flex: 1;
    }
    
    .order-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }
    
    .order-summary-row.total {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2e7d32;
        border-top: 2px solid #2e7d32;
        border-bottom: none;
        margin-top: 1rem;
        padding-top: 1rem;
    }
    
    .payment-method {
        border: 2px solid #ddd;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .payment-method:hover {
        border-color: #2e7d32;
        background: #f1f8f4;
    }
    
    .payment-method input[type="radio"] {
        width: 20px;
        height: 20px;
    }
    
    .payment-method.selected {
        border-color: #2e7d32;
        background: #e8f5e9;
    }
    
    .payment-icon {
        font-size: 2rem;
        color: #2e7d32;
    }
    
    @media (max-width: 768px) {
        .checkout-content {
            grid-template-columns: 1fr;
        }
        
        .checkout-sidebar {
            position: static;
        }
    }
</style>

<div class="checkout-container">
    <!-- Progress Steps -->
    <div class="checkout-steps">
        <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
            <div class="step-circle">
                <?php echo $step > 1 ? '✓' : '1'; ?>
            </div>
            <div class="step-label">Cart Review</div>
        </div>
        <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
            <div class="step-circle">
                <?php echo $step > 2 ? '✓' : '2'; ?>
            </div>
            <div class="step-label">Payment</div>
        </div>
        <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
            <div class="step-circle">3</div>
            <div class="step-label">Confirmation</div>
        </div>
    </div>

    <div class="checkout-content">
        <!-- Main Content -->
        <div class="checkout-main">
            <?php if ($step == 1): ?>
                <!-- Step 1: Cart Review & Address -->
                <h2 class="mb-4">Review Your Order</h2>
                
                <div class="mb-4">
                    <h5 class="mb-3">Order Items (<?php echo $total_items; ?> items)</h5>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo $item['image'] ?: 'https://via.placeholder.com/60'; ?>" 
                                 class="order-item-image" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="order-item-details">
                                <div class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="text-secondary small">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="fw-bold">
                                <?php echo formatPrice(($item['discount_price'] ?? $item['price']) * $item['quantity']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Billing Information</h5>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="billing_name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="billing_email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" name="billing_phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" name="billing_address" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-control" name="billing_city" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State *</label>
                            <input type="text" class="form-control" name="billing_state" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode *</label>
                            <input type="text" class="form-control" name="billing_pincode" required>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="submit" name="proceed_to_payment" class="btn btn-primary btn-lg px-5">
                            Proceed to Payment <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 2): ?>
                <!-- Step 2: Payment Method -->
                <h2 class="mb-4">Select Payment Method</h2>
                
                <form method="POST" id="paymentForm">
                    <div class="payment-methods">
                        <label class="payment-method" onclick="selectPayment(this, 'upi')">
                            <input type="radio" name="payment_method" value="upi" required>
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">UPI Payment</div>
                                <div class="text-secondary small">Pay using Google Pay, PhonePe, Paytm, etc.</div>
                            </div>
                        </label>

                        <label class="payment-method" onclick="selectPayment(this, 'card')">
                            <input type="radio" name="payment_method" value="card" required>
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Credit/Debit Card</div>
                                <div class="text-secondary small">Visa, Mastercard, Rupay accepted</div>
                            </div>
                        </label>

                        <label class="payment-method" onclick="selectPayment(this, 'netbanking')">
                            <input type="radio" name="payment_method" value="netbanking" required>
                            <div class="payment-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Net Banking</div>
                                <div class="text-secondary small">All major banks supported</div>
                            </div>
                        </label>

                        <label class="payment-method" onclick="selectPayment(this, 'cod')">
                            <input type="radio" name="payment_method" value="cod" required>
                            <div class="payment-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Cash on Delivery</div>
                                <div class="text-secondary small">Pay when you receive</div>
                            </div>
                        </label>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <a href="?step=1" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" name="place_order" class="btn btn-success btn-lg px-5 flex-grow-1">
                            <i class="fas fa-check me-2"></i>Place Order
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="checkout-sidebar">
            <h5 class="mb-3">Order Summary</h5>
            
            <div class="order-summary-row">
                <span>Subtotal (<?php echo $total_items; ?> items)</span>
                <span><?php echo formatPrice($subtotal); ?></span>
            </div>
            
            <?php if ($discount > 0): ?>
            <div class="order-summary-row" style="color: #4caf50;">
                <span>Discount (<?php echo $coupon_code; ?>)</span>
                <span>-<?php echo formatPrice($discount); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="order-summary-row">
                <span>Tax (<?php echo $tax_percentage; ?>%)</span>
                <span><?php echo formatPrice($tax); ?></span>
            </div>
            
            <div class="order-summary-row total">
                <span>Total</span>
                <span><?php echo formatPrice($total); ?></span>
            </div>

            <?php if (!isset($_SESSION['applied_coupon'])): ?>
            <div class="mt-4">
                <h6 class="mb-2">Have a coupon?</h6>
                <form method="POST" class="d-flex gap-2">
                    <input type="text" class="form-control" name="coupon_code" placeholder="Enter code">
                    <button type="submit" name="apply_coupon" class="btn btn-outline-primary">Apply</button>
                </form>
                <?php if (isset($error)): ?>
                    <div class="text-danger small mt-2"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="mt-4">
                <form method="POST">
                    <button type="submit" name="remove_coupon" class="btn btn-sm btn-outline-danger w-100">
                        Remove Coupon
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <div class="mt-4 p-3" style="background: #f1f8f4; border-radius: 8px;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-shield-alt text-success"></i>
                    <span class="small fw-bold">Secure Checkout</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-truck text-success"></i>
                    <span class="small fw-bold">Fast Delivery</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectPayment(element, method) {
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
